<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function index(): Response
    {
        $foods = Food::with('category')
            ->where('pro_disponible', true)
            ->latest()
            ->get();

        $categories = Category::withCount(['foods' => function ($query) {
            $query->where('pro_disponible', true);
        }])->get();

        return Inertia::render('Menu/Index', [
            'productos' => $foods,
            'categorias' => $categories,
        ]);
    }
}
