<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController; // Tambahkan baris ini

Route::get('/', function () { return view('welcome'); })->name('home');

Route::get('/login', function () { return view('auth.login'); })->name('login');

// Ini rute untuk memproses validasi password
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Dashboard dikunci kembali dengan middleware
Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->middleware('auth')->name('dashboard');