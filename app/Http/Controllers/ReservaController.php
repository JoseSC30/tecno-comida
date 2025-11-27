<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Reserva;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReservaController extends Controller
{
    /**
     * Display the reservation page with available tables
     */
    public function index(Request $request): Response
    {
        $clienteRolId = Rol::where('rol_nombre', Rol::CLIENTE)->value('rol_id');
        
        $clientes = User::where('rol_id', $clienteRolId)
            ->orderBy('usu_nombre')
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->full_name,
            ]);

        $mesas = Mesa::orderBy('mes_numero')->get()->map(fn($mesa) => [
            'id' => $mesa->mes_id,
            'numero' => $mesa->mes_numero,
            'capacidad' => $mesa->mes_capacidad,
            'icon' => $mesa->icon,
        ]);

        return Inertia::render('Reservas/Index', [
            'clientes' => $clientes,
            'mesas' => $mesas,
        ]);
    }

    /**
     * Get available tables for a specific date and time
     */
    public function getAvailability(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required|string',
        ]);

        // Get all tables
        $mesas = Mesa::orderBy('mes_numero')->get();

        // Get reserved table IDs for the specified date and time
        $reservedTableIds = Reserva::where('res_fecha', $validated['fecha'])
            ->where('res_hora', $validated['hora'])
            ->whereIn('res_estado', ['pendiente', 'confirmada', 'pagada', 'pagada_parcial'])
            ->pluck('mes_id')
            ->toArray();

        $availability = $mesas->map(fn($mesa) => [
            'id' => $mesa->mes_id,
            'numero' => $mesa->mes_numero,
            'capacidad' => $mesa->mes_capacidad,
            'icon' => $mesa->icon,
            'disponible' => !in_array($mesa->mes_id, $reservedTableIds),
        ]);

        return response()->json([
            'success' => true,
            'mesas' => $availability,
        ]);
    }

    /**
     * Store a new reservation
     */
    public function store(Request $request)
    {
        \Log::info('Reserva store request:', $request->all());

        $rules = [
            'cliente_id' => 'nullable|exists:usuarios,usu_id',
            'mes_id' => 'required|exists:mesas,mes_id',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required|string',
            'personas' => 'required|integer|min:1|max:20',
            'notas' => 'nullable|string|max:500',
            'metodo_pago' => 'required|in:efectivo,qr',
            'tipo_pago' => 'required|in:completo,cuotas',
            'num_cuotas' => 'required_if:tipo_pago,cuotas|in:1,2',
        ];

        // Solo validar transaction_id si es pago QR
        if ($request->input('metodo_pago') === 'qr') {
            $rules['transaction_id'] = 'required';
        }

        $validated = $request->validate($rules);

        // Si el usuario es cliente, forzar que la reserva sea para sí mismo
        $user = $request->user();
        if ($user->isCliente()) {
            $validated['cliente_id'] = $user->id;
        }

        // Check if table is available
        $isReserved = Reserva::where('mes_id', $validated['mes_id'])
            ->where('res_fecha', $validated['fecha'])
            ->where('res_hora', $validated['hora'])
            ->whereIn('res_estado', ['pendiente', 'confirmada', 'pagada', 'pagada_parcial'])
            ->exists();

        if ($isReserved) {
            return back()->withErrors(['mes_id' => 'Esta mesa ya está reservada para la fecha y hora seleccionada.']);
        }

        // Check capacity
        $mesa = Mesa::findOrFail($validated['mes_id']);
        if ($validated['personas'] > $mesa->mes_capacidad) {
            return back()->withErrors(['personas' => 'El número de personas excede la capacidad de la mesa.']);
        }

        // Calculate reservation cost (example: 20 Bs per person)
        $costoPorPersona = 20;
        $monto = $validated['personas'] * $costoPorPersona;

        // Determine payment type and calculate installments
        $tipoPago = $validated['tipo_pago'];
        $numCuotas = $tipoPago === Reserva::TIPO_PAGO_CUOTAS ? 2 : 1;
        $montoCuota = $monto / $numCuotas;

        // Calculate initial payment based on type
        $montoPagado = 0;
        $cuotasPagadas = 0;

        if ($validated['metodo_pago'] === 'qr') {
            // QR payment - first installment is paid
            $montoPagado = $montoCuota;
            $cuotasPagadas = 1;
        }

        // Determine state based on payment type and method
        if ($validated['metodo_pago'] === 'qr') {
            if ($tipoPago === Reserva::TIPO_PAGO_COMPLETO) {
                $estado = Reserva::ESTADO_PAGADA;
                $montoPagado = $monto; // Full amount paid
            } else {
                $estado = Reserva::ESTADO_PARCIAL; // Only first installment paid
            }
        } else {
            $estado = Reserva::ESTADO_CONFIRMADA; // Cash payment - not paid yet
        }

        $reserva = Reserva::create([
            'res_fecha' => $validated['fecha'],
            'res_hora' => $validated['hora'],
            'res_estado' => $estado,
            'res_monto' => $monto,
            'res_transaction_id' => $validated['transaction_id'] ?? null,
            'res_personas' => $validated['personas'],
            'res_notas' => $validated['notas'] ?? null,
            'usu_id' => $validated['cliente_id'] ?? $request->user()->id,
            'mes_id' => $validated['mes_id'],
            // New installment fields
            'res_tipo_pago' => $tipoPago,
            'res_num_cuotas' => $numCuotas,
            'res_monto_cuota' => $montoCuota,
            'res_monto_pagado' => $montoPagado,
            'res_cuotas_pagadas' => $cuotasPagadas,
        ]);

        $message = '¡Reserva creada exitosamente!';
        if ($tipoPago === Reserva::TIPO_PAGO_CUOTAS && $validated['metodo_pago'] === 'qr') {
            $message .= ' Has pagado la primera cuota de Bs ' . number_format($montoCuota, 2) . '. La segunda cuota deberá pagarse antes de tu reserva.';
        }

        return redirect()->route('reservas.list')->with('success', $message);
    }

    /**
     * Pay second installment for a reservation
     */
    public function paySecondInstallment(Request $request, $id)
    {
        \Log::info('paySecondInstallment called', [
            'id' => $id,
            'user_id' => $request->user()->id,
            'user_role' => $request->user()->rol_id,
            'data' => $request->all(),
        ]);

        // First try to find the reservation
        $reserva = Reserva::find($id);
        
        if (!$reserva) {
            \Log::error('Reservation not found', ['id' => $id]);
            return response()->json(['error' => 'Reserva no encontrada'], 404);
        }

        \Log::info('Reservation found', [
            'res_id' => $reserva->res_id,
            'usu_id' => $reserva->usu_id,
            'current_user_id' => $request->user()->id,
            'res_estado' => $reserva->res_estado,
            'res_tipo_pago' => $reserva->res_tipo_pago,
            'res_cuotas_pagadas' => $reserva->res_cuotas_pagadas,
        ]);

        // Check ownership - allow if user owns it OR if user is admin/seller
        $user = $request->user();
        $isOwner = $reserva->usu_id == $user->id;
        $isAdmin = $user->isAdmin() || $user->isSeller();
        
        if (!$isOwner && !$isAdmin) {
            \Log::error('User not authorized', [
                'user_id' => $user->id,
                'reserva_usu_id' => $reserva->usu_id,
            ]);
            return response()->json(['error' => 'No tienes permiso para esta reserva'], 403);
        }

        // Verify reservation can receive second payment
        if (!$reserva->tieneCuotaPendiente()) {
            \Log::error('No pending installment', [
                'tipo_pago' => $reserva->res_tipo_pago,
                'cuotas_pagadas' => $reserva->res_cuotas_pagadas,
                'num_cuotas' => $reserva->res_num_cuotas,
            ]);
            return response()->json(['error' => 'Esta reserva no tiene cuotas pendientes'], 400);
        }

        $validated = $request->validate([
            'transaction_id' => 'required|string',
        ]);

        $reserva->update([
            'res_cuotas_pagadas' => 2,
            'res_monto_pagado' => $reserva->res_monto, // Full amount now paid
            'res_transaction_id_2' => $validated['transaction_id'],
            'res_fecha_pago_2' => now(),
            'res_estado' => Reserva::ESTADO_PAGADA,
        ]);

        \Log::info('Second installment paid successfully', ['res_id' => $reserva->res_id]);

        return response()->json(['success' => true, 'message' => '¡Segunda cuota pagada exitosamente!']);
    }

    /**
     * Display all reservations (admin view)
     */
    public function list(Request $request): Response
    {
        $user = $request->user();
        
        $query = Reserva::with(['usuario', 'mesa']);

        // Cliente solo ve sus reservas
        if ($user->isCliente()) {
            $query->where('usu_id', $user->id);
        }

        $reservas = $query->orderBy('res_fecha', 'desc')
            ->orderBy('res_hora', 'desc')
            ->get()
            ->map(fn($reserva) => [
                'id' => $reserva->res_id,
                'fecha' => $reserva->res_fecha->format('Y-m-d'),
                'hora' => substr($reserva->res_hora, 0, 5),
                'estado' => $reserva->res_estado,
                'monto' => $reserva->res_monto,
                'personas' => $reserva->res_personas,
                'notas' => $reserva->res_notas,
                'cliente' => $reserva->usuario?->full_name ?? 'Sin cliente',
                'mesa' => [
                    'numero' => $reserva->mesa->mes_numero,
                    'capacidad' => $reserva->mesa->mes_capacidad,
                ],
                // Installment payment fields
                'tipo_pago' => $reserva->res_tipo_pago,
                'num_cuotas' => $reserva->res_num_cuotas,
                'monto_cuota' => $reserva->res_monto_cuota,
                'monto_pagado' => $reserva->res_monto_pagado,
                'cuotas_pagadas' => $reserva->res_cuotas_pagadas,
            ])
            ->values()
            ->toArray();

        return Inertia::render('Reservas/List', [
            'reservas' => $reservas,
        ]);
    }

    /**
     * Update reservation status
     */
    public function updateStatus(Request $request, Reserva $reserva)
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,confirmada,pagada,pagada_parcial,cancelada,completada',
        ]);

        $reserva->update([
            'res_estado' => $validated['estado'],
        ]);

        return back()->with('success', 'Estado de reserva actualizado.');
    }

    /**
     * Cancel a reservation
     */
    public function cancel(Reserva $reserva)
    {
        $reserva->update([
            'res_estado' => 'cancelada',
        ]);

        return back()->with('success', 'Reserva cancelada.');
    }
}
