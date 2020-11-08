<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;


    public function start_station()
    {
        return $this->belongsTo('App\Models\TripStation', 'start_station');
    }
    public function end_station()
    {
        return $this->belongsTo('App\Models\TripStation', 'end_station');
    }
    public function seats()
    {
        return $this->belongsToMany('App\Models\User')->using('App\Models\UserSeat');
    }
}
