<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OfferDatesLocationRoomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_dates_location_room', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('offer_date_id')->unsigned();
            $table->integer('location_id')->unsigned()->nullable();
            $table->integer('location_room_id')->unsigned();
            $table->integer('price_person')->unsigned()->nullable();
            $table->integer('person_number')->unsigned()->nullable();
            $table->integer('available_rooms')->unsigned()->nullable();
//            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
