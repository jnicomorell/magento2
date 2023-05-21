<?php

namespace Formax\Sniffer\Api\Data;

/**
 * Interface Sniffer
 * @api
 * @since 100.0.2
 */
interface SnifferInterface extends \Magento\Framework\Api\ExtensibleDataInterface {

    const ID = 'id'; //get | set
    const ID_TRACING = 'id_tracing'; //get | set
    const ENTITY = 'entity'; //get | set
    const BROWSER = 'browser'; //get | set
    const DEVICE = 'device'; //get | set
    const REFERER = 'referer'; //get | set
    const URI = 'uri'; //get | set
    const IP_ADDRESS = 'ip_address'; //get | set
    const ADDITIONAL_DATA = 'additional_data'; //get | set
    const STORE_ID = 'store_id'; // get | set
    const CREATED_AT = 'created_at'; // get | set
    const UPDATED_AT = 'updated_at'; // get | set

    /**
     * Returns the row ID.
     *
     * @return int|null Row ID. Otherwise, null.
     */
    public function getId();

    /**
     * Sets the row ID.
     *
     * @param int $id
     * @return \Formax\SimulatorConsumo\Api\Data\RangeInterface
     */
    public function setId($id);
   
    public function getEntity();
    public function setEntity($entity);

    public function getIdTracing();
    public function setIdTracing($idTracing);

    public function getBrowser();
    public function setBrowser($browser);
    
    public function getDevice();
    public function setDevice($device);

    public function getReferer();
    public function setReferer($referer);

    public function getUri();
    public function setUri($uri);
    
    public function getIpAddress();
    public function setIpAddress($ip_address);
    
    public function getAdditionalData();
    public function setAdditionalData($additional_data);

    public function getStoreId();
    public function setStoreId($storeId);
    
    public function getCreatedAt();
    public function setCreatedAt($createdAt);
    
    public function getUpdatedAt();
    public function setUpdatedAt($updatedAt);


}
