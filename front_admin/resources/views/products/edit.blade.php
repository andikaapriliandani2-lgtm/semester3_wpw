@extends('layouts.admin')

@section('content')
    <div class="container-fluid mb-4">
        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Data Master</div>
        <h1 class="h3 mb-0 text-gray-800">Edit produk</h1>
    </div>

    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('products.update', $product) }}">
                    @csrf
                    @method('PUT')
                    @include('products._form')
                    <div class="border-top pt-4 mt-4 text-right">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
