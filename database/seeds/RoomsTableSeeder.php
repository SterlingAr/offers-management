<?php

use Illuminate\Database\Seeder;
use App\Room;

class RoomsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $roomType0 = Room::create(['type' => '1/1']);
        $roomType1 = Room::create(['type' => '1/2']);
        $roomType2 = Room::create(['type' => '1/3']);
        $roomType3 = Room::create(['type' => '1/4']);
        $roomType4 = Room::create(['type' => '1/5']);

        //just for testing, it should not allow you
        //$roomType3 = Room::create(['type' => '1/2']);
    }
}
