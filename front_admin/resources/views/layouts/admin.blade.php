@php
    $isAdmin = auth()->user()->role === 'admin';
    $dashboardRoute = $isAdmin ? 'admin.dashboard' : 'kasir.dashboard';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $title ?? 'Minimarket' }}</title>
    <link href="{{ asset('admin_assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('admin_assets/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <style>
        :root {
            --main-green: #173f35;
            --main-green-light: #28745f;
            --main-gold: #f4b942;
            --main-background: #f4f7f2;
        }

        body {
            background: var(--main-background);
            font-family: 'Open Sans', sans-serif;
        }

        h1, h2, h3, h4, h5, h6, .sidebar-brand-text, .sidebar-heading, .topbar, .btn {
            font-family: 'Poppins', sans-serif;
        }

        .bg-gradient-primary, .sidebar {
            background: var(--main-green) !important;
            background-image: linear-gradient(180deg, var(--main-green) 10%, #0f2e27 100%) !important;
        }

        .sidebar .sidebar-brand, .sidebar .nav-item .nav-link, .sidebar .sidebar-heading {
            color: rgba(255, 255, 255, 0.8);
        }

        .sidebar .nav-item .nav-link:hover, .sidebar .nav-item.active .nav-link, .sidebar .sidebar-brand:hover {
            color: var(--main-gold);
        }

        .sidebar .sidebar-divider {
            border-top-color: rgba(255, 255, 255, 0.14);
        }

        .topbar {
            background: rgba(255, 255, 255, 0.96) !important;
        }

        .btn-primary, .bg-primary {
            background-color: var(--main-green-light) !important;
            border-color: var(--main-green-light) !important;
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--main-green) !important;
            border-color: var(--main-green) !important;
        }

        .text-primary {
            color: var(--main-green-light) !important;
        }

        .card {
            border-color: rgba(23, 63, 53, 0.08);
            border-radius: 0.9rem;
        }

        .text-gray-800 {
            color: var(--main-green) !important;
        }
    </style>
    @stack('styles')
</head>
<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route($dashboardRoute) }}">
                <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-store"></i></div>
                <div class="sidebar-brand-text mx-3">Minimarket</div>
            </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item {{ request()->routeIs('admin.dashboard', 'kasir.dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route($dashboardRoute) }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            @if ($isAdmin)
                <div class="sidebar-heading">Data Master</div>
                <li class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('products.index') }}"><i class="fas fa-fw fa-box"></i><span>Produk</span></a>
                </li>
                <li class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('categories.index') }}"><i class="fas fa-fw fa-tags"></i><span>Kategori</span></a>
                </li>
                <li class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('users.index') }}"><i class="fas fa-fw fa-users"></i><span>Pengguna</span></a>
                </li>
                <li class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('reports.index') }}"><i class="fas fa-fw fa-chart-area"></i><span>Laporan</span></a>
                </li>
            @endif

            <li class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('transactions.index') }}"><i class="fas fa-fw fa-receipt"></i><span>Transaksi</span></a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline"><button class="rounded-circle border-0" id="sidebarToggle"></button></div>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3"><i class="fa fa-bars"></i></button>
                    <span class="d-none d-sm-inline-block text-gray-600 small">{{ $pageLabel ?? 'Minimarket Workspace' }}</span>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ auth()->user()->name }}</span>
                                <i class="fas fa-user-circle fa-lg text-primary"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Profil</a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item" type="submit"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>Keluar</button>
                                </form>
                            </div>
                        </li>
                    </ul>
                </nav>

                @yield('content')
            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
    <script src="{{ asset('admin_assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('admin_assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin_assets/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('admin_assets/js/sb-admin-2.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
