<?php

namespace Formax\Sniffer\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Formax\Sniffer\Api\Data\SnifferInterface;

class Sniffer extends \Magento\Framework\Model\AbstractExtensibleModel implements SnifferInterface
{

    public function __construct(
            \Magento\Framework\Model\Context $context,
            \Magento\Framework\Registry $registry,
            \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
            AttributeValueFactory $customAttributeFactory,
            \Formax\Sniffer\Model\ResourceModel\Sniffer $sniffer = null,
            \Magento\Framework\Data\Collection\AbstractDb $snifferCollection = null,
            array $data = []
    )
    {


        parent::__construct(
                $context,
                $registry,
                $extensionFactory,
                $customAttributeFactory,
                $sniffer,
                $snifferCollection,
                $data
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('\Formax\Sniffer\Model\ResourceModel\Sniffer');
    }

    public function getId()
    {
        return $this->getData(self::ID);
    }

    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    public function getIdTracing()
    {
        return $this->getData(self::ID_TRACING);
    }

    public function getEntity()
    {
        return $this->getData(self::ENTITY);
    }

    public function getBrowser()
    {
        return $this->getData(self::BROWSER);
    }

    public function getDevice()
    {
        return $this->getData(self::DEVICE);
    }

    public function getReferer()
    {
        return $this->getData(self::REFERER);
    }

    public function getUri()
    {
        return $this->getData(self::URI);
    }

    public function getIpAddress()
    {
        return $this->getData(self::IP_ADDRESS);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setIdTracing($idTracing)
    {
        return $this->setData(self::ID_TRACING, $idTracing);
    }

    public function setEntity($entity)
    {
        return $this->setData(self::ENTITY, $entity);
    }

    public function setBrowser($browser)
    {
        return $this->setData(self::BROWSER, $browser);
    }

    public function setDevice($device)
    {
        return $this->setData(self::DEVICE, $device);
    }

    public function setReferer($referer)
    {
        return $this->setData(self::REFERER, $referer);
    }

    public function setUri($uri)
    {
        return $this->setData(self::URI, $uri);
    }

    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    public function setIpAddress($ip_address)
    {
        return $this->setData(self::IP_ADDRESS, $ip_address);
    }

    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    public function getAdditionalData()
    {
        return $this->getData(self::ADDITIONAL_DATA);
    }

    public function setAdditionalData($additional_data)
    {
        return $this->setData(self::ADDITIONAL_DATA, $additional_data);
    }

}
