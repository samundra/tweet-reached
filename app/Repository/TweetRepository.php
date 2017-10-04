<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

namespace App\Repository;

use Exception;
use Carbon\Carbon;
use App\Models\TweetReachModel;
use Illuminate\Support\Facades\Log;
use App\Contracts\CalculatorInterface;

class TweetRepository
{
    /**
     * Return the Tweet
     * @param string $id
     * @return TweetReachModel|null
     */
    public function getTweetById(string $id)
    {
        return TweetReachModel::where(['tweet_id' => $id])
            ->select(['id', 'total_sum', 'updated_at'])->first();
    }

    /**
     * Check if the record is already cached in DB or not
     * @param string $id
     * @return bool Returns true/false based on whether cache is valid or not
     * Returns true - if record found in DB and still valid
     *  - 2 hour has not passed yet
     * Returns false for following cases:
     *  - if record not found in DB
     *  - cache is expired and needs to be refreshed. By default cache is valid
     *    only for 2 hour, @see tweetreach.cache_expire key in config/tweetreach.php
     */
    public function isCacheValid(string $id) : bool
    {
        $records = $this->getTweetById($id);

        if ($records) {
            $updatedTime = Carbon::createFromFormat(
                config('tweetreach.date_format'),
                $records->updated_at
            );

            $now = Carbon::now('UTC');
            $hours = $now->diffInHours($updatedTime);

            return $hours < intval(config('tweetreach.cache_expire'));
        }

        return false;
    }

    /**
     * Query the DB and return the cached sum
     * @param string $id
     * @return int Total Sum
     * @throws \Exception
     */
    public function getCachedSum(string $id) : int
    {
        $records = TweetReachModel::where(['tweet_id' => $id])
          ->select(['total_sum'])->first();

        if (!$records) {
            throw new Exception("Trying to access invalid cache.");
        }

        return intval($records->total_sum);
    }

    /**
     * @param string $id
     * @param float $sum
     * @throws \Exception
     */
    public function persistInDB(string $id, float $sum)
    {
        Log::info('Try to persist in DB.', ['id' => $id, 'sum' => $sum]);

        // Delete the previous record, we don't want to insert duplicate
        $record = $this->getTweetById($id);
        if ($record) {
            Log::info('Deleted previous cache record.', ['id' => $id, 'sum' => $sum]);
            TweetReachModel::destroy($record->id);
        }

        TweetReachModel::where(['tweet_id' => $id])
           ->updateOrCreate([
               'tweet_id' => $id,
               'total_sum' => $sum,
               'updated_at' => Carbon::now('UTC'),
           ]);
        Log::info('Persisted cache in DB', ['id' => $id, 'sum' => $sum]);
    }

    /**
     * Returns the total number of people reached by the retweet
     * @param string $id Tweet ID
     * @param \App\Contracts\CalculatorInterface $calculator
     * @return int Total sum of the follower
     */
    public function retrieveTweetReach(string $id, CalculatorInterface $calculator) : int
    {
        Log::info('Calculating tweet reach.', ['id' => $id]);
        $sum = $calculator->calculate($id);
        Log::info('Calculated tweet reach.', ['id' => $id, 'sum' => $sum]);

        return $sum;
    }
}
