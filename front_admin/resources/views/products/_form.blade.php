<div class="row">
    <div class="col-md-12 form-group">
        <label for="name">Nama produk</label>
        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name ?? '') }}" required autofocus>
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="col-md-6 form-group">
        <label for="category">Kategori</label>
        <input id="category" name="category" type="text" class="form-control @error('category') is-invalid @enderror" value="{{ old('category', $product->category ?? '') }}">
        <x-input-error :messages="$errors->get('category')" class="mt-2" />
    </div>

    <div class="col-md-6 form-group">
        <label for="image">Nama file gambar</label>
        <input id="image" name="image" type="text" class="form-control @error('image') is-invalid @enderror" value="{{ old('image', $product->image ?? '') }}" placeholder="contoh: beras-5kg.jpg">
        <x-input-error :messages="$errors->get('image')" class="mt-2" />
    </div>

    <div class="col-md-6 form-group">
        <label for="price">Harga</label>
        <input id="price" name="price" type="number" min="0" step="0.01" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $product->price ?? '') }}" required>
        <x-input-error :messages="$errors->get('price')" class="mt-2" />
    </div>

    <div class="col-md-6 form-group">
        <label for="stock">Stok</label>
        <input id="stock" name="stock" type="number" min="0" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $product->stock ?? 0) }}" required>
        <x-input-error :messages="$errors->get('stock')" class="mt-2" />
    </div>

    <div class="col-md-12 form-group">
        <label for="description">Deskripsi</label>
        <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <label class="col-md-12 custom-control custom-checkbox">
        <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="is_active" @checked(old('is_active', $product->is_active ?? true))>
        <span class="custom-control-label" for="is_active">Produk aktif dan dapat dijual</span>
    </label>
</div>
