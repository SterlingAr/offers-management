<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LocationRoom extends Model
{
    //
    public $timestamps = false;

    protected $table = 'location_room';

//    protected $fillable = ['location_id', 'room_id', 'price', 'num_rooms'];
    protected $fillable = ['price', 'num_rooms'];

    public function offerDates()
    {
        return $this->belongsToMany('App\OfferDate','offer_dates_location_room');
    }

    public function room() {
        return $this->belongsTo('App\Room');
    }


}
