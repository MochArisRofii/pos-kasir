<?php

use App\Http\Controllers\CashierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProdsusController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;



// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// ==================== ADMIN ROUTES ====================
// Admin routes untuk prodsus
Route::middleware(['auth', 'admin'])->group(function () {
    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    
    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
    Route::get('/products/download-template', [ProductController::class, 'downloadTemplate'])->name('products.download-template');

    // Prodsus
    Route::get('/prodsus', [ProdsusController::class, 'index'])->name('prodsus.index');
    Route::get('/prodsus/create', [ProdsusController::class, 'create'])->name('prodsus.create');
    Route::post('/prodsus', [ProdsusController::class, 'store'])->name('prodsus.store');
    Route::get('/prodsus/{prodsus}/download-barcode', [ProdsusController::class, 'downloadBarcode'])->name('prodsus.download-barcode');
});

// ==================== CASHIER ROUTES ====================
Route::middleware(['auth', 'cashier'])->group(function () {
    // Cashier initialization
    Route::get('/cashier/initialize', [CashierController::class, 'showInitializeForm'])->name('cashier.initialize');
    Route::post('/cashier/initialize', [CashierController::class, 'initialize'])->name('cashier.initialize.submit');
    Route::get('/cashier/deinitialize', [CashierController::class, 'deinitialize'])->name('cashier.deinitialize.get');
    Route::post('/cashier/deinitialize', [CashierController::class, 'deinitialize'])->name('cashier.deinitialize');
    
    // Transactions
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    
    // Payments
    Route::get('transactions/{transaction}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('transactions/{transaction}/payments', [PaymentController::class, 'store'])->name('payments.store');


    Route::get('/prodsus/process', [ProdsusController::class, 'processIndex'])->name('prodsus.process');
    Route::post('/prodsus/{id}/process', [ProdsusController::class, 'process'])->name('prodsus.process.submit');
});

// ==================== SHARED ROUTES ====================
Route::middleware(['auth'])->group(function () {
    // Transactions (bisa diakses kedua role)
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/prodsus/process', [ProdsusController::class, 'processIndex'])->name('prodsus.process');
    Route::post('/prodsus/{id}/process', [ProdsusController::class, 'process'])->name('prodsus.process.submit');
    // Profile
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

    Route::get('/debug/prodsus-approved', function() {
        $prodsus = \App\Models\Prodsus::where('status', 'approved')->get();
        return response()->json($prodsus);
    });

    Route::get('/debug/products-all', function() {
        $products = \App\Models\Product::all();
        return response()->json($products);
    });

    Route::get('/debug/categories', function() {
        $categories = \App\Models\Catergory::all();
        return response()->json($categories);
    });

    Route::get('/prodsus/reprocess-approved', [ProdsusController::class, 'reprocessApproved']);
});
// NONAKTIFKAN SEMENTARA ROUTE QRIS
// Route::get('payments/{payment}/check-status', [PaymentController::class, 'checkQrisStatus'])->name('payments.check-status');
