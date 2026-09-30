<?php

use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;

test('admins can view the product index and product details', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::factory()->create(['name' => 'Beras Premium']);
    $this->actingAs($admin);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSeeText('Beras Premium');

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSeeText('Beras Premium');
});

test('admins see 200 seeded products in pages of 20', function () {
    $this->seed(ProductSeeder::class);
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $firstPage = $this->get(route('products.index'))->assertOk();
    $secondPage = $this->get(route('products.index', ['page' => 2]))->assertOk();
    $firstProducts = $firstPage->viewData('products');
    $secondProducts = $secondPage->viewData('products');

    $this->assertDatabaseCount('products', 200);
    expect($firstProducts->count())->toBe(20);
    expect($secondProducts->count())->toBe(20);
    expect($firstProducts->pluck('id')->intersect($secondProducts->pluck('id'))->isEmpty())->toBeTrue();
});

test('admins can create update and delete a product', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $createResponse = $this->post(route('products.store'), [
        'name' => 'Minyak Goreng',
        'code' => 'SKU-MINYAK01',
        'barcode' => '8991234567890',
        'category' => 'Sembako',
        'description' => 'Minyak goreng dua liter',
        'price' => 35000,
        'stock' => 12,
        'is_active' => '1',
    ]);

    $createResponse->assertRedirect(route('products.index'));
    $product = Product::query()->where('name', 'Minyak Goreng')->firstOrFail();
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock' => 12,
        'code' => 'SKU-MINYAK01',
        'barcode' => '8991234567890',
    ]);

    $updateResponse = $this->put(route('products.update', $product), [
        'name' => 'Minyak Goreng Premium',
        'code' => 'SKU-MINYAK02',
        'barcode' => '8991234567891',
        'category' => 'Sembako',
        'description' => 'Minyak goreng premium dua liter',
        'price' => 42000,
        'stock' => 8,
    ]);

    $updateResponse->assertRedirect(route('products.index'));
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Minyak Goreng Premium',
        'code' => 'SKU-MINYAK02',
        'barcode' => '8991234567891',
        'stock' => 8,
        'is_active' => false,
    ]);

    $deleteResponse = $this->delete(route('products.destroy', $product));

    $deleteResponse->assertRedirect(route('products.index'));
    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('product creation validates required and nonnegative fields', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $response = $this->post(route('products.store'), [
        'name' => '',
        'price' => -1,
        'stock' => -2,
    ]);

    $response->assertSessionHasErrors(['name', 'price', 'stock']);
    $this->assertDatabaseCount('products', 0);
});

test('cashiers cannot access product CRUD routes', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $product = Product::factory()->create();
    $this->actingAs($cashier);

    $this->get(route('products.index'))->assertForbidden();
    $this->get(route('products.create'))->assertForbidden();
    $this->get(route('products.show', $product))->assertForbidden();
});
