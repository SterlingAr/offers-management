<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_dates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('offer_id')->unsigned()->nullable();
            $table->date('start_date');
            $table->date('end_date');
//            $table->dateTime('start_date');
//            $table->dateTime('end_date');
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
    }
}
