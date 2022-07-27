<?php

namespace Timebug\Rocketmq;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
use Psr\Container\ContainerInterface;
use Timebug\Rocketmq\Annotation\Consumer as ConsumerAnnotation;
use Timebug\Rocketmq\Message\ConsumerMessageInterface;

class ConsumerManager
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function run(): void
    {
        $classes = AnnotationCollector::getClassesByAnnotation(ConsumerAnnotation::class);
        /**
         * @var string $class
         * @var ConsumerAnnotation $annotation
         */
        foreach ($classes as $class => $annotation) {
            $instance = make($class);
            if (! $instance instanceof ConsumerMessageInterface) {
                continue;
            }

            $annotation->poolName && $instance->setPoolName($annotation->poolName);
            $annotation->topic && $instance->setTopic($annotation->topic);
            $annotation->groupId && $instance->setGroupId($annotation->groupId);
            $annotation->messageTag && $instance->setMessageTag($annotation->messageTag);
            $annotation->numOfMessage && $instance->setNumOfMessage($annotation->numOfMessage);
            $annotation->waitSeconds && $instance->setWaitSeconds($annotation->waitSeconds);
            $annotation->enable && $instance->setEnable($instance->isEnable());
            $annotation->maxConsumption && $instance->setMaxConsumption($annotation->maxConsumption);
            $annotation->openCoroutine && $instance->setOpenCoroutine($annotation->openCoroutine);
            property_exists($instance, 'container') && $instance->container = $this->container;

            $process = $this->createProcess($instance);
            $process->nums = (int)$annotation->processNums;
            $process->name = $annotation->name . '-' . $instance->getMessageTag();
            ProcessManager::register($process);
        }
    }

    private function createProcess(ConsumerMessageInterface $consumerMessage): AbstractProcess
    {
        return new class($this->container, $consumerMessage) extends AbstractProcess {
            /**
             * @var Consumer
             */
            private Consumer $consumer;

            /**
             * @var ConsumerMessageInterface
             */
            private ConsumerMessageInterface $consumerMessage;

            public function __construct(ContainerInterface $container, ConsumerMessageInterface $consumerMessage)
            {
                parent::__construct($container);
                $this->consumer = $container->get(Consumer::class);
                $this->consumerMessage = $consumerMessage;
            }

            public function handle(): void
            {
                $this->consumer->consume($this->consumerMessage);
            }

            public function getConsumerMessage(): ConsumerMessageInterface
            {
                return $this->consumerMessage;
            }

            public function isEnable($server): bool
            {
                return $this->consumerMessage->isEnable();
            }
        };
    }
}