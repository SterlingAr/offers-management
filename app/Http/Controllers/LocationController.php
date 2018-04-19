<?php

namespace App\Http\Controllers;

use App\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{


    public function modelViewLocations()
    {
          $locations = Location::with('rooms')->get();
          return view('admin.location.locations', ['locations' => $locations]);
//        return view('admin.location.locations');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
//        $locations = Location::all();
        $locations = Location::with('rooms')->get();
        return response()->json(['locations' => $locations]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $location = Location::with('rooms')->where('id', $id)->first();
        return response()->json([
            'location' => $location
        ]);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //receives name, , address description
        $locationData = $request->json()->all();
//        print_r($locationJSON);

        $locationJSON = $locationData['location'];

        $location = Location::create([
           'name' =>  $locationJSON['name'],
           'address' =>  $locationJSON['address'],
           'phone' =>  $locationJSON['phone'],
            'description' =>  $locationJSON['description'],
            'landline' =>  $locationJSON['landline']
        ]);


        if(isset($locationJSON['rooms']))
        {
            foreach($locationJSON['rooms'] as $room)
            {
                $location->rooms()->attach($room['id'], [
                    'num_rooms' => $room['details']['num_rooms'],
                    'default_price' => $room['details']['price']
                ]);
            }
        }

    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Location $location)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function destroy(Location $location)
    {
        //
    }
}
