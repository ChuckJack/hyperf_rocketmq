<?php
namespace Timebug\Rocketmq\Components\AliyunMQ;

use Timebug\Rocketmq\Components\AliyunMQ\Exception\InvalidArgumentException;
use Timebug\Rocketmq\Components\AliyunMQ\Http\HttpClient;

class MQClient
{
    private HttpClient $client;

    /**
     *
     * @param endPoint: the host url
     *               could be "http://$accountId.mqrest.cn-hangzhou.aliyuncs.com"
     *               accountId could be found in aliyun.com
     * @param accessId: accessId from aliyun.com
     * @param accessKey: accessKey from aliyun.com
     * @param securityToken: securityToken from aliyun.com
     * @param config: necessary configs
     */
    public function __construct($endPoint, $accessId,
        $accessKey, $securityToken = NULL, Config $config = NULL)
    {
        if (empty($endPoint)) {
            throw new InvalidArgumentException(400, "Invalid endpoint");
        }
        if (empty($accessId)) {
            throw new InvalidArgumentException(400, "Invalid accessId");
        }
        if (empty($accessKey)) {
            throw new InvalidArgumentException(400, "Invalid accessKey");
        }
        $this->client = new HttpClient($endPoint, $accessId,
            $accessKey, $securityToken, $config);
    }


    /**
     * Returns a Producer reference for publish message to topic
     *
     * @param string $instanceId: instance id
     * @param string $topicName:  the topic name
     *
     * @return MQProducer: the Producer instance
     */
    public function getProducer(string $instanceId, string $topicName): MQProducer
    {
        if ($topicName == NULL || $topicName == "") {
            throw new InvalidArgumentException(400, "TopicName is null or empty");
        }
        return new MQProducer($this->client, $instanceId, $topicName);
    }

    /**
     * Returns a Transaction Producer reference for publish message to topic
     *
     * @param string $instanceId: instance id
     * @param string $topicName:  the topic name
     * @param string $groupId:  the group id
     *
     * @return MQTransProducer: the Transaction Producer instance
     */
    public function getTransProducer(string $instanceId, string $topicName, string $groupId, string $messageTag): MQTransProducer
    {
        if ($topicName == NULL || $topicName == "") {
            throw new InvalidArgumentException(400, "TopicName is null or empty");
        }
        return new MQTransProducer($this->client, $instanceId, $topicName, $groupId, $messageTag);
    }

    /**
     * Returns a Consumer reference for consume and ack message to topic
     *
     * @param string $instanceId: instance id
     * @param string $topicName:  the topic name
     * @param string $consumer: the consumer name / ons cid
     * @param string|null $messageTag: filter tag for consumer. If not empty, only consume the message which's messageTag is equal to it.
     *
     * @return MQConsumer: the Consumer instance
     */
    public function getConsumer(string $instanceId, string $topicName, string $consumer, string $messageTag = NULL): MQConsumer
    {
        if ($topicName == NULL || $topicName == "") {
            throw new InvalidArgumentException(400, "TopicName is null or empty");
        }
        if ($consumer == NULL || $consumer == "" ) {
            throw new InvalidArgumentException(400, "Consumer is null or empty");
        }
        return new MQConsumer($this->client, $instanceId, $topicName, $consumer, $messageTag);
    }

}

