<?php

namespace App\Http\Controllers;

use App\Coupon;
use Illuminate\Http\Request;
use Validator;

class CouponsController extends Controller
{

    public function create(Request $request){
        
        $coupon = Coupon::create($request->toArray()); //    
        
        return response()->json([
            "coupon"=>$coupon
        ]);
    }

    public function get(){
        
        return response()->json([
            "coupons"=>Coupon::all()
        ]);
    }
    
    public function show($couponCode){
        
        return Coupon::where('code', $couponCode)->first();
    }
   
    public function update(Request $request){
        
        $editedCoupon = $request->input('couponModel');
        $coupon = Coupon::where('id', $editedCoupon['id'])->first();
        
        $couponRules = [
            'code' => 'required|bail|alphanumerical',
            'redeems' => 'required|bail|integer',
            'reduction_value' => 'required|bail|integer'
        ];
        
        $couponValidator = Validator::create($editedCoupon, $couponRules);
        
        if($couponValidator->passes(){
            try {
              $coupon->code = $editedCoupon['code'];
              $coupon->redeems = $editedCoupon['redeems'];
              $coupon->reduction_value = $editedCoupon['reduction_value'];
              $coupon->save();
              return response()->json(['status' => 'coupon added'], 200);
            } catch(\Exception $e){
              return response()->json(['status' => 'An unexpected error ocurred while adding the coupon'], 400);
            }
        } else {
            return response()->json(['status' => 'validation failed'], 400);
        }
    }
    
    public function delete($couponId){
        
        try{
            Coupon::where('id', $couponId)->first()->delete(); 
            return response->json(['status' => 'Coupon deleted'], 200);
        } catch(\Exception $e){
            return response->json(['status' => 'Coupon deletion failed'], 400);
        }  
    }

}
