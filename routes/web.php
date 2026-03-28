<?php

use App\Http\Controllers\BaristaController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Customer routes
Route::get('/', [CustomerController::class, 'showRegistration'])->name('home');
Route::post('/register', [CustomerController::class, 'register'])->name('register');
Route::get('/customer/lookup', [CustomerController::class, 'getByPhone'])->name('customer.lookup');

// Menu routes
Route::get('/menu', [MenuController::class, 'index'])->name('menu');
Route::get('/api/menus', [MenuController::class, 'apiIndex'])->name('api.menus');

// Order routes
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders/history', [OrderController::class, 'history'])->name('orders.history');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/api/orders/{order}', [OrderController::class, 'apiShow'])->name('api.orders.show');
Route::get('/api/customers/{customer}/orders', [OrderController::class, 'apiHistory'])->name('api.customers.orders');
Route::put('/api/orders/{order}/items', [OrderController::class, 'updateItems'])->name('api.orders.update-items');
Route::patch('/api/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('api.orders.update-status');
Route::post('/api/orders/{order}/qris-proof', [OrderController::class, 'uploadQrisProof'])->name('api.orders.upload-qris-proof');

// Message routes
Route::get('/api/orders/{order}/messages', [MessageController::class, 'index'])->name('api.messages.index');
Route::post('/api/orders/{order}/messages', [MessageController::class, 'store'])->name('api.messages.store');
Route::post('/api/orders/{order}/messages/read', [MessageController::class, 'markRead'])->name('api.messages.read');

// Barista routes
Route::prefix('barista')->name('barista.')->group(function () {
    Route::get('/', [BaristaController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders/{order}', [BaristaController::class, 'order'])->name('order');
    Route::get('/menus', [BaristaController::class, 'menus'])->name('menus');
    Route::post('/menus', [BaristaController::class, 'storeMenu'])->name('menus.store');
    Route::put('/menus/{menu}', [BaristaController::class, 'updateMenu'])->name('menus.update');
    Route::delete('/menus/{menu}', [BaristaController::class, 'destroyMenu'])->name('menus.destroy');
    Route::get('/categories', [BaristaController::class, 'categories'])->name('categories');
    Route::post('/categories', [BaristaController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [BaristaController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [BaristaController::class, 'destroyCategory'])->name('categories.destroy');
    Route::get('/api/orders', [BaristaController::class, 'apiOrders'])->name('api.orders');
    Route::get('/api/orders/{order}', [BaristaController::class, 'apiOrder'])->name('api.order');
});

// Menu availability (barista)
Route::patch('/api/menus/{menu}/availability', [MenuController::class, 'update'])->name('api.menus.availability');
