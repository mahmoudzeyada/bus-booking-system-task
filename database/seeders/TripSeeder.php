<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trip;

class TripSeeder extends Seeder
{
    
    public function run()
    {
        Trip::factory(50)->create();
    }
}

