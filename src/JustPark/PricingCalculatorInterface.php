<?php

namespace JustPark;

interface PricingCalculatorInterface
{
    /**
     * Calculate a price based upon an array of start and
     * end date pairs.
     *
     * @param  array  $periods
     * @return float
     */
    public function calculate(array $periods);
}
