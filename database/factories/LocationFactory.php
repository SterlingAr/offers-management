<?php

use Faker\Generator as Faker;

$factory->define(App\Location::class, function (Faker $faker) {

    return [
        'name' => 'Hotel ' .  $faker->colorName,
        'address' => $faker->address,
        'phone' => $faker->phoneNumber,
        'description' => $faker->realText(),
        'landline' => $faker->phoneNumber,
    ];
});
