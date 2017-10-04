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
     * @var
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
     * Return the Tweet
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
     * Returns true - if record found in DB and still valid
     *  - 2 hour has not passed yet
     * Returns false for following cases:
     *  - if record not found in DB
     *  - cache is expired and needs to be refreshed. By default cache is valid
     *    only for 2 hour, @see tweetreach.cache_expire key in config/tweetreach.php
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
     * Query the DB and return the cached sum
     * @param string $id
     * @return int Total Sum
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
     * @param string $id
     * @param int $sum
     * @param Carbon $updatedAt
     * @param array $retweetInformation
     * @throws \Exception
     */
    public function persistInDB(string $id, int $sum, Carbon $updatedAt, array $retweetInformation)
    {
        $this->logger->info('Try to persist in DB.', ['id' => $id, 'sum' => $sum]);

        // Delete the previous record, we don't want to insert duplicate
        $record = $this->getTweetById($id);

        if ($record) {
            $this->logger->info('Deleted previous cache record.', ['id' => $id, 'sum' => $sum]);
            $this->tweet->destroy($record->id);
        }

        $this->tweet->where(['tweet_id' => $id])
           ->create([
               'tweet_id' => $id,
               'total_sum' => $sum,
               'updated_at' => $updatedAt,
               'info' => json_encode($retweetInformation),
           ]);

        $this->logger->info('Persisted cache in DB', ['id' => $id, 'sum' => $sum]);
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
        $users = $this->twitter->getUsersLookup(['user_id' => $userIds]);

        $sum = $calculator->calculate($users);
        $retweetInformation = $this->extractRetweetInformation($id, $users);

        $this->logger->info('Calculated tweet reach.', ['id' => $id, 'sum' => $sum, 'info' => json_encode($retweetInformation)]);

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
