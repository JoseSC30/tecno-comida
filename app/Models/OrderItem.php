<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $table = 'detalle_pedidos';
    protected $primaryKey = 'dpe_id';

    protected $fillable = [
        'order_id',
        'food_id',
        'quantity',
        'unit_price',
        'subtotal',
        'ped_id',
        'pro_id',
        'dpe_cantidad',
        'dpe_preciounit',
        'dpe_subtotal',
    ];

    protected $casts = [
        'dpe_cantidad' => 'integer',
        'dpe_preciounit' => 'decimal:2',
        'dpe_subtotal' => 'decimal:2',
    ];

    protected $hidden = [
        'dpe_id',
        'ped_id',
        'pro_id',
        'dpe_cantidad',
        'dpe_preciounit',
        'dpe_subtotal',
    ];

    protected $appends = [
        'id',
        'order_id',
        'food_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['dpe_id'] ?? null,
        );
    }

    protected function orderId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['ped_id'] ?? null,
            set: fn ($value) => ['ped_id' => $value],
        );
    }

    protected function foodId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['pro_id'] ?? null,
            set: fn ($value) => ['pro_id' => $value],
        );
    }

    protected function quantity(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) ($this->attributes['dpe_cantidad'] ?? 0),
            set: fn ($value) => ['dpe_cantidad' => (int) $value],
        );
    }

    protected function unitPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => isset($this->attributes['dpe_preciounit'])
                ? (float) $this->attributes['dpe_preciounit']
                : null,
            set: fn ($value) => ['dpe_preciounit' => $value],
        );
    }

    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: fn () => isset($this->attributes['dpe_subtotal'])
                ? (float) $this->attributes['dpe_subtotal']
                : null,
            set: fn ($value) => ['dpe_subtotal' => $value],
        );
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'ped_id', 'ped_id');
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class, 'pro_id', 'pro_id');
    }
}
