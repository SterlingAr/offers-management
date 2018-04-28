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

                $loc->rooms()->attach($room1,['num_rooms' => 5, 'price_person' => 150, 'person_number' => 2]);
                $loc->rooms()->attach($room2,['num_rooms' => 4, 'price_person' => 120, 'person_number' => 3]);
            });

        factory(App\Location::class,2)
            ->create()
            ->each(function($loc)
            {
                $room1 = Room::where('type', '1/1')->first();
                $room2 = Room::where('type', '1/5')->first();

                $loc->rooms()->attach($room1,['num_rooms' => 3, 'price_person' => 250, 'person_number' => 1]);
                $loc->rooms()->attach($room2,['num_rooms' => 2, 'price_person' => 120, 'person_number' => 5]);
            });


        factory(App\Location::class,2)
            ->create()
            ->each(function($loc)
            {
                $room1 = Room::where('type', '1/4')->first();
                $room2 = Room::where('type', '1/3')->first();

                $loc->rooms()->attach($room1,['num_rooms' => 1, 'price_person' => 1650, 'person_number' => 4]);
                $loc->rooms()->attach($room2,['num_rooms' => 0, 'price_person' => 110, 'person_number' => 3]);
            });

    }
}
