<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Requests\CategoriaRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoriaController extends Controller
{
    public function index(): Response
    {
        $categories = Categoria::withCount('foods')->latest()->get();
        return Inertia::render('Categorias/Index', [
            'categorias' => $categories,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Categorias/Create');
    }

    public function store(CategoriaRequest $request)
    {
        $validated = $request->validated();
        
        Categoria::create([
            'cat_nombre' => $validated['name'],
            'cat_descripcion' => $validated['description'] ?? null,
        ]);
        
        return redirect()->route('categorias.index')->with('success', 'Categoría creada exitosamente');
    }

    public function edit($id): Response
    {
        $category = Categoria::where('cat_id', $id)->firstOrFail();
        return Inertia::render('Categorias/Edit', [
            'categoria' => $category,
        ]);
    }

    public function update(CategoriaRequest $request, $id)
    {
        $category = Categoria::where('cat_id', $id)->firstOrFail();
        $validated = $request->validated();
        
        $category->cat_nombre = $validated['name'];
        $category->cat_descripcion = $validated['description'] ?? null;
        $category->save();
        
        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada exitosamente');
    }

    public function destroy($id)
    {
        $category = Categoria::where('cat_id', $id)->firstOrFail();
        $category->delete();
        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada exitosamente');
    }
}
