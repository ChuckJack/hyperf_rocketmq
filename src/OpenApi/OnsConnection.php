<?php

namespace Timebug\Rocketmq\OpenApi;

use AlibabaCloud\SDK\Ons\V20190214\Ons;
use Darabonba\OpenApi\Models\Config;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\SimplePool\Pool;
use Hyperf\Pool\SimplePool\PoolFactory;

class OnsConnection  implements ConnectionInterface
{
    private ?Ons $connection = null;

    private Pool $pool;

    public function __construct(
        private string $endpoint,
        private string $accessKey,
        private string $secretKey,
    )
    {
    }

    public function setPool(PoolFactory $factory, string $poolName = "default"): static
    {
        $this->pool = $factory->get($poolName, function () {
            $config = new Config(["accessKeyId" => $this->accessKey, "accessKeySecret" => $this->secretKey]);
            $config->endpoint = $this->endpoint;
            return new Ons($config);
        });
        return $this;
    }

    public function getConnection(): ?Ons
    {
        is_null($this->connection) && $this->connection = $this->pool->get()->getConnection();
        return $this->connection;
    }

    public function reconnect(): bool
    {
        $this->close();
        $this->connection = $this->getConnection();
        return true;
    }

    public function check(): bool
    {
        return true;
    }

    public function close(): bool
    {
        $this->connection = null;
        return true;
    }

    public function release(): void
    {
        $this->pool->release($this);
    }
}