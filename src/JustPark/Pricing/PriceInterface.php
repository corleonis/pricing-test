<?php
namespace JustPark\Pricing;

interface PriceInterface
{
    /**
     * Gets the total cost for the given number of units.
     * @param int $units
     * @return string Cost as string
     * @throws \InvalidArgumentException if the units is not integer or negative
     */
    public function getTotalCost($units);
}
