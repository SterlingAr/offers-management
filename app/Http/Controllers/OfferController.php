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
            ->select("offer_dates_location_room.price_person","offer_dates_location_room.person_number","offer_dates_location_room.available_rooms","rooms.type") //am sters coloana available_rooms, e necesara?
            ->get();

        return response()->json(["availableRooms"=>$rooms]);

    }

    public function dates($offerId){


        $dates = OfferDate::with(['locations'] )->where('offer_id', $offerId)->get();

        //  For each location, get all rooms, if any of the rooms is used in the date,
        //  add it to the rooms array belonging to that location + all individualRooms (offer_dates_location_room)
        foreach($dates as $date){

            foreach($date->locations as $location){

                $rooms = $location->rooms()->get();

                $roomsUsedInLocation = [];
                foreach($rooms as $room){

                    $locRoom = LocationRoom::where('location_id',$location->id)->where('room_id',$room->id)->first();
                    $individualRooms = \DB::table('offer_dates_location_room')
                        ->where('location_room_id', $locRoom->id)
                        ->where('offer_date_id', $date->id)
                        ->get();

                    if(count($individualRooms) > 0){
                        $room->individualRooms = $individualRooms;
//                        $roomsUsedInLocation = [$room];
                        array_push($roomsUsedInLocation, $room);
                    }
                }

                $location->rooms = $roomsUsedInLocation;
            }
        }


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


            //needed validation, check if any of the dates,locations,rooms,individualRooms exist
            //note: nest the try catch in a bigger one.
            if($request->has('removals')){
                try {
                    $this->applyRemovals($json['removals']);
                } catch( \Exception $e){
                    return response()->json([
                        'status' => 'An unexpected error has ocurred while removing items'
                    ]);
                }
            }
            if($request->has('dates')){

                $dates = $request->input('dates');
                $datesValidator = $this->validatorDates($dates);
                if($datesValidator->passes()){
                    try {

                        $this->updateDates($dates);

                    } catch(\Exception $e){
                        return response()->json([
                            'status' => 'An unexpected error has ocurred while updating, please try again'
                        ],400);
                    }
                } else {
                    return response()->json([
                        'status' => 'Dates contain errors',
                        'errors' => $datesValidator->errors()->all()
                    ],400);
                }
            }

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



    //if date is new, add the to an array
    //if date is not new, update it
    //at the end, use addDates() to add the new dates.
    private function updateDates(array $dates){

        $newDates = [];
        foreach($dates as $editedDate){

            if(isset($editedDate['isNew']) && $editedDate['isNew']){
                array_push($newDates,$editedDate);
            } else {
                $date = OfferDate::where('id',$editedDate['id'])->first();

                $start = \Carbon\Carbon::createFromFormat('Y-m-d', $editedDate['start_date']);
                $end = \Carbon\Carbon::createFromFormat('Y-m-d', $editedDate['end_date']);

                $date->start_date = $start;
                $date->end_date = $end;
                $date->save();

                if(isset($editedDate['locations'])){
                    $this->updateLocations($date,$editedDate['locations']);
                }
            }
        }

        //add new dates, the offer is the same for all of them
        if($newDates)
        {
            $offer = Offer::where('id', $newDates[0]['offer_id'])->first();
            $this->addDates($offer,$newDates);
        }
    }

    private function updateLocations(OfferDate $date, array $locations){
        foreach($locations as $location){

            if(isset($location['isNew']) && $location['isNew']){

                $loc  = Location::where('id', $location['id'])->first();
                $date->locations()->attach($loc);

                if(isset($location['rooms'])){
                    $this->addRooms($date,$loc,$location['rooms']);
                }
            }

            if(isset($location['rooms'])){
                $existingLocation = Location::where('id', $location['id'])->first();
                $this->updateRooms($date, $existingLocation, $location['rooms']);
            }
        }
    }

    private function updateRooms(OfferDate $date, Location $location, array $rooms){
        $newRooms = [];
        foreach($rooms as $room){

            if(isset($room['isNew']) && $room['isNew']){
                array_push($newRooms, $room);
            }

            if(isset($room['individualRooms'])){
                $this->updateIndividualRooms($date, $location, $room['id'], $room['individualRooms']);
            }
        }

        $this->addRooms($date,$location,$newRooms);
    }

    //add new individual rooms if any, else update existing ones.
    private function updateIndividualRooms(OfferDate $date, Location $location, $roomId, array $individualRooms){

        $newIndividualRooms = [];
        foreach($individualRooms as $individualRoom){

            if(isset($individualRoom['isNew']) && $individualRoom['isNew']){
                array_push($newIndividualRooms, $individualRoom);
            }

            \DB::table('offer_dates_location_room')
                ->where('id', $individualRoom['id'])
                ->update([
                    'price_person' => $individualRoom['price_person'],
                    'person_number' => $individualRoom['person_number']
                ]);

        }

        $locationRoom = LocationRoom::where('location_id', $location->id)
            ->where('room_id', $roomId)
            ->first();

        foreach($newIndividualRooms as $newIndividualRoom){
            $date->locationRooms()->attach($locationRoom, [
                'price_person' => $newIndividualRoom['price_person'],
                'person_number' => $newIndividualRoom['person_number']
            ]);
        }
    }


    //remove the arrays of locations,dates,offer_dates_location_rooms
    private function applyRemovals($removals){

        if(isset($removals['individualRooms'])){
            foreach($removals['individualRooms'] as $individualRoomData){
                \DB::table('offer_dates_location_room')
                    ->where('id', $individualRoomData['individual_room_id'])
                    ->delete();
            }
        }

        //remove offer_dates_location_room related to the given date and location_room
        if(isset($removals['rooms'])){
            foreach($removals['rooms'] as $roomData){

                $date = OfferDate::where('id', $roomData['date_id'])->first();

                $locationRoom = LocationRoom::where('location_id', $roomData['location_id'])
                    ->where('room_id',$roomData['room_id'])
                    ->first();

                $date->locationRooms()->detach($locationRoom);
            }
        }

        if(isset($removals['locations'])){
            foreach($removals['locations'] as $locationData){
                $date = OfferDate::where('id', $locationData['date_id'])
                    ->first();
                //for each room in the location, drop any existing row in offer_dates_location_room.
                $locationRooms = LocationRoom::where('location_id', $locationData['location_id'])->get();

                $locationRooms->each(function($l) use ($date) {
                   $date->locationRooms()->detach($l);
                });
                $date->locations()->detach($locationData['location_id']);
            }
        }

        if(isset($removals['dates'])){
            foreach($removals['dates'] as $dateId){
                OfferDate::where('id', $dateId)
                    ->first()
                    ->delete();
            }
        }
    }


    //VALIDATORS
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

}
