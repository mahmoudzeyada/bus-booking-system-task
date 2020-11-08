<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Station;
use App\Models\Trip;
use Faker\Factory as Faker;



class StationTripSeeder extends Seeder
{
    
    public function run()
    {
        $faker = Faker::create();
        $trips = Trip::all();
        $stations = Station::all();
        foreach($trips as $trip) {
            $limit = $faker->numberBetween(0, count($stations)-1);
            foreach(range(0,$limit) as $index) {
                DB::table('station_trip')->insert([
                    'trip_id' => $trip->id,
                    'station_id' => $stations[$index]->id,
                    'rank' => $index
	            ]);
            }
        }
    }
}

