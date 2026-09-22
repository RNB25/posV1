<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // Ambil semua data penjualan beserta relasi kasir dan detail item
        return Sale::with(['user', 'details'])->latest()->get();
    }

    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Tanggal & Waktu',
            'Nama Kasir',
            'Daftar Item',
            'Total Harga (Rp)',
        ];
    }

    public function map($sale): array
    {
        // Format daftar item menjadi string yang dipisahkan koma
        $itemList = $sale->details->map(function ($detail) {
            return $detail->product_name . ' (' . $detail->qty . 'x)';
        })->implode(', ');

        return [
            '#' . $sale->id,
            $sale->created_at->format('d/m/Y H:i'),
            $sale->user->name ?? 'Kasir',
            $itemList,
            $sale->total_price,
        ];
    }
}