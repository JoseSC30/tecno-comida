<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Models\Rol;
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
    Route::middleware(['role:' . Rol::ADMIN])->prefix('admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('productos', ProductoController::class);
        Route::resource('categorias', CategoriaController::class);
        Route::resource('insumos', InsumoController::class);
        Route::resource('movimientos', MovimientoController::class)->only(['index', 'create', 'store']);
        Route::get('productos/{producto}/receta', [RecetaController::class, 'edit'])->name('recetas.edit');
        Route::put('productos/{producto}/receta', [RecetaController::class, 'update'])->name('recetas.update');
        Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    });
    
    // Rutas para Vendedor
    Route::middleware(['role:' . Rol::VENDEDOR . ',' . Rol::ADMIN])->prefix('seller')->group(function () {
        Route::resource('productos', ProductoController::class)->names([
            'index' => 'seller.productos.index',
            'create' => 'seller.productos.create',
            'store' => 'seller.productos.store',
            'edit' => 'seller.productos.edit',
            'update' => 'seller.productos.update',
            'destroy' => 'seller.productos.destroy',
        ]);
        Route::resource('categorias', CategoriaController::class)->names([
            'index' => 'seller.categorias.index',
            'store' => 'seller.categorias.store',
            'update' => 'seller.categorias.update',
            'destroy' => 'seller.categorias.destroy',
        ]);
        Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('seller.orders.updateStatus');
    });
});

require __DIR__.'/settings.php';
