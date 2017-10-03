<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

use Faker\Generator as Faker;

$factory->define(App\Models\TweetReachModel::class, function (Faker $faker) {
    static $hour = 1;
    static $start = 915109755902988289;

    return [
        'tweet_id' => $start++,
        'total_sum' => $faker->randomNumber(7),
        'updated_at' => (new DateTime())
            ->setTimezone(new DateTimeZone('UTC'))
            ->add(new DateInterval('PT' . $hour++ . 'H')),
    ];
});
