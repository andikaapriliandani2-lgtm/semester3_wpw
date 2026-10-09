<?php

use App\Models\Product;
use App\Models\User;

test('admins can list and view products as structured json resources', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::factory()->create([
        'name' => 'Beras Premium',
        'code' => 'SKU-BERAS01',
    ]);
    $token = $admin->createToken('postman', ['products:read', 'products:write'])->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/products')
        ->assertOk()
        ->assertJsonPath('data.0.id', $product->id)
        ->assertJsonPath('data.0.name', 'Beras Premium')
        ->assertJsonPath('data.0.code', 'SKU-BERAS01');

    $this->withToken($token)
        ->getJson("/api/products/{$product->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $product->id)
        ->assertJsonMissingPath('data.password');
});

test('admins can create update and delete products through the api', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $token = $admin->createToken('postman', ['products:read', 'products:write'])->plainTextToken;

    $created = $this->withToken($token)
        ->postJson('/api/products', [
            'name' => 'Minyak Goreng',
            'code' => 'SKU-MINYAK01',
            'barcode' => '8991234567890',
            'category' => 'Sembako',
            'description' => 'Minyak goreng dua liter',
            'price' => 35000,
            'stock' => 12,
            'is_active' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Minyak Goreng')
        ->assertJsonPath('data.stock', 12);

    $productId = $created->json('data.id');
    $this->assertDatabaseHas('products', [
        'id' => $productId,
        'name' => 'Minyak Goreng',
        'stock' => 12,
    ]);

    $this->withToken($token)
        ->patchJson("/api/products/{$productId}", [
            'name' => 'Minyak Goreng Premium',
            'stock' => 8,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Minyak Goreng Premium')
        ->assertJsonPath('data.stock', 8);

    $this->assertDatabaseHas('products', [
        'id' => $productId,
        'name' => 'Minyak Goreng Premium',
        'stock' => 8,
    ]);

    $this->withToken($token)
        ->deleteJson("/api/products/{$productId}")
        ->assertOk()
        ->assertJsonPath('message', 'Product deleted');

    $this->assertDatabaseMissing('products', ['id' => $productId]);
});

test('product api rejects invalid product data without saving it', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $token = $admin->createToken('postman', ['products:write'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/products', [
            'name' => '',
            'price' => -1,
            'stock' => -2,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'price', 'stock']);

    $this->assertDatabaseCount('products', 0);
});

test('guests receive 401 when requesting products from the api', function () {
    $this->getJson('/api/products')->assertUnauthorized();
});

test('cashiers can read products but cannot write products using their token', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    Product::factory()->create(['name' => 'Teh Botol']);
    $token = $cashier->createToken('postman', ['products:read'])->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/products')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Teh Botol');

    $this->withToken($token)
        ->postJson('/api/products', [])
        ->assertForbidden();
});

test('cashiers cannot write products even when their token has write ability', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $token = $cashier->createToken('postman', ['products:read', 'products:write'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/products', [
            'name' => 'Teh Botol',
            'price' => 5000,
            'stock' => 10,
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('products', 0);
});

test('product api rejects tokens without the required read ability', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $token = $admin->createToken('postman', ['products:write'])->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/products')
        ->assertForbidden();
});
