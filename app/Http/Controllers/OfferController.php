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

    protected $offerErrors;

    public function modelViewOffers()
    {
//        $offers =  Offer::with('locations.dates.locationRooms')->get();
        $offers = Offer::all();
        return view('admin.offer.offers', ['offers' => $offers]);
//        return response()->json($offers);
    }



    public function modelViewOffer($offerId)
    {
//        $offer = Offer::with('dates.locations.relatedRooms')->where('id',$offerId)->first();
        $offer = Offer::with('dates.locations')->where('id',$offerId)->first();
//        var_dump(response()->json($offer));
//        return view('offer.offer', ['offer' => $offer]);
        return response()->json($offer);


//        return response()->json($offer);
    }


    public function show($offerId)
    {
        //add only the rooms that are in the offer_dates_location_room table.
        $offer = Offer::with('dates.locations')->where('id',$offerId)->first();

        $selectedLocations = [];

        foreach($offer->dates as $date)
        {
            foreach($date->locations as $location)
            {

                $rooms = [];

                $loc = Location::with('rooms')->where('id',$location->id)->first();
                array_push($selectedLocations,$loc);

                $locationRooms = LocationRoom::where('location_id',$location->id)->get();

                foreach($locationRooms as $locationRoom)
                {
                    //if there is a row that matches with current $locationRoom and date
                    // first, get the room and field 'type', then attach predefined_values, then the current offer_values.
                    $data = \DB::table('offer_dates_location_room')
                        ->where('offer_date_id',$date->id)
                        ->where('location_room_id', $locationRoom->id)
                        ->select(['price_person','person_number','available_rooms'])
                        ->first();

                    if(isset($data))
                    {
                        $room = Room::where('id', $locationRoom->room_id)->first();

                        $room->predefined_values = \DB::table('location_room')
                            ->where('location_id', $location->id)
                            ->where('room_id', $room->id)
                            ->select(['price_person','person_number','available_rooms'])->first();

                        $room->offer_details = $data;

                        array_push($rooms, $room);

                    } else { continue; }

                }

                $location->rooms = $rooms;
            }
        }

        $offer->selectedLocations = $selectedLocations;

        return response()->json([
            'offer' => $offer
        ]);

    }



    public function index(Request $request)
    {

        $data = Offer::select([
            'id',
            'title',
            'description'
        ]);


        if(!empty($request->query)){
            $all=$request->toArray();
            $search=$all['query'];

            $data->where(function ($query) use ($search) {
                $query->where("title", "like", "%$search%")
                    ->orWhere("description", "like", "%$search%");
            });
        }


        $count = $data->count();
        $data->limit($request->limit)
            ->skip($request->limit * ($request->page-1));

        if (isset($request->orderBy) && $request->orderBy):
            $direction = $request->ascending==1?"ASC":"DESC";
            $data->orderBy( $request->orderBy,$direction);
        endif;


        return response()->json(["data"=>$data->get()->toArray(),"count"=>$count]);

    }



    public function store(Request $request)
    {

        //future validation for date goes here
        $json = $request->json()->all();
        $newOffer = $json['newOffer'];


        $rules = [
            'title' => 'bail|required|max:255',
            'description' => 'required|max:5000',
            'dates.*.start_date' => 'required|bail|date_format:"Y-m-d"',
            'dates.*.end_date' => 'required|bail|date_format:"Y-m-d"',
            'dates.*.locations.*.id' => 'exists:locations,id',
            'dates.*.locations.*.rooms.*.id' => 'required|exists:rooms,id',
            'dates.*.locations.*.rooms.*.offer_details.price_person' => 'nullable|numeric',
            'dates.*.locations.*.rooms.*.offer_details.available_rooms' => 'nullable|numeric',
            'dates.*.locations.*.rooms.*.offer_details.person_number' => 'nullable|numeric',

        ];

        $validator = Validator::make($newOffer, $rules);

        if($validator->passes())
        {

            $offer = Offer::create([
                'title' => $newOffer['title'],
                'description' => $newOffer['description']
            ]);

            if(isset($newOffer['dates']))
            {
                $this->addDates($offer, $newOffer['dates']);
            }

            return response()->json($newOffer);
        }

        return response()->json($validator->errors()->all(),400);

    }




    public function update(Request $request, Offer $offer)
    {
        $offerJSON = $request->json()->all();

        $offer->title = $offerJSON['title'];

        if(isset($offerJSON['dates']))
        {
            $this->updateOfferDates($offerJSON['dates']);
        }

    //validate the same as in store()
    //also validate the removals object


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Offer $offer)
    {

//        $offer->delete();

    }


    // Methods that should be in a service/helper class

    //adds an array of dates to an offer
    private function addDates(Offer $offer, array $dates)
    {

        foreach($dates as $date)
        {

                $start = \Carbon\Carbon::createFromFormat('Y-m-d', $date['start_date']);
                $end = \Carbon\Carbon::createFromFormat('Y-m-d', $date['end_date']);

               $newDate =  OfferDate::create([
                    'start_date' => $start,
                   'end_date' => $end
                ]);

               if(isset($date['locations'])) {
                   $this->addLocations($newDate, $date['locations']);
               }
               $offer->dates()->save($newDate);
        }
    }




    //add locations to a date
    private function addLocations(OfferDate $date, array $locations)
    {
        foreach($locations as $location)
        {
            $loc  = Location::where('id', $location['id'])->first();

            if(isset($location['rooms']))
            {
                $this->addRooms($date, $location['rooms']);
            }
            $date->locations()->attach($loc);
        }

    }

    //add rooms to a location
    private function addRooms(OfferDate $date, array $rooms)
    {
        foreach($rooms as $room)
        {
            $locationRoom = LocationRoom::where('location_id', $room['predefined_values']['location_id'])->where('room_id', $room['id'])->first();

            // create available_room->length() *  offer_dates_location_room
            $date->locationRooms()->attach($locationRoom, [
                'price_person' => $room['offer_details']['price_person'],
                'person_number' => $room['offer_details']['person_number'],
                'available_rooms' => $room['offer_details']['available_rooms'],
            ]);
        }
    }



}
