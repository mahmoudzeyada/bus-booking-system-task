<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function trips()
    {
        return $this->belongsToMany('App\Models\Trip')->using('App\Models\TripStation');
    }
}
