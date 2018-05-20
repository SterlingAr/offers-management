<?php

namespace App\Http\Controllers;

use App\Coupon;
use Illuminate\Http\Request;


class CouponsController extends Controller
{



    public function create(Request $request){

        $coupon = Coupon::create($request->toArray());


        return response()->json([
            "coupon"=>$coupon
        ]);

    }

    public function get(){

        return response()->json([
            "coupons"=>Coupon::all()
        ]);

    }

}
