<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function combos(Request $request): Response
    {
        $combos = Menu::with(['products' => fn ($query) => $query->where('pro_disponible', true)->with('category')])
            ->orderByDesc('men_fini')
            ->get();

        $productos = Producto::with('category')
            ->where('pro_disponible', true)
            ->orderBy('pro_nombre')
            ->get();

        return Inertia::render('Menu/Combos', [
            'combos' => $combos,
            'productos' => $productos,
            'canCreate' => !$request->user()->isCliente(),
        ]);
    }

    public function storeCombo(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:ALMUERZO,CENA,INDEFINIDO,ESPECIAL',
            'estado' => 'required|in:activo,finalizado',
            'descuento' => 'required|numeric|min:0|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,pro_id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            $menu = Menu::create([
                'men_nombre' => $validated['name'],
                'men_descripcion' => $validated['description'] ?? null,
                'men_tipo' => $validated['type'],
                'men_estado' => $validated['estado'],
                'men_descuento' => $validated['descuento'],
                'men_fini' => $validated['fecha_inicio'],
                'men_ffin' => $validated['fecha_fin'],
            ]);

            $syncData = collect($validated['productos'])->mapWithKeys(function ($prod) {
                return [$prod['id'] => ['det_cantidad' => $prod['cantidad']]];
            })->all();

            $menu->products()->sync($syncData);
        });

        return redirect()->route('menu.combos')->with('success', 'Combo creado correctamente');
    }

    public function updateCombo(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:ALMUERZO,CENA,INDEFINIDO,ESPECIAL',
            'estado' => 'required|in:activo,finalizado',
            'descuento' => 'required|numeric|min:0|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,pro_id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($menu, $validated) {
            $menu->update([
                'men_nombre' => $validated['name'],
                'men_descripcion' => $validated['description'] ?? null,
                'men_tipo' => $validated['type'],
                'men_estado' => $validated['estado'],
                'men_descuento' => $validated['descuento'],
                'men_fini' => $validated['fecha_inicio'],
                'men_ffin' => $validated['fecha_fin'],
            ]);

            $syncData = collect($validated['productos'])->mapWithKeys(function ($prod) {
                return [$prod['id'] => ['det_cantidad' => $prod['cantidad']]];
            })->all();

            $menu->products()->sync($syncData);
        });

        return redirect()->route('menu.combos')->with('success', 'Combo actualizado correctamente');
    }

    public function destroyCombo(Menu $menu)
    {
        DB::transaction(function () use ($menu) {
            $menu->products()->detach();
            $menu->delete();
        });

        return redirect()->route('menu.combos')->with('success', 'Combo eliminado correctamente');
    }
}
