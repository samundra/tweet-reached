<?php
/**
 * @author Zen eServices Pte Ltd
 * @copyright Copyright (c) 2017 Zen eServices Pte Ltd
 */

namespace Tests\Unit\Fixtures;

class TweetRepositoryFixture
{
    public static function getAggregateFixtures()
    {
        return [
            'userIds' => [111, 2233, 333, 444],
            'expected' => [
                'sum' => 50,
                'retweetInformation' => [
                    'retweetCount' => 1000,
                    'retweeters' => [
                        [
                            'name' => 'sample_1',
                            'followersCount' => 10,
                        ],
                        [
                            'name' => 'sample_2',
                            'followersCount' => 20,
                        ],
                        [
                            'name' => 'sample_3',
                            'followersCount' => 20,
                        ],
                    ]
                ]
            ]
        ];
    }

    public static function getRetweetInformationFixture()
    {
        return [
            'retweetCount' => 118627,
            'retweeters' => [
                [
                    'name' => 'UserA',
                    'followersCount' => 1,
                ],
                [
                    'name' => 'UserB',
                    'followersCount' => 19,
                ],
            ]
        ];
    }

    public static function getTwitterApiTweetObject($retweetCount)
    {
        return (object) [
            'retweet_count' => $retweetCount
        ];
    }
}
