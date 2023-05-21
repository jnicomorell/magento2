<?php

namespace Ceg\OrdersIntegration\Model\Integration;

use Magento\Framework\Model\AbstractModel;
use Ceg\OrdersIntegration\Api\Data\Integration\ResultInterface;

class Result extends AbstractModel implements ResultInterface
{
    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS_KEY);
    }

    /**
     * @inheritdoc
     */
    public function getMessages()
    {
        $messages = $this->getData(self::MESSAGES_KEY);
        if (empty($messages)) {
            $messages = [];
        }
        return $messages;
    }

    public function addMessage($value)
    {
        $messages = $this->getMessages();
        if (!empty($value)) {
            array_push($messages, $value);
        }
        $this->setData(self::MESSAGES_KEY, $messages);
        return $this;
    }

    public function getMessage()
    {
        $message = '';
        $messages = $this->getData(self::MESSAGES_KEY);
        if (is_array($messages)) {
            foreach ($messages as $value) {
                $message .= $value ."\n\r";
            }
        }

        return $message;
    }

    public function setSuccessStatus()
    {
        $this->setData(self::STATUS_KEY, self::STATUS_SUCCESS);
        return $this;
    }

    public function setErrorStatus()
    {
        $this->setData(self::STATUS_KEY, self::STATUS_ERROR);
        return $this;
    }

    public function setPartialSuccessStatus()
    {
        $this->setData(self::STATUS_KEY, self::STATUS_PARTIAL_SUCCESS);
        return $this;
    }

    public function isError()
    {
        return $this->getStatus() === self::STATUS_ERROR;
    }

    public function isSuccess()
    {
        return $this->getStatus() === self::STATUS_SUCCESS;
    }
}
