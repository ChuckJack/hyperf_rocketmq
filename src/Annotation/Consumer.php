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
namespace Timebug\Rocketmq\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Consumer extends AbstractAnnotation
{
    public function __construct(
        public string $name = "Consumer",
        public string $poolName = "default",
        public string $topic = "",
        public string $groupId = "",
        public string $messageTag = "",
        public int $numOfMessage = 1,
        public int $waitSeconds = 3,
        public int $processNums = 1,
        public int $maxConsumption = 0,
        public bool $enable = true,
        public bool $openCoroutine = false,
    )
    {

    }
}