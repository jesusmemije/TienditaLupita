<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', [OrderController::class, 'index']);

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::post('/orders/{order}/deliver', [OrderController::class, 'deliver'])->name('orders.deliver');
Route::get('/debts', [OrderController::class, 'debts'])->name('debts.index');
Route::post('/debts/{order}/settle', [OrderController::class, 'settle'])->name('debts.settle');
Route::get('/history', [OrderController::class, 'history'])->name('orders.history');

Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');

Route::get('/limpiar-cache', function () {
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');
    return '<h1>⚡ Caché de Laravel optimizada correctamente.</h1>';
});
