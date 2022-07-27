<?php
namespace Timebug\Rocketmq\Components\AliyunMQ;

use Timebug\Rocketmq\Components\AliyunMQ\Exception\InvalidArgumentException;
use Timebug\Rocketmq\Components\AliyunMQ\Exception\MQException;
use Timebug\Rocketmq\Components\AliyunMQ\Exception\TopicNotExistException;
use Timebug\Rocketmq\Components\AliyunMQ\Http\HttpClient;
use Timebug\Rocketmq\Components\AliyunMQ\Model\Message;
use Timebug\Rocketmq\Components\AliyunMQ\Requests\AckMessageRequest;
use Timebug\Rocketmq\Components\AliyunMQ\Requests\ConsumeMessageRequest;
use Timebug\Rocketmq\Components\AliyunMQ\Responses\AckMessageResponse;
use Timebug\Rocketmq\Components\AliyunMQ\Responses\ConsumeMessageResponse;

class MQConsumer
{
    private string $instanceId;
    private string $topicName;
    private string $consumer;
    private string $messageTag;
    private HttpClient $client;


    function __construct(HttpClient $client, string $instanceId, string $topicName, string $consumer, string $messageTag = NULL)
    {
        if (empty($topicName)) {
            throw new InvalidArgumentException(400, "TopicName is null");
        }
        if (empty($consumer)) {
            throw new InvalidArgumentException(400, "TopicName is null");
        }

        $this->instanceId = $instanceId;
        $this->topicName = $topicName;
        $this->consumer = $consumer;
        $this->messageTag = $messageTag;
        $this->client = $client;
    }

    public function getInstanceId(): string
    {
        return $this->instanceId;
    }

    public function getTopicName(): string
    {
        return $this->topicName;
    }

    public function getConsumer(): string
    {
        return $this->consumer;
    }

    public function getMessageTag(): ?string
    {
        return $this->messageTag;
    }


    /**
     * consume message
     *
     * @param $numOfMessages: consume how many messages once, 1~16
     * @param int $waitSeconds: if > 0, means the time(second) the request holden at server if there is no message to consume.
     *                      If <= 0, means the server will response back if there is no message to consume.
     *                      It's value should be 1~30
     *
     * @return Message|Message[]
     *
     * @throws TopicNotExistException if queue does not exist
     * @throws MessageNotExistException if no message exists
     * @throws InvalidArgumentException if the argument is invalid
     * @throws MQException if any other exception happends
     */
    public function consumeMessage($numOfMessages, int $waitSeconds = -1)
    {
        if ($numOfMessages < 0 || $numOfMessages > 16) {
            throw new InvalidArgumentException(400, "numOfMessages should be 1~16");
        }
        if ($waitSeconds > 30) {
            throw new InvalidArgumentException(400, "numOfMessages should less then 30");
        }
        $request = new ConsumeMessageRequest($this->instanceId, $this->topicName, $this->consumer, $numOfMessages, $this->messageTag, $waitSeconds);
        $response = new ConsumeMessageResponse();
        return $this->client->sendRequest($request, $response);
    }

    /**
     * consume message orderly
     *
     * Next messages will be consumed if all of same shard are acked. Otherwise, same messages will be consumed again after NextConsumeTime.
     *
     * Attention: the topic should be order topic created at console, if not, mq could not keep the order feature.
     *
     * This interface is suitable for globally order and partitionally order messages, and could be used in multi-thread scenes.
     *
     * @param $numOfMessages: consume how many messages once, 1~16
     * @param int $waitSeconds: if > 0, means the time(second) the request holden at server if there is no message to consume.
     *                      If <= 0, means the server will response back if there is no message to consume.
     *                      It's value should be 1~30
     *
     * @return Message|Message[] may contains several shard's messages, the messages of one shard are ordered.
     *
     * @throws TopicNotExistException if queue does not exist
     * @throws MessageNotExistException if no message exists
     * @throws InvalidArgumentException if the argument is invalid
     * @throws MQException if any other exception happends
     */
    public function consumeMessageOrderly($numOfMessages, int $waitSeconds = -1)
    {
        if ($numOfMessages < 0 || $numOfMessages > 16) {
            throw new InvalidArgumentException(400, "numOfMessages should be 1~16");
        }
        if ($waitSeconds > 30) {
            throw new InvalidArgumentException(400, "numOfMessages should less then 30");
        }
        $request = new ConsumeMessageRequest($this->instanceId, $this->topicName, $this->consumer, $numOfMessages, $this->messageTag, $waitSeconds);
        $request->setTrans(Constants::TRANSACTION_ORDER);
        $response = new ConsumeMessageResponse();
        return $this->client->sendRequest($request, $response);
    }

    /**
     * ack message
     *
     * @param $receiptHandles:
     *            array of $receiptHandle, which is got from consumeMessage
     *
     * @return ?AckMessageResponse
     *
     * @throws TopicNotExistException if queue does not exist
     * @throws ReceiptHandleErrorException if the receiptHandle is invalid
     * @throws InvalidArgumentException if the argument is invalid
     * @throws AckMessageException if any message not deleted
     * @throws MQException if any other exception happends
     */
    public function ackMessage($receiptHandles): ?AckMessageResponse
    {
        $request = new AckMessageRequest($this->instanceId, $this->topicName, $this->consumer, $receiptHandles);
        $response = new AckMessageResponse();
        return $this->client->sendRequest($request, $response);
    }
}

