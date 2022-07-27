<?php

namespace Timebug\Rocketmq\Config;

class Config
{
    private string $endpoint;

    private string $accessKey;

    private string $secretKey;

    private string $instanceId;

    private Pool $pool;

    public function __construct(private array $data = [])
    {
        isset($data['host']) && $this->endpoint = $data['host'];
        isset($data['access_key']) && $this->accessKey = $data['access_key'];
        isset($data['secret_key']) && $this->secretKey = $data['secret_key'];
        isset($data['instance_id']) && $this->instanceId = $data['instance_id'];
        isset($data['pool']) && $this->pool = new Pool($data['pool'] ?? []);
    }

    /**
     * @return mixed|string
     */
    public function getEndpoint(): mixed
    {
        return $this->endpoint;
    }

    /**
     * @return mixed|string
     */
    public function getAccessKey(): mixed
    {
        return $this->accessKey;
    }

    /**
     * @return mixed|string
     */
    public function getSecretKey(): mixed
    {
        return $this->secretKey;
    }

    /**
     * @return mixed|string
     */
    public function getInstanceId(): mixed
    {
        return $this->instanceId ?? "";
    }

    /**
     * @return Pool
     */
    public function getPool(): Pool
    {
        return $this->pool;
    }

    public function toArray(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'access_key' => $this->getAccessKey(),
            'secret_key' => $this->getSecretKey(),
            'instance_id' => $this->getInstanceId(),
            'pool' => $this->pool->toArray(),
        ];
    }
}