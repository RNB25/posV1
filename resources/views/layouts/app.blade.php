<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System & Analytics</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            background: #1e293b;
            color: #fff;
            transition: all 0.3s;
            min-height: 100vh;
        }
        #sidebar .sidebar-header {
            padding: 20px;
            background: #0f172a;
        }
        #sidebar ul.components {
            padding: 15px 0;
        }
        #sidebar ul li a {
            padding: 12px 20px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            color: #94a3b8;
            text-decoration: none;
            transition: 0.2s;
        }
        #sidebar ul li a:hover, 
        #sidebar ul li a.active {
            color: #fff;
            background: #334155;
            border-left: 4px solid #3b82f6;
        }
        #content {
            width: 100%;
            padding: 30px;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header d-flex align-items-center justify-content-between">
            <h4 class="mb-0 text-white fw-bold"><i class="bi bi-shop me-2"></i>POS App</h4>
        </div>

        <ul class="list-unstyled components">
            @auth
                @if(auth()->user()->role === 'admin')
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid-1x2-fill me-3 fs-5"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam me-3 fs-5"></i> Kelola Produk
                    </a>
                </li>
                @endif
                <li>
                    <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.*') ? 'active' : '' }}">
                        <i class="bi bi-cart3 me-3 fs-5"></i> Kasir POS
                    </a>
                </li>
                <li>
                    <a href="{{ route('sales.history') }}" class="{{ request()->routeIs('sales.history') ? 'active' : '' }}">
                        <i class="bi bi-clock-history me-3 fs-5"></i> Riwayat
                    </a>
                </li>
            @endauth
        </ul>

        <div class="px-3 pt-4 border-top border-secondary mt-auto">
            @auth
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:38px; height:38px;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <h6 class="mb-0 text-white text-truncate" style="max-width: 140px;">{{ auth()->user()->name }}</h6>
                        <small class="text-capitalize text-muted">{{ auth()->user()->role }}</small>
                    </div>
                </div>

                <!-- Form & Tombol Logout yang Aman -->
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline w-100">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-box-arrow-right me-1"></i> Keluar
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </a>
            @endauth
        </div>
    </nav>

    <div id="content">
        <div class="container-fluid p-0">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then(() => console.log('Offline SW Ready'))
            .catch(err => console.log('SW Failed', err));
    }
</script>
@stack('scripts')
</body>
</html>