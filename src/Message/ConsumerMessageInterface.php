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

use Timebug\Rocketmq\Components\AliyunMQ\Model\Message as RocketMQMessage;
use Throwable;

interface ConsumerMessageInterface extends MessageInterface
{
    public function consumeMessage(RocketMQMessage $message): void;

    public function getGroupId(): string;

    public function setGroupId(string $groupId): static;

    public function getMessageTag(): ?string;

    public function setMessageTag(string $messageTag): static;

    public function getNumOfMessage(): int;

    public function setNumOfMessage(int $num): static;

    public function getWaitSeconds(): int;

    public function setWaitSeconds(int $seconds): static;

    public function getProcessNums(): int;

    public function setProcessNums(int $num): static;

    public function isEnable(): bool;

    public function setEnable(bool $enable): static;

    public function getMaxConsumption(): int;

    public function setMaxConsumption(int $num): static;

    public function getOpenCoroutine(): bool;

    public function setOpenCoroutine(bool $isOpen): static;

    public function getMqInfo(RocketMQMessage $message): array;

}