<?php

use App\Models\User;

test('admin users can access only the admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeText('Dashboard Admin')
        ->assertSeeText($admin->name)
        ->assertSeeText('Produk')
        ->assertSeeText('Kategori')
        ->assertSeeText('Pengguna')
        ->assertSeeText('Laporan')
        ->assertSeeText('Transaksi');

    $this->get(route('kasir.dashboard'))->assertForbidden();
});

test('cashier users can access only the cashier dashboard', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $this->actingAs($cashier);

    $this->get(route('kasir.dashboard'))
        ->assertOk()
        ->assertSeeText('Dashboard Kasir')
        ->assertSeeText($cashier->name)
        ->assertSeeText('Transaksi')
        ->assertDontSeeText('Produk')
        ->assertDontSeeText('Kategori');

    $this->get(route('admin.dashboard'))->assertForbidden();
});

test('admins can access admin modules and transactions', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    foreach (['products.index', 'categories.index', 'users.index', 'reports.index', 'transactions.index'] as $route) {
        $this->get(route($route))->assertOk();
    }
});

test('cashiers can access transactions but not admin modules', function () {
    $this->actingAs(User::factory()->create(['role' => 'kasir']));

    $this->get(route('transactions.index'))->assertOk();

    foreach (['products.index', 'categories.index', 'users.index', 'reports.index'] as $route) {
        $this->get(route($route))->assertForbidden();
    }
});

test('guests are redirected to login from both role dashboards', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    $this->get(route('kasir.dashboard'))->assertRedirect(route('login'));
});

test('public registration cannot assign an admin role', function () {
    $response = $this->post('/register', [
        'name' => 'Untrusted User',
        'email' => 'untrusted@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'admin',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertDatabaseHas('users', [
        'email' => 'untrusted@example.com',
        'role' => 'kasir',
    ]);
});
