<?php

namespace Timebug\Rocketmq\Producer;

use Exception;
use Throwable;
use Timebug\Rocketmq\Components\AliyunMQ\Exception\AckMessageException;
use Timebug\Rocketmq\Components\AliyunMQ\Exception\MessageNotExistException;
use Timebug\Rocketmq\Components\AliyunMQ\Model\AckMessageErrorItem;
use Timebug\Rocketmq\Components\AliyunMQ\Model\TopicMessage;
use Timebug\Rocketmq\Components\AliyunMQ\MQTransProducer;
use Timebug\Rocketmq\Message\ProducerMessageInterface;

class TransProducer extends BaseProducer
{

    /**
     * 每次消费消息数量
     */
    protected const NUM_OF_MSG = 3;

    /**
     * 等待时间
     */
    protected const WAIT_SECONDS = 3;

    /**
     * 发布消息
     *
     * @param ProducerMessageInterface $producerMessage
     * @return bool
     */
    public function produce(ProducerMessageInterface $producerMessage): bool
    {
        try {
            return retry(1, function () use ($producerMessage) {
                return $this->produceMessage($producerMessage);
            });
        } catch (Throwable $e) {
            $this->logger->error("TransProducerRetryError:" . $e->getMessage(), $e->getTrace());
            return false;
        }
    }

    /**
     * 发布消息
     * @param ProducerMessageInterface $producerMessage
     * @return bool
     */
    private function produceMessage(ProducerMessageInterface $producerMessage): bool
    {
        $this->injectMessageProperty($producerMessage);

        $config = $this->factory->getConfigs($producerMessage->getPoolName());

        $message = new TopicMessage($producerMessage->payload());
        // 设置自定义属性
        if ($producerMessage->getProperties()) {
            foreach ($producerMessage->getProperties() as $property => $value) {
                $message->putProperty($property, $value);
            }
        }
        // 设置消息标签
        $producerMessage->getMessageTag() && $message->setMessageTag($producerMessage->getMessageTag());
        // 设置消息分区(顺序消息)
        $producerMessage->getShardingKey() && $message->setShardingKey($producerMessage->getShardingKey());
        // 生成消息Key
        $finalMsgKey = hash("md5", $message->getMessageBodyMD5() . $producerMessage->getMessageKey());
        $producerMessage->getMessageKey() && $message->setMessageKey($finalMsgKey);
        // 如果定义了定时/延时发送的时间，则设置该消息为定时/延时消息
        if ($timeInMillis = $producerMessage->getDeliverTime()) {
            $message->setStartDeliverTime($timeInMillis);
        }
        // 设置事务回查时间。(同时会标识为事务消息)
        $message->setTransCheckImmunityTime(10);

        // 通过链接获取事务生产者
        $connection = $this->factory->getConnection($producerMessage->getPoolName());
        $producer = $connection->getConnection()->getTransProducer($config->getInstanceId(), $producerMessage->getTopic(), $producerMessage->getGroupId(), $producerMessage->getMessageTag());

        // 发布消息
        $retMsg = $producer->publishMessage($message);

        try {
            // 发送完事务消息后能获取到半消息句柄，可以直接Commit或Rollback事务消息。
            $producer->commit($retMsg->getReceiptHandle());
        } catch (Exception $e) {
            // 如果Commit或Rollback时超过了TransCheckImmunityTime则会失败。
             $this->processAckError($e);
        }

        // 启动一个Coroutine来检查没有确认的事务消息
        $this->consumeHalfMessage($producer);
        $connection->close();
        return true;
    }

    /**
     * 启动一个协程检查没有确认的事务消息
     *
     * @param MQTransProducer $producer
     * @return void
     */
    private function consumeHalfMessage(MQTransProducer $producer): void
    {
        go(function() use ($producer) {
            $loop = 0;
            while ($loop < 5) {
                $loop++;

                try {
                    $messages = $producer->consumeHalfMessage(self::NUM_OF_MSG, self::WAIT_SECONDS);
                } catch (MessageNotExistException|Exception $e) {
                    $messages = [];
                }

                if (empty($messages)) continue;

                foreach ($messages as $message) {
                    // 消息的重试次数
                    $consumeTimes = $message->getConsumedTimes();
                    // 超过2次没有成功的消息回滚; 否则重新提交
                    try {
                        if ($consumeTimes > 1) {
                            $producer->rollback($message->getReceiptHandle());
                        } else {
                            $producer->commit($message->getReceiptHandle());
                        }
                    } catch (Exception $e) {
                        $this->processAckError($e);
                    }
                }
            }
        });
    }

    /**
     * 处理 ACK 错误
     *
     * @param Exception $e
     * @return void
     */
    private function processAckError(Exception $e): void
    {
        if ($e instanceof AckMessageException) {
            $this->logger->error("Commit/Rollback Error, RequestId: {$e->getRequestId()}");
            foreach ($e->getAckMessageErrorItems() as $item) {
                /**
                 * @var AckMessageErrorItem $item
                 */
                $this->logger->error("AckMessageError:Handle:{$item->getReceiptHandle()}:ErrorCode:{$item->getErrorCode()}:ErrorMessage:{$item->getErrorMessage()}", [
                    'RequestId' => $e->getRequestId(),
                    'onsErrorCode'  => $e->getOnsErrorCode(),
                    'ReceiptHandle' => $item->getReceiptHandle(),
                    'ErrorCode' => $item->getErrorCode(),
                    'ErrorMessage' => $item->getErrorMessage()
                ]);
            }
        } else {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
    }
}