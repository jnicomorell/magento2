<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Model\Integration;

use Ceg\BitrixIntegration\Api\Data\Integration\AbstractElementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;

abstract class AbstractElement implements AbstractElementInterface
{
    const ELEMENT_TYPE = '';
    public $model = null;
    public $scopeConfig;
    public $storeManager;

    /**
     * @param ScopeConfigInterface  $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function setModel($value)
    {
        $this->model = $value;
    }

    public function getType()
    {
        return '';
    }

    public function getId()
    {
        return 0;
    }

    public function getWebsiteId()
    {
        return 0;
    }

    public function isEnabled()
    {
        return false;
    }

    public function useAccessToken()
    {
        return false;
    }

    public function getUrl()
    {
        return '';
    }

    public function getRequestData()
    {
        return [];
    }

    public function validateResponse($response)
    {
        throw new LocalizedException(__('Could not get data from Bitrix API.'));
    }
}
