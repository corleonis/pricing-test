<?php

use Illuminate\Container\Container;

class PricingCalculatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Pricing calculator instance.
     *
     * @var JustPark\PricingCalculator
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
        $calculator = $container->make('JustPark\\PricingCalculator');
        $this->assertTrue($calculator instanceof \JustPark\PricingCalculatorInterface);
    }

    /**
     * Ensure that an empty array of time periods returns zero.
     */
    public function testEmptyArrayOfPeriodsReturnsZero()
    {
        $result = $this->calculator->calculate([]);
        $this->assertEquals(0, $result);
    }
}
