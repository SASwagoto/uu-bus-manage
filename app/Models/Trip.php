<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    protected $fillable = ['bus_id', 'route_id', 'driver_id', 'status', 'current_lat', 'current_lng', 'passenger_count'];

    public function bus(): BelongsTo { return $this->belongsTo(Bus::class); }
    public function route(): BelongsTo { return $this->belongsTo(Route::class); }
    public function driver(): BelongsTo { return $this->belongsTo(User::class, 'driver_id'); }
}
