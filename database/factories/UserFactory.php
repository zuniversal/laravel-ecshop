<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

// use App\User;
use App\Models\User\User;// 8-3
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'username' => $faker->name,
        'password' => Hash::make('123456'), 
        'gender' => $faker->randomKey([0, 1, 2]),
        'mobile' => $faker->phoneNumber,
        'avatar' => $faker->imageUrl(),
    ];
});
