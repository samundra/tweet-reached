<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

namespace App\Repository;

use Exception;
use Carbon\Carbon;
use Psr\Log\LoggerInterface;
use Thujohn\Twitter\Twitter;
use App\Models\TweetReachModel;
use App\Contracts\CalculatorInterface;

class TweetRepository
{
    /**
     * Twitter returns at max 100 users at a time, if we need more than 100 users,
     * then we have to paginate the request ourself using Twitter Cursor
     */
    const TWITTER_RESPONSE_LIMIT = 100;

    /**
     * @var \App\Models\TweetReachModel $tweet
     */
    protected $tweet;

    /**
     * @var \Thujohn\Twitter\Twitter $twitter
     */
    protected $twitter;

    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * TweetRepository constructor.
     * @param \App\Models\TweetReachModel $tweet
     * @param \Thujohn\Twitter\Twitter $twitter
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        TweetReachModel $tweet,
        Twitter $twitter,
        LoggerInterface $logger
    ) {
        $this->tweet = $tweet;
        $this->twitter = $twitter;
        $this->logger = $logger;
    }

    /**
     * Return the Tweet from the cached content
     * @param string $id
     * @return TweetReachModel|null
     */
    public function getTweetById(string $id)
    {
        return $this->tweet->where(['tweet_id' => $id])
            ->select(['id', 'total_sum', 'updated_at', 'info'])->first();
    }

    /**
     * Check if the record is already cached in DB or not
     * @param string $id Tweet ID
     * @param string $format Format for the date supplied to carbon
     * @param int $expire Cache expire time
     * @return bool Returns true/false based on whether cache is valid or not
     * Returns true - if record found in DB and still valid &&
     *  - 2 hour has not passed yet
     * Returns false for following cases:
     *  - if record not found in DB
     *  - cache is expired and needs to be refreshed.
     * By default cache is valid only for 2 hour,
     * @see tweetreach.cache_expire key in config/tweetreach.php
     */
    public function isCacheValid(string $id, string $format, int $expire) : bool
    {
        $records = $this->getTweetById($id);

        if ($records) {
            $updatedTime = Carbon::createFromFormat($format, $records->updated_at);

            $now = Carbon::now('UTC');
            $hours = $now->diffInHours($updatedTime);

            return $hours < intval($expire);
        }

        return false;
    }

    /**
     * Query the DB and return the cached sum. This is count of people reached
     * by the retweet.
     * @param string $id Tweet ID
     * @return int count of people reached by the retweet.
     * @throws \Exception
     */
    public function getCachedSum(string $id) : int
    {
        $records = $this->tweet->where(['tweet_id' => $id])
          ->select(['total_sum'])->first();

        if (!$records) {
            throw new Exception("Trying to access invalid cache.");
        }

        return intval($records->total_sum);
    }

    /**
     * Delete the existing record for the requested tweet.
     * @param int $id Record to delete from DB
     * @return bool True if deleted otherwise false
     */
    public function destroy(int $id) : bool
    {
        $this->logger->info('Destroying retweet', ['id' => $id]);

        return (bool) $this->tweet->destroy($id);
    }

    /**
     * Persist the record in the database. Before trying to persist it checks
     * if the record is already there or not. If it exists then it deletes the
     * record and makes a new entry. This is simpler and faster than doing the
     * update.
     * @param string $id Tweet ID
     * @param int $peopleReached Total Followers Reached
     * @param Carbon $updatedAt Timetamp when record i updated
     * @param array $retweetInformation Retweet Information array
     * @throws \Exception Throws Database related exceptions
     */
    public function persistInDB(string $id, int $peopleReached, Carbon $updatedAt, array $retweetInformation)
    {
        $this->logger->info('Try to persist in DB.', ['id' => $id, 'peopleReached' => $peopleReached]);

        $record = $this->getTweetById($id);

        if ($record) {
            $this->logger->info('Deleted previous cache record.', ['id' => $id, 'peopleReached' => $peopleReached]);
            $this->destroy($record->id);
        }

        $this->tweet->where(['tweet_id' => $id])
           ->create([
               'tweet_id' => $id,
               'total_sum' => $peopleReached,
               'updated_at' => $updatedAt,
               'info' => json_encode($retweetInformation),
           ]);

        $this->logger->info('Persisted cache in DB', ['id' => $id, 'peopleReached' => $peopleReached]);
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
        $this->logger->info('Calculating tweet reach.', ['id' => $id]);
        $followers = $this->getRetweeters($id);
        $userIds = implode(',', $followers['ids']);

        // UsersLookup allows us to make bulk request per 100 user,
        // It's efficient then making 100 individual request with user/show/:id
        // @see https://developer.twitter.com/en/docs/accounts-and-users/follow-search-get-users/api-reference/get-users-lookup
        $users = $this->twitter->getUsersLookup(['user_id' => $userIds]);

        $peopleReached = $calculator->calculate($users);
        $retweetInformation = $this->extractRetweetInformation($id, $users);

        $this->logger->info('Calculated tweet reach.', [
            'id' => $id,
            'peopleReached' => $peopleReached,
            'info' => json_encode($retweetInformation)
        ]);

        return [
            'peopleReached' => $peopleReached,
            'retweetInformation' => $retweetInformation,
        ];
    }

    /**
     * Extract Tweet information like, retweet count, retweeters
     * @param string $id Tweet ID
     * @param array $users Array of users
     * @return array Retweet Information
     */
    protected function extractRetweetInformation(string $id, array $users) : array
    {
        $tweet = $this->twitter->getTweet($id, ['format' => 'array']);

        $information = [];
        $information['retweetCount'] = number_format($tweet['retweet_count']);
        $information['retweeters'] = [];

        foreach ($users as $user) {
            $information['retweeters'][] = [
                'name' => $user->screen_name,
                'followersCount' => number_format($user->followers_count),
            ];
        }

        return $information;
    }

    /**
     * Return ReTweet Information from the cached data
     * @param string $id Twitter ID
     * @return array Retweet Information obtained From DB
     */
    public function getRetweetInformation(string $id) : array
    {
        $tweet = $this->getTweetById($id);

        return json_decode($tweet->info, true);
    }

    /**
     * Get Retweet Count for the tweet. This make API call to the twitter and
     * should only be used as guard to process other requests which depend on
     * retweet count. Alternate way is to directly check the tweet->retweeted_status
     * @param string $id Tweet ID
     * @return bool Returns true if retweeted otherwise false
     */
    public function isRetweeted(string $id)
    {
        $tweet = $this->twitter->getTweet($id);

        return (bool) $tweet->retweet_count;
    }
}
