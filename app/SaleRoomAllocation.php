<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleRoomAllocation extends Model
{
    protected $fillable=[
        "offer_dates_location_room_id",
        "sale_id",
        "persons_going",
        "persons_names",

    ];

    protected $casts=[
        "persons_names"=>"array"
    ];
}
