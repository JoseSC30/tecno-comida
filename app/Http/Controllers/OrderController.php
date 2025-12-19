<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Producto;
use App\Models\Pago;
use App\Models\DetallePago;
use App\Models\Menu;
use App\Http\Requests\OrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function checkout(Request $request): Response
    {
        $clienteRolId = \App\Models\Rol::where('rol_nombre', \App\Models\Rol::CLIENTE)->value('rol_id');
        
        $users = \App\Models\User::where('rol_id', $clienteRolId)
            ->orderBy('usu_nombre')
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->full_name,
            ]);

        return Inertia::render('Checkout', [
            'clientes' => $users,
        ]);
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $query = Order::with(['user', 'items.food']);

        // Cliente solo ve sus pedidos
        if ($user->isCliente()) {
            $query->where('usu_id', $user->id);
        }

        $orders = $query->latest()->get();

        return Inertia::render('Orders/Index', [
            'pedidos' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        $user = $request->user();

        // Cliente solo puede ver sus propios pedidos
        if ($user->isCliente() && $order->user_id !== $user->id) {
            abort(403, 'No tienes permiso para ver este pedido');
        }

        return Inertia::render('Orders/Show', [
            'pedido' => $order->load(['user', 'items.food']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'nullable|exists:usuarios,usu_id',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:product,combo',
            'items.*.food_id' => 'required_if:items.*.type,product|nullable|exists:productos,pro_id',
            'items.*.menu_id' => 'required_if:items.*.type,combo|nullable|exists:menus,men_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.components' => 'sometimes|array',
            'items.*.components.*.product_id' => 'required_with:items.*.components|exists:productos,pro_id',
            'items.*.components.*.quantity' => 'required_with:items.*.components|integer|min:1',
            'metodo_pago' => 'nullable|in:efectivo,qr',
        ]);

        $order = DB::transaction(function () use ($request, $validated) {
            $total = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                if (($item['type'] ?? 'product') === 'combo') {
                    $menu = Menu::with('products')->findOrFail($item['menu_id']);

                    if ($menu->products->isEmpty()) {
                        abort(422, 'El combo seleccionado no tiene productos asociados.');
                    }

                    $components = collect($item['components'] ?? []);
                    if ($components->isEmpty()) {
                        $components = $menu->products->mapWithKeys(fn ($product) => [
                            $product->id => $product->pivot?->det_cantidad ?? 1,
                        ]);
                    } else {
                        $components = $components->mapWithKeys(fn ($component) => [
                            $component['product_id'] => $component['quantity'] ?? 1,
                        ]);
                    }

                    $filteredProducts = $menu->products->filter(fn ($product) => $components->has($product->id));

                    if ($filteredProducts->isEmpty()) {
                        abort(422, 'El combo no tiene productos válidos.');
                    }

                    $productCount = $filteredProducts->count();
                    $baseSum = $filteredProducts->sum(function ($product) use ($components) {
                        $qty = $components->get($product->id, 1);
                        return $product->price * $qty;
                    });

                    $discount = max(0, min((float) $menu->discount, 100));
                    $discountedPerCombo = $baseSum > 0 ? round($baseSum * (1 - $discount / 100), 2) : 0;
                    $remainingPerCombo = $discountedPerCombo;

                    $filteredProducts->values()->each(function ($product, $index) use (&$remainingPerCombo, $productCount, $discountedPerCombo, $components, $item, &$orderItems, $baseSum) {
                        $qty = $components->get($product->id, 1);
                        $share = $baseSum > 0 ? ($product->price * $qty) / $baseSum : (1 / max($productCount, 1));
                        $unitPrice = $index === $productCount - 1
                            ? round($remainingPerCombo / max($qty, 1), 2)
                            : round(($discountedPerCombo * $share) / max($qty, 1), 2);
                        $remainingPerCombo -= round($unitPrice * $qty, 2);

                        $orderItems[] = [
                            'food_id' => $product->id,
                            'quantity' => $item['quantity'] * $qty,
                            'unit_price' => $unitPrice,
                            'subtotal' => round($unitPrice * $item['quantity'] * $qty, 2),
                        ];
                    });

                    $total += round($discountedPerCombo * $item['quantity'], 2);
                    continue;
                }

                $product = Producto::findOrFail($item['food_id']);
                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                $orderItems[] = [
                    'food_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            // Si el usuario es cliente, forzar que el pedido sea para sí mismo
            $user = $request->user();
            if ($user->isCliente()) {
                $userId = $user->id;
            } else {
                // Si no se seleccionó cliente, usar el usuario autenticado
                $userId = $validated['cliente_id'] ?? $user->id;
            }

            $order = Order::create([
                'user_id' => $userId,
                'total' => $total,
                'status' => 'confirmado',
                'notes' => null,
            ]);

            foreach ($orderItems as $orderItem) {
                $order->items()->create($orderItem);
            }

            // Registrar el pago en las tablas pagos y detalle_pagos
            $metodoPago = $validated['metodo_pago'] ?? 'efectivo';
            $metodoPagoDb = $metodoPago === 'qr' ? Pago::METODO_QR : Pago::METODO_EFECTIVO;
            
            DetallePago::registrarPago(
                $order->ped_id,
                $metodoPagoDb,
                $total
            );

            return $order;
        });

        return redirect()->route('orders.index')->with('success', 'Pedido creado exitosamente');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pendiente,preparando,listo,entregado,cancelado',
        ]);

        $order->update($validated);

        return redirect()->back()->with('success', 'Estado del pedido actualizado');
    }
}
