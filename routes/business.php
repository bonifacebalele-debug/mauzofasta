<?php

use App\Http\Controllers\Business\CategoryController;
use App\Http\Controllers\Business\CustomerController;
use App\Http\Controllers\Business\DashboardController;
use App\Http\Controllers\Business\InvoiceController;
use App\Http\Controllers\Business\OrderController;
use App\Http\Controllers\Business\PaymentController;
use App\Http\Controllers\Business\ProductController;
use App\Http\Controllers\Business\ReceiptController;
use App\Http\Controllers\Business\RegistrationController;
use App\Http\Controllers\Business\StockController;
use App\Http\Controllers\Business\SwitchBusinessController;
use Illuminate\Support\Facades\Route;

Route::get('/register', [RegistrationController::class, 'create'])
    ->middleware('guest')
    ->name('business.register');

Route::post('/register', [RegistrationController::class, 'store'])
    ->middleware('guest');

Route::middleware(['auth', 'business'])->prefix('app')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::post('/switch-business/{business:uuid}', SwitchBusinessController::class)
        ->name('business.switch');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/products/{product}/stock', [StockController::class, 'edit'])->name('products.stock.edit');
    Route::put('/products/{product}/stock', [StockController::class, 'update'])->name('products.stock.update');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::post('/customers/{customer}/notes', [CustomerController::class, 'storeNote'])->name('customers.notes.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/quick-sale', [OrderController::class, 'quickSale'])->name('orders.quickSale');
    Route::post('/orders/quick-sale', [OrderController::class, 'storeQuickSale'])->name('orders.quickSale.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');

    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

    Route::get('/receipts', [ReceiptController::class, 'index'])->name('receipts.index');
    Route::get('/receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
    Route::get('/receipts/{receipt}/pdf', [ReceiptController::class, 'pdf'])->name('receipts.pdf');
});
