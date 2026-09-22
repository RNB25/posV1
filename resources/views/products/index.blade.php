@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Manajemen Stok & Produk</h3>
        <p class="text-muted mb-0">Tambah produk baru beserta foto merch dan sesuaikan jumlah stok.</p>
    </div>
    <button type="button" class="btn btn-primary px-4 py-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="bi bi-plus-circle me-1"></i> Tambah Produk Baru
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Gambar Merch</th>
                        <th>Nama Produk</th>
                        <th>Harga Satuan</th>
                        <th>Stok</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="ps-4">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 50px; height: 50px;">
                                    <i class="bi bi-image fs-4"></i>
                                </div>
                            @endif
                        </td>
                        <td class="fw-medium">{{ $product->name }}</td>
                        <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                        <td>
                            @if($product->stock <= 5)
                                <span class="badge bg-danger-subtle text-danger px-3 py-2">{{ $product->stock }} sisa</span>
                            @else
                                <span class="badge bg-success-subtle text-success px-3 py-2">{{ $product->stock }} unit</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editModal{{ $product->id }}">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </button>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>

                            <!-- Modal Edit -->
                            <div class="modal fade text-start" id="editModal{{ $product->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Edit Produk</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Produk</label>
                                                    <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Harga Satuan (Rp)</label>
                                                    <input type="number" name="price" class="form-control" value="{{ $product->price }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Jumlah Stok</label>
                                                    <input type="number" name="stock" class="form-control" value="{{ $product->stock }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Gambar Merch (Opsional)</label>
                                                    <input type="file" name="image" class="form-control" accept="image/*">
                                                    @if($product->image)
                                                        <small class="text-muted d-block mt-1">Biarkan kosong jika tidak ingin mengubah gambar.</small>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada data produk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3">
    {{ $products->links() }}
</div>

<!-- Modal Tambah Produk Baru -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Produk Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk / Merch</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Kimono Kimetsu" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga Satuan (Rp)</label>
                        <input type="number" name="price" class="form-control" placeholder="150000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Stok Awal</label>
                        <input type="number" name="stock" class="form-control" placeholder="10" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar Merch</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection