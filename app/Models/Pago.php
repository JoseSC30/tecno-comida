<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pago extends Model
{
    protected $table = 'pagos';
    protected $primaryKey = 'pag_id';
    public $timestamps = false;

    // Métodos de pago
    const METODO_QR = 'QR';
    const METODO_EFECTIVO = 'EFECTIVO';

    protected $fillable = [
        'pag_metodo',
    ];

    /**
     * Get the detalle_pagos for this pago
     */
    public function detallePagos(): HasMany
    {
        return $this->hasMany(DetallePago::class, 'pag_id', 'pag_id');
    }

    /**
     * Get or create a Pago by method
     */
    public static function getOrCreateByMetodo(string $metodo): self
    {
        return self::firstOrCreate(['pag_metodo' => $metodo]);
    }
}
