<?php

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;

test('cashiers can search products and open the POS page', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    Product::factory()->create([
        'name' => 'Indomie Goreng',
        'code' => 'BRG001',
        'barcode' => '899123456001',
        'stock' => 5,
    ]);
    $this->actingAs($cashier);

    $this->get(route('transactions.index'))
        ->assertOk()
        ->assertSeeText('Tambah Barang')
        ->assertSeeText('Ringkasan Pembayaran');

    $this->get(route('transactions.products', ['q' => '899123456001']))
        ->assertOk()
        ->assertJsonPath('products.0.name', 'Indomie Goreng');
});

test('cashiers can complete a transaction and stock is reduced', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $product = Product::factory()->create([
        'name' => 'Indomie Goreng',
        'code' => 'BRG001',
        'price' => 3500,
        'stock' => 4,
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
        'quantity' => 2,
        'subtotal' => 7000,
    ]);
    expect($product->fresh()->stock)->toBe(2);
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
