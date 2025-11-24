<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Models\Role;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Rutas protegidas para usuarios autenticados
Route::middleware(['auth'])->group(function () {
    
    // Dashboard dinámico según rol
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Menú público para todos los usuarios autenticados
    Route::get('menu', [MenuController::class, 'index'])->name('menu');
    
    // Checkout
    Route::get('checkout', function () {
        return Inertia::render('Checkout');
    })->name('checkout');
    
    // Pedidos - todos pueden crear y ver sus pedidos
    Route::resource('orders', OrderController::class)->only(['index', 'show', 'store']);

    // Búsqueda global
    Route::get('search', SearchController::class)->name('search.global');
    
    // Rutas para Administrador
    Route::middleware(['role:' . Role::ADMIN])->prefix('admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('foods', FoodController::class);
        Route::resource('categories', CategoryController::class);
        Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    });
    
    // Rutas para Vendedor
    Route::middleware(['role:' . Role::VENDEDOR . ',' . Role::ADMIN])->prefix('seller')->group(function () {
        Route::resource('foods', FoodController::class)->names([
            'index' => 'seller.foods.index',
            'create' => 'seller.foods.create',
            'store' => 'seller.foods.store',
            'edit' => 'seller.foods.edit',
            'update' => 'seller.foods.update',
            'destroy' => 'seller.foods.destroy',
        ]);
        Route::resource('categories', CategoryController::class)->names([
            'index' => 'seller.categories.index',
            'store' => 'seller.categories.store',
            'update' => 'seller.categories.update',
            'destroy' => 'seller.categories.destroy',
        ]);
        Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('seller.orders.updateStatus');
    });
});

require __DIR__.'/settings.php';
