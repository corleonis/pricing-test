<?php
namespace JustPark\Core\Component\Console;

use Illuminate\Contracts\Container\Container;
use Symfony\Component\Console\Application;

/**
 * This Console Application that allows configuring a Laravel Container
 * and Dependencies Injection for common classes.
 * Class ContainerAwareApplication
 * @package JustPark\Core\Component\Console
 */
class ContainerAwareApplication extends Application
{
    /**
     * The Laravel application instance.
     *
     * @var Container
     */
    private $container;

    public function __construct(Container $container, $name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
        $this->container = $container;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
