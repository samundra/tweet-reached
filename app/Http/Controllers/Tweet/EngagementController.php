<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

namespace App\Http\Controllers\Tweet;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use App\Repository\TweetRepository;
use App\Tweets\EngagementCalculator;
use App\Http\Controllers\Controller;
use App\Http\Requests\CalculateRequest;

class EngagementController extends Controller
{
    /**
     * @var \App\Repository\TweetRepository
     */
    protected $tweetRepository;

    /**
     * @var \App\Http\Controllers\Tweet\EngagementCalculator
     */
    protected $calculator;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        TweetRepository $tweetRepository,
        EngagementCalculator $calculator,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->tweetRepository = $tweetRepository;
        $this->calculator = $calculator;
        $this->logger = $logger;
    }

    /**
     * Extracts the tweet id from the supplied query url
     * @param string $query
     * @return string Tweet ID
     * @throws \Exception
     */
    private function extractIdFromRequestQuery(string $query) : string
    {
        $url = explode('/', parse_url($query)['path']);

        if (!isset($url[2]) || !isset($url[3])) {
            throw new Exception("Not enough data to retrieve tweet information.");
        }

        if ($url[2] === 'status') {
            return $url[3]; // Position of the status id
        }
    }

    /**
     * Calculate the total reached engagement
     * @param \App\Http\Requests\CalculateRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function calculate(CalculateRequest $request) : JsonResponse
    {
        $id = $this->extractIdFromRequestQuery($request->get('query'));
        $format = config('tweetreach.date_format');
        $expire =  config('tweetreach.cache_expire');
        $isCached = $this->tweetRepository->isCacheValid($id, $format, $expire);

        if ($isCached) {
            $peopleReached = $this->tweetRepository->getCachedSum($id);
            $retweetInformation = $this->tweetRepository->getRetweetInformation($id);

            $this->logger->info('Returning from cache', ['id' => $id, 'peopleReached' => $peopleReached]);
            return $this->showJsonResponse(true, [
                'id' => $id,
                'peopleReached' => $peopleReached,
                'tweet' => $retweetInformation,
                'message' => __('message.from_cache'),
            ]);
        }

        $this->logger->info('Cache needs to be refreshed', ['id' => $id]);

        try {
            // if it has never been retweeted then no need to aggregate data
            $updatedAt = new Carbon('now', 'UTC');
            if (false === $this->tweetRepository->isRetweeted($id)) {
                $peopleReached = 0;
                $retweetInformation = ['retweetCount' => 0, 'retweeters' => []];
                $defaultRecord = [
                    'id' => $id,
                    'peopleReached' => $peopleReached,
                    'tweet' => $retweetInformation,
                    'message' => __('message.no_retweet'),
                ];
                $this->tweetRepository->persistInDB($id, $peopleReached, $updatedAt, $retweetInformation);

                return $this->showJsonResponse(false, $defaultRecord);
            }

            $aggregatedData = $this->tweetRepository->aggregate($id, $this->calculator);
            $peopleReached = $aggregatedData['peopleReached'];
            $retweetInformation = $aggregatedData['retweetInformation'];

            $this->tweetRepository->persistInDB($id, $peopleReached, $updatedAt, $retweetInformation);

            return $this->showJsonResponse(true, [
                'id' => $id,
                'peopleReached' => $peopleReached,
                'tweet' => $retweetInformation,
                'message' => __('message.from_twitter_api'),
            ]);
        } catch (Exception $exception) {
            $this->logger->error('Error occured', [
                'id' => $id,
                'stack_trace' => $exception->getTraceAsString(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return $this->showJsonResponse(false, [
                'id' => $id,
                'message' => 'Error ::' . $exception->getMessage(),
            ]);
        }
    }

    /**
     * Success Json response
     * @param $type Type of Response (true|false)
     * @param $data Data to send to client
     * @return \Illuminate\Http\JsonResponse
     */
    public function showJsonResponse($type, $data)
    {
        return new JsonResponse([
            'success' => $type,
            'data' => $data,
        ]);
    }
}
