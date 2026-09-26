<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StoreDebtController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/admin', [OrderController::class, 'index'])->name('admin');
    Route::get('/dashboard', [OrderController::class, 'index'])->name('dashboard');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{order}/deliver', [OrderController::class, 'deliver'])->name('orders.deliver');
    Route::get('/debts', [OrderController::class, 'debts'])->name('debts.index');
    Route::post('/debts/{order}/settle', [OrderController::class, 'settle'])->name('debts.settle');
    Route::post('/debts/{order}/payments', [OrderController::class, 'recordPayment'])->name('debts.payments.store');
    Route::get('/history', [OrderController::class, 'history'])->name('orders.history');

    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');

    Route::get('/store-debts', [StoreDebtController::class, 'index'])->name('store-debts.index');
    Route::post('/store-debts', [StoreDebtController::class, 'storeDebtor'])->name('store-debts.store');
    Route::post('/store-debts/{debtor}/charges', [StoreDebtController::class, 'storeCharge'])->name('store-debts.charges.store');
    Route::post('/store-debts/{debtor}/payments', [StoreDebtController::class, 'storePayment'])->name('store-debts.payments.store');

    Route::get('/limpiar-cache', function () {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        return '<h1>Cache de Laravel optimizada correctamente.</h1>';
    })->name('cache.clear');
});
