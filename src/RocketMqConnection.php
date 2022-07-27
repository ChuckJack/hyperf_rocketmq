<?php

namespace Timebug\Rocketmq;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool;
use Hyperf\Pool\SimplePool\PoolFactory;
use Timebug\Rocketmq\Components\AliyunMQ\Config;
use Timebug\Rocketmq\Components\AliyunMQ\MQClient;
use Psr\Log\LoggerInterface;

class RocketMqConnection implements ConnectionInterface
{

    protected ?MQClient $connection = null;

    protected ?LoggerInterface $logger;

    protected Pool $pool;

    /**
     * @var float
     */
    protected float $lastUseTime = 0.0;

    public function __construct(
        protected string $endpoint,
        protected string $accessKey,
        protected string $secretKey,
        protected ?string $securityToken = null,
        protected ?Config $config = null,
    )
    {
    }

    public function setPool(PoolFactory $factory, string $poolName = "default", array $options = []): static
    {
        $this->pool = $factory->get($poolName, function () {
            return new MQClient(
                $this->endpoint,
                $this->accessKey,
                $this->secretKey,
                $this->securityToken,
                $this->config,
            );
        }, $options);
        return $this;
    }

    public function setLogger(?LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function getConnection(): MQClient
    {
        $this->connection = $this->pool->get()->getConnection();
        return $this->connection;
    }

    public function reconnect(): bool
    {
        $this->close();
        $this->getConnection();
        $this->lastUseTime = microtime(true);
        return true;
    }

    public function check(): bool
    {
        $maxIdleTime = $this->pool->getOption()->getMaxIdleTime();
        $now = microtime(true);

        if ($now > $maxIdleTime + $this->lastUseTime) {
            return false;
        }

        $this->lastUseTime = $now;
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