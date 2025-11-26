<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Http\Requests\ProductoRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductoController extends Controller
{
    public function index(): Response
    {
        $productos = Producto::with('category')->latest()->get();
        return Inertia::render('Productos/Index', [
            'productos' => $productos,
        ]);
    }

    public function create(): Response
    {
        $categories = Categoria::all();
        return Inertia::render('Productos/Create', [
            'categorias' => $categories,
        ]);
    }

    public function store(ProductoRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['available'] = $request->boolean('available', true);

        $data = [
            'pro_nombre'      => $validated['name'],
            'pro_descripcion' => $validated['description'] ?? null,
            'pro_precioventa' => $validated['price'],
            'pro_costounit'   => $validated['cost'] ?? 0,
            'pro_imagen'      => $validated['image'] ?? null,
            'pro_disponible'  => $validated['available'] ?? true,
            'cat_id'          => $validated['category_id'],
        ];

        Producto::create($data);

        return redirect()->route('productos.index')->with('success', 'Producto creado exitosamente');
    }

    public function edit(Producto $producto): Response
    {
        $categories = Categoria::all();
        return Inertia::render('Productos/Edit', [
            'producto' => $producto->load('category'),
            'categorias' => $categories,
        ]);
    }

    public function update(ProductoRequest $request, $id)
    {
        $producto = Producto::where('pro_id', $id)->firstOrFail();
        $validated = $request->validated();

        $imagePath = $producto->pro_imagen;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $producto->pro_nombre = $validated['name'];
        $producto->pro_descripcion = $validated['description'] ?? null;
        $producto->pro_precioventa = $validated['price'];
        $producto->pro_costounit = $validated['cost'] ?? $producto->pro_costounit;
        $producto->pro_imagen = $imagePath;
        $producto->pro_disponible = $request->boolean('available', $producto->available);
        $producto->cat_id = $validated['category_id'];
        $producto->save();

        return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente');
    }
}
