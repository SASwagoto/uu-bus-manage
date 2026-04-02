<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
        'route_name',
        'start_point',
        'end_point',
        'stoppages',
    ];

    protected $casts = [
        'stoppages' => 'array',
    ];
}
