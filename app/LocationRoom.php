<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LocationRoom extends Model
{
    //
    public $timestamps = false;

    protected $table = 'location_room';

    protected $fillable = ['location_id', 'room_id', 'price'];

    public function offerDates()
    {
        return $this->belongsToMany('App\OfferDate','offer_dates_location_room');
    }


}
