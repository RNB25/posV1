@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Riwayat Transaksi</h3>
        <p class="text-muted mb-0">Daftar pencatatan seluruh transaksi penjualan toko.</p>
    </div>
    
    @if(auth()->user()->role === 'admin')
        <a href="{{ route('sales.export') }}" class="btn btn-success px-4 py-2 rounded-pill shadow-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Data CSV
        </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">No.</th>
                        <th>Tanggal & Waktu</th>
                        <th>Kasir</th>
                        <th>Daftar Item</th>
                        <th>Total & Pembayaran</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <!-- Penomoran Otomatis Pagination -->
                        <td class="ps-4 fw-bold">{{ $sales->firstItem() + $loop->index }}</td>
                        <td>{{ $sale->created_at->format('d M Y, H:i') }}</td>
                        <td>
                            <span class="badge bg-secondary-subtle text-dark border">
                                <i class="bi bi-person me-1"></i>{{ $sale->user->name ?? 'Kasir' }}
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#detailModal{{ $sale->id }}">
                                <i class="bi bi-list-check me-1"></i> {{ $sale->details->count() }} Item
                            </button>

                            <!-- Modal Detail Item -->
                            <div class="modal fade" id="detailModal{{ $sale->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Detail Transaksi #{{ $sale->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <ul class="list-group list-group-flush mb-3">
                                                @foreach($sale->details as $detail)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                        <div>
                                                            <div class="fw-medium">{{ $detail->product_name }}</div>
                                                            <small class="text-muted">{{ $detail->qty }} x Rp {{ number_format($detail->price, 0, ',', '.') }}</small>
                                                        </div>
                                                        <span class="fw-bold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-3">
                                                <span>Total:</span>
                                                <span class="text-success">Rp {{ number_format($sale->total_price, 0, ',', '.') }}</span>
                                            </div>
                                            
                                            <!-- Catatan Pembayaran di Modal -->
                                            <div class="mt-3 text-muted small bg-light p-2 rounded border">
                                                <i class="bi bi-info-circle me-1"></i> <strong>Rincian:</strong> {{ $sale->payment_note ?? 'Pembayaran selesai' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-success">Rp {{ number_format($sale->total_price, 0, ',', '.') }}</div>
                            <!-- Menampilkan Catatan Pembayaran (Cash/QRIS/Kembalian) -->
                            <small class="text-muted d-block" style="font-size: 0.8rem;">
                                <i class="bi bi-credit-card me-1"></i>{{ $sale->payment_note ?? 'Pembayaran selesai' }}
                            </small>
                        </td>
                        <td class="text-end pe-4">
                            <button type="button" class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editModal{{ $sale->id }}">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </button>

                            <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </form>

                            <!-- Modal Edit -->
                            <div class="modal fade text-start" id="editModal{{ $sale->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('sales.update', $sale->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Edit Transaksi #{{ $sale->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Tanggal Transaksi</label>
                                                    <input type="text" class="form-control" value="{{ $sale->created_at->format('d M Y, H:i') }}" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Total Harga (Rp)</label>
                                                    <input type="number" name="total_price" class="form-control" value="{{ $sale->total_price }}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Koreksi</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada transaksi tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination Rapi Menggunakan Bootstrap 5 -->
<div class="mt-3 d-flex justify-content-end">
    {{ $sales->links('pagination::bootstrap-5') }}
</div>
@endsection