<?php

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('cashiers can search products and open the POS page', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    Storage::fake('public');
    $imagePath = UploadedFile::fake()->image('noodles.png')->storeAs('products', 'noodles.png', 'public');
    Product::factory()->create([
        'name' => 'Indomie Goreng',
        'code' => 'BRG001',
        'barcode' => '899123456001',
        'stock' => 5,
        'image' => $imagePath,
    ]);
    $this->actingAs($cashier);

    $this->get(route('transactions.index'))
        ->assertOk()
        ->assertSeeText('Tambah Barang')
        ->assertSeeText('Ringkasan Pembayaran');

    $this->get(route('transactions.products', ['q' => '899123456001']))
        ->assertOk()
        ->assertJsonPath('products.0.name', 'Indomie Goreng')
        ->assertJsonPath('products.0.image_url', Storage::disk('public')->url($imagePath));
});

test('cashiers can complete a transaction and stock is reduced', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    Storage::fake('public');
    $imagePath = UploadedFile::fake()->image('noodles.png')->storeAs('products', 'noodles.png', 'public');
    $product = Product::factory()->create([
        'name' => 'Indomie Goreng',
        'code' => 'BRG001',
        'price' => 3500,
        'stock' => 4,
        'image' => $imagePath,
    ]);
    $this->actingAs($cashier)->get(route('transactions.index'));

    $response = $this->post(route('transactions.store'), [
        'transaction_number' => 'TRX-20260930-CHECK01',
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
        'customer_name' => 'Umum',
        'customer_phone' => '',
        'discount_percent' => 10,
        'discount_amount' => 500,
        'tax' => 1000,
        'other_fee' => 500,
        'paid_amount' => 10000,
        'payment_method' => 'Tunai',
    ]);

    $transaction = Transaction::query()->firstOrFail();
    $response->assertRedirect(route('transactions.receipt', $transaction));
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'subtotal' => 7000,
        'discount' => 1200,
        'grand_total' => 7300,
        'change_amount' => 2700,
    ]);
    $this->assertDatabaseHas('transaction_details', [
        'transaction_id' => $transaction->id,
        'product_name' => 'Indomie Goreng',
        'product_image' => $imagePath,
        'quantity' => 2,
        'subtotal' => 7000,
    ]);
    expect($product->fresh()->stock)->toBe(2);

    $this->get(route('transactions.receipt', $transaction))
        ->assertSee(asset('storage/'.$imagePath));
});

test('checkout rejects insufficient payment without changing stock or saving a transaction', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $product = Product::factory()->create(['price' => 3500, 'stock' => 4]);
    $this->actingAs($cashier)->get(route('transactions.index'));

    $this->post(route('transactions.store'), [
        'transaction_number' => 'TRX-20260930-CHECK02',
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
        'customer_name' => 'Umum',
        'discount_percent' => 0,
        'discount_amount' => 0,
        'tax' => 0,
        'other_fee' => 0,
        'paid_amount' => 6999,
        'payment_method' => 'Tunai',
    ])->assertSessionHasErrors('paid_amount');

    $this->assertDatabaseCount('transactions', 0);
    expect($product->fresh()->stock)->toBe(4);
});

test('checkout rejects quantities above stock without saving partial changes', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $product = Product::factory()->create(['price' => 3500, 'stock' => 1]);
    $this->actingAs($cashier)->get(route('transactions.index'));

    $this->post(route('transactions.store'), [
        'transaction_number' => 'TRX-20260930-CHECK03',
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
        'customer_name' => 'Umum',
        'discount_percent' => 0,
        'discount_amount' => 0,
        'tax' => 0,
        'other_fee' => 0,
        'paid_amount' => 10000,
        'payment_method' => 'Tunai',
    ])->assertSessionHasErrors('items');

    $this->assertDatabaseCount('transactions', 0);
    expect($product->fresh()->stock)->toBe(1);
});

test('guests cannot access transactions', function () {
    $this->get(route('transactions.index'))->assertRedirect(route('login'));
});

test('cashiers can download a transaction invoice as a PDF', function () {
    Storage::fake('public');
    $imagePath = UploadedFile::fake()->image('coffee.png')->storeAs('products', 'coffee.png', 'public');
    $cashier = User::factory()->create(['role' => 'kasir']);
    $transaction = Transaction::query()->create([
        'transaction_number' => 'TRX-PDF-TEST01',
        'user_id' => $cashier->id,
        'customer_name' => 'Pelanggan Invoice',
        'subtotal' => 25000,
        'discount_percent' => 0,
        'discount' => 0,
        'tax' => 0,
        'other_fee' => 0,
        'grand_total' => 25000,
        'paid_amount' => 25000,
        'change_amount' => 0,
        'payment_method' => 'Tunai',
        'status' => 'completed',
    ]);
    $transaction->details()->create([
        'product_name' => 'Kopi Susu',
        'product_code' => 'KOPI01',
        'product_image' => $imagePath,
        'price' => 25000,
        'quantity' => 1,
        'subtotal' => 25000,
    ]);

    $this->actingAs($cashier)
        ->get(route('transactions.receipt', $transaction))
        ->assertSee(route('transactions.invoice', $transaction));

    $response = $this->get(route('transactions.invoice', $transaction));

    $response->assertDownload('invoice-'.$transaction->id.'.pdf');
    expect(substr($response->getContent(), 0, 5))->toBe('%PDF-')
        ->and($response->getContent())->toContain('/Subtype /Image');
});

test('transaction images remain available after the product is deleted', function () {
    Storage::fake('public');
    $imagePath = UploadedFile::fake()->image('tea.png')->storeAs('products', 'tea.png', 'public');
    $cashier = User::factory()->create(['role' => 'kasir']);
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::factory()->create(['image' => $imagePath]);
    $transaction = Transaction::query()->create([
        'transaction_number' => 'TRX-PDF-TEST02',
        'user_id' => $cashier->id,
        'customer_name' => 'Pelanggan',
        'subtotal' => 1000,
        'discount_percent' => 0,
        'discount' => 0,
        'tax' => 0,
        'other_fee' => 0,
        'grand_total' => 1000,
        'paid_amount' => 1000,
        'change_amount' => 0,
        'payment_method' => 'Tunai',
        'status' => 'completed',
    ]);
    $transaction->details()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_image' => $imagePath,
        'price' => 1000,
        'quantity' => 1,
        'subtotal' => 1000,
    ]);

    $this->actingAs($admin)
        ->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'));

    expect(Storage::disk('public')->exists($imagePath))->toBeTrue();
    $this->actingAs($cashier)
        ->get(route('transactions.receipt', $transaction))
        ->assertSee(asset('storage/'.$imagePath));
});

test('transaction images are preserved when a product image is replaced', function () {
    Storage::fake('public');
    $oldImagePath = UploadedFile::fake()->image('old-tea.png')->storeAs('products', 'old-tea.png', 'public');
    $cashier = User::factory()->create(['role' => 'kasir']);
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::factory()->create(['image' => $oldImagePath]);
    $transaction = Transaction::query()->create([
        'transaction_number' => 'TRX-PDF-TEST03',
        'user_id' => $cashier->id,
        'customer_name' => 'Pelanggan',
        'subtotal' => 1000,
        'discount_percent' => 0,
        'discount' => 0,
        'tax' => 0,
        'other_fee' => 0,
        'grand_total' => 1000,
        'paid_amount' => 1000,
        'change_amount' => 0,
        'payment_method' => 'Tunai',
        'status' => 'completed',
    ]);
    $transaction->details()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => 1000,
        'quantity' => 1,
        'subtotal' => 1000,
    ]);

    $this->actingAs($admin)
        ->put(route('products.update', $product), [
            'name' => $product->name,
            'code' => $product->code,
            'barcode' => $product->barcode,
            'category' => $product->category,
            'description' => $product->description,
            'price' => $product->price,
            'stock' => $product->stock,
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('new-tea.png'),
        ])
        ->assertRedirect(route('products.index'));

    $product->refresh();
    expect($product->image)->not->toBe($oldImagePath);
    $this->assertDatabaseHas('transaction_details', [
        'transaction_id' => $transaction->id,
        'product_image' => $oldImagePath,
    ]);
    expect(Storage::disk('public')->exists($oldImagePath))->toBeTrue();

    $this->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'));

    expect(Storage::disk('public')->exists($oldImagePath))->toBeTrue();
    $this->actingAs($cashier)
        ->get(route('transactions.receipt', $transaction))
        ->assertSee(asset('storage/'.$oldImagePath));
});
