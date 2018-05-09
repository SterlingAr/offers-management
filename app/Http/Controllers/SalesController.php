<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Sale;
use App\Offer;

class SalesController extends Controller
{

    public function indexTable(Request $request){

        return response()->json(['status' => 'test']);
    }

    public function show($saleId){

    }

    public function store(Request $request){

        //validate sale fields: first_name, last_name, email, phone,
        $saleFields = $request->input('saleFields');
        $saleRules = [
            'first_name' => 'required|bail',
            'last_name' => 'required|bail',
            'email' => 'required|bail|email',
            'phone' => 'required|bail|max:20',
            'offer_id' => 'required|bail|exists:offers,id'
//            'coupon' => 'exists:coupons,id'
        ];
        $validator = Validator::make($saleFields,$saleRules);

        if($validator->passes()){

            $sale = Sale::create([
                'first_name' => $saleFields['first_name'],
                'last_name' => $saleFields['last_name'],
                'email' => $saleFields['email'],
                'phone' => $saleFields['phone'],
            ]);

//            if($request->has('allocatedRooms')){
//                $allocatedRooms = $request->input('allocatedRooms');
//            }
            $sale->save();
            $offer = Offer::where('id', $saleFields['offer_id'])->first();
            $sale->offer()->associate($offer);
            $sale->save();

        } else {
            return response()->json([
                'errors' => $validator->errors()->all()
            ],400);
        }



        //create a new sale with the given values, assign the offer_id, don't save yet.

        //foreach room in roomsInSale, fetch the offer_dates_location_room record from database
        //check that the given room.persons_going value doesn't surpass the offer_dates_location_room.vacant_places in the record.
        //substract room.persons_going from offer_dates_location_room.vacant_places and update the offer_dates_location_room record,
        //update the local variable totalPersons as such, totalPersons += room.persons_going.
        //update the local variable totalAmount as such, totalAmount += room.total_price.
        //apply any coupoun code, if existent.
        //assign those values to the Sale attributes: total_person_number, total_amount
        //finally, save and assign the offer_id



    }

    public function update(Request $request, $saleId){

    }

    public function delete($saleId){

    }


}
