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
use Thujohn\Twitter\Twitter;

class TweetRepository
{
    /**
     * Twitter returns at max 100 users at a time, if we need more than 100 users,
     * then we have to paginate the request ourself using Twitter Cursor
     */
    const TWITTER_RESPONSE_LIMIT = 100;

    /**
     * @var \Thujohn\Twitter\Twitter
     */
    protected $twitter;

    public function __construct(Twitter $twitter)
    {
        $this->twitter = $twitter;
    }

    /**
     * Return the Tweet
     * @param string $id
     * @return TweetReachModel|null
     */
    public function getTweetById(string $id)
    {
        return TweetReachModel::where(['tweet_id' => $id])
            ->select(['id', 'total_sum', 'updated_at', 'info'])->first();
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
     * @param array $retweetInformation
     * @throws \Exception
     */
    public function persistInDB(string $id, float $sum, array $retweetInformation)
    {
        Log::info('Try to persist in DB.', ['id' => $id, 'sum' => $sum]);

        // Delete the previous record, we don't want to insert duplicate
        $record = $this->getTweetById($id);
        if ($record) {
            Log::info('Deleted previous cache record.', ['id' => $id, 'sum' => $sum]);
            TweetReachModel::destroy($record->id);
        }

        TweetReachModel::where(['tweet_id' => $id])
           ->create([
               'tweet_id' => $id,
               'total_sum' => $sum,
               'updated_at' => new Carbon('now', 'UTC'),
               'info' => json_encode($retweetInformation),
           ]);

        Log::info('Persisted cache in DB', ['id' => $id, 'sum' => $sum]);
    }

    /**
     * Return all retweeters for the tweet
     * @param string $id Status ID
     * @return array Result set
     */
    private function getRetweeters(string $id) : array
    {
        /**
         * There is known issue that the result sets for the retweet cannot be
         * paginated. The maximum results that can be obtained is 100. So, setting
         * the count to 100 is workaround.
         *
         * @see https://twittercommunity.com/t/paging-is-not-possible-with-statuses-retweeters-ids-json/71298/8
         */
        $parameters = [
            'id' => $id,
            'count' => self::TWITTER_RESPONSE_LIMIT,
            'format' => 'array',
        ];

        return $this->twitter->getRters($parameters);
    }

    /**
     * Returns the total number of people reached by the retweet
     * @param string $id Tweet ID
     * @param \App\Contracts\CalculatorInterface $calculator
     * @return array aggregated data
     */
    public function aggregate(string $id, CalculatorInterface $calculator) : array
    {
        Log::info('Calculating tweet reach.', ['id' => $id]);
        $followers = $this->getRetweeters($id);
        $userIds = implode(',', $followers['ids']);

        // UsersLookup allows us to make bulk request per 100 user,
        // It's efficient then making 100 individual request with user/show/:id
        $users = $this->twitter->getUsersLookup(['user_id' => $userIds]);

        $sum = $calculator->calculate($users);
        $retweetInformation = $this->extractRetweetInformation($id, $users);

        Log::info('Calculated tweet reach.', ['id' => $id, 'sum' => $sum, 'info' => json_encode($retweetInformation)]);

        return [
            'sum' => $sum,
            'retweetInformation' => $retweetInformation,
        ];
    }

    /**
     * Extract Tweet information like, retweet count, retweeters
     * @param string $id
     * @param array $users
     * @return array
     */
    protected function extractRetweetInformation(string $id, array $users) : array
    {
        $tweet = $this->twitter->getTweet($id, ['format' => 'array']);

        $information = [];
        $information['retweetCount'] = $tweet['retweet_count'];
        $information['retweeters'] = [];

        foreach ($users as $user) {
            $information['retweeters'][] = [
                'name' => $user->screen_name,
                'followersCount' => $user->followers_count,
            ];
        }

        return $information;
    }

    /**
     * Return Tweet Information
     * @param string $id
     * @return array
     */
    public function getRetweetInformation(string $id) : array
    {
        $tweet = $this->getTweetById($id);

        return json_decode($tweet->info, true);
    }

    /**
     * Get Retweet Count for the tweet
     * @param string $id
     * @return mixed
     */
    public function isRetweeted(string $id)
    {
        $tweet = $this->twitter->getTweet($id);

        return (bool) $tweet->retweet_count;
    }
}
