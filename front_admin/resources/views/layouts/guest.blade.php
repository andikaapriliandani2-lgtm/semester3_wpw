<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Minimarket') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|space-grotesk:500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#f4f7f2] font-sans text-slate-900 antialiased">
        <div class="min-h-screen overflow-hidden lg:grid lg:grid-cols-[minmax(0,1.08fr)_minmax(420px,0.92fr)]">
            <section class="relative hidden overflow-hidden bg-[#173f35] px-10 py-10 text-white lg:flex lg:flex-col lg:justify-between xl:px-20">
                <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full border-[48px] border-[#f4b942]/20"></div>
                <div class="absolute -bottom-40 -left-32 h-96 w-96 rounded-full bg-[#28745f]/60"></div>

                <a href="{{ url('/') }}" class="relative z-10 flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f4b942] text-xl font-bold text-[#173f35]">M</span>
                    <span class="font-display text-xl font-bold tracking-tight">Minimarket</span>
                </a>

                <div class="relative z-10 max-w-xl py-12">
                    <p class="mb-5 text-sm font-semibold uppercase tracking-[0.24em] text-[#f4b942]">Workspace penjualan</p>
                    <h1 class="font-display text-5xl font-bold leading-[1.05] xl:text-6xl">Kelola toko dengan lebih tenang.</h1>
                    <p class="mt-6 max-w-md text-lg leading-8 text-emerald-50/75">Satu ruang kerja untuk mengatur produk, transaksi, dan aktivitas minimarket Anda.</p>
                </div>

                <p class="relative z-10 text-sm text-emerald-50/55">Akses aman untuk tim minimarket</p>
            </section>

            <main class="flex min-h-screen items-center justify-center px-5 py-8 sm:px-10">
                <div class="w-full max-w-md">
                    <div class="mb-8 flex items-center justify-between lg:hidden">
                        <a href="{{ url('/') }}" class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#173f35] text-lg font-bold text-[#f4b942]">M</span>
                            <span class="font-display text-lg font-bold text-[#173f35]">Minimarket</span>
                        </a>
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Workspace</span>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200/80 bg-white p-6 shadow-[0_24px_70px_-35px_rgba(23,63,53,0.45)] sm:p-9">
                        {{ $slot }}
                    </div>

                    <p class="mt-6 text-center text-xs text-slate-400">© {{ date('Y') }} Minimarket Workspace</p>
                </div>
            </main>
        </div>
    </body>
</html>
