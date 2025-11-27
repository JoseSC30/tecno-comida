<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mesa extends Model
{
    protected $table = 'mesas';
    protected $primaryKey = 'mes_id';
    public $timestamps = false;

    protected $fillable = [
        'mes_numero',
        'mes_capacidad',
    ];

    protected $casts = [
        'mes_numero' => 'integer',
        'mes_capacidad' => 'integer',
    ];

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'mes_id', 'mes_id');
    }

    /**
     * Get the table icon based on capacity
     */
    public function getIconAttribute(): string
    {
        return match(true) {
            $this->mes_capacidad <= 2 => '💑',
            $this->mes_capacidad <= 4 => '👨‍👩‍👧',
            $this->mes_capacidad <= 6 => '👨‍👩‍👧‍👦',
            $this->mes_capacidad <= 8 => '👥',
            default => '🎉',
        };
    }
}
