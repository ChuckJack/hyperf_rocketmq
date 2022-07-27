<?php

namespace Timebug\Rocketmq\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Psr\Container\ContainerInterface;
use Timebug\Rocketmq\ConsumerManager;

class BeforeMainServerStartListener implements ListenerInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BeforeMainServerStart::class,
            MainCoroutineServerStart::class,
        ];
    }

    public function process(object $event)
    {
        $consumerManager = $this->container->get(ConsumerManager::class);
        $consumerManager->run();
    }
}