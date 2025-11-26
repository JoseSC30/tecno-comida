<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'categorias';
    protected $primaryKey = 'cat_id';
    public $incrementing = true;

    protected $fillable = [
        'cat_nombre',
        'cat_descripcion',
    ];

    protected $hidden = [
        'cat_id',
        'cat_nombre',
        'cat_descripcion',
    ];

    protected $appends = [
        'id',
        'name',
        'description',
    ];

    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['cat_id'] ?? null,
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['cat_nombre'] ?? null,
            set: fn ($value) => ['cat_nombre' => $value],
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['cat_descripcion'] ?? null,
            set: fn ($value) => ['cat_descripcion' => $value],
        );
    }

    public function products(): HasMany
    {
        return $this->hasMany(Producto::class, 'cat_id', 'cat_id');
    }

    public function foods(): HasMany
    {
        return $this->products();
    }

    public function getRouteKeyName(): string
    {
        return 'cat_id';
    }
}
