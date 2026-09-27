@extends('layouts.admin')

@section('content')
    <div class="container-fluid mb-4">
        <div class="d-sm-flex align-items-center justify-content-between">
            <div>
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Data Master</div>
                <h1 class="h3 mb-0 text-gray-800">Detail produk</h1>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white py-4">
                <small>Produk minimarket</small>
                <h1 class="h3 mb-2 text-white">{{ $product->name }}</h1>
                <span class="badge {{ $product->is_active ? 'badge-warning' : 'badge-secondary' }}">{{ $product->is_active ? 'Aktif' : 'Tidak aktif' }}</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Kategori</dt><dd class="col-sm-9">{{ $product->category ?: '-' }}</dd>
                    <dt class="col-sm-3">Harga</dt><dd class="col-sm-9 font-weight-bold">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</dd>
                    <dt class="col-sm-3">Stok</dt><dd class="col-sm-9">{{ $product->stock }}</dd>
                    <dt class="col-sm-3">Gambar</dt><dd class="col-sm-9">{{ $product->image ?: '-' }}</dd>
                    <dt class="col-sm-3">Deskripsi</dt><dd class="col-sm-9">{{ $product->description ?: '-' }}</dd>
                </dl>
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">Edit produk</a>
            </div>
        </div>
    </div>
@endsection
