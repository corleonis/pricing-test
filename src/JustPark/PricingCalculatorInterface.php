<?php

namespace JustPark;

use JustPark\Interval\Interval;

interface PricingCalculatorInterface
{
    /**
     * Calculate a price based upon an array of start and
     * end date pairs.
     *
     * @param  Interval[] $intervals
     * @return float
     */
    public function calculate(array $intervals);
}
