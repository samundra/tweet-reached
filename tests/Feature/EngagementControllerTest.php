<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Class EngagementControllerTest
 * @package Tests\Feature
 * @coversDefaultClass \App\Http\Controllers\Tweet\EngagementController
 */
class EngagementControllerTest extends TestCase
{
    public function testCanAccessHomepage()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * @covers ::calculate
     */
    public function testCanAccessCalculatePage()
    {
        $response = $this->json('GET', '/calculate', [
            'query' => 'https://twitter.com/envydatropic/status/914208834700398592'
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure();

        $jsonResponse = $response->json();
        $response->assertStatus(200);

        $this->assertArrayHasKey('success', $jsonResponse);
        $this->assertArrayHasKey('data', $jsonResponse);
        $this->assertArrayHasKey('message', $jsonResponse['data']);
    }

    /**
     * @covers ::calculate
     */
    public function testCalculatePageReturnNoRetweetResponse()
    {
        $response = $this->json('GET', '/calculate', [
            'query' => 'https://twitter.com/samushr/status/914512720484904960'
        ]);

        $response->assertStatus(200);

        $data = $response->json();
        $expected = [
            'success' => false,
            'data' => [
                'message' => 'This tweet has not been retweeted yet.'
            ]
        ];

        $this->assertEquals($data, $expected);
    }

    /**
     * @covers ::calculate
     */
    public function testCalculateReturnValidationError()
    {
        $response = $this->json('GET', '/calculate');

        $actual = $response->json();
        $expected = [
            'message' => 'The given data was invalid.',
            'errors' => [
                'query' => [
                    'The query field is required.'
                ]
            ]
        ];

        $this->assertEquals($actual, $expected);
    }

    /**
     * @covers ::calculate
     */
    public function testTweetIsPersistedInDB()
    {
        // First Insert then reuse
        $response = $this->json('GET', '/calculate', [
            'query' => 'https://twitter.com/littledan/status/913224214760566784'
        ]);

        $response->assertStatus(200);

        // Get the tweet
        $tweetRepository = app('\App\Repository\TweetRepository');
        $tweet = $tweetRepository->getTweetById(913224214760566784);

        $this->assertInternalType('int', $tweet->id);
        $tweetRepository->destroy($tweet->id);

        $tweet = $tweetRepository->getTweetById(913224214760566784);
        $this->assertNull($tweet);

        $this->json('GET', '/calculate', [
            'query' => 'https://twitter.com/littledan/status/913224214760566784'
        ]);

        $response->assertStatus(200);
    }

    /**
     * @covers ::calculate
     */
    public function testTweetIsReturnedFromCache()
    {
        // Delete the previous entry if any
        $tweetRepository = app('\App\Repository\TweetRepository');
        $tweet = $tweetRepository->getTweetById(913224214760566784);
        $tweetRepository->destroy($tweet->id);

        // Insert New one, First Record
        $response = $this->json('GET', '/calculate', [
            'query' => 'https://twitter.com/littledan/status/913224214760566784'
        ]);

        $message1 = $response->json()['data']['message'];

        $response = $this->json('GET', '/calculate', [
            'query' => 'https://twitter.com/littledan/status/913224214760566784'
        ]);

        $message2 = $response->json()['data']['message'];

        $this->assertEquals($message1, 'Retrieved from Twitter API. Persisted in DB Cache.');
        $this->assertEquals($message2, 'Retrieved from cache. Cache is refreshed in every 2 hour.');
    }
}
