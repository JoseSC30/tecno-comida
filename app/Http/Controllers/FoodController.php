<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Category;
use App\Http\Requests\FoodRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FoodController extends Controller
{
    public function index(): Response
    {
        $foods = Food::with('category')->latest()->get();
        return Inertia::render('Foods/Index', [
            'productos' => $foods,
        ]);
    }

    public function create(): Response
    {
        $categories = Category::all();
        return Inertia::render('Foods/Create', [
            'categorias' => $categories,
        ]);
    }

    public function store(FoodRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('foods', 'public');
        }

        $validated['available'] = $request->boolean('available', true);

        Food::create($validated);

        return redirect()->route('foods.index')->with('success', 'Comida creada exitosamente');
    }

    public function edit(Food $food): Response
    {
        $categories = Category::all();
        return Inertia::render('Foods/Edit', [
            'producto' => $food->load('category'),
            'categorias' => $categories,
        ]);
    }

    public function update(FoodRequest $request, Food $food)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('foods', 'public');
        }

        $validated['available'] = $request->boolean('available', $food->available);

        $food->update($validated);

        return redirect()->route('foods.index')->with('success', 'Comida actualizada exitosamente');
    }

    public function destroy(Food $food)
    {
        $food->delete();
        return redirect()->route('foods.index')->with('success', 'Comida eliminada exitosamente');
    }
}
