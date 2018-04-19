<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferDateLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_date_location', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('offer_date_id')->unsigned();
            $table->integer('location_id')->unsigned();
            $table->timestamps();
        });


        Schema::table('offer_date_location', function(Blueprint $table) {

            $table->foreign('offer_date_id')
                ->references('id')
                ->on('offer_dates')
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
        Schema::dropIfExists('offer_date_location');
    }
}