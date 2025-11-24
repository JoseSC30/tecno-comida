<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'rol_id';
    public $timestamps = false;

    protected $fillable = ['rol_nombre', 'name'];
    protected $appends = ['name'];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['rol_nombre'] ?? null,
            set: fn ($value) => ['rol_nombre' => $value],
        );
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id', 'rol_id');
    }

    public const ADMIN = 'Administrador';
    public const CAJERO = 'Cajero';
    public const COCINERO = 'Cocinero';
    public const MESERO = 'Mesero';
    public const CLIENTE = 'Cliente';
    public const VENDEDOR = self::CAJERO; // compatibilidad con código existente
}
