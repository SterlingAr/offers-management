<?php

namespace App\Http\Controllers;

use App\Location;
use Illuminate\Http\Request;
use Validator;

class LocationController extends Controller
{


    public function modelViewLocations()
    {
          $locations = Location::with('rooms')->get();
          return view('admin.location.locations', ['locations' => $locations]);
//        return view('admin.location.locations');
    }



    public function index(Request $request)
    {
        $locations = Location::all();
//        $locations = Location::with('rooms')->get();
        return response()->json(['locations' => $locations]);
    }


    //query locations for table using search parameters sent in the request
    public function indexTable(Request $request)
    {

        $locations = Location::select([
            'id',
            'name',
            'description',
            'address',
            'phone',
            'landline'
        ]);


        if(!empty($request->query)){

            $all= $request->toArray();
            $search= $all['query'];
            //hello, my name is; drop tables employees;
            $locations->where(function ($query) use ($search) {
                $query->where("name", "like", "%$search%")
                    ->orWhere("description", "like", "%$search%")
                    ->orWhere("address", "like", "%$search%")
                    ->orWhere("phone", "like", "%$search%")
                    ->orWhere("landline", "like", "%$search%");
            });
        }


        return response()->json([
            "locations" => $locations->get()->toArray()
        ]);

    }


    public function show($id)
    {
        $location = Location::with('rooms')->where('id', $id)->first();
        return response()->json([
            'location' => $location
        ]);
    }


    public function store(Request $request)
    {

        $json = $request->json()->all();

        if(!isset($json['locationValues'])) {
            return response()->json(['No data was sent'],400);
        }

        $rules = [
            'name' => 'bail|required|max:50',
            'address' => 'bail|required|max:250',
            'description' => 'bail|required|max:1500',
            'phone' => 'sometimes|max:20',
            'landline' => 'sometimes|max:20'
        ];

        $validator = Validator::make($json['locationValues'], $rules);

        if(!$validator->passes()) {

            return response()->json($validator->errors()->all(),400);

        }

        $location = Location::create([
            'name' =>  $json['locationValues']['name'],
            'address' =>  $json['locationValues']['address'],
            'description' =>  $json['locationValues']['description'],
            'landline' =>  $json['locationValues']['landline']
        ]);

        if(isset($json['locationValues']['phone'])) {
            $location->phone = $json['locationValues']['phone'];
        }

        if(isset($json['locationValues']['landline'])) {
            $location->landline = $json['locationValues']['phone'];
        }


        if($request->has('rooms'))
        {
            foreach($json['rooms'] as $room)
            {
                $location->rooms()->attach($room['id'], [
                    'num_rooms' => $room['predefined_values']['num_rooms'],
                    'default_price' => $room['predefined_values']['price']
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
