<?php

namespace Timebug\Rocketmq;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Pool\SimplePool\PoolFactory;
use Hyperf\Utils\Arr;
use InvalidArgumentException;
use Timebug\Rocketmq\Components\AliyunMQ\Config as MqConfig;
use Psr\Container\ContainerInterface;
use Timebug\Rocketmq\Config\Config;

class ConnectionFactory
{
    protected ConfigInterface $config;

    protected string $poolName = "default";

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $this->container->get(ConfigInterface::class);
    }

    public function getConnection(string $pool = "default"): RocketMqConnection
    {
        $config = $this->getConfigs($pool);
        return $this->make($config);
    }

    public function make(Config $config): RocketMqConnection
    {
        $endpoint = $config->getEndpoint() ?? "";
        $accessKey = $config->getAccessKey() ?? "";
        $secretKey = $config->getSecretKey() ?? "";

        $connection = new RocketMqConnection(
            $endpoint,
            $accessKey,
            $secretKey,
            null,
            $this->getMQConfig($config),
        );

        $connection->setPool(
            $this->container->get(PoolFactory::class),
            $this->poolName,
            $config->getPool()->toArray(),
        )->setLogger(
            $this->container->get(StdoutLoggerInterface::class)
        );
        return $connection;
    }

    public function setPoolName(string $poolName = "default"): static
    {
        $this->poolName = $poolName;
        return $this;
    }

    public function getConfig(string $poolName = "default"): array
    {
        $poolName = $poolName == "" ? $this->poolName : $poolName;
        $key = sprintf("rocketmq.%s", $poolName);
        if (! $this->config->has($key)) {
            throw new InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }
        return $this->config->get($key);
    }

    public function getConfigs(string $poolName = "default"): Config
    {
        $poolName = $poolName == "" ? $this->poolName : $poolName;
        $key = sprintf("rocketmq.%s", $poolName);
        if (! $this->config->has($key)) {
            throw new InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }

        return new Config($this->config->get($key, []));
    }

    protected function getMQConfig(Config $config): MqConfig
    {
        $pool = $config->getPool();

        $conf =  new MqConfig();
        $conf->setConnectTimeout($pool->getConnectTimeout() ?? 3);
        $conf->setRequestTimeout($pool->getWaitTimeout() ?? 60);
        return $conf;
    }
}