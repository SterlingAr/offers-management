<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OfferDatesLocationRoomConstraint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offer_dates_location_room', function(Blueprint $table) {

            $table->foreign('offer_date_id')
                ->references('id')
                ->on('offer_dates')
                ->onDelete('cascade');

            $table->foreign('location_room_id')
                ->references('id')
                ->on('location_room')
                ->onDelete('cascade');

            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onDelete('cascade');
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
