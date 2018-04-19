<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    public $timestamps = false;

    protected $fillable = ['id', 'type'];

    public function locations()
    {
        return $this->belongsToMany('App\Location');
    }




}
