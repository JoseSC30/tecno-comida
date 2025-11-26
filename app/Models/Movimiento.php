<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movimiento extends Model
{
    public $timestamps = false;

    protected $table = 'movimientos';
    protected $primaryKey = 'mov_id';

    protected $fillable = [
        'mov_tipo',
        'mov_cantidad',
        'mov_unidad',
        'mov_fecha',
        'ins_id',
    ];

    protected $hidden = [
        'mov_id',
        'mov_tipo',
        'mov_cantidad',
        'mov_unidad',
        'mov_fecha',
        'ins_id',
    ];

    protected $appends = [
        'id',
        'tipo',
        'cantidad',
        'unidad',
        'fecha',
        'insumo_id',
    ];

    protected function casts(): array
    {
        return [
            'mov_fecha' => 'datetime',
            'mov_cantidad' => 'decimal:2',
        ];
    }

    // Accessors
    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->mov_id,
            set: fn($value) => ['mov_id' => $value],
        );
    }

    protected function tipo(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->mov_tipo,
            set: fn($value) => ['mov_tipo' => $value],
        );
    }

    protected function cantidad(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->mov_cantidad,
            set: fn($value) => ['mov_cantidad' => $value],
        );
    }

    protected function unidad(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->mov_unidad,
            set: fn($value) => ['mov_unidad' => $value],
        );
    }

    protected function fecha(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->mov_fecha,
            set: fn($value) => ['mov_fecha' => $value],
        );
    }

    protected function insumoId(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->ins_id,
            set: fn($value) => ['ins_id' => $value],
        );
    }

    public function getRouteKeyName(): string
    {
        return 'mov_id';
    }

    // Relationships
    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'ins_id', 'ins_id');
    }
}
