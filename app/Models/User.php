<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'usu_id';

    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'role_id',
        'usu_nombre',
        'usu_apellido',
        'usu_email',
        'usu_password',
        'rol_id',
        'email_verified_at',
    ];

    protected $hidden = [
        'usu_password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $appends = [
        'name',
        'last_name',
        'email',
        'role_id',
        'full_name',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->getFullNameAttribute();
            },
            set: function ($value) {
                [$nombre, $apellido] = $this->splitFullName($value);

                return [
                    'usu_nombre' => $nombre,
                    'usu_apellido' => $apellido,
                ];
            },
        );
    }

    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['usu_id'] ?? null,
        );
    }

    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['usu_apellido'] ?? null,
            set: fn ($value) => ['usu_apellido' => $value ?: ($this->attributes['usu_nombre'] ?? null)],
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['usu_email'] ?? null,
            set: fn ($value) => ['usu_email' => Str::lower($value)],
        );
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['usu_password'] ?? null,
            set: fn ($value) => ['usu_password' => $this->hashPassword($value)],
        );
    }

    protected function roleId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['rol_id'] ?? null,
            set: fn ($value) => ['rol_id' => $value],
        );
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'rol_id', 'rol_id');
    }

    public function isAdmin(): bool
    {
        return $this->role?->rol_nombre === Role::ADMIN;
    }

    public function isVendedor(): bool
    {
        return $this->hasRole(Role::CAJERO) || $this->hasRole(Role::MESERO);
    }

    public function isCajero(): bool
    {
        return $this->role?->rol_nombre === Role::CAJERO;
    }

    public function isCocinero(): bool
    {
        return $this->role?->rol_nombre === Role::COCINERO;
    }

    public function isMesero(): bool
    {
        return $this->role?->rol_nombre === Role::MESERO;
    }

    public function isCliente(): bool
    {
        return $this->role?->rol_nombre === Role::CLIENTE;
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role?->rol_nombre === $roleName;
    }

    public function getFullNameAttribute(): ?string
    {
        $nombre = $this->attributes['usu_nombre'] ?? null;
        $apellido = $this->attributes['usu_apellido'] ?? null;

        return trim(collect([$nombre, $apellido])->filter()->implode(' ')) ?: null;
    }

    private function splitFullName(?string $value): array
    {
        $value = trim((string) $value);

        if ($value === '') {
            return ['', ''];
        }

        $parts = preg_split('/\s+/', $value, 2) ?: [];
        $nombre = $parts[0] ?? $value;
        $apellido = $parts[1] ?? $nombre;

        return [$nombre, $apellido];
    }

    private function hashPassword(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $this->attributes['usu_password'] ?? null;
        }

        return Hash::needsRehash($value) ? Hash::make($value) : $value;
    }
}
