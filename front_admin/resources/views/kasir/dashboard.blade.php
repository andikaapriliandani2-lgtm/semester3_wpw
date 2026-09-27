<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Kasir</title>
    <link href="{{ asset('admin_assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin_assets/css/sb-admin-2.min.css') }}" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand navbar-light bg-white shadow-sm">
        <a class="navbar-brand font-weight-bold text-primary" href="{{ route('kasir.dashboard') }}">Minimarket</a>
        <a class="nav-link text-gray-700" href="{{ route('kasir.dashboard') }}">Dashboard</a>
        <a class="nav-link text-gray-700" href="{{ route('transactions.index') }}">Transaksi</a>
        <div class="ml-auto d-flex align-items-center">
            <span class="text-gray-700 mr-3">{{ auth()->user()->name }} · Kasir</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-secondary btn-sm" type="submit">Logout</button>
            </form>
        </div>
    </nav>

    <main class="container py-5">
        <h1 class="h3 text-gray-800">Dashboard Kasir</h1>
        <p class="text-gray-600">Selamat datang, {{ auth()->user()->name }}</p>
    </main>
</body>

</html>