<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function index(): Response
    {
        $products = Producto::with('category')
            ->where('pro_disponible', true)
            ->latest()
            ->get();

        $categories = Categoria::withCount(['foods' => function ($query) {
            $query->where('pro_disponible', true);
        }])->get();

        return Inertia::render('Menu/Index', [
            'productos' => $products,
            'categorias' => $categories,
        ]);
    }
}
