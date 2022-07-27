<?php

namespace Timebug\Rocketmq\OpenApi;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Pool\SimplePool\PoolFactory;
use InvalidArgumentException;
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

    public function getConnection(string $pool = "default"): OnsConnection
    {
        $config = $this->getConfig($pool);
        return $this->make($config);
    }

    private function make(array $config): OnsConnection
    {
        $endpoint = $config["endpoint"] ?? '';
        $accessKey = $config["access_key"] ?? '';
        $secretKey = $config["secret_key"] ?? '';

        $connection = new OnsConnection($endpoint, $accessKey, $secretKey);
        $connection->setPool($this->container->get(PoolFactory::class));
        return $connection;
    }

    private function getConfig(string $pool = "default"): array
    {
        $poolName = $pool == "" ? $this->poolName : $pool;
        $key = sprintf("alicloud.%s", $poolName);
        if (! $this->config->has($key)) {
            throw new InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }
        return $this->config->get($key);
    }

    public function getMQConfig(string $pool = "default"): Config
    {
        $poolName = $pool == "" ? $this->poolName : $pool;
        $key = sprintf("rocketmq.%s", $poolName);
        if (! $this->config->has($key)) {
            throw new InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }

        return new Config($this->config->get($key, []));
    }
}