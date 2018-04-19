<?php

use Faker\Generator as Faker;

$factory->define(App\Offer::class, function (Faker $faker) {
    return [
        'title' => 'Offer ' . $faker->city,
        'description' => $faker->realText()
    ];
});
