<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'pedidos';
    protected $primaryKey = 'ped_id';
    public const CREATED_AT = 'ped_fecha';
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'total',
        'status',
        'notes',
        'usu_id',
        'ped_total',
        'ped_estado',
        'ped_notas',
    ];

    protected $casts = [
        'ped_total' => 'decimal:2',
        'ped_fecha' => 'datetime',
    ];

    protected $hidden = [
        'ped_id',
        'ped_total',
        'ped_estado',
        'ped_notas',
        'usu_id',
    ];

    protected $appends = [
        'id',
        'user_id',
        'total',
        'status',
        'notes',
        'created_at',
    ];

    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['ped_id'] ?? null,
        );
    }

    protected function userId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['usu_id'] ?? null,
            set: fn ($value) => ['usu_id' => $value],
        );
    }

    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn () => isset($this->attributes['ped_total'])
                ? (float) $this->attributes['ped_total']
                : null,
            set: fn ($value) => ['ped_total' => $value],
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['ped_estado'] ?? null,
            set: fn ($value) => ['ped_estado' => $value],
        );
    }

    protected function notes(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['ped_notas'] ?? null,
            set: fn ($value) => ['ped_notas' => $value],
        );
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['ped_fecha'] ?? null,
            set: fn ($value) => ['ped_fecha' => $value],
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usu_id', 'usu_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'ped_id', 'ped_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pendiente';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'entregado';
    }
}
