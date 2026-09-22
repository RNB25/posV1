<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Parameter filter chart bawaan (Average Sales)
        $filter = $request->query('filter', 'week');

        // Parameter Filter Keseluruhan (Global From - To)
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // 1. Query Builder Dasar untuk Data Global
        $globalSaleQuery = Sale::query();
        $globalSaleDetailQuery = SaleDetail::query();

        if ($startDate && $endDate) {
            $globalSaleQuery->whereDate('created_at', '>=', $startDate)
                            ->whereDate('created_at', '<=', $endDate);
            
            $globalSaleDetailQuery->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
            });
        }

        // 2. Quick Insights (Hari ini & Bulan ini - Tidak terpengaruh filter agar tetap jadi patokan realtime)
        $todaySales = Sale::whereDate('created_at', today())->sum('total_price');
        $monthlySales = Sale::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_price');
        $totalTransactionsToday = Sale::whereDate('created_at', today())->count();
        $avgTransactionValue = Sale::whereDate('created_at', today())->avg('total_price') ?? 0;

        // 3. Breakdown Metode Pembayaran & Total Transaksi (Terdampak Filter Global)
        $totalRevenue = (clone $globalSaleQuery)->sum('total_price');
        $cashRevenue  = (clone $globalSaleQuery)->where('payment_method', 'cash')->sum('total_price');
        $qrisRevenue  = (clone $globalSaleQuery)->where('payment_method', 'qris')->sum('total_price');
        $totalTransactions = (clone $globalSaleQuery)->count();

        // 4. Stok Menipis & Top Produk (Top Produk Terdampak Filter Global)
        $lowStockProducts = Product::where('stock', '<=', 5)->get();

        $topProducts = $globalSaleDetailQuery->select('product_name', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // 5. Data Chart (Jika filter global aktif, chart mengikuti rentang global)
        if ($startDate && $endDate) {
            $chartData = Sale::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as total'))
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->groupBy('date')->orderBy('date', 'ASC')->get();
            $filterLabel = date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate));
            $filter = 'custom';
        } else {
            // Logika filter dropdown chart standar
            if ($filter === 'today') {
                $chartData = Sale::select(DB::raw('strftime("%H:00", created_at) as date'), DB::raw('SUM(total_price) as total'))
                    ->whereDate('created_at', today())
                    ->groupBy('date')->orderBy('date', 'ASC')->get();
                $filterLabel = 'Hari Ini (Per Jam)';
            } elseif ($filter === 'month') {
                $chartData = Sale::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as total'))
                    ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)
                    ->groupBy('date')->orderBy('date', 'ASC')->get();
                $filterLabel = 'Bulan Ini';
            } elseif ($filter === 'year') {
                $chartData = Sale::select(DB::raw('strftime("%Y-%m", created_at) as date'), DB::raw('SUM(total_price) as total'))
                    ->whereYear('created_at', now()->year)
                    ->groupBy('date')->orderBy('date', 'ASC')->get();
                $filterLabel = 'Tahun Ini';
            } else {
                $chartData = Sale::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as total'))
                    ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                    ->groupBy('date')->orderBy('date', 'ASC')->get();
                $filterLabel = '7 Hari Terakhir';
                $filter = 'week';
            }
        }

        return view('dashboard', compact(
            'todaySales', 
            'monthlySales', 
            'totalTransactionsToday', 
            'avgTransactionValue', 
            'totalTransactions',
            'totalRevenue',
            'cashRevenue',
            'qrisRevenue',
            'lowStockProducts', 
            'topProducts', 
            'chartData',
            'filter',
            'filterLabel',
            'startDate',
            'endDate'
        ));
    }

    public function history()
    {
        $sales = Sale::with(['details', 'user'])->oldest()->paginate(10);
        return view('sales.history', compact('sales'));
    }

    // Update Data Transaksi
    public function updateSale(Request $request, Sale $sale)
    {
        $request->validate([
            'total_price' => 'required|numeric|min:0',
        ]);

        $sale->update([
            'total_price' => $request->total_price,
        ]);

        return redirect()->route('sales.history')->with('success', 'Data transaksi #' . $sale->id . ' berhasil diperbarui!');
    }

    // Hapus Data Transaksi
    public function destroySale(Sale $sale)
    {
        $sale->delete();
        return redirect()->route('sales.history')->with('success', 'Transaksi #' . $sale->id . ' berhasil dihapus!');
    }

    // Export CSV/Excel
    public function exportCsv()
    {
        $fileName = 'laporan-penjualan-' . date('Y-m-d') . '.csv';
        // Menggunakan oldest() agar data terurut dari transaksi awal di CSV
        $sales = Sale::with(['user', 'details'])->oldest()->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($sales) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM untuk Excel

            fputcsv($file, [
                'No.', 
                'ID Database',
                'Tanggal & Waktu', 
                'Kasir', 
                'Nama Produk', 
                'Harga Satuan (Rp)', 
                'Qty', 
                'Subtotal (Rp)', 
                'Total Transaksi (Rp)'
            ]);

            $grandTotal = 0;
            $no = 1;

            foreach ($sales as $sale) {
                $grandTotal += $sale->total_price;
                $isFirstItem = true;

                if ($sale->details->count() > 0) {
                    foreach ($sale->details as $detail) {
                        fputcsv($file, [
                            $isFirstItem ? $no : '',
                            $isFirstItem ? '#' . $sale->id : '',
                            $isFirstItem ? $sale->created_at->format('d/m/Y H:i') : '',
                            $isFirstItem ? ($sale->user->name ?? 'Kasir') : '',
                            $detail->product_name,
                            $detail->price,
                            $detail->qty,
                            $detail->subtotal,
                            $isFirstItem ? $sale->total_price : ''
                        ]);
                        $isFirstItem = false;
                    }
                } else {
                    fputcsv($file, [
                        $no,
                        '#' . $sale->id,
                        $sale->created_at->format('d/m/Y H:i'),
                        $sale->user->name ?? 'Kasir',
                        '-',
                        0,
                        0,
                        0,
                        $sale->total_price
                    ]);
                }

                $no++;
                fputcsv($file, ['', '', '', '', '', '', '', '', '']); // Pemisah transaksi
            }

            fputcsv($file, ['TOTAL KESELURUHAN PENJUALAN', '', '', '', '', '', '', '', $grandTotal]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}