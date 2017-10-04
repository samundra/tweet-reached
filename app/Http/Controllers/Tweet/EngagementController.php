<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

namespace App\Http\Controllers\Tweet;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
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

    public function __construct(TweetRepository $tweetRepository, EngagementCalculator $calculator)
    {
        $this->tweetRepository = $tweetRepository;
        $this->calculator = $calculator;
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
            $sum = $this->tweetRepository->getCachedSum($id);
            $retweetInformation = $this->tweetRepository->getRetweetInformation($id);

            Log::info('Returning from cache', ['id' => $id, 'sum' => $sum]);
            return $this->showSuccessJsonResponse([
                'id' => $id,
                'sum' => $sum,
                'tweet' => $retweetInformation,
                'message' => __('message.from_cache'),
            ]);
        }

        Log::info('Cache needs to be refreshed', ['id' => $id]);

        try {
            // if it has never been retweeted then no need to aggregate data
            if (false === $this->tweetRepository->isRetweeted($id)) {
                return $this->showNoRetweetJsonResponse();
            }

            $aggregatedData = $this->tweetRepository->aggregate($id, $this->calculator);
            $sum = $aggregatedData['sum'];
            $retweetInformation = $aggregatedData['retweetInformation'];

            $this->tweetRepository->persistInDB(
                $id,
                $sum,
                new Carbon('now', 'UTC'),
                $retweetInformation
            );

            return $this->showSuccessJsonResponse([
                'id' => $id,
                'sum' => $sum,
                'tweet' => $retweetInformation,
                'message' => __('message.from_twitter_api'),
            ]);
        } catch (Exception $exception) {
            Log::error('Error occured', [
                'id' => $id,
                'stack_trace' => $exception->getTraceAsString(),
                'method' => __METHOD__,
                'line' => __LINE__,
            ]);
            return $this->showErrorJsonResponse([
                'id' => $id,
                'message' => 'Error ::' . $exception->getMessage(),
            ]);
        }
    }

    /**
     * Success Json response
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function showErrorJsonResponse($data)
    {
        return new JsonResponse([
            'success' => false,
            'data' => $data,
        ]);
    }


    /**
     * Success Json response
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function showSuccessJsonResponse($data)
    {
        return new JsonResponse([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Show the Empty Response
     * @return \Illuminate\Http\JsonResponse
     */
    public function showNoRetweetJsonResponse()
    {
        return new JsonResponse([
            'success' => false,
            'data' => [
                'message' => __('message.no_retweet')
            ]
        ]);
    }
}
