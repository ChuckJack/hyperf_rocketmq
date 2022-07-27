<?php
namespace Timebug\Rocketmq\Components\AliyunMQ;

class Config
{
    private $proxy;  // http://username:password@192.168.16.1:10
    private $connectTimeout;
    private $requestTimeout;
    private $expectContinue;

    public function __construct()
    {
        $this->proxy = NULL;
        $this->requestTimeout = 35; // 35 seconds
        $this->connectTimeout = 3;  // 3 seconds
        $this->expectContinue = false;
    }


    public function getProxy()
    {
        return $this->proxy;
    }

    public function setProxy($proxy): static
    {
        $this->proxy = $proxy;
        return $this;
    }

    public function getRequestTimeout(): int
    {
        return $this->requestTimeout;
    }

    public function setRequestTimeout($requestTimeout): static
    {
        $this->requestTimeout = $requestTimeout;
        return $this;
    }

    public function setConnectTimeout($connectTimeout): static
    {
        $this->connectTimeout = $connectTimeout;
        return $this;
    }

    public function getConnectTimeout(): int
    {
        return $this->connectTimeout;
    }

    public function getExpectContinue(): bool
    {
        return $this->expectContinue;
    }

    public function setExpectContinue($expectContinue): static
    {
        $this->expectContinue = $expectContinue;
        return $this;
    }
}

