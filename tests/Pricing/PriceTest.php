<?php
namespace JustPark\Pricing;

use InvalidArgumentException;

class PriceTest extends \PHPUnit_Framework_TestCase
{
    public function testTotalCost() {
        $price = new Price("testPrice", "3", "GBP");
        $this->assertEquals("GBP", $price->getCurrencyCode());
        $this->assertEquals("testPrice", $price->getName());
        $this->assertEquals(3, $price->getUnitCost());
        $this->assertEquals("30", $price->getTotalCost(10));
    }

    public function testTotalCostWithFloatingPoint() {
        $price = new Price("testPrice", "3.15", "GBP");
        $this->assertEquals("315", $price->getTotalCost(100));
    }

    public function testTotalCostWithFloatingPointAndNegativeUnitCost() {
        $price = new Price("testPrice", "-3.15", "GBP");
        $this->assertEquals("-315", $price->getTotalCost(100));
    }

    public function testTotalCostWithFloatingPoints() {
        $price = new Price("testPrice", "3.157", "GBP");
        $this->assertEquals("9.47", $price->getTotalCost(3));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Property 'name' is mandatory and cannot be empty!
     */
    public function testEmptyNameThrowsException() {
        new Price("", 3, 10, "GBP");
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Property 'unitCost' is invalid, should be string in '12.99' or '12' format!
     */
    public function testInvalidUnitCostThrowsException() {
        new Price("testPrice", 3, 10, "GBP");
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Property 'unitCost' is invalid, should be string in '12.99' or '12' format!
     */
    public function testFloatUnitCostThrowsException() {
        new Price("testPrice", 3.12, 10, "GBP");
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid argument provided. Units parameter have to be a positive number.
     */
    public function testNegativeUnitForTotalCostThrowsException() {
        $price = new Price("testPrice", "3", "GBP");
        $price->getTotalCost(-10);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid argument provided. Units parameter have to be a positive number.
     */
    public function testInvalidUnitsThrowsException() {
        $price = new Price("testPrice", "3", "GBP");
        $price->getTotalCost("i10");
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid argument provided. Units parameter have to be a positive number.
     */
    public function testFloatingPointUnitsThrowsException() {
        $price = new Price("testPrice", "3", "GBP");
        $price->getTotalCost(10.5);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Property 'currencyCode' is mandatory and should have following format 'XXX'!
     */
    public function testInvalidCurrencyThrowsException() {
        new Price("testPrice", "3", 10, "TEST");
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Property 'currencyCode' is mandatory and should have following format 'XXX'!
     */
    public function testEmptyCurrencyThrowsException() {
        new Price("testPrice", "3", 10, "");
    }
}
