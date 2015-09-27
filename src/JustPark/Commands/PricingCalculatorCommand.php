<?php
namespace JustPark\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use JustPark\Core\Component\Console\ContainerAwareApplication;
use JustPark\Interval\Interval;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PricingCalculatorCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('calculator:pricing')
            ->setDescription('Pricing calculator for given list of date and time intervals')
            ->addArgument(
                'dates',
                InputArgument::REQUIRED,
                'List of dates to calculate prices for?'
            )
            ->addOption(
               'yell',
               null,
               InputOption::VALUE_NONE,
               'If set, the task will yell in uppercase letters'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ContainerAwareApplication $app */
        $app = $this->getApplication();
        $container = $app->getContainer();

        /** @var \JustPark\PricingCalculator $calculator */
        $calculator = $container->make('JustPark\\PricingCalculator');

        $dates = json_decode($input->getArgument('dates'));
        $intervals = [];
        foreach ($dates as $date) {
            $intervals[] = new Interval(
                Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $date->start),
                Carbon::createFromFormat(Carbon::DEFAULT_TO_STRING_FORMAT, $date->end)
            );
        }

        $text = $calculator->calculate($intervals);

        $output->writeln($text);
    }
}