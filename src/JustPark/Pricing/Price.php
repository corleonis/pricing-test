<?php

namespace JustPark\Pricing;

use InvalidArgumentException;

class Price implements PriceInterface
{
    const MONETARY_SCALE = "2";

    /**
     * @var string Price name, ex: hourly, daily, monthly
     */
    private $name;

    /**
     * @var string Cost for 1 unit of time
     */
    private $unitCost;

    /**
     * @var string Currency code, ex: GBP, USD, EUR
     */
    private $currencyCode;

    /**
     * @param $name
     * @param $unitCost
     * @param $currencyCode
     */
    public function __construct($name, $unitCost, $currencyCode) {
        if (empty($name)) {
            throw new InvalidArgumentException("Property 'name' is mandatory and cannot be empty!");
        }
        if (!is_numeric($unitCost) || !is_string($unitCost)) {
            throw new InvalidArgumentException("Property 'unitCost' is invalid, should be string in '12.99' or '12' format!");
        }
        if (strlen($currencyCode) != 3) {
            throw new InvalidArgumentException("Property 'currencyCode' is mandatory and should have following format 'XXX'!");
        }

        $this->name = $name;
        $this->unitCost = $unitCost;
        $this->currencyCode = strtoupper($currencyCode);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUnitCost()
    {
        return $this->unitCost;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @param int $units Positive number of units to be billed.
     * @return string
     */
    public function getTotalCost($units) {
        if (!is_int($units) || $units < 0) {
            throw new InvalidArgumentException("Invalid argument provided. Units parameter have to be a positive number.");
        }
        return bcmul($this->unitCost, $units, self::MONETARY_SCALE);
    }
}
