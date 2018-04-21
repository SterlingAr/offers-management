<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleRoomAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_room_allocations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('offer_dates_location_room_id')->unsigned();
            $table->integer('sale_id')->unsigned();
            $table->integer('persons_number');
            $table->text('persons_names');
            $table->timestamps();

            $table->foreign('offer_dates_location_room_id')
                ->references('id')
                ->on('offer_dates_location_room')
                ->onDelete('cascade');

            $table->foreign('sale_id')
                ->references('id')
                ->on('sales')
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
        Schema::dropIfExists('sale_room_allocations');
    }
}
