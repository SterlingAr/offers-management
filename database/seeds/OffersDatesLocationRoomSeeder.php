<?php

use Illuminate\Database\Seeder;
use App\Room;
use App\LocationRoom;
use App\OfferDate;

class OffersDatesLocationRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $location =  factory(App\Location::class)->create();
        $room = Room::where('type', '1/4')->first();

        //this model is used only in combination with OfferDate. When you want to add a price for a certain roomType for a certain offerDate
        $locationRoom = LocationRoom::create(['location_id' => $location->id, 'room_id' => $room->id]);

//        $location->rooms()->attach($room);

        $offer = factory(App\Offer::class)->create();

        $offer_date = OfferDate::create([
            'start_date' => new DateTime('2016-01-01'),
            'end_date' => new DateTime('2016-01-05')
        ]);

        $offer->dates()->save($offer_date);

        $offer_date->locationRooms()->attach($locationRoom);

    }
}
