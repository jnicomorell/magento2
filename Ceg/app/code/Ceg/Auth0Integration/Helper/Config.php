<?php
declare(strict_types=1);

namespace Ceg\Auth0Integration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Config extends AbstractHelper
{
    const CONFIG_PATH = 'auth0_settings';
    const ACTIVE = 'active';
    const DOMAIN = 'domain';
    const CLIENT_ID = 'client_id';
    const CLIENT_SECRET = 'client_secret';
    const REDIRECT_URI = 'redirect_uri';
    const AUDIENCE = 'audience';
    const SCOPE = 'scope';
    const PERSIST_ID_TOKEN = 'persist_id_token';
    const PERSIST_ACCESS_TOKEN = 'persist_access_token';
    const PERSIST_REFRESH_TOKEN = 'persist_refresh_token';
    const TOKEN_ALGORITHM = "token_algorithm";
    const NEW_CUSTOMER_GROUP = "new_customer_group";
    const PAGINATION = "pagination";
    const COUNTRIES = "countries";

    const RS256 = "RS256";
    const HS256 = "HS256";

    const SITE_RESTRICTED = 'restricted';

    protected $serialize;

    public function __construct(
        Json $serialize,
        Context $context
    ) {
        $this->serialize = $serialize;
        parent::__construct($context);
    }

    /**
     * @param $path
     * @param bool $managementApi
     * @return mixed
     */
    protected function getModuleConfig($path, $managementApi)
    {
        $realPath = ($managementApi) ? self::CONFIG_PATH . '/management_api/' : self::CONFIG_PATH  . '/general/';
        return $this->scopeConfig->getValue($realPath . $path);
    }

    /**
     * @return string
     */
    public function getActive()
    {
        return $this->getModuleConfig(self::ACTIVE,false);
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->getModuleConfig(self::DOMAIN,false);
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->getModuleConfig(self::CLIENT_ID,false);
    }

    /**
     * @return mixed
     */
    public function getClientSecret()
    {
        return $this->getModuleConfig(self::CLIENT_SECRET,false);
    }

    /**
     * @return mixed
     */
    public function getRedirectUri()
    {
        return $this->getModuleConfig(self::REDIRECT_URI,false);
    }

    /**
     * @return mixed
     */
    public function getAudience()
    {
        return $this->getModuleConfig(self::AUDIENCE,false);
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->getModuleConfig(self::SCOPE,false);
    }

    /**
     * @return mixed
     */
    public function getPersistIdToken()
    {
        return $this->getModuleConfig(self::PERSIST_ID_TOKEN,false);
    }

    /**
     * @return mixed
     */
    public function getPersistAccessToken()
    {
        return $this->getModuleConfig(self::PERSIST_ACCESS_TOKEN,false);
    }

    /**
     * @return mixed
     */
    public function getPersistRefreshToken()
    {
        return $this->getModuleConfig(self::PERSIST_REFRESH_TOKEN,false);
    }

    /**
     * @return mixed
     */
    public function getTokenAlgorithm()
    {
        return $this->getModuleConfig(self::TOKEN_ALGORITHM,false);
    }

    /**
     * @return mixed
     */
    public function getNewCustomerGroupId()
    {
        // TODO: * Temporal option: Group will be obteined from BITRIX service
        return $this->getModuleConfig(self::NEW_CUSTOMER_GROUP,false);
    }

    /**
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->getDomain().'/v2/logout';
    }

    /**
     * @return mixed
     */
    public function getManagementDomain()
    {
        return $this->getModuleConfig(self::DOMAIN, true);
    }

    /**
     * @return mixed
     */
    public function getManagementClientId()
    {
        return $this->getModuleConfig(self::CLIENT_ID, true);
    }

    /**
     * @return mixed
     */
    public function getManagementClientSecret()
    {
        return $this->getModuleConfig(self::CLIENT_SECRET, true);
    }

    /**
     * @return mixed
     */
    public function getManagementAudience()
    {
        return $this->getModuleConfig(self::AUDIENCE, true);
    }

    /**
     * @return string
     */
    public function getRestricted()
    {
        return $this->getModuleConfig(self::SITE_RESTRICTED,false);
    }
    /**
     * @return integer
     */
    public function getPagination()
    {
        return $this->getModuleConfig(self::PAGINATION, true);
    }

    public function getCountries()
    {

        $countries = $this->getModuleConfig(self::COUNTRIES, true);

        if ($countries == '' || $countries == null) {
            return;
        }

        $unserializeCountry = $this->serialize->unserialize($countries);

        $countryArray = [];
        foreach($unserializeCountry as $row){
            $countryArray[$row['website_code']] = $row['auth0_code'];
        }

        return $countryArray;
    }
}
