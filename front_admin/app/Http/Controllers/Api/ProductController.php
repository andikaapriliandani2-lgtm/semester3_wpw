<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::query()->orderBy('id')->paginate(20),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $product = Product::query()->create($request->validate($this->rules()));

        return (new ProductResource($product))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    public function update(Request $request, Product $product): ProductResource
    {
        $product->update($request->validate($this->rules($product)));

        return new ProductResource($product->refresh());
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(?Product $product = null): array
    {
        $required = $product ? ['sometimes', 'required'] : ['required'];
        $uniqueCode = Rule::unique('products', 'code');
        $uniqueBarcode = Rule::unique('products', 'barcode');

        if ($product) {
            $uniqueCode->ignore($product);
            $uniqueBarcode->ignore($product);
        }

        return [
            'name' => [...$required, 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', $uniqueCode],
            'barcode' => ['nullable', 'string', 'max:255', $uniqueBarcode],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => [...$required, 'numeric', 'min:0'],
            'stock' => [...$required, 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
