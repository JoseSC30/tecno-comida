<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Food;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $data = [];

        if ($user->isAdmin()) {
            $data = $this->getAdminData();
        } elseif ($user->isVendedor()) {
            $data = $this->getVendedorData();
        } else {
            $data = $this->getClienteData($user);
        }

        return Inertia::render('Dashboard', $data);
    }

    private function getAdminData(): array
    {
        return [
            'estadisticas' => array_merge(
                [
                    'total_usuarios' => User::count(),
                    'total_productos' => $this->hasFoods() ? Food::count() : 0,
                    'total_categorias' => $this->hasCategories() ? Category::count() : 0,
                ],
                $this->hasOrders() ? [
                    'total_pedidos' => Order::count(),
                    'pedidos_pendientes' => Order::where('ped_estado', 'pendiente')->count(),
                    'ingresos_totales' => Order::where('ped_estado', 'entregado')->sum('ped_total'),
                ] : $this->defaultAdminOrderStats()
            ),
            'pedidos_recientes' => $this->hasOrders()
                ? Order::with(['user', 'items'])->latest()->take(5)->get()
                : [],
            'productos_populares' => $this->canLoadPopularFoods()
                ? Food::withCount('orderItems')->orderBy('order_items_count', 'desc')->take(5)->get()
                : [],
        ];
    }

    private function getVendedorData(): array
    {
        return [
            'estadisticas' => array_merge(
                [
                    'total_productos' => $this->hasFoods() ? Food::count() : 0,
                ],
                $this->hasOrders() ? [
                    'total_pedidos' => Order::count(),
                    'pedidos_pendientes' => Order::where('ped_estado', 'pendiente')->count(),
                    'pedidos_preparacion' => Order::where('ped_estado', 'preparando')->count(),
                ] : $this->defaultVendedorOrderStats()
            ),
            'pedidos_recientes' => $this->hasOrders()
                ? Order::with(['user', 'items'])->latest()->take(10)->get()
                : [],
            'productos_populares' => $this->canLoadPopularFoods()
                ? Food::withCount('orderItems')->orderBy('order_items_count', 'desc')->take(5)->get()
                : [],
        ];
    }

    private function getClienteData(User $user): array
    {
        return [
            'estadisticas' => $this->hasOrders() ? [
                'mis_pedidos' => Order::where('usu_id', $user->id)->count(),
                'pedidos_pendientes' => Order::where('usu_id', $user->id)->where('ped_estado', 'pendiente')->count(),
                'gasto_total' => Order::where('usu_id', $user->id)->where('ped_estado', 'entregado')->sum('ped_total'),
            ] : $this->defaultClienteStats(),
            'pedidos_recientes' => $this->hasOrders()
                ? Order::where('usu_id', $user->id)->with('items.food')->latest()->take(5)->get()
                : [],
            'productos_destacados' => $this->hasFoods()
                ? Food::where('pro_disponible', true)->inRandomOrder()->take(6)->get()
                : [],
        ];
    }

    private function canLoadPopularFoods(): bool
    {
        return $this->hasFoods() && $this->hasOrderItems();
    }

    private function hasOrders(): bool
    {
        return Schema::hasTable('pedidos');
    }

    private function hasOrderItems(): bool
    {
        return Schema::hasTable('detalle_pedidos');
    }

    private function hasFoods(): bool
    {
        return Schema::hasTable('productos');
    }

    private function hasCategories(): bool
    {
        return Schema::hasTable('categorias');
    }

    private function defaultAdminOrderStats(): array
    {
        return [
            'total_pedidos' => 0,
            'pedidos_pendientes' => 0,
            'ingresos_totales' => 0,
        ];
    }

    private function defaultVendedorOrderStats(): array
    {
        return [
            'total_pedidos' => 0,
            'pedidos_pendientes' => 0,
            'pedidos_preparacion' => 0,
        ];
    }

    private function defaultClienteStats(): array
    {
        return [
            'mis_pedidos' => 0,
            'pedidos_pendientes' => 0,
            'gasto_total' => 0,
        ];
    }
}
