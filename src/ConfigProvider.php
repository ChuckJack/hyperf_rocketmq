<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Timebug\Rocketmq;

use Hyperf\Utils\Packer\JsonPacker;
use Timebug\Rocketmq\Listener\BeforeMainServerStartListener;
use Timebug\Rocketmq\Packer\Packer;
use Timebug\Rocketmq\Producer\Producer;
use Timebug\Rocketmq\Producer\TransProducer;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Producer::class => Producer::class,
                Packer::class => JsonPacker::class,
                Consumer::class => ConsumerFactory::class,
                TransProducer::class => TransProducer::class
            ],
            'commands' => [
            ],
            'listeners' => [
                BeforeMainServerStartListener::class => 99,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for rocketmq.',
                    'source' => __DIR__ . '/../publish/rocketmq.php',
                    'destination' => BASE_PATH . '/config/autoload/rocketmq.php',
                ],
                [
                    'id' => 'config',
                    'description' => 'The config for alibaba cloud openapi.',
                    'source' => __DIR__ . '/../publish/alicloud.php',
                    'destination' => BASE_PATH . '/config/autoload/alicloud.php',
                ],
            ],
        ];
    }
}
