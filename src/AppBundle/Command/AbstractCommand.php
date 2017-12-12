<?php

namespace AppBundle\Command;

use AppBundle\DataProcessor\DataProcessorFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;

abstract class AbstractCommand extends Command implements ServiceSubscriberInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    public static function getSubscribedServices()
    {
        return [
            DataProcessorFactory::class
        ];
    }

    /**
     * @return DataProcessorFactory
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getDataProcessorFactory()
    {
        return $this->container->get(DataProcessorFactory::class);
    }

    /**
     * AbstractCommand constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

}