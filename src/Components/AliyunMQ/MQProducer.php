<?php
namespace Timebug\Rocketmq\Components\AliyunMQ;

use Timebug\Rocketmq\Components\AliyunMQ\Exception\InvalidArgumentException;
use Timebug\Rocketmq\Components\AliyunMQ\Http\HttpClient;
use Timebug\Rocketmq\Components\AliyunMQ\Model\TopicMessage;
use Timebug\Rocketmq\Components\AliyunMQ\Requests\PublishMessageRequest;
use Timebug\Rocketmq\Components\AliyunMQ\Responses\PublishMessageResponse;

class MQProducer
{
    protected string $instanceId;
    protected string $topicName;
    protected HttpClient $client;

    function __construct(HttpClient $client, string $instanceId, string $topicName)
    {
        if (empty($topicName)) {
            throw new InvalidArgumentException(400, "TopicName is null");
        }
        $this->instanceId = $instanceId;
        $this->client = $client;
        $this->topicName = $topicName;
    }

    public function getInstanceId(): string
    {
        return $this->instanceId;
    }

    public function getTopicName(): string
    {
        return $this->topicName;
    }

    /**
     * @param TopicMessage $topicMessage
     * @return null|TopicMessage|mixed
     */
    public function publishMessage(TopicMessage $topicMessage)
    {

        $request = new PublishMessageRequest(
            $this->instanceId, $this->topicName, $topicMessage->getMessageBody(),
            $topicMessage->getProperties(), $topicMessage->getMessageTag()
        );
        $response = new PublishMessageResponse();
        return $this->client->sendRequest($request, $response);
    }
}

