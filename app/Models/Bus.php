<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    protected $fillable = [
        'bus_number',
        'model_name',
        'capacity',
        'status',
    ];
}
