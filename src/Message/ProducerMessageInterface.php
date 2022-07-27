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

interface ProducerMessageInterface extends MessageInterface
{
    public function setPayload(string $payload): static;

    public function payload(): string;

    public function getMessageKey(): string;

    public function setMessageKey(string $messageKey): static;

    public function getMessageTag(): string;

    public function setMessageTag(string $messageTag): static;

    public function getDeliverTime(): ?int;

    public function setDeliverTime(int $timestamp): static;

    public function getGroupId(): string;

    public function setGroupId(string $groupId): static;

    public function getProperties(): array;

    public function setProperties(array $properties = []): static;

    public function getProperty(string $key = ""): mixed;

    public function setProperty(string $key, mixed $value): static;

    public function getShardingKey(): int|string;

    public function setShardingKey(string|int $shardingKey): static;

    public function getProduceInfo(): array;
}