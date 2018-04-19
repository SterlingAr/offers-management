<?php

use Illuminate\Database\Seeder;
use App\OfferDate;
class OffersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        factory(App\Offer::class, 3)->create()->each(function($offer)
        {


            $offer_date = OfferDate::create([
                'start_date' => new DateTime('2016-01-01'),
                'end_date' => new DateTime('2016-01-05')
            ]);




            $offer->dates()->save($offer_date);

        });







    }
}
