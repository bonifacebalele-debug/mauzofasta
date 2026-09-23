<?php

use App\Http\Controllers\Business\CategoryController;
use App\Http\Controllers\Business\DashboardController;
use App\Http\Controllers\Business\ProductController;
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
});
