<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    public $timestamps = false;

    protected $fillable = ['id', 'name', 'address', 'phone', 'landline', 'description'];


//    public function offers()
//    {
//        return $this->belongsToMany('App\Offer','offer_location');
//    }

    public function dates()
    {
        return $this->belongsToMany('App\OfferDate','offer_date_location');
    }


    public function relatedRooms()
    {
//

        return $this->belongsToMany('App\LocationRoom','offer_dates_location_room')
            ->withPivot('price_person','person_number','available_rooms')->as('offer_details');

    }

    public function rooms()
    {
        return $this->belongsToMany('App\Room')->withPivot('price_person','person_number','available_rooms')->as('predefined_values');
        ;
    }

}
