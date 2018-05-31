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

        $coupon = Coupon::where('code', $couponCode)->first();

        if(isset($coupon)){
            if($coupon->redeems > 0){
                return response()->json(['coupon' => $coupon]);
            } else {
                return response()->json(['status' => 'Redeems number reached 0'],400);
            }
        }

        return response()->json(['status', 'Coupon does not exist'], 404);
    }
   
    public function update(Request $request){
        
        $editedCoupon = $request->input('coupon');
        $coupon = Coupon::where('id', $editedCoupon['id'])->first();
        
        $couponRules = [
            'code' => 'required|bail|alpha_num',
            'redeems' => 'required|bail|integer',
            'reduction_value' => 'required|bail|integer'
        ];
        
        $couponValidator = Validator::make($editedCoupon, $couponRules);
        
        if($couponValidator->passes()){
//            try {
              $coupon->code = $editedCoupon['code'];
              $coupon->redeems = $editedCoupon['redeems'];
              $coupon->reduction_value = $editedCoupon['reduction_value'];
              $coupon->save();
              return response()->json(['status' => 'coupon updated'], 200);
//            } catch(\Exception $e){
//              return response()->json(['status' => 'An unexpected error ocurred while adding the coupon'], 400);
//            }
        } else {
            return response()->json([
                'status' => 'validation failed',
                'errors' => $couponValidator->errors()->all()
                ], 400);
        }
    }
    
    public function delete($couponId){
        
        try{
            Coupon::where('id', $couponId)->first()->delete(); 
            return response()->json(['status' => 'Coupon deleted'], 200);
        } catch(\Exception $e){
            return response()->json(['status' => 'Coupon deletion failed'], 400);
        }  
    }

}
