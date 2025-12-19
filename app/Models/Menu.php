<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Menu extends Model
{
    protected $table = 'menus';
    protected $primaryKey = 'men_id';
    public $timestamps = false;

    protected $fillable = [
        'men_nombre',
        'men_descripcion',
        'men_tipo',
        'men_fini',
        'men_ffin',
        'men_estado',
        'men_descuento',
    ];

    protected $casts = [
        'men_descuento' => 'decimal:2',
        'men_fini' => 'date',
        'men_ffin' => 'date',
    ];

    protected $hidden = [
        'men_id',
        'men_nombre',
        'men_descripcion',
        'men_tipo',
        'men_fini',
        'men_ffin',
        'men_estado',
        'men_descuento',
    ];

    protected $appends = [
        'id',
        'name',
        'description',
        'type',
        'start_date',
        'end_date',
        'state',
        'discount',
    ];

    public function getRouteKeyName(): string
    {
        return 'men_id';
    }

    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['men_id'] ?? null,
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['men_nombre'] ?? null,
            set: fn ($value) => ['men_nombre' => $value],
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['men_descripcion'] ?? null,
            set: fn ($value) => ['men_descripcion' => $value],
        );
    }

    protected function type(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['men_tipo'] ?? null,
            set: fn ($value) => ['men_tipo' => $value],
        );
    }

    protected function startDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['men_fini'] ?? null,
            set: fn ($value) => ['men_fini' => $value],
        );
    }

    protected function endDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['men_ffin'] ?? null,
            set: fn ($value) => ['men_ffin' => $value],
        );
    }

    protected function state(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['men_estado'] ?? null,
            set: fn ($value) => ['men_estado' => $value],
        );
    }

    protected function discount(): Attribute
    {
        return Attribute::make(
            get: fn () => isset($this->attributes['men_descuento'])
                ? (float) $this->attributes['men_descuento']
                : 0.0,
            set: fn ($value) => ['men_descuento' => $value],
        );
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'detalle_menus', 'men_id', 'pro_id')
            ->withPivot('det_cantidad');
    }
}
