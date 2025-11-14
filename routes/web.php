<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Products - CUSTOM ROUTES FIRST
Route::get('/products/download-template', [ProductController::class, 'downloadTemplate'])->name('products.download-template');
Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
Route::resource('products', ProductController::class); // RESOURCE ROUTE LAST

// Transactions
Route::resource('transactions', TransactionController::class);

// Payments
Route::get('transactions/{transaction}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
Route::post('transactions/{transaction}/payments', [PaymentController::class, 'store'])->name('payments.store');
// NONAKTIFKAN SEMENTARA ROUTE QRIS
// Route::get('payments/{payment}/check-status', [PaymentController::class, 'checkQrisStatus'])->name('payments.check-status');
