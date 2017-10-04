<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

namespace App\Contracts;

interface CalculatorInterface
{
    /**
     * Calculate the total follower sum for the twitter user objects
     * @param mixed $users Array of Twitter User Object
     * @see https://developer.twitter.com/en/docs/tweets/data-dictionary/overview/user-object
     * @return int
     */
    public function calculate($users) : int;
}
