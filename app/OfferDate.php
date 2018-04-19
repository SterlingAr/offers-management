<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfferDate extends Model
{
    //

    protected $dates = ['start_date', 'end_date'];

//    protected $dateFormat = 'Y-m-d';

    protected $fillable = ['offer_id','start_date', 'end_date'];

    public $timestamps = false;


    public function offer()
    {
        return $this->belongsTo('App\Offer');
    }


    public function locationRooms()
    {
        return $this->belongsToMany('App\LocationRoom','offer_dates_location_room')
            ->withPivot('price_person','person_number','available_rooms')->as('offer_details');
    }

    public function locations()
    {
        return $this->belongsToMany('App\Location','offer_date_location');
    }



    //European date format
    public function getStartDateAttribute($value) {
        return \Carbon\Carbon::parse($value)->format('d-m-Y');
    }

    public function getEndDateAttribute($value) {
        return \Carbon\Carbon::parse($value)->format('d-m-Y');
    }

    //one OfferDate instance can have many LocationRoom instances and one LocationRoom


}
