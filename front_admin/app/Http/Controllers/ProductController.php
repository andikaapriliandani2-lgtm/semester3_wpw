<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TransactionDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('products.index', [
            'products' => Product::query()->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedProduct($request);
        $validated['is_active'] = $request->boolean('is_active');

        unset($validated['image']);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->uploadImage($request);
        }

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product): View
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validatedProduct($request);
        $validated['is_active'] = $request->boolean('is_active');

        unset($validated['image']);

        if ($request->hasFile('image')) {
            $newImage = $this->uploadImage($request);

            if ($product->image) {
                $this->preserveTransactionImages($product);
                $this->deleteImageIfUnused($product->image, $product->id);
            }

            $validated['image'] = $newImage;
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $image = $product->image;

        if ($image) {
            $this->preserveTransactionImages($product);
        }

        $product->delete();

        if ($image) {
            $this->deleteImageIfUnused($image, $product->id);
        }

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    private function uploadImage(Request $request): string
    {
        $file = $request->file('image');

        // Membuat Image Manager menggunakan driver GD
        $manager = new ImageManager(new Driver);

        // Membaca file gambar (Intervention Image versi 4)
        $image = $manager->decodePath($file->getRealPath());

        // Resize gambar maksimal 800 x 800 pixel
        $image->scaleDown(width: 800, height: 800);

        // Membuat nama file unik
        $filename = (string) Str::uuid().'.jpg';

        // Menentukan lokasi penyimpanan
        $path = 'products/'.$filename;

        // Mengubah gambar menjadi JPEG dengan kualitas 85%
        $encoded = $image->encodeUsingFormat(
            Format::JPEG,
            quality: 85
        );

        // Menyimpan gambar ke storage Laravel
        Storage::disk('public')->put(
            $path,
            (string) $encoded
        );

        return $path;
    }

    private function validatedProduct(Request $request): array
    {
        $product = $request->route('product');

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable', 'string', 'max:255',
                Rule::unique('products', 'code')->ignore($product),
            ],
            'barcode' => [
                'nullable', 'string', 'max:255',
                Rule::unique('products', 'barcode')->ignore($product),
            ],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    private function deleteImageIfUnused(string $image, int $productId): void
    {
        $isUsedByTransaction = TransactionDetail::query()
            ->where('product_image', $image)
            ->exists();

        if (! $isUsedByTransaction) {
            Storage::disk('public')->delete($image);
        }
    }

    private function preserveTransactionImages(Product $product): void
    {
        TransactionDetail::query()
            ->where('product_id', $product->id)
            ->whereNull('product_image')
            ->update(['product_image' => $product->image]);
    }
}
