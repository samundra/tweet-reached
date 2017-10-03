<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

namespace App\Repository;

use App\Contracts\CalculatorInterface;
use App\Tweets\EngagementCalculator;
use Illuminate\Support\Facades\Log;

class TweetRepository
{
    /**
     * Check if the record is already cached in DB or not
     * @param string $id
     * @return bool Returns true/false based on whether cache is valid or not
     * Returns true - if record found in DB and still valid
     *  - 2 hour has not passed yet
     * Returns false - if record not found in DB
     *  - 2 hour has passed
     */
    public function isCacheValid(string $id) : bool
    {
        return false;
        // TODO Implement cache Logic
    }

    public function getCachedSum()
    {
        // TODO Get from DB
    }

    /**
     * @param string $id
     * @param float $sum
     */
    public function persistInDB(string $id, float $sum)
    {
        // TODO Write code to persist in DB
    }

    public function retrieveTweetReach(string $id, CalculatorInterface $calculator)
    {
        Log::info('Calculating tweet reach.', ['id' => $id]);
        $sum = $calculator->calculate($id);
        Log::info('Calculated tweet reach.', ['id' => $id, 'sum' => $sum]);

        return $sum;
    }
}
