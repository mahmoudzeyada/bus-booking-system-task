<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TripStation extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'rank',
    ];

    public function trip_station()
    {
        return $this->hasMany('App\Models\Seat');
    }
}
