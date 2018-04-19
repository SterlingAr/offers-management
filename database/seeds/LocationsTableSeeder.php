<?php

use Illuminate\Database\Seeder;
use App\Room;
class LocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

//        $location0 = Location::create([''])

        factory(App\Location::class,2)
            ->create()
            ->each(function($loc)
            {
                $room1 = Room::where('type', '1/2')->first();
                $room2 = Room::where('type', '1/3')->first();

                $loc->rooms()->save($room1);
                $loc->rooms()->save($room2);
            });

        factory(App\Location::class,2)
            ->create()
            ->each(function($loc)
            {
                $room1 = Room::where('type', '1/1')->first();
                $room2 = Room::where('type', '1/5')->first();

                $loc->rooms()->save($room1);
                $loc->rooms()->save($room2);
            });


        factory(App\Location::class,2)
            ->create()
            ->each(function($loc)
            {
                $room1 = Room::where('type', '1/4')->first();
                $room2 = Room::where('type', '1/3')->first();

                $loc->rooms()->save($room1);
                $loc->rooms()->save($room2);
            });

    }
}
