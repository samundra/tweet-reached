<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

namespace App\Tweets;

use App\Contracts\CalculatorInterface;

class EngagementCalculator implements CalculatorInterface
{
    /**
     * Calculates the engagement for the supplied id
     * @param array $users Array of Followers
     * @return int Sum of Followers
     */
    public function calculate($users) : int
    {
        return $this->sumFollowerCount($users);
    }

    /**
     * Sum the followers count
     * @param array $users
     * @return int Sum of Followers
     */
    private function sumFollowerCount(array $users) : int
    {
        return array_sum(array_column($users, 'followers_count'));
    }
}
