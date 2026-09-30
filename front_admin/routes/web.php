<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $dashboardRoute = auth()->user()->role === 'admin'
            ? 'admin.dashboard'
            : 'kasir.dashboard';

        return redirect()->route($dashboardRoute);
    })->name('dashboard');

    Route::middleware('role:admin')->group(function () {
        Route::view('/admin/dashboard', 'admin.dashboard')->name('admin.dashboard');
    });

    Route::middleware('role:kasir')->group(function () {
        Route::view('/kasir/dashboard', 'admin.dashboard')->name('kasir.dashboard');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('products', ProductController::class);
        Route::view('/categories', 'modules.placeholder', ['module' => 'Kategori'])->name('categories.index');
        Route::view('/users', 'modules.placeholder', ['module' => 'Pengguna'])->name('users.index');
        Route::view('/reports', 'modules.placeholder', ['module' => 'Laporan'])->name('reports.index');
    });

    Route::middleware('role:admin,kasir')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/products', [TransactionController::class, 'searchProducts'])->name('transactions.products');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])
            ->name('transactions.receipt');
    });
});

require __DIR__.'/auth.php';
