<?php
namespace JustPark;

use Carbon\Carbon;
use Illuminate\Container\Container;
use JustPark\Interval\Interval;
use JustPark\Pricing\PriceFactory;
use JustPark\Pricing\Price;
use PHPUnit_Framework_TestCase;

class PricingCalculatorTest extends PHPUnit_Framework_TestCase
{
    private static $HOURLY_RATE = "2";
    private static $DAILY_RATE = "5";
    private static $WEEKLY_RATE = "20";
    private static $MONTHLY_RATE = "70";

    /**
     * Pricing calculator instance.
     *
     * @var PricingCalculator
     */
    private $calculator;

    /**
     * Instantiate the PricingCalculator class using the Laravel IoC container.
     *
     * This container will allow for automatic dependency injection based upon
     * constructor type hinting. See the Laravel documentation at
     *
     *  http://laravel.com/docs/ioc
     *
     * for more information.
     */
    public function setUp()
    {
        // Create a new Laravel container instance.
        $container = new Container;

        $container->instance("JustPark\\Pricing\\PriceFactory", new PriceFactory(array(
            new Price("hourly", static::$HOURLY_RATE, "GBP"),
            new Price("daily", static::$DAILY_RATE, "GBP"),
            new Price("weekly", static::$WEEKLY_RATE, "GBP"),
            new Price("monthly", static::$MONTHLY_RATE, "GBP")
        )));

        // Resolve the pricing calculator (and any type hinted dependencies)
        // and set to class attribute.
        $this->calculator = $container->make('JustPark\\PricingCalculator');
    }

    /**
     * Ensure that the pricing calculator can be resolved from the
     * Laravel IoC container.
     */
    public function testPricingCalculatorCanBeResolved()
    {
        $container = new Container;
        $container->instance("JustPark\\Pricing\\PriceFactory", new PriceFactory(array()));
        $calculator = $container->make('JustPark\\PricingCalculator');
        $this->assertTrue($calculator instanceof PricingCalculatorInterface);
    }

    /**
     * Ensure that an empty array of time periods returns zero.
     */
    public function testEmptyArrayOfPeriodsReturnsZero()
    {
        $result = $this->calculator->calculate([]);
        $this->assertEquals(0, $result);
    }

    /**
     * ===============
     * = BASIC RULES =
     * ===============
     */

    public function testZeroHoursBookingHasCost()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-01-09 12:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-01-09 12:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(static::$HOURLY_RATE, $result);
    }

    public function testHourlyRateCalculatesCorrectly()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-10 14:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-10 16:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(bcmul(static::$HOURLY_RATE, 2), $result);
    }

    public function testDailyRateCalculatesCorrectly()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-10 14:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-12 14:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(bcmul(static::$DAILY_RATE, 3), $result);
    }

    public function testWeeklyRateCalculatesCorrectly()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-10 14:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-23 14:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(bcmul(static::$WEEKLY_RATE, 2), $result);
    }

    public function testMonthlyRateCalculatesCorrectly()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-10 14:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-11-10 14:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(bcmul(static::$MONTHLY_RATE, 2), $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLaterBeginningDateThenEndDateThrowsAnException()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-10 14:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2014-11-10 14:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(bcmul(static::$MONTHLY_RATE, 2), $result);
    }

    /**
     * ==================
     * = ADVANCED RULES =
     * ==================
     */

    public function testHourlyRateBeforeFiveAmCalculatesCorrectly()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-10 24:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-11 02:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(bcmul(static::$HOURLY_RATE, 2), $result);
    }

    public function testHourlyRateAfterFiveAm()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-10 14:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-11 06:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
    }

    /**
     * If a booking lasts longer than 24 hours, we no longer calculate prices in terms of hours parked.
     */
    public function test25HoursAreTreatedAsDaysForCalculation()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-10 06:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-11 07:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(bcmul(static::$DAILY_RATE, 2), $result);
    }

    /**
     * If booking spans multiple days and ends before 5am, then the final day is not included in the calculation.
     */
    public function testMultipleDaysBookingDisregardsPartialDayBeforeFiveAm()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-10 14:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-13 03:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(bcmul(static::$DAILY_RATE, 3), $result);
    }

    /**
     * The daily rate is used where the hourly rate is more expensive.
     */
    public function testDailyRateIsUsedIfHourlyMoreExpensive()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-10 14:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-11 12:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(bcmul(static::$DAILY_RATE, 2), $result);
    }

    /**
     * The weekly rate is used where the daily rate is more expensive.
     */
    public function testWeeklyRateIsUsedIfDailyMoreExpensive()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-10 14:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-09-16 14:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(static::$WEEKLY_RATE, $result);
    }

    /**
     * The monthly rate is used where the weekly and daily rate is more expensive.
     */
    public function testMonthlyCostCalculation()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-01-24 14:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-02-18 15:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(static::$MONTHLY_RATE, $result);
    }

    public function testWeeksAndDaysMixtureCalculatesCorrectly()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-01-24 14:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-02-15 04:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(65, $result);
    }

    /**
     * =================
     * = EXAMPLE TESTS =
     * =================
     */

    /**
     * | 1 | 24th Jan, 14:00 | 25th Jan, 03:00 | 1 day | £5 | Finishes before 5am. |
     */
    public function testExampleTestOne()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-01-24 14:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-01-25 03:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(static::$DAILY_RATE, $result);
    }

    /**
     * | 2 | 24th Jan, 14:00 | 25th Jan, 12:00 | 2 days | £10 | Finishes after 5am,
     * so 2 days despite it being less than 24 hours. |
     */
    public function testExampleTestTwo()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-01-24 14:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-01-25 12:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(bcmul(static::$DAILY_RATE, 2), $result);
    }

    /**
     * | 3 | 24th Jan, 14:00 | 18th Feb, 15:00 | 3 weeks and 5 days | £70 |
     * Monthly price (£70) is less than (3 (weeks) * £20 + 5 (days) * £5) and (4 (weeks) * £20). |
     */
    public function testExampleTestThree()
    {
        $timeFrom = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-01-24 14:00:00", "UTC");
        $timeTo = Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, "2015-02-18 15:00:00", "UTC");
        $result = $this->calculator->calculate(array(new Interval($timeFrom, $timeTo)));
        $this->assertEquals(static::$MONTHLY_RATE, $result);
    }
}
