<?php

namespace Timebug\Rocketmq;


use Psr\Container\ContainerInterface;

abstract class Builder
{
    public function __construct(
        protected ContainerInterface $container,
        protected ConnectionFactory $factory
    )
    {
    }
}