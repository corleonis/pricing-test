<?php
require __DIR__.'/vendor/autoload.php';

use Illuminate\Container\Container;
use JustPark\Commands\PricingCalculatorCommand;
use JustPark\Core\Component\Console\ContainerAwareApplication;
use JustPark\Pricing\Price;
use JustPark\Pricing\PriceFactory;

// Instantiate the base container
$container = new Container();

// Add all dependencies
$container->instance("JustPark\\Pricing\\PriceFactory", new PriceFactory(array(
    new Price("hourly", "2", "GBP"),
    new Price("daily", "5", "GBP"),
    new Price("weekly", "20", "GBP"),
    new Price("monthly", "70", "GBP")
)));

$application = new ContainerAwareApplication($container);
$application->add(new PricingCalculatorCommand());
$application->run();