<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    protected $table = 'reservas';
    protected $primaryKey = 'res_id';
    public $timestamps = true;

    // Constantes para tipos de pago
    const TIPO_PAGO_COMPLETO = 'completo';
    const TIPO_PAGO_CUOTAS = 'cuotas';

    // Constantes para estados
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_PARCIAL = 'pagada_parcial';  // Pagó primera cuota
    const ESTADO_PAGADA = 'pagada';           // Pagó todo
    const ESTADO_CONFIRMADA = 'confirmada';   // Confirmada (efectivo)
    const ESTADO_COMPLETADA = 'completada';
    const ESTADO_CANCELADA = 'cancelada';

    protected $fillable = [
        'res_fecha',
        'res_hora',
        'res_estado',
        'res_tipo_pago',
        'res_num_cuotas',
        'res_monto',
        'res_monto_cuota',
        'res_monto_pagado',
        'res_cuotas_pagadas',
        'res_transaction_id',
        'res_transaction_id_2',
        'res_fecha_pago_2',
        'res_personas',
        'res_notas',
        'usu_id',
        'mes_id',
    ];

    protected $casts = [
        'res_fecha' => 'date',
        'res_monto' => 'decimal:2',
        'res_monto_cuota' => 'decimal:2',
        'res_monto_pagado' => 'decimal:2',
        'res_fecha_pago_2' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usu_id', 'usu_id');
    }

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'mes_id', 'mes_id');
    }

    /**
     * Check if reservation is for today
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->res_fecha->isToday();
    }

    /**
     * Get formatted time
     */
    public function getFormattedTimeAttribute(): string
    {
        return substr($this->res_hora, 0, 5);
    }

    /**
     * Check if payment is complete
     */
    public function isPagadaCompleta(): bool
    {
        return $this->res_monto_pagado >= $this->res_monto;
    }

    /**
     * Check if has pending installment
     */
    public function tieneCuotaPendiente(): bool
    {
        return $this->res_tipo_pago === self::TIPO_PAGO_CUOTAS 
            && $this->res_cuotas_pagadas < $this->res_num_cuotas;
    }

    /**
     * Get remaining amount to pay
     */
    public function getMontoPendienteAttribute(): float
    {
        return max(0, $this->res_monto - $this->res_monto_pagado);
    }

    /**
     * Get payment progress percentage
     */
    public function getPorcentajePagadoAttribute(): int
    {
        if ($this->res_monto <= 0) return 100;
        return min(100, (int)(($this->res_monto_pagado / $this->res_monto) * 100));
    }
}
