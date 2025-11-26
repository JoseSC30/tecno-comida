<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'pro_id';

    public function getRouteKeyName(): string
    {
        return 'pro_id';
    }

    protected $fillable = [
        'pro_nombre',
        'pro_descripcion',
        'pro_precioventa',
        'pro_costounit',
        'pro_imagen',
        'pro_disponible',
        'cat_id',
    ];

    protected $casts = [
        'pro_precioventa' => 'decimal:2',
        'pro_costounit' => 'decimal:2',
        'pro_disponible' => 'boolean',
    ];

    protected $hidden = [
        'pro_id',
        'pro_nombre',
        'pro_descripcion',
        'pro_precioventa',
        'pro_costounit',
        'pro_imagen',
        'pro_disponible',
        'cat_id',
    ];


       // Para que aparezcan en JSON
    protected $appends = [
        'id',
        'name',
        'description',
        'price',
        'cost',
        'image',
        'available',
        'category_id',
    ];


    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['pro_id'] ?? null,
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['pro_nombre'] ?? null,
            set: fn ($value) => ['pro_nombre' => $value],
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['pro_descripcion'] ?? null,
            set: fn ($value) => ['pro_descripcion' => $value],
        );
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn () => isset($this->attributes['pro_precioventa'])
                ? (float) $this->attributes['pro_precioventa']
                : null,
            set: fn ($value) => ['pro_precioventa' => $value],
        );
    }

    protected function cost(): Attribute
    {
        return Attribute::make(
            get: fn () => isset($this->attributes['pro_costounit'])
                ? (float) $this->attributes['pro_costounit']
                : null,
            set: fn ($value) => ['pro_costounit' => $value],
        );
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['pro_imagen'] ?? null,
            set: fn ($value) => ['pro_imagen' => $value],
        );
    }

    protected function available(): Attribute
    {
        return Attribute::make(
            get: fn () => (bool) ($this->attributes['pro_disponible'] ?? false),
            set: fn ($value) => ['pro_disponible' => (bool) $value],
        );
    }

    protected function categoryId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['cat_id'] ?? null,
            set: fn ($value) => ['cat_id' => $value],
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'cat_id', 'cat_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'pro_id', 'pro_id');
    }

    public function insumos(): BelongsToMany
    {
        return $this->belongsToMany(Insumo::class, 'receta', 'pro_id', 'ins_id')
            ->withPivot('rec_cantidad', 'rec_unidad');
    }
}
