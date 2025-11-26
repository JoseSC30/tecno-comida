<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InsumoController extends Controller
{
    public function index(): Response
    {
        $insumos = Insumo::all();
        
        return Inertia::render('Insumos/Index', [
            'insumos' => $insumos
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Insumos/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'unidad' => 'required|string|max:50',
            'stock' => 'required|numeric|min:0',
        ]);

        Insumo::create([
            'ins_nombre' => $validated['name'],
            'ins_unidad' => $validated['unidad'],
            'ins_stock' => $validated['stock'],
        ]);

        return redirect()->route('insumos.index')->with('success', 'Insumo creado exitosamente');
    }

    public function edit(Insumo $insumo): Response
    {
        return Inertia::render('Insumos/Edit', [
            'insumo' => $insumo
        ]);
    }

    public function update(Request $request, Insumo $insumo)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'unidad' => 'required|string|max:50',
            'stock' => 'required|numeric|min:0',
        ]);

        $insumo->update([
            'ins_nombre' => $validated['name'],
            'ins_unidad' => $validated['unidad'],
            'ins_stock' => $validated['stock'],
        ]);

        return redirect()->route('insumos.index')->with('success', 'Insumo actualizado exitosamente');
    }

    public function destroy(Insumo $insumo)
    {
        $insumo->delete();
        return redirect()->route('insumos.index')->with('success', 'Insumo eliminado exitosamente');
    }
}
