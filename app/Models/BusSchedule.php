<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusSchedule extends Model
{
    protected $fillable = ['bus_id', 'route_id', 'driver_id', 'departure_time', 'arrival_time', 'days_of_week', 'is_active'];

    protected $casts = [
        'days_of_week' => 'array',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }
    public function route()
    {
        return $this->belongsTo(Route::class);
    }
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
