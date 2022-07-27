<?php

declare(strict_types=1);

return [
    "default" => [
        "endpoint" => env("ALI_CLOUD_ENDPOINT", "ons.cn-beijing.aliyuncs.com"),
        "access_key" => env("ALI_CLOUD_ACCESS_KEY", ""),
        "secret_key" => env("ALI_CLOUD_SECRET_KEY", ""),
    ],
];