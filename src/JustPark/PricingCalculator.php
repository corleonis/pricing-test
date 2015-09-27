<?php

namespace JustPark;

use Carbon\Carbon;
use JustPark\Interval\Interval;
use JustPark\Pricing\PriceFactory;
use JustPark\Pricing\Price;

class PricingCalculator implements PricingCalculatorInterface
{
    /**
     * @var PriceFactory
     */
    private $priceFactory;

    /**
     * @var string Total cost for all bookings.
     */
    private $totalCost = "0";

    public function __construct(PriceFactory $priceFactory) {
        $this->priceFactory = $priceFactory;
    }

    /**
     * Calculate a price based upon an array of start and
     * end date pairs.
     *
     * @param Interval[] $intervals
     * @return float
     */
    public function calculate(array $intervals)
    {
        foreach ($intervals as $interval) {
            $from = $interval->getFrom();
            $to = $interval->getTo();

            $hours = $this->getHours($from, $to);
            $days = $this->getDays($from, $to);
            $weeks = $this->getWeeks($from, $to);
            $months = $this->getMonths($from, $to);

            if ($months > 0) {
                $this->totalCost = bcadd($this->totalCost, $this->calculateMonthlyCost($months));
            } else {
                $availableCosts = [$this->calculateHourlyCost($hours)];

                if ($days > 0) {
                    $availableCosts[] = $this->calculateDailyCost($days);
                    $availableCosts[] = $this->calculateWeeksAndDaysCost($days);
                }

                if ($weeks > 0) {
                    $availableCosts[] = $this->calculateWeeklyCost($weeks);
                    $availableCosts[] = $this->calculateMonthlyCost(1);
                }

                $this->totalCost = bcadd($this->totalCost, min($availableCosts));
            }
        }

        return (float) $this->totalCost;
    }

    /**
     * Get all available hours, if there is no difference between
     * the dates or less then an hours we need to return 1 hours
     * as a basic unit for calculation.
     *
     * @param Carbon $from From date and time
     * @param Carbon $to To date and time
     * @return int Number of hours to be billed
     */
    private function getHours(Carbon $from, Carbon $to)
    {
        return ($hours = $from->diffInHours($to)) > 0 ? $hours : 1;
    }

    /**
     * Get all days to be billed. This method calculates the days difference
     * disregarding the time and then add any extra days based on the beginning
     * and end hours. If beginning before 5am we add a day and if it ends after
     * 5am we add another day.
     *
     * @param Carbon $from From date and time
     * @param Carbon $to To date and time
     * @return int Number of days to be billed
     */
    private function getDays(Carbon $from, Carbon $to)
    {
        $tmpFrom = Carbon::create($from->year, $from->month, $from->day, 0, 0, 0);
        $tmpTo = Carbon::create($to->year, $to->month, $to->day, 0, 0, 0);
        $days = $tmpFrom->diffInDays($tmpTo);

        if ($from->hour < 5) {
            $days++;
        }
        if ($to->hour > 5) {
            $days++;
        }

        return $days;
    }

    /**
     * Gets all weeks for billing purposes. If there are some
     * days remaining the number of weeks is <b>ROUNDED UP</b>
     * to closest integer number so we can bill for the whole period.
     *
     * @param Carbon $from From date and time
     * @param Carbon $to To date and time
     * @return int Number of weeks to be billed
     */
    private function getWeeks(Carbon $from, Carbon $to)
    {
        return (int)ceil($this->getDays($from, $to)/7);
    }

    /**
     * Gets all billable months disregarding the time of the beginning
     * and end dates.
     *
     * @param Carbon $from From date and time
     * @param Carbon $to To date and time
     * @return int Number of months to be billed
     */
    private function getMonths(Carbon $from, Carbon $to)
    {
        $tmpFrom = Carbon::create($from->year, $from->month, $from->day, 0, 0, 0);
        $tmpTo = Carbon::create($to->year, $to->month, $to->day, 0, 0, 0);
        return $tmpFrom->diffInMonths($tmpTo);
    }

    /**
     * Calculates hourly cost for given hour units.
     *
     * @param $hours
     * @return float
     */
    private function calculateHourlyCost($hours)
    {
        return $this->priceFactory->getPrice("hourly")->getTotalCost($hours);
    }

    /**
     * Calculates daily cost for given day units.
     *
     * @param $days
     * @return float
     */
    private function calculateDailyCost($days)
    {
        return $this->priceFactory->getPrice("daily")->getTotalCost($days);
    }

    /**
     * Calculates weekly cost for given week units.
     *
     * @param $weeks
     * @return float
     */
    private function calculateWeeklyCost($weeks)
    {
        return $this->priceFactory->getPrice("weekly")->getTotalCost($weeks);
    }

    /**
     * Calculates cost for given total days. Price is calculated in two
     * separate calls once for the full weeks and the rest for the
     * remaining days.
     *
     * @param $totalDays
     * @return float
     */
    private function calculateWeeksAndDaysCost($totalDays)
    {
        $days = $totalDays % 7;
        $weeks = (int)($totalDays / 7);

        $dailyCost = $this->priceFactory->getPrice("daily")->getTotalCost($days);
        $weeklyCost = $this->priceFactory->getPrice("weekly")->getTotalCost($weeks);

        return bcadd($dailyCost, $weeklyCost, Price::MONETARY_SCALE);
    }

    /**
     * Calculates monthly cost for given month units.
     *
     * @param $months
     * @return float
     */
    private function calculateMonthlyCost($months)
    {
        return $this->priceFactory->getPrice("monthly")->getTotalCost($months);
    }
}
