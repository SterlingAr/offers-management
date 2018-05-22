<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Sale;
use App\Offer;
use App\Coupon;
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


        $allocatedRooms = \DB::table('sale_room_allocations')
            ->where('sale_room_allocations.sale_id', $saleId)
            ->join('offer_dates_location_room','sale_room_allocations.offer_dates_location_room_id','=','offer_dates_location_room.id')
            ->join('offer_dates', 'offer_dates_location_room.offer_date_id', '=', 'offer_dates.id')
            ->join('location_room','offer_dates_location_room.location_room_id','=','location_room.id')
            ->join('locations','location_room.location_id','=','locations.id')
            ->join('rooms','location_room.room_id','=','rooms.id')
            ->select([
                'sale_room_allocations.id',
                'sale_room_allocations.offer_dates_location_room_id',
                'sale_room_allocations.persons_going', //persons_going in frontend
                'sale_room_allocations.persons_names',
                'rooms.type',
                'locations.name AS location_name',
                'offer_dates.start_date',
                'offer_dates.end_date',
                'offer_dates_location_room.person_number', //total persons that fit in a room
                'offer_dates_location_room.vacant_places',
                'offer_dates_location_room.price_person'
            ])
            ->get()->each(function($room){
                $room->persons_names = SaleRoomAllocation::where('id', $room->id)->first()->persons_names;
            });



        $sale = Sale::with('offer')->where('id', $saleId)->first();
        return response()->json([
            'offer' => $sale->offer,
            'allocatedRooms' => $allocatedRooms
        ]);

    }

    public function store(Request $request){

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
                'coupon_code' => $saleFields['coupon_code']

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
                try {
                    $this->allocateRoomsToSale($sale, $allocatedRooms);
                } catch (\Exception $e) {
                    $sale->delete();
                    return response()->json([
                        'status' => 'O facut poc'
                    ],  400);
                }
            }
        }

    }

    public function update(Request $request, $saleId){

        $sale = '';


        if($request->has('saleFields')) {

            $saleFields = $request->input('saleFields');
            $saleValidator = $this->validateSale($saleFields);

            if (!$saleValidator->passes()) {
                return response()->json([
                    'errors' => $saleValidator->errors()->all()
                ], 400);
            }

            $sale = Sale::where('id', $saleId)->first();
            $sale->first_name = $saleFields['first_name'];
            $sale->last_name = $saleFields['last_name'];
            $sale->offer_id = $saleFields['offer_id'];
            $sale->email = $saleFields['email'];
            $sale->coupon_code = $saleFields['coupon_code'];
        }

        if($request->has('deallocatedRooms')){

            $dealocatedRooms = $request->input('deallocatedRooms');
            //try catch
            $this->deallocateRoomsFromSale($sale,$dealocatedRooms);
        }

        if($request->has('allocatedRooms')) {
            $allocatedRooms = $request->input('allocatedRooms');
            if($allocatedRooms){
                $roomsValidator = $this->validateAllocatedRooms($allocatedRooms);
                if (!$roomsValidator->passes()) {
                    return response()->json([
                        'errors' => $roomsValidator->errors()->all()
                    ], 400);
                }
                //try catch
                $this->updateAllocatedRooms($sale, $allocatedRooms);
            }
        }
    }

    public function delete($saleId){

        $sale_room_allocations = \DB::table('sale_room_allocations')->where('sale_id', $saleId)->get();

        foreach($sale_room_allocations as $allocatedRoom){

            $offer_dates_location_room = \DB::table('offer_dates_location_room')
                ->where('id', $allocatedRoom->offer_dates_location_room_id )->first();

            $remaining_vacant_places = $offer_dates_location_room->vacant_places + $allocatedRoom->persons_going;

            \DB::table('offer_dates_location_room')
                ->where('id', $allocatedRoom->offer_dates_location_room_id )
                ->update([
                    'vacant_places' => $remaining_vacant_places
                ]);
        }

        Sale::where('id', $saleId)->delete();

    }

    private function allocateRoomsToSale(Sale $sale, $allocatedRooms){

        $total_persons = 0;
        $total_amount = 0;
        $reduction = 0;

        if(isset($sale->coupon_code)){
            $reduction = Coupon::where('code', $sale->coupon_code)->first()->reduction_value;

        }

        foreach($allocatedRooms as $room){

            $offer_dates_location_room = \DB::table('offer_dates_location_room')->where('id', $room['offer_dates_location_room_id'])->first();

            if( $offer_dates_location_room->vacant_places > 0 &&
                $room['persons_going'] <= $offer_dates_location_room->person_number &&
                $room['persons_going'] <= $offer_dates_location_room->vacant_places
            ){

                $total_amount += $room['persons_going'] * $room['price_person'];

                if($reduction > 0){
                    $total_amount -= $reduction * $room['persons_going'];
                }

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
                  'persons_going' => $room['persons_going'],
                  'persons_names' => $room['persons_names']
                ])->save();
            }
        }
        $sale->total_person_number += $total_persons;
        $sale->total_amount += $total_amount;
        $sale->save();
    }

    private function updateAllocatedRooms(Sale $sale, array $allocatedRooms){
        $newAllocatedRooms = [];
        $reduction = 0;
        if(isset($sale->coupon_code)){
            $reduction = Coupon::where('code', $sale->coupon_code)->first()->reduction_value;
        }
        foreach($allocatedRooms as $room){

            if(isset($room['isNew']) && $room['isNew']){
                array_push($newAllocatedRooms, $room);
                continue;
            } else {

                $allocatedRoom = SaleRoomAllocation::where('id', $room['id'])->first();
                if($allocatedRoom->persons_going != $room['persons_going']){

                    if($room['persons_going'] > $allocatedRoom->persons_going){
                        $sale->total_person_number += $room['persons_going'] - $allocatedRoom->persons_going;
                    } else {
                        $sale->total_person_number -= $allocatedRoom->persons_going - $room['persons_going'];
                    }

                    $newPrice = $room['price_person'] * $room['persons_going'];
                    $oldPrice = $room['price_person'] * $allocatedRoom->persons_going;

                    if($reduction > 0){
                        $newPrice -= $reduction * $room['persons_going'];
                    }

                    $sale->total_amount -= $oldPrice;
                    $sale->total_amount += $newPrice;

                    $allocatedRoom->persons_going = $room['persons_going'];
                    $allocatedRoom->persons_names = $room['persons_names'];
                    $allocatedRoom->save();
                }
            }
        }
        if($newAllocatedRooms){
            $this->allocateRoomsToSale($sale, $newAllocatedRooms);
        } else {
            $sale->save();
        }
    }

    private function deallocateRoomsFromSale(Sale $sale, array $allocatedRooms){

        $reduction = 0;

        foreach($allocatedRooms as $room){
            $allocatedRoom = \DB::table('sale_room_allocations')
                ->where('id', $room['id'])
                ->first(); //join

            $offer_dates_location_room = \DB::table('offer_dates_location_room')
                ->where('id', $room['offer_dates_location_room_id'])
                ->first();


            $sale->total_person_number -= $allocatedRoom->persons_going;

            $roomTotalPrice = $allocatedRoom->persons_going * $offer_dates_location_room->price_person;

            if($reduction > 0){
                $roomTotalPrice += $reduction;
            }

            $sale->total_amount -= $roomTotalPrice ;

            SaleRoomAllocation::where('id', $room['id'])->delete();

            if($offer_dates_location_room->vacant_places <= $offer_dates_location_room->person_number){
                \DB::table('offer_dates_location_room')
                    ->where('id', $room['offer_dates_location_room_id'])->update([
                        'vacant_places' => $offer_dates_location_room->vacant_places + $allocatedRoom->persons_going
                    ]);
            }
            $sale->save();
        }
    }



    //helpers, validators.
    private function validateSale($saleFields){

        $saleRules = [
            'first_name' => 'required|bail',
            'last_name' => 'required|bail',
            'email' => 'required|bail|email',
            'phone' => 'required|bail|max:20',
            'offer_id' => 'required|bail|exists:offers,id',
            'coupon_id' => 'exists:coupons,id'
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
