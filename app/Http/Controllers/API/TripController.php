<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;
use App\Models\TripStation;
use App\Models\Seat;
use App\Models\Station;
use Illuminate\Support\Facades\Validator;
class TripController extends Controller
{
    public function store(Request $request, $name)
    {
        // validaing
        $trip = Trip::where('name',$name)->first();
        if (!$trip) {
            return $this->sendError('trip not found', [], 404);
        }
        // validating input
        $validator = Validator::make(request()->all(), [
            'start_station_name' => ['required', 'max:255'],
            'end_station_name' => ['required', 'max:255']
       ]);
        if ($validator->fails()) {
           return $this->sendError('validation error', $validator->errors()->getMessages(), 400);
        };
        // checking if names exists
        $start_station = Station::where('name', $request->start_station_name)->first();
        if (!$start_station) {
            return $this->sendError('start_station_not_exist', [], 400);
        };
        $end_station = Station::where('name', $request->end_station_name)->first();
        if (!$end_station) {
            return $this->sendError('end_station_not_exist', [], 400);
        };
        // check if they in the same trip
        $start_station_trip = TripStation::where('station_id', $start_station->id)->where('trip_id', $trip->id)->first();
        if (!$start_station_trip) {
            return $this->sendError('start station not exist in this trip', [], 400);
        }
        $end_station_trip = TripStation::where('station_id', $end_station->id)->where('trip_id', $trip->id)->first();
        if (!$end_station_trip) {
            return $this->sendError('end station not exist in this trip', [], 400);
        }
        // check for rank
        if($start_station_trip->rank > $end_station_trip->rank) {
            return $this->sendError('end station can not be before start station', [], 400);
        }
        if($start_station_trip->rank == $end_station_trip->rank) {
            return $this->sendError('end station can not be start station', [], 400);
        }
        // check for avaliable seats
        $avalibale_seats_array = $this->number_of_avaliable_seats_for_each_station_in_trip($trip);
        $remaining_seats = $this->get_remaining_seats_for_station($avalibale_seats_array, $start_station);

        if (!$remaining_seats) {
            return $this->sendError('there is no seats left in this start station', [], 406);
        }
        //creating seat
        $seat = new Seat();
        $seat->start_station = $start_station_trip->id;
        $seat->end_station = $end_station_trip->id;
        $seat->save();

        return $this->sendResponse($seat,'seat has been reserved successfully');
    }


    public function show($name)
    {
        // validaing
        $trip = Trip::where('name',$name)->first();
        if (!$trip) {
            return $this->sendError('trip not found', [], 404);
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

    private function get_remaining_seats_for_station($array, $station) {
        $remaining_seats = 0;
        foreach($array as $rank => $obj) {
            if ($station->id == $obj->station->id) {
                $remaining_seats = $obj->remaining_seats;
                break;
            }
        }
        return $remaining_seats;
    }


}
