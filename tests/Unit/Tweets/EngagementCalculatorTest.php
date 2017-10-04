<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

namespace Tests\Unit\Tweets;

use Tests\TestCase;
use App\Tweets\EngagementCalculator;
use Tests\Unit\Fixtures\EngagementCalculatorFixture;

/**
 * Class EngagementCalculatorTest
 * @package Tests\Unit\Tweets
 * @coversDefaultClass \App\Tweets\EngagementCalculator
 */
class EngagementCalculatorTest extends TestCase
{
    /**
     * @var \App\Tweets\EngagementCalculator
     */
    protected $calculator;

    public function setUp()
    {
        $this->calculator = new EngagementCalculator();
    }

    /**
     * @covers ::calculate()
     */
    public function testCalculate()
    {
        $users = EngagementCalculatorFixture::getUsers();

        $actual = $this->calculator->calculate($users['users']);
        $expected = $users['expected'];

        $this->assertEquals($actual, $expected);
    }
}
