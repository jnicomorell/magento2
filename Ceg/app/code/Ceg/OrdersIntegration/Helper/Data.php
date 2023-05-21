<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Data extends AbstractHelper
{
    const CONFIG_PATH_ACTIVE = 'ceg_ordersintegration/general/active';
    const CONFIG_PATH_ENABLE_SEND = 'ceg_ordersintegration/api/enable_send';
    const CONFIG_PATH_TOKEN_USER = 'ceg_ordersintegration/api/token_user';
    const CONFIG_PATH_TOKEN_PASSWORD = 'ceg_ordersintegration/api/token_password';
    const CONFIG_PATH_CREATE_API_URL = 'ceg_ordersintegration/api/create_api_url';
    const CONFIG_PATH_UPDATE_API_URL = 'ceg_ordersintegration/api/update_api_url';
    const CONFIG_PATH_DELETE_API_URL = 'ceg_ordersintegration/api/delete_api_url';
    const CONFIG_PATH_TIMEOUT = 'ceg_ordersintegration/api/timeout';

    const CONFIG_PATH_APP_ACTIVE = 'ceg_ordersintegration/app/active_redirect';
    const CONFIG_PATH_APP_URL = 'ceg_ordersintegration/app/url';
    const CONFIG_PATH_APP_TITLE = 'ceg_ordersintegration/app/title';

    const CONFIG_PATH_TOKEN_URL = 'ceg_ordersintegration/api/token/url';
    const CONFIG_API_TOKEN_CLIENT_ID = 'ceg_ordersintegration/api/token/client_id';
    const CONFIG_API_TOKEN_CLIENT_SECRET = 'ceg_ordersintegration/api/token/client_secret';
    const CONFIG_API_TOKEN_AUDIENCE = 'ceg_ordersintegration/api/token/audience';

    protected $encryptor;

    public function __construct(
        Context $context,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function isActive($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function isSendEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_ENABLE_SEND,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getTokenUrl($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_TOKEN_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getCreateApiUrl($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_CREATE_API_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getUpdateApiUrl($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_UPDATE_API_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getDeleteApiUrl($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_DELETE_API_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getTimeout($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_TIMEOUT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function isRedirectEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH_APP_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param string $storeId
     * @return mixed
     */
    public function getAppUrl($storeId = null)
    {
        $url = $this->_urlBuilder->getUrl('sales/order/history');
        if ($this->isRedirectEnabled($storeId)) {
            $url = $this->scopeConfig->getValue(
                self::CONFIG_PATH_APP_URL,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return $url;
    }

    /**
     * @param string $storeId
     * @return mixed
     */
    public function getAppTitle($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_APP_TITLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $websiteId
     * @return mixed
     */
    public function getTokenClientId($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_API_TOKEN_CLIENT_ID,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * @param int $websiteId
     * @return mixed
     */
    public function getTokenClientSecret($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_API_TOKEN_CLIENT_SECRET,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * @param int $websiteId
     * @return mixed
     */
    public function getTokenAudience($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_API_TOKEN_AUDIENCE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }
}
