<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Model;

use Magento\Framework\Model\AbstractModel;
use Ceg\OrdersIntegration\Api\Data\QueueInterface;

class Queue extends AbstractModel implements QueueInterface
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(
            ResourceModel\Queue::class
        );
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($value)
    {
        $this->setData(self::ID, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setQuoteId($value)
    {
        $this->setData(self::QUOTE_ID, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($value)
    {
        $this->setData(self::STATUS, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAction()
    {
        return $this->getData(self::ACTION);
    }

    /**
     * @inheritdoc
     */
    public function setAction($value)
    {
        $this->setData(self::ACTION, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @inheritdoc
     */
    public function setMessage($value)
    {
        $this->setData(self::MESSAGE, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDatetime()
    {
        return $this->getData(self::DATETIME);
    }

    /**
     * @inheritdoc
     */
    public function setDatetime($value)
    {
        $this->setData(self::DATETIME, (string) $value);
        return $this;
    }
}
