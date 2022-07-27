<?php

namespace Timebug\Rocketmq;

use Closure;
use Hyperf\Process\ProcessManager;
use Timebug\Rocketmq\Components\AliyunMQ\Exception\AckMessageException;
use Timebug\Rocketmq\Components\AliyunMQ\Exception\MessageNotExistException;
use Timebug\Rocketmq\Components\AliyunMQ\Model\AckMessageErrorItem;
use Timebug\Rocketmq\Components\AliyunMQ\Model\Message;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Timebug\Rocketmq\Event\AfterConsume;
use Timebug\Rocketmq\Event\BeforeConsume;
use Timebug\Rocketmq\Event\FailToConsume;
use Timebug\Rocketmq\Message\ConsumerMessageInterface;

class Consumer extends Builder
{
    protected ?EventDispatcherInterface $eventDispatcher = null;

    public function __construct(
        ContainerInterface $container,
        ConnectionFactory $factory,
        private LoggerInterface $logger
    ) {
        parent::__construct($container, $factory);
        if ($container->has(EventDispatcherInterface::class)) {
            $this->eventDispatcher = $container->get(EventDispatcherInterface::class);
        }
    }

    /**
     * @throws Throwable
     */
    public function consume(ConsumerMessageInterface $consumerMessage): void
    {
        // 获取消费者
        $config = $this->factory->getConfigs($consumerMessage->getPoolName());
        $connection = $this->factory->getConnection($consumerMessage->getPoolName())->getConnection();
        $consumer = $connection->getConsumer(
            $config->getInstanceId(),
            $consumerMessage->getTopic(),
            $consumerMessage->getGroupId(),
            $consumerMessage->getMessageTag()
        );

        while (ProcessManager::isRunning()) {
            try {
                $messages = $consumer->consumeMessage(
                    $consumerMessage->getNumOfMessage(), // 每次消费消息数量
                    $consumerMessage->getWaitSeconds()   // 等待时间
                );
            } catch (MessageNotExistException $e) {
                continue;
            } catch (Throwable $exception) {
                $this->logger->error($exception->getMessage());
                throw $exception;
            }

            $maxConsumption = $consumerMessage->getMaxConsumption();
            $currentConsumption = 0;

            $receiptHandles = [];
            if ($consumerMessage->getOpenCoroutine() && count($messages) > 1) {
                $callback = [];
                foreach ($messages as $key => $message) {
//                    if (strcmp($consumerMessage->getMessageTag(), $message->getMessageTag()) !== 0) {
//                        continue;
//                    }
                    $callback[$key] = $this->getCallBack($consumerMessage, $message);
                }
                $receiptHandles[] = parallel($callback);
            } else {
                foreach ($messages as $message) {
//                    if (strcmp($consumerMessage->getMessageTag(), $message->getMessageTag()) !== 0) {
//                        continue;
//                    }
                    $receiptHandles[] = call($this->getCallBack($consumerMessage, $message));
                }
            }

            try {
                $receiptHandles = array_filter($receiptHandles);
                $receiptHandles && $consumer->ackMessage($receiptHandles);
                if ($maxConsumption > 0 && ++$currentConsumption >= $maxConsumption) {
                    break;
                }
            } catch (AckMessageException $exception) {
                // 某些消息的句柄可能超时了会导致确认不成功
                $this->logger->error('ack_error', ['RequestId' => $exception->getRequestId()]);
                foreach ($exception->getAckMessageErrorItems() as $errorItem) {
                    /**
                     * @var AckMessageErrorItem $errorItem
                     */
                    $this->logger->error('ack_error:receipt_handle', [
                        'receiptHandle' => $errorItem->getReceiptHandle(),
                        'errorCode' => $errorItem->getErrorCode(),
                        'errorMessage' => $errorItem->getErrorMessage(),
                    ]);
                }
            } catch (Throwable $e) {
                $this->logger->error($e->getMessage(), $e->getTrace());
                break;
            }
        }

    }

    protected function getCallBack(ConsumerMessageInterface $consumerMessage, Message $message): Closure
    {
        return function () use ($consumerMessage, $message) {
            try {
                $this->eventDispatcher && $this->eventDispatcher->dispatch(new BeforeConsume($consumerMessage));
                $consumerMessage->consumeMessage($message);
                $this->eventDispatcher && $this->eventDispatcher->dispatch(new AfterConsume($consumerMessage));

                return $message->getReceiptHandle();
            } catch (Throwable $throwable) {
                $this->eventDispatcher && $this->eventDispatcher->dispatch(new FailToConsume($consumerMessage, $throwable));
                $this->logger->error($throwable->getMessage());
                return null;
            }
        };
    }
}