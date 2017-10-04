<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

namespace Tests\Unit\Repository;

use Mockery;
use Tests\TestCase;
use Thujohn\Twitter\Twitter;
use Psr\Log\LoggerInterface;
use App\Models\TweetReachModel;
use App\Repository\TweetRepository;
use App\Contracts\CalculatorInterface;
use Tests\Unit\Fixtures\TweetRepositoryFixture;
use Tests\Unit\Fixtures\EngagementCalculatorFixture;

/**
 * Class TweetRepositoryTest
 * @package Tests\Unit\Repository
 * @coversDefaultClass \App\Repository\TweetRepository
 */
class TweetRepositoryTest extends TestCase
{
    /**
     * @var \App\Models\TweetReachModel|\Mockery\MockInterface
     */
    protected $tweet;

    /**
     * @var \Thujohn\Twitter\Twitter|\Mockery\MockInterface
     */
    protected $twitter;

    /**
     * @var \Psr\Log\LoggerInterface|\Mockery\MockInterface
     */
    protected $logger;

    /**
     * @var \App\Repository\TweetRepository
     */
    protected $repository;

    public function setUp()
    {
        $this->tweet = Mockery::mock(TweetReachModel::class);
        $this->twitter = Mockery::mock(Twitter::class);
        $this->logger = Mockery::mock(LoggerInterface::class);

        $this->logger->shouldReceive('info');

        $this->repository = new TweetRepository(
            $this->tweet,
            $this->twitter,
            $this->logger
        );
    }

    /**
     * @covers ::getTweetById
     */
    public function testGetTweetByIdReturnsTweetReachModel()
    {
        $model = new TweetReachModel([
            'id' => 1,
            'tweet_id' => 10000,
            'total_sum' => 118627,
            'info' => '{"retweetCount":140,"retweeters":[{"name":"Sam Shrestha","followersCount":700},{"name":"Sam Shrestha","followersCount":980}]}',
        ]);

        $this->tweet->shouldReceive('where')->once()->andReturnSelf()
            ->shouldReceive('select')->with(['id', 'total_sum', 'updated_at', 'info'])->andReturnSelf()
            ->shouldReceive('first')->andReturn($model);

        $tweet = $this->repository->getTweetById('10000');

        $this->assertInstanceOf(TweetReachModel::class, $tweet);
    }

    /**
     * Returns the dummy TweetReachModel
     * @return \App\Models\TweetReachModel
     */
    public function getDummyTweetReachModel()
    {
        return new TweetReachModel([
            'tweet_id' => 10000,
            'total_sum' => 118627,
            'updated_at' => '2017-10-04 02:16:41+00',
            'info' => '{"retweetCount":118627,"retweeters":[{"name":"UserA","followersCount":1},{"name":"UserB","followersCount":19}]}',
        ]);
    }

    /**
     * Helper Method to set the expectation for the timetamp
     * @param string $updatedAt Timestamp string
     */
    public function setTweetReachModelExpectation($updatedAt = '2017-10-04 02:16:41+00')
    {
        $model = $this->getDummyTweetReachModel();
        $model->updated_at = $updatedAt;
        $model->id = 37;

        $this->tweet->shouldReceive('where')->once()->andReturnSelf()
            ->shouldReceive('select')->with(['id', 'total_sum', 'updated_at', 'info'])->andReturnSelf()
            ->shouldReceive('first')->andReturn($model);
    }

    /**
     * @covers ::isCacheValid
     * @dataProvider timestampProviderForCache
     * @param string $timetamp Timestamp string
     * @param bool $expectation Boolean
     */
    public function testIsCacheValid($timetamp, $expectation)
    {
        $this->setTweetReachModelExpectation();

        \Carbon\Carbon::setTestNow($timetamp);
        $isValid = $this->repository->isCacheValid(10000, 'Y-m-d H:i:sO', 2);

        $this->assertEquals($expectation, $isValid);
    }

    /**
     * Scenarios for the cache expire
     * @return array
     */
    public function timestampProviderForCache()
    {
        return [
           ['2017-10-04 02:16:41+00', true],
           ['2017-10-04 01:16:41+00', true],
           ['2017-10-04 03:16:41+00', true],
           ['2017-10-04 05:16:41+00', false],
           ['2017-10-04 09:16:41+00', false],
           ['2017-11-04 09:16:41+00', false],
        ];
    }

    /**
     * @covers ::getCachedSum
     */
    public function testGetCachedSumReturnsTotalSum()
    {
        $model = $this->getDummyTweetReachModel();

        $this->tweet->shouldReceive('where')->once()->with(['tweet_id' => $model->tweet_id])->andReturnSelf()
            ->shouldReceive('select')->once()->with(['total_sum'])->andReturnSelf()
            ->shouldReceive('first')->andReturn($model);

        $totalSum = $this->repository->getCachedSum($model->tweet_id);
        $this->assertInternalType('int', $totalSum);
        $this->assertEquals($totalSum, $model->total_sum);
    }

    /**
     * @covers ::persistInDB
     */
    public function testPersistInDB()
    {
        $this->setTweetReachModelExpectation();

        \Carbon\Carbon::setTestNow('2017-10-04 02:16:41+00');
        $updatedAt = new \Carbon\Carbon('2017-10-04 02:16:41+00', 'UTC');
        $this->tweet->shouldReceive('destroy')->once()->with(37)->andReturn(true);
        $this->tweet->shouldReceive('where')->once()->andReturnSelf()
            ->shouldReceive('create')->with([
                'tweet_id' => 10000,
                'total_sum' => 118627,
                'updated_at' => $updatedAt,
                'info' => '{"retweetCount":118627,"retweeters":[{"name":"UserA","followersCount":1},{"name":"UserB","followersCount":19}]}',
            ])->andReturn(true);

        $this->repository->persistInDB(10000, 118627, new \Carbon\Carbon('now', 'UTC'), [
            'retweetCount' => 118627,
            'retweeters' => [
                [
                    'name' => 'UserA',
                    'followersCount' => 1,
                ],
                [
                    'name' => 'UserB',
                    'followersCount' => 19,
                ]
            ]
        ]);
    }

    /**
     * @covers ::aggregate
     */
    public function testAggregate()
    {
        $fixtures = TweetRepositoryFixture::getAggregateFixtures();
        $retweeters = [
            'ids' => $fixtures['userIds']
        ];

        $users = EngagementCalculatorFixture::getUsersAsObject();

        $this->twitter->shouldReceive('getRters')->andReturn($retweeters);
        $this->twitter->shouldReceive('getUsersLookup')->with(
            ['user_id' => implode(',', $fixtures['userIds'])]
        )->andReturn($users);
        $this->twitter->shouldReceive('getTweet')->once()->andReturn(['retweet_count' => 1000]);

        $calculator = Mockery::mock(CalculatorInterface::class);
        $calculator->shouldReceive('calculate')->with($users)->andReturn(50);

        $aggregatedData = $this->repository->aggregate(10000, $calculator);

        $this->assertEquals($aggregatedData, $fixtures['expected']);
    }

    /**
     * @covers ::getRetweetInformation
     */
    public function testGetRetweetInformation()
    {
        $this->setTweetReachModelExpectation();

        $retweetInformation = $this->repository->getRetweetInformation(10000);

        $this->assertEquals($retweetInformation, TweetRepositoryFixture::getRetweetInformationFixture());
    }

    /**
     * @covers ::isRetweeted
     */
    public function testIsRetweetedReturnsTrue()
    {
        $this->twitter->shouldReceive('getTweet')->andReturn(TweetRepositoryFixture::getTwitterApiTweetObject(29));
        $status = $this->repository->isRetweeted(10000);

        $this->assertTrue($status);
    }

    /**
     * @covers ::isRetweeted
     */
    public function testIsRetweetedReturnsFalse()
    {
        $this->twitter->shouldReceive('getTweet')->andReturn(TweetRepositoryFixture::getTwitterApiTweetObject(0));
        $status = $this->repository->isRetweeted(10000);

        $this->assertFalse($status);
    }
}
