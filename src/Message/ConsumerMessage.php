<?php
declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Timebug\Rocketmq\Message;

use Hyperf\Utils\ApplicationContext;
use Timebug\Rocketmq\Components\AliyunMQ\Model\Message as RocketMQMessage;
use Throwable;
use Timebug\Rocketmq\Packer\Packer;

abstract class ConsumerMessage extends Message implements ConsumerMessageInterface
{
    protected string $groupId;

    /**
     * filter tag for consumer. If not empty, only consume the message which's messageTag is equal to it.
     * @var string
     */
    protected string $messageTag;

    /**
     * consume how many messages once, 1~16.
     */
    public int $numOfMessage = 1;

    /**
     * if > 0, means the time(second) the request holden at server if there is no message to consume.
     * If <= 0, means the server will response back if there is no message to consume.
     * It's value should be 1~30.
     */
    public ?int $waitSeconds = 3;

    /**
     * 进程数量.
     */
    public int $processNums = 1;

    /**
     * 是否初始化时启动.
     */
    public bool $enable = true;

    /**
     * 进程最大消费数.
     */
    public int $maxConsumption = 0;

    /**
     * 是否开启协程并发消费.
     */
    public bool $openCoroutine = true;


    abstract public function consumeMessage(RocketMQMessage $message): void;

    public function getGroupId(): string
    {
        return $this->groupId ?? '';
    }

    public function setGroupId(string $groupId): static
    {
        $this->groupId = $groupId;
        return $this;
    }

    public function getMessageTag(): ?string
    {
        return $this->messageTag;
    }

    public function setMessageTag(string $messageTag): static
    {
        $this->messageTag = $messageTag;
        return $this;
    }

    public function getNumOfMessage(): int
    {
        return $this->numOfMessage;
    }

    public function setNumOfMessage(int $num): static
    {
        $this->numOfMessage = $num;
        return $this;
    }

    public function getWaitSeconds(): int
    {
        return $this->waitSeconds;
    }

    public function setWaitSeconds(int $seconds): static
    {
        $this->waitSeconds = $seconds;
        return $this;
    }

    public function getProcessNums(): int
    {
        return $this->processNums;
    }

    public function setProcessNums(int $num): static
    {
        $this->processNums = $num;
        return $this;
    }

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;
        return $this;
    }

    public function getMaxConsumption(): int
    {
        return $this->maxConsumption;
    }

    public function setMaxConsumption(int $num): static
    {
        $this->maxConsumption = $num;
        return $this;
    }

    public function getOpenCoroutine(): bool
    {
        return $this->openCoroutine;
    }

    public function setOpenCoroutine(bool $isOpen): static
    {
        $this->openCoroutine = $isOpen;
        return $this;
    }

    public function unserialize(string $data)
    {
        $packer = ApplicationContext::getContainer()->get(Packer::class);
        return $packer->unpack($data);
    }

    public function getMqInfo(RocketMQMessage $message): array
    {
        return [
            'topic' => $this->getTopic(),
            'message_tag' => $message->getMessageTag(),
            'message_key' => $message->getMessageKey(),
            'message_id'  => $message->getMessageId(),
            'payload'     => $message->getMessageBody(),
        ];
    }
}