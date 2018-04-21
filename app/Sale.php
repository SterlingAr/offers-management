<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        "first_name",
        "last_name",
        "email",
        "phone",
        "offer_id",
        "location_id",
        "offer_date_id",
        "total_person_number",
        "payment_status",
        "coupon_code",
        "total_amount"

    ];

}
