<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PagoFacilController;
use App\Http\Controllers\ReservaController;
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

// Callback de PagoFácil (debe estar fuera de auth para recibir notificaciones)
Route::post('pagofacil/callback', [PagoFacilController::class, 'callback'])->name('pagofacil.callback');

// Rutas protegidas para usuarios autenticados
Route::middleware(['auth'])->group(function () {
    
    // Dashboard dinámico según rol
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Menú público para todos los usuarios autenticados
    Route::get('menu', [MenuController::class, 'index'])->name('menu');
    Route::get('menu/combos', [MenuController::class, 'combos'])->name('menu.combos');
    Route::middleware(['role:' . Rol::ADMIN . ',' . Rol::CAJERO . ',' . Rol::MESERO . ',' . Rol::COCINERO])
        ->post('menu/combos', [MenuController::class, 'storeCombo'])->name('menu.combos.store');
    Route::middleware(['role:' . Rol::ADMIN . ',' . Rol::CAJERO . ',' . Rol::MESERO . ',' . Rol::COCINERO])
        ->put('menu/combos/{menu}', [MenuController::class, 'updateCombo'])->name('menu.combos.update');
    Route::middleware(['role:' . Rol::ADMIN . ',' . Rol::CAJERO . ',' . Rol::MESERO . ',' . Rol::COCINERO])
        ->delete('menu/combos/{menu}', [MenuController::class, 'destroyCombo'])->name('menu.combos.destroy');
    
    // Checkout - todos menos cocinero
    Route::middleware(['role:' . Rol::ADMIN . ',' . Rol::CAJERO . ',' . Rol::MESERO . ',' . Rol::CLIENTE])
        ->get('checkout', [OrderController::class, 'checkout'])->name('checkout');
    
    // Pedidos - todos pueden ver sus pedidos
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders', [OrderController::class, 'store'])->name('orders.store');

    // Reservas - Solo Admin, Cajero y Cliente pueden acceder
    Route::middleware(['role:' . Rol::ADMIN . ',' . Rol::CAJERO . ',' . Rol::CLIENTE])
        ->prefix('reservas')->group(function () {
            Route::get('/', [ReservaController::class, 'index'])->name('reservas.index');
            Route::post('/availability', [ReservaController::class, 'getAvailability'])->name('reservas.availability');
            Route::post('/store', [ReservaController::class, 'store'])->name('reservas.store');
            Route::get('/list', [ReservaController::class, 'list'])->name('reservas.list');
            Route::get('/reporte', [ReservaController::class, 'reporte'])->name('reservas.reporte');
            Route::patch('/{reserva}/status', [ReservaController::class, 'updateStatus'])->name('reservas.updateStatus');
            Route::delete('/{reserva}/cancel', [ReservaController::class, 'cancel'])->name('reservas.cancel');
            Route::post('/{id}/pay-second-installment', [ReservaController::class, 'paySecondInstallment'])->name('reservas.paySecondInstallment');
        });

    // PagoFácil - Rutas para pago QR (todos menos cocinero)
    Route::middleware(['role:' . Rol::ADMIN . ',' . Rol::CAJERO . ',' . Rol::MESERO . ',' . Rol::CLIENTE])
        ->prefix('pagofacil')->group(function () {
            Route::post('generate-qr', [PagoFacilController::class, 'generateQr'])->name('pagofacil.generateQr');
            Route::post('query-transaction', [PagoFacilController::class, 'queryTransaction'])->name('pagofacil.queryTransaction');
            Route::post('callback-status', [PagoFacilController::class, 'getCallbackStatus'])->name('pagofacil.callbackStatus');
            Route::post('transaction-data', [PagoFacilController::class, 'getTransactionData'])->name('pagofacil.transactionData');
        });

    // Búsqueda global - Admin y Cajero
    Route::middleware(['role:' . Rol::ADMIN . ',' . Rol::CAJERO])
        ->get('search', SearchController::class)->name('search.global');
    
    // Rutas para Administrador (acceso total)
    Route::middleware(['role:' . Rol::ADMIN])->prefix('admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('productos', ProductoController::class);
        Route::resource('categorias', CategoriaController::class);
        Route::resource('insumos', InsumoController::class);
        Route::get('movimientos/reporte', [MovimientoController::class, 'reporte'])->name('movimientos.reporte');
        Route::resource('movimientos', MovimientoController::class)->only(['index', 'create', 'store']);
        Route::get('productos/{producto}/receta', [RecetaController::class, 'edit'])->name('recetas.edit');
        Route::put('productos/{producto}/receta', [RecetaController::class, 'update'])->name('recetas.update');
        Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    });
    
    // Rutas para Cajero (todo menos usuarios)
    Route::middleware(['role:' . Rol::CAJERO . ',' . Rol::ADMIN])->prefix('seller')->group(function () {
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
