<?php
namespace JustPark\Pricing;

use InvalidArgumentException;
use JustPark\Pricing\Exception\DuplicatePriceEntryException;

class PriceFactory
{
    /**
     * @var Price[] List of prices.
     */
    private $prices = array();

    /**
     * Base constructor for the pricing options.
     *
     * @param Price[] $prices List of available prices
     * @throws InvalidArgumentException If wrong type of Price is pased
     * @throws DuplicatePriceEntryException If price is duplicated in the list
     */
    public function __construct(array $prices) {
        foreach ($prices as $price) {
            /** @var $price Price */
            if (!($price instanceof PriceInterface)) {
                throw new InvalidArgumentException("Invalid price type: " . gettype($price) . ". Ecpected PriceInterface.");
            }

            $name = $price->getName();

            if (array_key_exists($name, $this->prices)) {
                throw new DuplicatePriceEntryException("Cannot add price of type: {$name} there is another price matching that key.");
            }
            $this->prices[$name] = $price;
        }
    }

    /**
     * Retrieve a matching Price defined in the list.
     * @param string $name Price name
     * @return Price
     * @throws InvalidArgumentException If no matching price found
     */
    public function getPrice($name) {
        if (array_key_exists($name, $this->prices)) {
            return $this->prices[$name];
        }

        throw new InvalidArgumentException("There is no matching price of type: {$name}");
    }
}
