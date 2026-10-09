<?php

use App\Models\User;

test('users can log in and access their api profile with the bearer token', function () {
    $user = User::factory()->create([
        'name' => 'Kasir API',
        'email' => 'kasir-api@example.test',
        'password' => 'correct-password',
        'role' => 'kasir',
    ]);

    $login = $this->postJson('/api/login', [
        'email' => 'kasir-api@example.test',
        'password' => 'correct-password',
    ])->assertOk()
        ->assertJsonPath('token_type', 'Bearer');

    $token = $login->json('access_token');
    expect($token)->toBeString()->not->toBeEmpty();

    $this->withToken($token)
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.name', 'Kasir API')
        ->assertJsonPath('data.role', 'kasir')
        ->assertJsonMissingPath('data.password');

    $this->withToken($token)->getJson('/api/products')->assertOk();

    $this->withToken($token)
        ->postJson('/api/products', [
            'name' => 'Produk Terlarang',
            'price' => 1000,
            'stock' => 1,
        ])
        ->assertForbidden();
});

test('admin api tokens can create products', function () {
    User::factory()->create([
        'email' => 'admin-api@example.test',
        'password' => 'correct-password',
        'role' => 'admin',
    ]);

    $login = $this->postJson('/api/login', [
        'email' => 'admin-api@example.test',
        'password' => 'correct-password',
    ])->assertOk();

    $this->withToken($login->json('access_token'))
        ->postJson('/api/products', [
            'name' => 'Beras API',
            'price' => 18000,
            'stock' => 10,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Beras API');

    $this->assertDatabaseHas('products', [
        'name' => 'Beras API',
        'stock' => 10,
    ]);
});

test('api login returns 401 for invalid credentials', function () {
    User::factory()->create([
        'email' => 'kasir-api@example.test',
        'password' => 'correct-password',
    ]);

    $this->postJson('/api/login', [
        'email' => 'kasir-api@example.test',
        'password' => 'wrong-password',
    ])->assertUnauthorized()
        ->assertJsonPath('message', 'Invalid credentials');
});

test('api profile returns 401 without a bearer token', function () {
    $this->getJson('/api/user')->assertUnauthorized();
});

test('logging out revokes the current api token', function () {
    $user = User::factory()->create(['role' => 'kasir']);
    $accessToken = $user->createToken('postman', ['products:read']);

    $this->withToken($accessToken->plainTextToken)
        ->postJson('/api/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logged out');

    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $accessToken->accessToken->id,
    ]);
});
