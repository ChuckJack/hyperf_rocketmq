<?php
namespace Timebug\Rocketmq\Components\AliyunMQ\Responses;

use Timebug\Rocketmq\Components\AliyunMQ\Common\XMLParser;
use Timebug\Rocketmq\Components\AliyunMQ\Constants;
use Timebug\Rocketmq\Components\AliyunMQ\Exception\AckMessageException;
use Timebug\Rocketmq\Components\AliyunMQ\Exception\InvalidArgumentException;
use Timebug\Rocketmq\Components\AliyunMQ\Exception\MQException;
use Timebug\Rocketmq\Components\AliyunMQ\Exception\ReceiptHandleErrorException;
use Timebug\Rocketmq\Components\AliyunMQ\Exception\TopicNotExistException;
use Timebug\Rocketmq\Components\AliyunMQ\Model\AckMessageErrorItem;

class AckMessageResponse extends BaseResponse
{
    public function __construct()
    {
    }

    public function parseResponse($statusCode, $content)
    {
        $this->statusCode = $statusCode;
        if ($statusCode == 204) {
            $this->succeed = TRUE;
        } else {
            $this->parseErrorResponse($statusCode, $content);
        }
    }

    public function parseErrorResponse($statusCode, $content, MQException $exception = NULL)
    {
        $this->succeed = FALSE;
        $xmlReader = $this->loadXmlContent($content);

        try {
            while ($xmlReader->read())
            {
                if ($xmlReader->nodeType == \XMLReader::ELEMENT) {
                    switch ($xmlReader->name) {
                    case Constants::ERROR:
                        $this->parseNormalErrorResponse($xmlReader);
                        break;
                    default: // case Constants::Messages
                        $this->parseAckMessageErrorResponse($xmlReader);
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            if ($exception != NULL) {
                throw $exception;
            } elseif($e instanceof MQException) {
                throw $e;
            } else {
                throw new MQException($statusCode, $e->getMessage());
            }
        } catch (\Throwable $t) {
            throw new MQException($statusCode, $t->getMessage());
        }
    }

    private function parseAckMessageErrorResponse($xmlReader)
    {
        $ex = new AckMessageException($this->statusCode, "AckMessage Failed For Some ReceiptHandles");
        $ex->setRequestId($this->getRequestId());
        while ($xmlReader->read())
        {
            if ($xmlReader->nodeType == \XMLReader::ELEMENT && $xmlReader->name == Constants::ERROR) {
                $ex->addAckMessageErrorItem(AckMessageErrorItem::fromXML($xmlReader));
            }
        }
        throw $ex;
    }

    private function parseNormalErrorResponse($xmlReader)
    {
        $result = XMLParser::parseNormalError($xmlReader);

        if ($result['Code'] == Constants::INVALID_ARGUMENT)
        {
            throw new InvalidArgumentException($this->getStatusCode(), $result['Message'], NULL, $result['Code'], $result['RequestId'], $result['HostId']);
        }
        if ($result['Code'] == Constants::TOPIC_NOT_EXIST)
        {
            throw new TopicNotExistException($this->getStatusCode(), $result['Message'], NULL, $result['Code'], $result['RequestId'], $result['HostId']);
        }
        if ($result['Code'] == Constants::RECEIPT_HANDLE_ERROR)
        {
            throw new ReceiptHandleErrorException($this->getStatusCode(), $result['Message'], NULL, $result['Code'], $result['RequestId'], $result['HostId']);
        }

        throw new MQException($this->getStatusCode(), $result['Message'], NULL, $result['Code'], $result['RequestId'], $result['HostId']);
    }
}

?>
