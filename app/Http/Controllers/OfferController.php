<?php

namespace App\Http\Controllers;

use App\Offer;
use App\OfferDate;
use App\LocationRoom;
use App\Location;
use App\Room;
use DateTime;
use Illuminate\Http\Request;
use Validator;

class OfferController extends Controller
{


    public function indexTable(Request $request) {
        $offers = Offer::select([
            'id',
            'title',
            'description'
        ]);

        if(!empty($request->query)){

            $all = $request->toArray();
            $search = $all['query'];
            $offers->where(function ($query) use ($search) {
                $query->where("title", "like", "%$search%")
                    ->orWhere("description", "like", "%$search%");
            });
        }

        return response()->json([
            "offers" => $offers->get()->toArray()
        ]);
    }


//    public function show($offerId)
//    {
//
//        //add only the rooms that are in the offer_dates_location_room table.
//        $offer = Offer::with('dates.locations')->where('id',$offerId)->first();
//
//        //locations and rooms belonging to this offer
//        $selectedLocations = [];
//
//        foreach($offer->dates as $date)
//        {
//            foreach($date->locations as $location)
//            {
//
//                $rooms = [];
//                $selectedRooms = [];
//
//                $loc = Location::where('id',$location->id)->first();
//
//                $locationRooms = LocationRoom::where('location_id',$location->id)->get();
//
//                foreach($locationRooms as $locationRoom)
//                {
//                    //if there is a row that matches with current $locationRoom and date
//                    // first, get the room and field 'type', then attach predefined_values, then the current offer_values.
//                    $data = \DB::table('offer_dates_location_room')
//                        ->where('offer_date_id',$date->id)
//                        ->where('location_room_id', $locationRoom->id)
//
//                        ->select(['id','price_person','person_number','num_rooms'])
//                        ->get();
//
//
//                        if(isset($data))
//                    {
//
//                        foreach($data as $d){
//                            $room = Room::where('id', $locationRoom->room_id)->first();
//                            array_push($selectedRooms,$room);
//
//                            $room->predefined_values = \DB::table('location_room')
//                                ->where('location_id', $location->id)
//                                ->where('room_id', $room->id)
//                                ->select(['price_person','person_number','num_rooms'])->first();
//
//                            $room->offer_details = $d;
//
//                            array_push($rooms, $room);
//                        }
//
//
//
//
//                    } else { continue; }
//
//                }
//
//                $loc->rooms = $selectedRooms;
//                array_push($selectedLocations,$loc);
//
//
//                $location->rooms = $rooms;
//            }
//        }
//
//        $offer->selectedLocations = $selectedLocations;
//
//
//
//        return response()->json([
//            'offer' => $offer
//        ]);
//
//    }


    //retrieve dates belonging to the offer
    public function dates($offerId){

        $dates = OfferDate::with('locations')->where('offer_id', $offerId)->get();

        return response()->json([
            'dates' => $dates
        ]);
    }


    public function store(Request $request){

        $json = $request->json()->all();
        $newOffer = $json['newOffer'];

        $offerValidator = $this->validatorOffer($newOffer);

        if($offerValidator->passes()){

            $offer = Offer::create([
                'title' => $newOffer['title'],
                'description' => $newOffer['description']
            ]);

            if($request->has('dates')){

                $dates = $request->input('dates');
                $datesValidator = $this->validatorDates($dates);

                if($datesValidator->passes()){

                    $this->addDates($offer, $dates);

                    return response()->json([
                        'status' => 'offer stored'
                    ],200);

                } else {
                    return response()->json([
                        'status' => 'Dates contain errors',
                        'errors' => $datesValidator->errors()->all()
                    ],400);
                }
            }
        } else {
            return response()->json([
                'errors' => $offerValidator->errors()->all(),
                'status' => 'Validation failed',
            ],400);
        }
    }



    public function update(Request $request){

        $json = $request->json()->all();

        $editedOffer = $json['editedOffer'];

        $offerValidator = $this->validatorOffer($editedOffer);

        if($offerValidator->passes()){

            $offer =  Offer::where('id', $editedOffer['id'])->first();
            $offer->title = $editedOffer['title'];
            $offer->description = $editedOffer['description'];
            $offer->save();



//            if($request->has('removals')){
//                try {
//                    $this->applyRemovals($json['removals']);
//                } catch( \Exception $e){
//                    return response()->json([
//                        'status' => 'An unexpected error has ocurred while removing items'
//                    ]);
//                }
//            }
//
//
//
//            if($request->has('dates')){
//
//                $dates = $request->input('dates');
//
//                if($this->validatorDates($dates)->passes()){
//                    try {
//
//                        $this->updateDates($dates);
//
//                    } catch(\Exception $e){
//                        return response()->json([
//                            'status' => 'An unexpected error has ocurred while updating'
//                        ]);
//                    }
//                }
//            }

            return response()->json([
                'status' => 'Offer updated successfully'
            ],200);

        } else {

            return response()->json([
                'status' => 'Validation failed',
                'errors' => $offerValidator->errors()->all()
            ],400);
        }

    }


    public function destroy($offerID){
        $response = Offer::where('id', $offerID)->first()->delete();

        return $response ? response()->json(['status' => 'Offer deleted'],200) : response()->json(['status' => 'Offer could not be deleted'], 404);
    }


    public function getOffersList(){

        $offers=Offer::all();

        return response()->json(["offers"=>$offers]);

     }

     public function getAvailableRooms(Request $request){

        $rooms= \DB::table('offer_dates_location_room')
            ->where('offer_date_id',$request->offer_date_id)
            ->where('location_room_id', $request->location_id)
            ->join('location_room','offer_dates_location_room.location_room_id',"=","location_room.id")
            ->join('rooms','location_room.room_id',"=","rooms.id")
            ->select("offer_dates_location_room.price_person","offer_dates_location_room.person_number","offer_dates_location_room.available_rooms","rooms.type")
            ->get();

         return response()->json(["availableRooms"=>$rooms]);

     }

    // Methods that should be in a service/helper class
    //adds an array of dates to an offer
    private function addDates(Offer $offer, array $dates){
        foreach($dates as $date)
        {
            $start = \Carbon\Carbon::createFromFormat('Y-m-d', $date['start_date']);
            $end = \Carbon\Carbon::createFromFormat('Y-m-d', $date['end_date']);

            $newDate =  OfferDate::create([
                'start_date' => $start,
                'end_date' => $end
            ]);

            if(isset($date['locations']))
            {
                $this->addLocations($newDate, $date['locations']);
            }

            $offer->dates()->save($newDate);
        }
    }

    private function addLocations(OfferDate $date, array $locations){

        foreach($locations as $location){

            $loc  = Location::where('id', $location['id'])->first();

            if(isset($location['rooms'])){
                $this->addRooms($date,$loc,$location['rooms']);
            }
            $date->locations()->attach($loc);
        }

    }

    private function addRooms(OfferDate $date, Location $location, array $rooms){

        foreach($rooms as $room){

            $locationRoom = LocationRoom::where('location_id', $location->id)
                ->where('room_id', $room['id'])
                ->first();

            if(isset($room['individualRooms'])){
                foreach($room['individualRooms'] as $individualRoom){
                    $date->locationRooms()->attach($locationRoom, [
                        'price_person' => $individualRoom['price_person'],
                        'person_number' => $individualRoom['person_number']
                    ]);
                }
            }
        }
    }

    private function validatorDates(array $dates){

        $dateRules = [
            'dates.*.start_date' => 'required|bail|date_format:"Y-m-d"',
            'dates.*.end_date' => 'required|bail|date_format:"Y-m-d"',
            'dates.*.locations.*.id' => 'exists:locations,id',
            'dates.*.locations.*.rooms.*.id' => 'required|exists:rooms,id',
            'dates.*.locations.*.rooms.*.individualRooms.*.price_person' => 'required|integer',
            'dates.*.locations.*.rooms.*.individualRooms.*.person_number' => 'required|integer',
        ];

        $validator = Validator::make($dates, $dateRules);

        return $validator;
    }

    private function validatorOffer($offer){
        $rules = [
            'title' => 'bail|required|max:255',
            'description' => 'required|max:5000',
        ];
        $validator = Validator::make($offer, $rules);

        return $validator;
    }

    private function updateDates(array $dates){

    }

    //remove the arrays of locations,dates,offer_dates_location_rooms
    private function applyRemovals($removals){

    }


}
