<?php

namespace Timebug\Rocketmq\Producer;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Timebug\Rocketmq\Annotation\Producer as ProducerAnnotation;
use Timebug\Rocketmq\Builder;
use Timebug\Rocketmq\ConnectionFactory;
use Timebug\Rocketmq\Message\ProducerMessageInterface;

abstract class BaseProducer extends Builder implements ProducerInterface
{
    protected LoggerInterface $logger;

    public function __construct(
        protected ContainerInterface $container,
        protected ConnectionFactory $factory,
    )
    {
        parent::__construct($container, $factory);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * 生产消息依赖注入。
     * 通过生产者注解更新消息
     *
     * @param ProducerMessageInterface $producerMessage
     * @return void
     */
    protected function injectMessageProperty(ProducerMessageInterface $producerMessage): void
    {
        if (class_exists(AnnotationCollector::class)) {
            /** @var ProducerAnnotation $annotation */
            $annotation = AnnotationCollector::getClassAnnotation(get_class($producerMessage), ProducerAnnotation::class);
            if ($annotation) {
                $annotation->topic && $producerMessage->setTopic($annotation->topic);
                $annotation->groupId && $producerMessage->setGroupId($annotation->groupId);
                $annotation->startDeliverTime && $annotation->startDeliverTime > 0 && $producerMessage->setDeliverTime($annotation->startDeliverTime);
                $annotation->messageTag && $producerMessage->setMessageTag($annotation->messageTag);
                if ($annotation->properties) {
                    foreach ($annotation->properties as $property => $value) {
                        $producerMessage->setProperty($property, $value);
                    }
                }
                $messageKey = hash("md5", sprintf("%s:%s:%f", $annotation->topic ?? '', $annotation->groupId ?? '', microtime(true)));
                $producerMessage->setMessageKey($messageKey);
            }
        }
    }
}