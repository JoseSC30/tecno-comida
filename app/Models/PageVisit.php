<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_name',
        'path',
        'visits',
        'last_visited_at',
    ];

    protected $casts = [
        'visits' => 'integer',
        'last_visited_at' => 'datetime',
    ];
}
