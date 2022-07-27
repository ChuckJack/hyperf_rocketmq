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

use Timebug\Rocketmq\Exception\MessageException;

abstract class Message implements MessageInterface
{
    protected string $poolName = 'default';

    protected string $topic = '';

    protected string $dbConnection = 'default';

    public function getPoolName(): string
    {
        return $this->poolName;
    }

    public function setPoolName(string $poolName): static
    {
        $this->poolName = $poolName;
        return $this;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): static
    {
        $this->topic = $topic;
        return $this;
    }

    public function serialize(): string
    {
        throw new MessageException('You have to overwrite serialize() method.');
    }

    public function unserialize(string $data)
    {
        throw new MessageException('You have to overwrite unserialize() method.');
    }
}