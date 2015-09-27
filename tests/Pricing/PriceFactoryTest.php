<?php
namespace JustPark\Pricing;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

class PriceFactoryTest extends PHPUnit_Framework_TestCase
{
    private static $CURRENCY_CODE = "GBP";

    public function testCostConstructor()
    {
        $costs = new PriceFactory(array(
            new Price("hourly", "1.2", static::$CURRENCY_CODE),
            new Price("daily", "2.3", static::$CURRENCY_CODE),
            new Price("weekly", "3.4", static::$CURRENCY_CODE),
            new Price("monthly", "4.5", static::$CURRENCY_CODE),
        ));

        $this->assertEquals("hourly", $costs->getPrice("hourly")->getName());
        $this->assertEquals("1.2", $costs->getPrice("hourly")->getUnitCost());
        $this->assertEquals(static::$CURRENCY_CODE, $costs->getPrice("hourly")->getCurrencyCode());
        $this->assertEquals("daily", $costs->getPrice("daily")->getName());
        $this->assertEquals("2.3", $costs->getPrice("daily")->getUnitCost());
        $this->assertEquals(static::$CURRENCY_CODE, $costs->getPrice("daily")->getCurrencyCode());
        $this->assertEquals("weekly", $costs->getPrice("weekly")->getName());
        $this->assertEquals("3.4", $costs->getPrice("weekly")->getUnitCost());
        $this->assertEquals(static::$CURRENCY_CODE, $costs->getPrice("weekly")->getCurrencyCode());
        $this->assertEquals("monthly", $costs->getPrice("monthly")->getName());
        $this->assertEquals("4.5", $costs->getPrice("monthly")->getUnitCost());
        $this->assertEquals(static::$CURRENCY_CODE, $costs->getPrice("monthly")->getCurrencyCode());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNonPricesArrayThrowsException()
    {
        $costs = new PriceFactory(array(
            "hourly", "daily", "weekly", "monthly"
        ));
    }

    /**
     * @expectedException \JustPark\Pricing\Exception\DuplicatePriceEntryException
     */
    public function testDuplicatePricesEntriesThrowsException()
    {
        $costs = new PriceFactory(array(
            new Price("hourly", "1.2", static::$CURRENCY_CODE),
            new Price("hourly", "2.3", static::$CURRENCY_CODE)
        ));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMissingPriceTypeThrowsException()
    {
        $costs = new PriceFactory(array(
            new Price("hourly", "1.2", static::$CURRENCY_CODE),
            new Price("daily", "2.3", static::$CURRENCY_CODE),
            new Price("weekly", "3.4", static::$CURRENCY_CODE),
            new Price("monthly", "4.5", static::$CURRENCY_CODE),
        ));

        $costs->getPrice("test");
    }
}
