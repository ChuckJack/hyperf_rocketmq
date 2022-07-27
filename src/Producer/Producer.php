<?php

namespace Timebug\Rocketmq\Producer;

use Timebug\Rocketmq\Components\AliyunMQ\Model\TopicMessage;
use Timebug\Rocketmq\Message\ProducerMessageInterface;

class Producer extends BaseProducer
{
    public function produce(ProducerMessageInterface $producerMessage): bool
    {
        return retry(1, function () use ($producerMessage) {
            return $this->produceMessage($producerMessage);
        });
    }

    private function produceMessage(ProducerMessageInterface $producerMessage): bool
    {
        $this->injectMessageProperty($producerMessage);

        // 获取配置
        $config = $this->factory->getConfigs($producerMessage->getPoolName());
        // 新建一条主题消息
        $message = new TopicMessage($producerMessage->payload());
        // 设置自定义属性
        if ($producerMessage->getProperties()) {
            foreach ($producerMessage->getProperties() as $property => $value) {
                $message->putProperty($property, $value);
            }
        }
        // 设置消息分区(顺序消息)
        $producerMessage->getShardingKey() && $message->setShardingKey($producerMessage->getShardingKey());

        // 设置消息Key
        $finalMsgKey = hash("md5", $message->getMessageBodyMD5() . $producerMessage->getMessageKey());
        $producerMessage->getMessageKey() && $message->setMessageKey($producerMessage->getMessageKey());
        // 设置消息Tag
        $producerMessage->getMessageTag() && $message->setMessageTag($finalMsgKey);
        // 设置是否定时发送
        if ($timeInMillis = $producerMessage->getDeliverTime()) {
            $message->setStartDeliverTime($timeInMillis);
        }

        $connection = $this->factory->getConnection($producerMessage->getPoolName())->getConnection();
        $producer = $connection->getProducer($config->getInstanceId(), $producerMessage->getTopic());

        // 发布消息
        $retMsg = $producer->publishMessage($message);

        return isset($retMsg->messageId) && $retMsg->messageId;
    }
}