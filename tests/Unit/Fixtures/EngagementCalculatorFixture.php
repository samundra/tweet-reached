<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

namespace Tests\Unit\Fixtures;

class EngagementCalculatorFixture
{
    /**
     * Users fixture for the Engagement Calculator
     * @return array
     */
    public static function getUsers()
    {
        return [
            'users' => [
                [
                    'id' => 10,
                    'id_str' => '10',
                    'name' => 'Sample',
                    'screen_name' => 'sample',
                    'followers_count' => 10,
                    'protected' => false,
                ],
                [
                    'id' => 11,
                    'id_str' => '11',
                    'name' => 'Sample',
                    'screen_name' => 'sample',
                    'followers_count' => 440,
                    'protected' => false,
                ],
                [
                    'id' => 12,
                    'id_str' => '11',
                    'name' => 'Sample',
                    'screen_name' => 'sample',
                    'followers_count' => 140,
                    'protected' => false,
                ]
            ],
            'expected' => 590,
        ];
    }

    /**
     * Users fixture for the Engagement Calculator
     * @return array
     */
    public static function getUsersAsObject()
    {
        return [
            (object) [
                'id' => 10,
                'id_str' => '10',
                'name' => 'Sample',
                'screen_name' => 'sample_1',
                'followers_count' => 10,
                'protected' => false,
            ],
            (object) [
                'id' => 11,
                'id_str' => '11',
                'name' => 'Sample',
                'screen_name' => 'sample_2',
                'followers_count' => 20,
                'protected' => false,
            ],
            (object) [
                'id' => 12,
                'id_str' => '11',
                'name' => 'Sample',
                'screen_name' => 'sample_3',
                'followers_count' => 20,
                'protected' => false,
            ]
        ];
    }
}
