<?php

declare(strict_types=1);

return [
    'default' => [
        'host' => env('ROCKETMQ_ENDPOINT', 'localhost'),
        'access_key' => env('ROCKETMQ_ACCESS_KEY', ''),
        'secret_key' => env('ROCKETMQ_SECRET_KEY', ''),
        'instance_id' => env('ROCKETMQ_INSTANCE_ID', ''),
        'pool' => [
            'min_connections' => env('ROCKETMQ_MIN_CONNECTIONS', 10),
            'max_connections' => env('ROCKETMQ_MAX_CONNECTIONS', 50),
            'connect_timeout' => env('ROCKETMQ_CONNECT_TIMEOUT', 3),
            'wait_timeout' => env('ROCKETMQ_WAIT_TIMEOUT', 60),
            'heartbeat' => env('ROCKETMQ_HEARTBEAT', -1),
            'max_idle_time' => env('ROCKETMQ_MAX_IDLE_TIME', 60)
        ],
    ],
];