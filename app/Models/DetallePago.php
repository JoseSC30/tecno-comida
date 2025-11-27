<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetallePago extends Model
{
    protected $table = 'detalle_pagos';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'pag_id',
        'ped_id',
        'dpa_fecha',
        'dpa_monto',
    ];

    protected $casts = [
        'dpa_fecha' => 'datetime',
        'dpa_monto' => 'decimal:2',
    ];

    /**
     * Get the pago that owns this detalle
     */
    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'pag_id', 'pag_id');
    }

    /**
     * Get the pedido (order) that owns this detalle
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'ped_id', 'ped_id');
    }

    /**
     * Register a payment for an order
     */
    public static function registrarPago(int $pedidoId, string $metodoPago, float $monto): self
    {
        // Get or create the payment method
        $pago = Pago::getOrCreateByMetodo($metodoPago);

        // Create the payment detail
        return self::create([
            'pag_id' => $pago->pag_id,
            'ped_id' => $pedidoId,
            'dpa_fecha' => now(),
            'dpa_monto' => $monto,
        ]);
    }
}
