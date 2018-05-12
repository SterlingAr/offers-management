<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Sale;
use App\Offer;
use App\SaleRoomAllocation;
class SalesController extends Controller
{

    public function indexTable(Request $request){


        $sales = Sale::select([
            "id",
            "first_name",
            "last_name",
            "email",
            "phone",
            "total_person_number",
            "payment_status",
            "total_amount"
        ]);

        if(!empty($request->query)){

            $all = $request->toArray();
            $search = $all['query'];
            $sales->where(function ($query) use ($search) {
                $query->where("first_name", "like", "%$search%")
                    ->orWhere("last_name", "like", "%$search%")
                    ->orWhere("phone", "like", "%$search%");
            });
        }

        return response()->json([
            "sales" => $sales->get()->toArray()
        ]);

    }

    public function show($saleId){

    }

    public function store(Request $request){


        //validate sale fields: first_name, last_name, email, phone,


        if($request->has('saleFields')){

            $saleFields = $request->input('saleFields');
            $saleValidator = $this->validateSale($saleFields);

            if(!$saleValidator->passes()){
                return response()->json([
                    'errors' => $saleValidator->errors()->all()
                ],400);
            }

            $sale = Sale::create([
                'first_name' => $saleFields['first_name'],
                'last_name' => $saleFields['last_name'],
                'offer_id' => $saleFields['offer_id'],
                'email' => $saleFields['email'],
                'phone' => $saleFields['phone'],
            ]);

            $sale->save();

            if($request->has('allocatedRooms')){
                $allocatedRooms = $request->input('allocatedRooms');
                $roomsValidator = $this->validateAllocatedRooms($allocatedRooms);

                if(!$roomsValidator->passes()){
                    $sale->delete();
                    return response()->json([
                        'errors' => $roomsValidator->errors()->all()
                    ],400);
                }
//                try {
                    $this->allocateRoomsToSale($sale, $allocatedRooms);
//                } catch (\Exception $e) {
//                    $sale->delete();
//                    return response()->json([
//                        'status' => 'O facut poc'
//                    ],  400);
//                }
            }
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

        Sale::where('id', $saleId)->delete();

    }

    private function allocateRoomsToSale(Sale $sale, $allocatedRooms){

        $total_persons = 0;
        $total_amount = 0;

        foreach($allocatedRooms as $room){

            $offer_dates_location_room = \DB::table('offer_dates_location_room')
                ->where('id', $room['offer_dates_location_room_id'] )->first();

            if( $offer_dates_location_room->vacant_places > 0 &&
                $room['persons_going'] <= $offer_dates_location_room->person_number &&
                $room['persons_going'] <= $offer_dates_location_room->vacant_places
            ){

                $total_amount += $room['persons_going'] * $room['price_person'];
                $total_persons += $room['persons_going'];
                $remaining_vacant_places = $offer_dates_location_room->vacant_places - $room['persons_going'];
                \DB::table('offer_dates_location_room')
                    ->where('id', $room['offer_dates_location_room_id'] )
                    ->update([
                        'vacant_places' => $remaining_vacant_places
                    ]);


               SaleRoomAllocation::create([
                  'offer_dates_location_room_id' => $room['offer_dates_location_room_id'],
                  'sale_id' => $sale->id,
                  'person_number' => $room['persons_going'],
                  'persons_names' => $room['persons_names']
                ])->save();
            }
        }
        $sale->total_person_number = $total_persons;
        $sale->total_amount = $total_amount;
        $sale->save();
    }


    //helpers, validators.
    private function validateSale($saleFields){

        $saleRules = [
            'first_name' => 'required|bail',
            'last_name' => 'required|bail',
            'email' => 'required|bail|email',
            'phone' => 'required|bail|max:20',
            'offer_id' => 'required|bail|exists:offers,id'
//            'coupon' => 'exists:coupons,id'
        ];

        return Validator::make($saleFields,$saleRules);

    }

    private function validateAllocatedRooms($allocatedRooms){


        $roomRules = [
            'allocatedRooms.*.persons_going' => 'required|bail|integer',
        ];

        return Validator::make($allocatedRooms, $roomRules);
    }


}
