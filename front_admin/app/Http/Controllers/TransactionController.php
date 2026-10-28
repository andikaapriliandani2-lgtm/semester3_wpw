<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        return view('transactions.index', [
            'transactionNumber' => 'TRX-'.now()->format('Ymd').'-'.Str::upper(Str::random(10)),
        ]);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $keyword = $request->string('q')->trim()->toString();

        if ($keyword === '') {
            return response()->json(['products' => []]);
        }

        $products = Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->where(function ($query) use ($keyword): void {
                $query->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('code', 'like', '%'.$keyword.'%')
                    ->orWhere('barcode', 'like', '%'.$keyword.'%');
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'code', 'barcode', 'price', 'stock', 'image'])
            ->map(function (Product $product): array {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'barcode' => $product->barcode,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image_url' => $product->image
                        ? Storage::disk('public')->url($product->image)
                        : null,
                ];
            });

        return response()->json(['products' => $products]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'transaction_number' => ['required', 'string', 'max:32', 'unique:transactions,transaction_number'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'tax' => ['required', 'numeric', 'min:0'],
            'other_fee' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:Tunai,QRIS,Debit,Kredit,E-Wallet,Transfer'],
        ]);

        $transaction = DB::transaction(function () use ($request, $validated): Transaction {
            $items = collect($validated['items'])->keyBy(fn (array $item): int => (int) $item['product_id']);
            $products = Product::query()
                ->whereKey($items->keys())
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $items->count()) {
                throw ValidationException::withMessages([
                    'items' => 'Salah satu produk sudah tidak tersedia.',
                ]);
            }

            $subtotal = 0.0;
            foreach ($items as $productId => $item) {
                $product = $products->get($productId);
                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak mencukupi. Sisa stok: {$product->stock}.",
                    ]);
                }
                $subtotal += (float) $product->price * $item['quantity'];
            }

            $subtotal = round($subtotal, 2);
            $discountPercent = (float) $validated['discount_percent'];
            $discount = min($subtotal, round($subtotal * $discountPercent / 100) + (float) $validated['discount_amount']);
            $tax = (float) $validated['tax'];
            $otherFee = (float) $validated['other_fee'];
            $grandTotal = round($subtotal - $discount + $tax + $otherFee, 2);
            $paidAmount = (float) $validated['paid_amount'];

            if ($paidAmount < $grandTotal) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Uang pembayaran masih kurang dari total transaksi.',
                ]);
            }

            $transaction = Transaction::query()->create([
                'transaction_number' => $validated['transaction_number'],
                'user_id' => $request->user()->id,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'discount_percent' => $discountPercent,
                'discount' => $discount,
                'tax' => $tax,
                'other_fee' => $otherFee,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => round($paidAmount - $grandTotal, 2),
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
            ]);

            foreach ($items as $productId => $item) {
                $product = $products->get($productId);
                $quantity = (int) $item['quantity'];
                $transaction->details()->create([
                    'product_id' => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'product_image' => $product->image,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'subtotal' => round((float) $product->price * $quantity, 2),
                ]);
                $product->decrement('stock', $quantity);
            }

            return $transaction;
        });

        return redirect()->route('transactions.receipt', $transaction);
    }

    public function receipt(Transaction $transaction): View
    {
        return view('transactions.receipt', [
            'transaction' => $transaction->load(['details.product', 'user']),
        ]);
    }

    public function invoice(Transaction $transaction): Response
    {
        $transaction->load(['details.product', 'user']);
        $invoiceImages = $transaction->details->mapWithKeys(function (TransactionDetail $detail): array {
            $imagePath = $detail->product_image ?: $detail->product?->image;
            if (! $imagePath || ! Storage::disk('public')->exists($imagePath)) {
                return [$detail->id => null];
            }

            $mimeType = Storage::disk('public')->mimeType($imagePath);
            if (! $mimeType || ! str_starts_with($mimeType, 'image/')) {
                return [$detail->id => null];
            }

            $image = Storage::disk('public')->get($imagePath);

            return [$detail->id => 'data:'.$mimeType.';base64,'.base64_encode($image)];
        });

        return Pdf::loadView('invoices.invoice', [
            'transaction' => $transaction,
            'invoiceImages' => $invoiceImages,
        ])
            ->setPaper('a4')
            ->download('invoice-'.$transaction->id.'.pdf');
    }
}
