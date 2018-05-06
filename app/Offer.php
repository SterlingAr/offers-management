<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    public $timestamps = false;

    protected $fillable = ['id', 'title','description'];

    public function locations()
    {
        return $this->belongsToMany('App\Location','offer_location');
    }


    public function dates()
    {
        return $this->hasMany('App\OfferDate');
    }
//
//    public function sale(){
//        return $this->hasMany('App\Sale');
//    }

}
