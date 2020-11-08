<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'numberOfSeats'
    ];

    public function stations()
    {
        return $this->belongsToMany('App\Models\Station')->using('App\Models\TripStation');
    }

   
}
