<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Insumo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class RecetaController extends Controller
{
    public function edit(Producto $producto): Response
    {
        $producto->load('insumos');
        $insumos = Insumo::all();
        
        return Inertia::render('Recetas/Edit', [
            'producto' => $producto,
            'insumos' => $insumos,
            'receta' => $producto->insumos->map(function ($insumo) {
                return [
                    'insumo_id' => $insumo->id,
                    'insumo_name' => $insumo->name,
                    'cantidad' => $insumo->pivot->rec_cantidad,
                    'unidad' => $insumo->pivot->rec_unidad,
                ];
            })
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'receta' => 'required|array',
            'receta.*.insumo_id' => 'required|exists:insumos,ins_id',
            'receta.*.cantidad' => 'required|numeric|min:0',
            'receta.*.unidad' => 'required|string|max:120',
        ]);

        // Eliminar receta anterior
        DB::table('receta')->where('pro_id', $producto->pro_id)->delete();

        // Insertar nueva receta
        foreach ($validated['receta'] as $item) {
            DB::table('receta')->insert([
                'pro_id' => $producto->pro_id,
                'ins_id' => $item['insumo_id'],
                'rec_cantidad' => $item['cantidad'],
                'rec_unidad' => $item['unidad'],
            ]);
        }

        return redirect()->route('productos.index')->with('success', 'Receta actualizada exitosamente');
    }
}
