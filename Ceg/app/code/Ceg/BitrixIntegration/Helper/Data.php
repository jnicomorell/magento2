<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Data extends AbstractHelper
{
    const CONFIG_ACTIVE = 'ceg_bitrixintegration/general/active';
    const CONFIG_API_TIMEOUT = 'ceg_bitrixintegration/api/timeout';
    const CONFIG_API_TOKEN_URL = 'ceg_bitrixintegration/api/token/url';
    const CONFIG_API_TOKEN_CLIENT_ID = 'ceg_bitrixintegration/api/token/client_id';
    const CONFIG_API_TOKEN_CLIENT_SECRET = 'ceg_bitrixintegration/api/token/client_secret';
    const CONFIG_API_TOKEN_AUDIENCE = 'ceg_bitrixintegration/api/token/audience';
    const CONFIG_API_TOKEN_AUTH0_USER = 'ceg_bitrixintegration/api/token/auth0_user';
    const CONFIG_API_TOKEN_AUTH0_PASSWORD = 'ceg_bitrixintegration/api/token/auth0_password';

    /**
     * @var EncryptorInterface
     */
    public $encryptor;

    /**
     * @param Context            $context
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
    }

    /**
     * @param int $websiteId
     * @return mixed
     */
    public function isActive($websiteId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_ACTIVE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
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

    /**
     * @param int $websiteId
     * @return mixed
     */
    public function getTokenAuth0User($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_API_TOKEN_AUTH0_USER,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * @param int $websiteId
     * @return mixed
     */
    public function getTokenAuth0Password($websiteId = null)
    {
        $value = $this->scopeConfig->getValue(
            self::CONFIG_API_TOKEN_AUTH0_PASSWORD,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return $this->encryptor->decrypt($value);
    }

    /**
     * @param int $websiteId
     * @return mixed
     */
    public function getTokenUrl($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_API_TOKEN_URL,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * @param int $websiteId
     * @return mixed
     */
    public function getTimeout($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_API_TIMEOUT,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }
}
