@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Data Master</div>
                <h1 class="h3 mb-0 text-gray-800">Produk</h1>
            </div>
            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah produk
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Daftar produk</h6>
                <small class="text-muted">Kelola harga, stok, dan status produk minimarket.</small>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>
                                        <a href="{{ route('products.show', $product) }}" class="font-weight-bold text-primary">{{ $product->name }}</a>
                                        @if ($product->description)
                                            <small class="d-block text-muted">{{ Str::limit($product->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $product->category ?: '-' }}</td>
                                    <td>Rp {{ number_format((float) $product->price, 0, ',', '.') }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td><span class="badge {{ $product->is_active ? 'badge-success' : 'badge-secondary' }}">{{ $product->is_active ? 'Aktif' : 'Tidak aktif' }}</span></td>
                                    <td>
                                        <div class="d-flex" style="gap: 0.35rem">
                                            <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-info text-white">Detail</a>
                                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">Edit</a>
                                            <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-4">Belum ada data produk.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $products->links('pagination::bootstrap-4') }}</div>
            </div>
        </div>
    </div>
@endsection
