<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2'],
        ]);

        $term = trim($validated['q']);
        $operator = $this->likeOperator();
        $user = $request->user();

        $products = Producto::query()
            ->with('category:cat_id,cat_nombre,cat_descripcion')
            ->where('pro_disponible', true)
            ->where(function ($query) use ($term, $operator) {
                $query->where('pro_nombre', $operator, "%{$term}%")
                    ->orWhere('pro_descripcion', $operator, "%{$term}%");
            })
            ->limit(5)
            ->get();

        $categories = Categoria::query()
            ->where(function ($query) use ($term, $operator) {
                $query->where('cat_nombre', $operator, "%{$term}%")
                    ->orWhere('cat_descripcion', $operator, "%{$term}%");
            })
            ->limit(5)
            ->get();

        $orders = collect();
        if ($user->isAdmin() || $user->isVendedor()) {
            $orders = Order::query()
                ->with('user:usu_id,usu_nombre,usu_email')
                ->where(function ($query) use ($term, $operator) {
                    $query->where('ped_estado', $operator, "%{$term}%")
                        ->orWhereHas('user', function ($subQuery) use ($term, $operator) {
                            $subQuery->where('usu_nombre', $operator, "%{$term}%");
                        });

                    if (is_numeric($term)) {
                        $query->orWhere('ped_id', (int) $term);
                    }
                })
                ->limit(5)
                ->get();
        }

        $users = collect();
        if ($user->isAdmin()) {
            $users = User::query()
                ->selectRaw('usu_id as id, usu_nombre as name, usu_email as email')
                ->where(function ($query) use ($term, $operator) {
                    $query->where('usu_nombre', $operator, "%{$term}%")
                        ->orWhere('usu_email', $operator, "%{$term}%");
                })
                ->limit(5)
                ->get();
        }

        return response()->json([
            'productos' => $products,
            'categorias' => $categories,
            'pedidos' => $orders,
            'usuarios' => $users,
        ]);
    }

    private function likeOperator(): string
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        return $driver === 'pgsql' ? 'ILIKE' : 'LIKE';
    }
}
