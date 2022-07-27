<?php
namespace Timebug\Rocketmq\Components\AliyunMQ\Exception;

use Timebug\Rocketmq\Components\AliyunMQ\Constants;
use Timebug\Rocketmq\Components\AliyunMQ\Model\AckMessageErrorItem;

/**
 * Ack message could fail for some receipt handles,
 *     and AckMessageException will be thrown.
 * All failed receiptHandles are saved in "$ackMessageErrorItems"
 */
class AckMessageException extends MQException
{
    protected array $ackMessageErrorItems;

    public function __construct($code, $message, $previousException = NULL, $requestId = NULL, $hostId = NULL)
    {
        parent::__construct($code, $message, $previousException, Constants::ACK_FAIL, $requestId, $hostId);

        $this->ackMessageErrorItems = array();
    }

    public function addAckMessageErrorItem(AckMessageErrorItem $item)
    {
        $this->ackMessageErrorItems[] = $item;
    }

    public function getAckMessageErrorItems(): array
    {
        return $this->ackMessageErrorItems;
    }
}

