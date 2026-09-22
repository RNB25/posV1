@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<style>
    /* 1. MESH GRADIENT BACKGROUND */
    .glass-wrapper {
        background-color: #f6f5f8;
        background-image: 
            radial-gradient(at 0% 0%, rgba(243, 232, 255, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(254, 226, 226, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, rgba(224, 242, 254, 1) 0px, transparent 50%),
            radial-gradient(at 0% 100%, rgba(255, 237, 213, 1) 0px, transparent 50%);
        border-radius: 30px;
        padding: 2.5rem;
        min-height: 85vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* 2. GLASSMORPHISM CARDS */
    .glass-card {
        background: rgba(255, 255, 255, 0.55);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        padding: 1.5rem;
        transition: transform 0.3s ease;
    }
    .glass-card:hover { transform: translateY(-3px); }

    /* 3. TYPOGRAPHY & ICONS */
    .glass-icon { font-size: 1.4rem; color: #1e293b; }
    .glass-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.03em;
        margin-top: 1rem; margin-bottom: 0.2rem;
    }
    .glass-label { font-size: 0.85rem; font-weight: 500; color: #64748b; }
    .glass-badge {
        background: rgba(255, 255, 255, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.9);
        color: #334155;
        border-radius: 20px;
        font-weight: 600; font-size: 0.75rem;
        padding: 0.4em 1em;
    }

    /* DROPDOWN GLASSMORPHISM */
    .glass-dropdown-menu {
        background: rgba(255, 255, 255, 0.85) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255, 255, 255, 0.9) !important;
        border-radius: 16px !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
        padding: 0.5rem !important;
    }
    .glass-dropdown-menu .dropdown-item {
        border-radius: 8px; padding: 0.5rem 1rem; font-weight: 500; color: #475569; transition: all 0.2s;
    }
    .glass-dropdown-menu .dropdown-item:hover { background: rgba(167, 139, 250, 0.15); color: #6d28d9; }
    .glass-dropdown-menu .dropdown-item.active { background: #a78bfa; color: white; }

    /* MEMPERBAIKI AREA KLIK KALENDER BROWSER */
    input[type="date"] {
        position: relative;
        cursor: pointer;
    }
    input[type="date"]::-webkit-calendar-picker-indicator {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }
</style>

<div class="glass-wrapper">
    
    <!-- HEADER & GLOBAL FILTER (FROM - TO) -->
    <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-0">Good morning, {{ auth()->user()->name ?? 'Admin' }}!</h4>
        </div>
        <div>
            <form action="{{ route('dashboard') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                <div class="d-flex align-items-center bg-white bg-opacity-75 px-3 py-1" style="border-radius: 20px; border: 1px solid rgba(255,255,255,0.9); backdrop-filter: blur(10px); box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                    <i class="bi bi-calendar-range text-muted me-2"></i>
                    
                    <!-- Input Tanggal Mulai -->
                    <input type="date" name="start_date" class="form-control form-control-sm bg-transparent border-0 shadow-none px-1 text-dark cursor-pointer" style="width: 130px; font-weight: 600; font-size: 0.8rem;" value="{{ $startDate }}" required title="Tanggal Mulai">
                    
                    <span class="mx-2 text-muted fw-bold small">to</span>
                    
                    <!-- Input Tanggal Akhir -->
                    <input type="date" name="end_date" class="form-control form-control-sm bg-transparent border-0 shadow-none px-1 text-dark cursor-pointer" style="width: 130px; font-weight: 600; font-size: 0.8rem;" value="{{ $endDate }}" required title="Tanggal Akhir">
                </div>
                
                <button type="submit" class="btn btn-sm btn-dark px-3 py-2 shadow-sm" style="border-radius: 20px; font-weight: 500;">Apply</button>
                @if($startDate && $endDate)
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-danger px-3 py-2 shadow-sm" style="border-radius: 20px; font-weight: 500;">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <!-- METRICS ROW -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <i class="bi bi-wallet2 glass-icon"></i>
                </div>
                <h3 class="glass-value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                <p class="glass-label mb-0">{{ $startDate ? 'Total Filtered' : 'Total Keseluruhan' }}</p>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <i class="bi bi-cash-stack glass-icon"></i>
                </div>
                <h3 class="glass-value">Rp {{ number_format($cashRevenue, 0, ',', '.') }}</h3>
                <p class="glass-label mb-0">Pendapatan Tunai</p>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <i class="bi bi-qr-code-scan glass-icon"></i>
                </div>
                <h3 class="glass-value">Rp {{ number_format($qrisRevenue, 0, ',', '.') }}</h3>
                <p class="glass-label mb-0">Pendapatan QRIS</p>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <i class="bi bi-receipt glass-icon"></i>
                </div>
                <h3 class="glass-value">{{ $totalTransactions }} <span class="fs-6 text-muted fw-normal">Tx</span></h3>
                <p class="glass-label mb-0">Total Transaksi</p>
            </div>
        </div>
    </div>

    <!-- MAIN CHART & SIDEBAR -->
    <div class="row g-4">
        <!-- Chart Section -->
        <div class="col-12 col-xl-8">
            <div class="glass-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Average Sales</h5>
                        <p class="glass-label mb-0">Ringkasan penjualan berdasarkan filter</p>
                    </div>
                    
                    <!-- DROPDOWN CHART FILTER (Disembunyikan jika Global Filter aktif) -->
                    @if(!$startDate)
                    <div class="dropdown">
                        <button class="glass-badge border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ $filterLabel }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end glass-dropdown-menu">
                            <li><a class="dropdown-item {{ $filter == 'today' ? 'active' : '' }}" href="?filter=today">Hari Ini (Per Jam)</a></li>
                            <li><a class="dropdown-item {{ $filter == 'week' ? 'active' : '' }}" href="?filter=week">7 Hari Terakhir</a></li>
                            <li><a class="dropdown-item {{ $filter == 'month' ? 'active' : '' }}" href="?filter=month">Bulan Ini</a></li>
                            <li><a class="dropdown-item {{ $filter == 'year' ? 'active' : '' }}" href="?filter=year">Tahun Ini (Per Bulan)</a></li>
                        </ul>
                    </div>
                    @else
                    <span class="glass-badge">{{ $filterLabel }}</span>
                    @endif
                </div>
                
                <div class="d-flex align-items-end gap-4 mb-4 mt-3">
                    <div>
                        <p class="glass-label mb-1" style="font-size: 0.75rem;">Hari Ini</p>
                        <h4 class="fw-bold mb-0">Rp {{ number_format($todaySales, 0, ',', '.') }}</h4>
                    </div>
                    <div>
                        <p class="glass-label mb-1" style="font-size: 0.75rem;">Bulan Ini</p>
                        <h4 class="fw-bold mb-0">Rp {{ number_format($monthlySales, 0, ',', '.') }}</h4>
                    </div>
                </div>

                <!-- Div untuk ApexCharts -->
                <div id="chart-sales" style="min-height: 280px;"></div>
            </div>
        </div>

        <!-- Sidebar Section (Top Products & Stock) -->
        <div class="col-12 col-xl-4 d-flex flex-column gap-4">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Top 5 Produk</h6>
                    <i class="bi bi-arrow-up-right text-muted"></i>
                </div>
                <div class="d-flex flex-column gap-3 mt-3">
                    @forelse($topProducts as $product)
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $product->product_name }}</span>
                            <span class="glass-badge" style="background: rgba(167, 139, 250, 0.2); border: none; color: #6d28d9;">
                                {{ $product->total_qty }} Terjual
                            </span>
                        </div>
                    @empty
                        <p class="glass-label mb-0">Belum ada data.</p>
                    @endforelse
                </div>
            </div>

            <div class="glass-card flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Stok Menipis</h6>
                    <span class="glass-badge text-danger" style="background: rgba(254, 226, 226, 0.5);">≤ 5 Pcs</span>
                </div>
                <div class="d-flex flex-column gap-3 mt-3">
                    @forelse($lowStockProducts as $product)
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $product->name }}</span>
                            <span class="fw-bold text-danger">{{ $product->stock }}</span>
                        </div>
                    @empty
                        <p class="glass-label mb-0">Semua stok aman.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    const chartData = @json($chartData);
    const activeFilter = '{{ $filter }}';

    const options = {
        chart: {
            type: 'area',
            height: 280,
            fontFamily: 'inherit',
            toolbar: { show: false },
            background: 'transparent'
        },
        stroke: { curve: 'smooth', width: 3 },
        series: [{
            name: 'Total Penjualan',
            data: chartData.map(d => d.total)
        }],
        xaxis: {
            categories: chartData.map(d => {
                if (activeFilter === 'today') {
                    return d.date; 
                } else if (activeFilter === 'year') {
                    let date = new Date(d.date + '-01'); 
                    return date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' }); 
                } else {
                    let date = new Date(d.date); 
                    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }); 
                }
            }),
            labels: { style: { colors: '#94a3b8', fontWeight: 500 } },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: { colors: '#94a3b8', fontWeight: 500 },
                formatter: (val) => { return 'Rp ' + (val / 1000).toFixed(0) + 'k'; }
            }
        },
        grid: {
            borderColor: 'rgba(255, 255, 255, 0.4)',
            strokeDashArray: 4,
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } },
            padding: { top: 0, right: 0, bottom: 0, left: 10 }
        },
        colors: ['#a78bfa'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [0, 100]
            }
        },
        dataLabels: { enabled: false },
        tooltip: {
            theme: 'light',
            y: { formatter: function (val) { return "Rp " + val.toLocaleString('id-ID'); } }
        }
    };

    const chart = new ApexCharts(document.querySelector("#chart-sales"), options);
    chart.render();
</script>
@endpush