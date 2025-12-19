<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Insumo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class MovimientoController extends Controller
{
    public function index(): Response
    {
        $movimientos = Movimiento::with('insumo')
            ->orderBy('mov_fecha', 'desc')
            ->get()
            ->map(function ($movimiento) {
                return [
                    'id' => $movimiento->id,
                    'tipo' => $movimiento->tipo,
                    'cantidad' => $movimiento->cantidad,
                    'unidad' => $movimiento->unidad,
                    'fecha' => $movimiento->fecha->format('Y-m-d H:i:s'),
                    'insumo_id' => $movimiento->insumo_id,
                    'insumo_name' => $movimiento->insumo->name,
                ];
            });

        return Inertia::render('Movimientos/Index', [
            'movimientos' => $movimientos,
        ]);
    }

    public function reporte()
    {
        $movimientos = Movimiento::with('insumo')
            ->orderBy('mov_fecha', 'desc')
            ->get()
            ->map(function ($movimiento) {
                return [
                    'fecha' => $movimiento->fecha->format('d/m/Y H:i'),
                    'insumo' => $movimiento->insumo->name,
                    'tipo' => ucfirst($movimiento->tipo),
                    'cantidad' => $movimiento->cantidad,
                    'unidad' => $movimiento->unidad,
                ];
            });

        $pdf = Pdf::loadView('reports.movimientos', [
            'movimientos' => $movimientos,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('movimientos.pdf');
    }

    public function create(): Response
    {
        $insumos = Insumo::all()->map(function ($insumo) {
            return [
                'id' => $insumo->id,
                'name' => $insumo->name,
                'unidad' => $insumo->unidad,
                'stock' => $insumo->stock,
            ];
        });

        return Inertia::render('Movimientos/Create', [
            'insumos' => $insumos,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'insumo_id' => 'required|exists:insumos,ins_id',
            'tipo' => 'required|in:entrada,salida',
            'cantidad' => 'required|numeric|min:0.01',
            'unidad' => 'required|string|max:50',
        ]);

        // Obtener el insumo
        $insumo = Insumo::where('ins_id', $validated['insumo_id'])->firstOrFail();

        // Crear el movimiento
        Movimiento::create([
            'mov_tipo' => $validated['tipo'],
            'mov_cantidad' => $validated['cantidad'],
            'mov_unidad' => $validated['unidad'],
            'ins_id' => $validated['insumo_id'],
        ]);

        // Actualizar el stock del insumo
        if ($validated['tipo'] === 'entrada') {
            $insumo->ins_stock += $validated['cantidad'];
        } else { // salida
            $insumo->ins_stock -= $validated['cantidad'];
        }
        $insumo->save();

        return redirect()->route('movimientos.index')
            ->with('success', 'Movimiento registrado exitosamente.');
    }
}
