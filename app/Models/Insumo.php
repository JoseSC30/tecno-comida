<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Insumo extends Model
{
    protected $table = 'insumos';
    protected $primaryKey = 'ins_id';
    public $timestamps = false;

    public function getRouteKeyName(): string
    {
        return 'ins_id';
    }

    protected $fillable = [
        'ins_nombre',
        'ins_unidad',
        'ins_stock',
    ];

    protected $casts = [
        'ins_stock' => 'decimal:2',
    ];

    protected $hidden = [
        'ins_id',
        'ins_nombre',
        'ins_unidad',
        'ins_stock',
    ];

    protected $appends = [
        'id',
        'name',
        'unidad',
        'stock',
    ];

    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['ins_id'] ?? null,
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['ins_nombre'] ?? null,
            set: fn ($value) => ['ins_nombre' => $value],
        );
    }

    protected function unidad(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['ins_unidad'] ?? null,
            set: fn ($value) => ['ins_unidad' => $value],
        );
    }

    protected function stock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['ins_stock'] ?? 0,
            set: fn ($value) => ['ins_stock' => $value],
        );
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'receta', 'ins_id', 'pro_id')
            ->withPivot('rec_cantidad', 'rec_unidad');
    }
}
