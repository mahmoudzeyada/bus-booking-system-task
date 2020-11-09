<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trip;
use App\Models\TripStation;
use App\Models\Seat;

use Faker\Factory as Faker;




class SeatSeeder extends Seeder
{

    public function run()
    {
        $faker = Faker::create();
        $trips = Trip::all();
        $trips_stations = TripStation::orderBy('rank', 'asc')->get();


        foreach($trips as $trip) {
            $trip_stations = [];
            foreach($trips_stations as $trip_station) {
                if ($trip->id == $trip_station->trip_id) {
                    array_push($trip_stations,$trip_station);
                }
            }

            /* we will seed data like following
                1-compute the reaming avaliable seats.
                2-make random number for making new seats.
                3-make random seats between the start station and (end stations => must be random and the rank greater than start station).
            **/
            foreach($trip_stations as $trip_station) {

                $number_of_seats_for_each_station_in_trip = $this->number_of_avaliable_seats_for_each_station_in_trip($trip->id);
                $remaining_seats = $trip->numberOfSeats;
                $trip_stations_ids_to_choose= [];

                // first step
                foreach($number_of_seats_for_each_station_in_trip as $rank => $station_trip_res) {
                    $selected_trip_station = $station_trip_res[0];
                    $number_of_seats = $station_trip_res[1];
                    if ($rank >= $trip_station->rank) {
                        $remaining_seats -= $number_of_seats;
                        if ($selected_trip_station->id != $trip_station->id) {
                            array_push($trip_stations_ids_to_choose, $selected_trip_station->id);
                        }
                    }
                }
                //second step
                if ($remaining_seats <= 0 Or count($trip_stations_ids_to_choose) == 0)   continue;
                $generated_number_of_seats = $faker->numberBetween(0, $remaining_seats);

                //third step
                for ($x = 1; $x <=  $generated_number_of_seats; $x++) {
                    $seat = new Seat();
                    $seat->start_station = $trip_station->id;
                    $seat->end_station =  $faker->randomElement($trip_stations_ids_to_choose);
                    $seat->save();
                }

            }
        }

    }

     private function number_of_avaliable_seats_for_each_station_in_trip($trip_id) {
        $trip = Trip::where('id',$trip_id)->first();
        $tripStations = TripStation::where('trip_id', $trip->id)->orderby('rank', 'desc')->get();
        $number_of_seats_in_stations = [];
        forEach($tripStations as $tripStation) {
            $trip_stations_has_greater_rank_ids = $this->get_greater_ranks_ids($tripStations, $tripStation->rank);
            $trip_stations_has_greater_or_equal_rank_ids = $trip_stations_has_greater_rank_ids;
            array_push($trip_stations_has_greater_or_equal_rank_ids, $tripStation->id);
            $number_of_seats = Seat::whereIn('start_station', $trip_stations_has_greater_or_equal_rank_ids)
                                    ->orWhereIn('end_station', $trip_stations_has_greater_rank_ids)
                                    ->count();
            $remaining_seats = $trip->numberOfSeats - $number_of_seats;
            $remaining_seats = $remaining_seats > 0 ? $remaining_seats : 0;



            $number_of_seats_in_stations[$tripStation->rank] = [$tripStation, $number_of_seats];
        }
        return $number_of_seats_in_stations;
     }



    private function get_greater_ranks_ids($array, $excluded_rank) {
        $new_array = [];
        foreach($array as $item) {
            if ($item->rank > $excluded_rank) {
                array_push($new_array, $item->id);
            }
        }
        return $new_array;
    }
}

