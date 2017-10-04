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

}
