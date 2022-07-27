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

interface MessageInterface
{
    public function getPoolName(): string;

    public function setPoolName(string $poolName): static;

    public function getTopic(): string;

    public function setTopic(string $topic): static;

    /**
     * Serialize the message body to a string.
     */
    public function serialize(): string;

    /**
     * Unserialize the message body.
     */
    public function unserialize(string $data);
}