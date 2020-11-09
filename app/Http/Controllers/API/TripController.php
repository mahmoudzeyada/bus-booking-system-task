<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;
use App\Models\TripStation;
use App\Models\Seat;
use App\Models\Station;
class TripController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
        'name' => ['required', 'max:255'],
        ]);

        $trip = Trip::create([
            'name' => $request->get('name'),
        ]);

        return $this->sendResponse($trip,'trip created sussfully');
    }


    public function show($name)
    {
        // validaing
        $trip = Trip::where('name',$name)->first();
        if (!$trip) {
            return $this->sendError('trip not found', []);
        }
        $trip->avalibale_seats = $this->number_of_avaliable_seats_for_each_station_in_trip($trip);
        return $this->sendResponse($trip, '');
    }


    private function number_of_avaliable_seats_for_each_station_in_trip($trip) {
        /**
         * we sort it by rank desc because we sort stations in trip by rank(ex):
         * 1-cairo 0 -> start_of_line
         * 2-mansora 1
         * 3-dametta 2
         * 4-bortsaid 3 -> end_of_line
         * so here if we want to compute number of avaliable seats in mansora we should
         * count the number of seats in dametta and bortsaid as (start or end station) and the number of start station in mansora
         * and substart that with
         * the original number of seat of the trip
         * */
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

            $station = Station::where('id', $tripStation->station_id)->get()->first();

            array_push($number_of_seats_in_stations,(object)['remaining_seats' => $remaining_seats, 'station' => $station, 'rank' => $tripStation->rank]);
        }
        return array_reverse($number_of_seats_in_stations);
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
