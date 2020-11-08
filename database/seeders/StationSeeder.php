<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Station;


class StationSeeder extends Seeder
{
    
    public function run()
    {
       
        Station::factory(50)->create();

    }
}
