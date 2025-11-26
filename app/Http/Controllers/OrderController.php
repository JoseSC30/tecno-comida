<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Producto;
use App\Http\Requests\OrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
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

    public function store(OrderRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated) {
            $total = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
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

            $order = Order::create([
                'user_id' => $request->user()->id,
                'total' => $total,
                'status' => 'pendiente',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($orderItems as $orderItem) {
                $order->items()->create($orderItem);
            }
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
