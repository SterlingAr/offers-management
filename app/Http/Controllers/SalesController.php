<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class SalesController extends Controller
{

    public function indexTable(Request $request){

        return response()->json(['status' => 'test']);
    }

    public function show($saleId){

    }

    public function store(Request $request){

    }

    public function update(Request $request, $saleId){

    }

    public function delete($saleId){

    }


}
