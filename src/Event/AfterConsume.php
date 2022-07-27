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
namespace Timebug\Rocketmq\Event;

use Timebug\Rocketmq\Message\ConsumerMessageInterface;

class AfterConsume extends ConsumeEvent
{
    public function __construct(ConsumerMessageInterface $message)
    {
        parent::__construct($message);
    }
}