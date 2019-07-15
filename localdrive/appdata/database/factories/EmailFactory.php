<?php

$factory->define(App\Email::class, function (Faker\Generator $faker) {
    return [
        "from" => $faker->safeEmail,
        "to" => $faker->safeEmail,
    ];
});
