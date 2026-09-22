@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Kasir POS & Katalog Visual</h3>
        <p class="text-muted mb-0">Klik gambar merch atau pilih produk secara manual.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <!-- BAR KIRI: Katalog Gambar Merch -->
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-transparent border-0 pt-3 px-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-grid-fill me-2 text-primary"></i>Katalog Merch</h5>
            </div>
            <div class="card-body px-3 pb-3">
                <div class="row row-cols-2 row-cols-md-3 g-3" style="max-height: 540px; overflow-y: auto;">
                    @forelse($products as $product)
                        <div class="col">
                            <div class="card h-100 border shadow-sm product-card" 
                                 onclick="addCatalogToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }}, {{$product->stock }})"
                                 style="cursor: pointer; transition: transform 0.2s;"
                                 onmouseover="this.style.transform='translateY(-3px)'"
                                 onmouseout="this.style.transform='translateY(0)'">
                                <div class="position-relative">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top" style="height: 120px; object-fit: cover;" alt="{{ $product->name }}">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center text-muted card-img-top" style="height: 120px;">
                                            <i class="bi bi-box-seam fs-1"></i>
                                        </div>
                                    @endif
                                    <span class="position-absolute top-0 end-0 bg-dark text-white bg-opacity-75 badge m-2">
                                        Stok: {{ $product->stock }}
                                    </span>
                                </div>
                                <div class="card-body p-2 text-center">
                                    <h6 class="card-title fw-bold text-truncate mb-1" title="{{ $product->name }}">{{ $product->name }}</h6>
                                    <p class="card-text text-primary fw-bold mb-0">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5 text-muted">Belum ada produk terdaftar.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- BAR KANAN: Form Nota & Pembayaran -->
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-transparent border-0 pt-3 px-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-success"></i>Nota Transaksi</h5>
            </div>
            <div class="card-body px-3 pb-3" id="posCardBody">
                <form id="posForm" action="{{ route('pos.store') }}" method="POST">
                    @csrf
                    <div class="table-responsive mb-2" style="max-height: 260px; overflow-y: auto;">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Produk</th>
                                    <th style="width: 28%;">Harga (Rp)</th>
                                    <th style="width: 18%;">Qty</th>
                                    <th style="width: 5%;">#</th>
                                </tr>
                            </thead>
                            <tbody id="itemRows">
                                <tr>
                                    <td>
                                        <select class="form-select form-select-sm mb-1 product-select" onchange="selectProduct(this)">
                                            <option value="">-- Pilih Merch --</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}">
                                                    {{ $product->name }} (Stok: {{$product->stock }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="items[0][product_name]" class="form-control form-control-sm product-name" required placeholder="Nama Item">
                                        <input type="hidden" name="items[0][product_id]" class="product-id">
                                    </td>
                                    <td><input type="number" name="items[0][price]" class="form-control form-control-sm product-price" oninput="calculateTotal()" required placeholder="Harga"></td>
                                    <td><input type="number" name="items[0][qty]" class="form-control form-control-sm product-qty" value="1" min="1" oninput="calculateTotal()" required></td>
                                    <td><button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="removeRow(this)"><i class="bi bi-x-lg"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-3" onclick="addRow()">+ Tambah Pembelian</button>
                    
                    <!-- BOX TOTAL & METODE PEMBAYARAN -->
                    <div class="bg-light p-3 rounded border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold fs-6">Total Belanja:</span>
                            <span class="fw-bold fs-5 text-primary" id="grandTotalText">Rp 0</span>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold mb-1">Metode Pembayaran:</label>
                            <div class="d-flex gap-2">
                                <input type="radio" class="btn-check" name="payment_method" id="payCash" value="cash" checked onchange="togglePaymentMethod()">
                                <label class="btn btn-outline-primary btn-sm flex-fill" for="payCash"><i class="bi bi-cash-stack me-1"></i> Cash</label>

                                <input type="radio" class="btn-check" name="payment_method" id="payQris" value="qris" onchange="togglePaymentMethod()">
                                <label class="btn btn-outline-success btn-sm flex-fill" for="payQris"><i class="bi bi-qr-code-scan me-1"></i> QRIS</label>
                            </div>
                        </div>

                        <div id="cashInputGroup">
                            <div class="mb-2">
                                <label class="form-label small fw-bold mb-1">Jumlah Uang Diterima (Rp):</label>
                                <input type="number" name="pay_amount" id="payAmountInput" class="form-control form-control-sm" placeholder="0" onkeyup="calculateChange()" oninput="calculateChange()">
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                <span>Kembalian:</span>
                                <span class="fw-bold text-dark" id="changeText">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fs-6 fw-bold">
                        <i class="bi bi-check-circle me-1"></i> Simpan Transaksi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let rowIndex = 1;
    let serverProducts = @json($products);

    if (navigator.onLine && serverProducts.length > 0) {
        localStorage.setItem('cached_products', JSON.stringify(serverProducts));
    }
    const productsData = JSON.parse(localStorage.getItem('cached_products')) || serverProducts;

    // Kalkulasi Total Belanja
    function calculateTotal() {
        let grandTotal = 0;
        document.querySelectorAll('#itemRows tr').forEach(row => {
            const price = parseFloat(row.querySelector('.product-price').value) || 0;
            const qty = parseInt(row.querySelector('.product-qty').value) || 0;
            grandTotal += (price * qty);
        });

        document.getElementById('grandTotalText').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        calculateChange(grandTotal);
        return grandTotal;
    }

    // Kalkulasi Kembalian
    function calculateChange(passedTotal = null) {
        const total = (passedTotal !== null && typeof passedTotal === 'number') ? passedTotal : calculateTotal();
        const payAmount = parseFloat(document.getElementById('payAmountInput').value) || 0;
        const change = payAmount - total;

        const changeEl = document.getElementById('changeText');
        if (payAmount <= 0) {
            changeEl.innerText = 'Rp 0';
            changeEl.className = 'fw-bold text-dark';
        } else if (change >= 0) {
            changeEl.innerText = 'Rp ' + change.toLocaleString('id-ID');
            changeEl.className = 'fw-bold text-success';
        } else {
            changeEl.innerText = 'Kurang Rp ' + Math.abs(change).toLocaleString('id-ID');
            changeEl.className = 'fw-bold text-danger';
        }
    }

    // Switch Tampilan Cash vs QRIS
    function togglePaymentMethod() {
        const isCash = document.getElementById('payCash').checked;
        const cashGroup = document.getElementById('cashInputGroup');
        cashGroup.style.display = isCash ? 'block' : 'none';
        if (!isCash) {
            document.getElementById('payAmountInput').value = '';
        }
        calculateChange();
    }

    // Tambah dari Katalog Gambar
    function addCatalogToCart(id, name, price, stock) {
        const rows = document.querySelectorAll('#itemRows tr');
        const firstRowNameInput = rows[0].querySelector('.product-name');

        if (rows.length === 1 && !firstRowNameInput.value) {
            const row = rows[0];
            row.querySelector('.product-id').value = id;
            row.querySelector('.product-name').value = name;
            row.querySelector('.product-price').value = price;
            row.querySelector('.product-qty').value = 1;
            const select = row.querySelector('.product-select');
            if (select) select.value = id;
            calculateTotal();
            return;
        }

        let exists = false;
        rows.forEach(row => {
            const pid = row.querySelector('.product-id').value;
            if (pid == id) {
                const qtyInput = row.querySelector('.product-qty');
                qtyInput.value = parseInt(qtyInput.value) + 1;
                exists = true;
            }
        });

        if (exists) {
            calculateTotal();
            return;
        }

        let optionsHtml = '<option value="">-- Pilih Merch --</option>';
        productsData.forEach(p => {
            optionsHtml += `<option value="${p.id}" data-name="${p.name}" data-price="${p.price}" ${p.id == id ? 'selected' : ''}>${p.name} (Stok: ${p.stock})</option>`;
        });

        const newRow = `
            <tr>
                <td>
                    <select class="form-select form-select-sm mb-1 product-select" onchange="selectProduct(this)">${optionsHtml}</select>
                    <input type="text" name="items[${rowIndex}][product_name]" class="form-control form-control-sm product-name" value="${name}" required placeholder="Nama Item">
                    <input type="hidden" name="items[${rowIndex}][product_id]" class="product-id" value="${id}">
                </td>
                <td><input type="number" name="items[${rowIndex}][price]" class="form-control form-control-sm product-price" value="${price}" oninput="calculateTotal()" required placeholder="Harga"></td>
                <td><input type="number" name="items[${rowIndex}][qty]" class="form-control form-control-sm product-qty" value="1" min="1" oninput="calculateTotal()" required></td>
                <td><button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="removeRow(this)"><i class="bi bi-x-lg"></i></button></td>
            </tr>`;
        
        document.getElementById('itemRows').insertAdjacentHTML('beforeend', newRow);
        rowIndex++;
        calculateTotal();
    }

    function selectProduct(selectEl) {
        const row = selectEl.closest('tr');
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const productId = selectedOption.value;

        if (productId) {
            row.querySelector('.product-id').value = productId;
            row.querySelector('.product-name').value = selectedOption.getAttribute('data-name');
            row.querySelector('.product-price').value = selectedOption.getAttribute('data-price');
        } else {
            row.querySelector('.product-id').value = '';
        }
        calculateTotal();
    }

    function addRow() {
        let optionsHtml = '<option value="">-- Pilih Merch --</option>';
        productsData.forEach(p => {
            optionsHtml += `<option value="${p.id}" data-name="${p.name}" data-price="${p.price}">${p.name} (Stok: ${p.stock})</option>`;
        });

        const newRow = `
            <tr>
                <td>
                    <select class="form-select form-select-sm mb-1 product-select" onchange="selectProduct(this)">${optionsHtml}</select>
                    <input type="text" name="items[${rowIndex}][product_name]" class="form-control form-control-sm product-name" required placeholder="Nama Item">
                    <input type="hidden" name="items[${rowIndex}][product_id]" class="product-id">
                </td>
                <td><input type="number" name="items[${rowIndex}][price]" class="form-control form-control-sm product-price" oninput="calculateTotal()" required placeholder="Harga"></td>
                <td><input type="number" name="items[${rowIndex}][qty]" class="form-control form-control-sm product-qty" value="1" min="1" oninput="calculateTotal()" required></td>
                <td><button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="removeRow(this)"><i class="bi bi-x-lg"></i></button></td>
            </tr>`;
        document.getElementById('itemRows').insertAdjacentHTML('beforeend', newRow);
        rowIndex++;
        calculateTotal();
    }

    function removeRow(button) {
        const rows = document.querySelectorAll('#itemRows tr');
        if (rows.length > 1) {
            button.closest('tr').remove();
        } else {
            const row = rows[0];
            row.querySelector('.product-id').value = '';
            row.querySelector('.product-name').value = '';
            row.querySelector('.product-price').value = '';
            row.querySelector('.product-qty').value = 1;
            const select = row.querySelector('.product-select');
            if (select) select.value = '';
        }
        calculateTotal();
    }

    // Submit Transaksi + Validasi Stok & Pembayaran
    document.getElementById('posForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const items = [];
        let isStockValid = true;
        let errorMessage = '';
        
        let grandTotal = 0;
        document.querySelectorAll('#itemRows tr').forEach(row => {
            const price = parseFloat(row.querySelector('.product-price').value) || 0;
            const qty = parseInt(row.querySelector('.product-qty').value) || 0;
            grandTotal += (price * qty);
        });

        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        const payAmount = parseFloat(document.getElementById('payAmountInput').value) || 0;

        // Validasi Pembayaran Cash
        if (paymentMethod === 'cash' && payAmount < grandTotal) {
            alert('⚠️ Uang bayar Cash kurang dari total belanja!');
            return;
        }

        document.querySelectorAll('#itemRows tr').forEach((row) => {
            const productId = row.querySelector('.product-id').value;
            const qtyInput = parseInt(row.querySelector('.product-qty').value) || 0;

            if (productId) {
                const product = productsData.find(p => p.id == productId);
                if (product && qtyInput > product.stock) {
                    isStockValid = false;
                    errorMessage = `Stok '${product.name}' tidak mencukupi! (Diminta: ${qtyInput}, Sisa stok: ${product.stock})`;
                }
            }

            items.push({
                product_id: productId,
                product_name: row.querySelector('.product-name').value,
                price: row.querySelector('.product-price').value,
                qty: qtyInput,
            });
        });

        if (!isStockValid) {
            alert('⚠️ ' + errorMessage);
            return;
        }

        const transactionData = {
            items: items,
            payment_method: paymentMethod,
            pay_amount: paymentMethod === 'qris' ? grandTotal : payAmount,
            timestamp: new Date().toISOString()
        };

        if (navigator.onLine) {
            sendTransactionToServer(transactionData);
        } else {
            saveTransactionOffline(transactionData);
        }
    });

    function saveTransactionOffline(data) {
        let offlineSales = JSON.parse(localStorage.getItem('offline_sales')) || [];
        offlineSales.push(data);
        localStorage.setItem('offline_sales', JSON.stringify(offlineSales));

        alert('⚠️ Jaringan offline. Transaksi disimpan di perangkat!');
        updateOfflineBadge();
        location.reload();
    }

    function sendTransactionToServer(data) {
        fetch('{{ route("pos.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            if (response.ok) {
                alert('✅ Transaksi berhasil disimpan ke database!');
                location.reload();
            } else {
                saveTransactionOffline(data);
            }
        })
        .catch(() => saveTransactionOffline(data));
    }

    async function syncOfflineTransactions() {
        let offlineSales = JSON.parse(localStorage.getItem('offline_sales')) || [];
        if (offlineSales.length === 0 || !navigator.onLine) return;

        let successCount = 0;
        let remainingSales = [];

        for (let data of offlineSales) {
            try {
                let response = await fetch('{{ route("pos.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (response.ok) successCount++;
                else remainingSales.push(data);
            } catch (err) {
                remainingSales.push(data);
            }
        }

        localStorage.setItem('offline_sales', JSON.stringify(remainingSales));
        updateOfflineBadge();

        if (successCount > 0) {
            alert(`🔄 Terhubung kembali! ${successCount} transaksi offline berhasil tersimpan.`);
            location.reload();
        }
    }

    function updateOfflineBadge() {
        let offlineSales = JSON.parse(localStorage.getItem('offline_sales')) || [];
        let badgeContainer = document.getElementById('offlineSyncStatus');
        
        if (offlineSales.length > 0) {
            if (!badgeContainer) {
                let header = document.getElementById('posCardBody');
                let badgeHtml = `
                    <div id="offlineSyncStatus" class="alert alert-warning d-flex justify-content-between align-items-center mb-3">
                        <span><i class="bi bi-cloud-arrow-up-fill me-2"></i> Ada <strong>${offlineSales.length}</strong> transaksi offline belum tersinkronisasi.</span>
                        <button class="btn btn-sm btn-warning fw-bold" onclick="syncOfflineTransactions()">Sinkronkan Sekarang</button>
                    </div>`;
                header.insertAdjacentHTML('afterbegin', badgeHtml);
            } else {
                badgeContainer.querySelector('strong').innerText = offlineSales.length;
            }
        } else if (badgeContainer) {
            badgeContainer.remove();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        calculateTotal();
        updateOfflineBadge();
        if (navigator.onLine) syncOfflineTransactions();
    });

    window.addEventListener('online', syncOfflineTransactions);
</script>
@endsection