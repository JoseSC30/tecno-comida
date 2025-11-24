<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Food extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'pro_id';

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'available',
        'category_id',
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

    protected $appends = [
        'id',
        'name',
        'description',
        'price',
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
        return $this->belongsTo(Category::class, 'cat_id', 'cat_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'pro_id', 'pro_id');
    }
}
