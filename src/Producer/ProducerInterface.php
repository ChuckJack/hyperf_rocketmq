<?php

namespace Timebug\Rocketmq\Producer;

use Timebug\Rocketmq\Message\ProducerMessageInterface;

interface ProducerInterface
{
    /**
     * 发布消息
     *
     * @param ProducerMessageInterface $producerMessage
     * @return bool
     */
    public function produce(ProducerMessageInterface $producerMessage): bool;
}