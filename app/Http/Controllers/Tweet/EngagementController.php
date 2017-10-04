<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

namespace App\Http\Controllers\Tweet;

use Exception;
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
        $isCached = $this->tweetRepository->isCacheValid($id);

        if ($isCached) {
            $sum = $this->tweetRepository->getCachedSum($id);
            Log::info('Returning from cache', ['id' => $id, 'sum' => $sum]);
            return new JsonResponse([
                'success' => true,
                'data' => [
                    'id' => $id,
                    'sum' => $sum,
                ]
            ]);
        }

        Log::info('Cache needs to be refreshed', ['id' => $id]);

        try {
            $sum = $this->tweetRepository->retrieveTweetReach($id, $this->calculator);

            $this->tweetRepository->persistInDB($id, $sum);
            return new JsonResponse([
                'success' => true,
                'data' => [
                    'sum' => $sum,
                    'id' => $id
                ]
            ]);
        } catch (Exception $exception) {
            Log::error('Error during engagement calculation', [
                'id' => $id,
                'stack_trace' => $exception->getTraceAsString(),
            ]);
            return new JsonResponse([
                'success' => false,
                'data' => [
                    'id' => $id,
                    'message' => 'Error occured during engagement calculation.',
                ]
            ]);
        }
    }
}
