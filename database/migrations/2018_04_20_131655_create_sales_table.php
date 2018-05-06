<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('offer_id')->unsigned();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->integer('total_person_number')->nullable();
            $table->enum('payment_status', ['paid', 'notpaid'])->default("notpaid");
            $table->string('coupon_code')->nullable();
            $table->decimal('total_amount',4,2)->nullable();

            $table->timestamps();

//            $table->foreign('location_id')
//                ->references('id')
//                ->on('locations')
//                ->onDelete('cascade');



//            $table->foreign('offer_date_id')
//                ->references('id')
//                ->on('offer_dates')
//                ->onDelete('cascade');


        });

        Schema::table('sales', function(Blueprint $table) {

            $table->foreign('offer_id')
                ->references('id')
                ->on('offers')
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
        Schema::dropIfExists('sales');
    }
}
