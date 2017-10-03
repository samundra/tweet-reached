<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

namespace App\Tweets;

use Thujohn\Twitter\Twitter;
use App\Contracts\CalculatorInterface;

class EngagementCalculator extends Twitter implements CalculatorInterface
{
    /**
     * Twitter returns at max 100 users at a time, if we need more than 100 users,
     * then we have to paginate the request ourself using Twitter Cursor
     */
    const TWITTER_RESPONSE_LIMIT = 100;

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

        return $this->getRters($parameters);
    }

    /**
     * Calculates the engagement for the supplied id
     * @param $id
     * @return int Sum of Followers
     */
    public function calculate($id) : int
    {
        $followers = $this->getRetweeters($id);

        $userIds = implode(',', $followers['ids']);

        // UsersLookup allows us to make bulk request per 100 user,
        // It's efficient then making 100 individual request with user/show/:id
        $users = $this->getUsersLookup(['user_id' => $userIds]);

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
