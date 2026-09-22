<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('stock', '>', 0)->get();
        return view('pos.index', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.qty' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,qris',
            'pay_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $totalPrice = 0;

            // 1. Cek Ketersediaan Stok Terlebih Dahulu
            foreach ($request->items as $item) {
                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);

                    if ($product) {
                        if ($item['qty'] > $product->stock) {
                            DB::rollBack();
                            
                            $msg = "Stok untuk produk '{$product->name}' tidak mencukupi! (Sisa stok: {$product->stock})";

                            if ($request->wantsJson()) {
                                return response()->json(['message' => $msg], 422);
                            }

                            return redirect()->back()->with('error', $msg);
                        }
                    }
                }
            }

            // Hitung Total Belanja
            foreach ($request->items as $item) {
                $totalPrice += ($item['price'] * $item['qty']);
            }

            // 2. Olah Metode Pembayaran & Buat Catatan Pembayaran
            $paymentMethod = $request->payment_method;
            $payAmount = $paymentMethod === 'qris' ? $totalPrice : floatval($request->pay_amount ?? $totalPrice);

            // Validasi jika pembayaran Cash kurang
            if ($paymentMethod === 'cash' && $payAmount < $totalPrice) {
                DB::rollBack();
                $msg = "Uang bayar Cash kurang dari total belanja!";

                if ($request->wantsJson()) {
                    return response()->json(['message' => $msg], 422);
                }

                return redirect()->back()->with('error', $msg);
            }

            $changeAmount = $paymentMethod === 'qris' ? 0 : ($payAmount - $totalPrice);

            // Generate Catatan Pembayaran
            if ($paymentMethod === 'qris') {
                $paymentNote = "pembayaran dengan Qris sejumlah Rp " . number_format($totalPrice, 0, ',', '.');
            } else {
                if ($changeAmount > 0) {
                    $paymentNote = "pembayaran cash sejumlah Rp " . number_format($payAmount, 0, ',', '.') . ", kembali Rp " . number_format($changeAmount, 0, ',', '.');
                } else {
                    $paymentNote = "pembayaran cash sejumlah Rp " . number_format($payAmount, 0, ',', '.');
                }
            }

            // 3. Buat Record Transaksi Utama
            $sale = Sale::create([
                'user_id' => auth()->id() ?? 1,
                'total_price' => $totalPrice,
                'payment_method' => $paymentMethod,
                'pay_amount' => $payAmount,
                'change_amount' => $changeAmount,
                'payment_note' => $paymentNote,
            ]);

            // 4. Simpan Detail Transaksi & Kurangi Stok Produk
            foreach ($request->items as $item) {
                $subtotal = $item['price'] * $item['qty'];

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'subtotal' => $subtotal,
                ]);

                // Kurangi stok jika produk terdaftar di database
                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->decrement('stock', $item['qty']);
                    }
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Transaksi berhasil disimpan!'], 200);
            }

            return redirect()->route('pos.index')->with('success', 'Transaksi berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }
}