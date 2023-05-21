<?php

namespace Ceg\Impo\Helper;

use Ceg\Impo\Model\Impo;
use Composer\Cache;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

class Data extends AbstractHelper
{
    const XML_PATH_IMPO = 'impo/';
    const CURRENT_IMPO_KEY = 'current_impo';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;
    protected $cacheTypeList;
    protected $cachePool;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SessionManagerInterface $sessionManager,
        TypeListInterface $cacheTypeList,
        Pool $pool
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->sessionManager = $sessionManager;
        $this->cacheTypeList = $cacheTypeList;
        $this->cachePool = $pool;
    }

    /**
     * getConfigValue function
     *
     * @param int $field
     * @param int $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    /**
     * getGeneralConfig function
     *
     * @param int $code
     * @param int $storeId
     * @return mixed
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_IMPO .'general/'. $code, $storeId);
    }

    /**
     * @return Impo
     */
    public function getCurrentImpo()
    {
        return $this->registry->registry(self::CURRENT_IMPO_KEY);
    }

    /**
     * @param Impo $impo
     */
    public function setCurrentImpo($impo)
    {
        $this->registry->register(self::CURRENT_IMPO_KEY, $impo);
    }

    public function clearCurrentImpo()
    {
        $this->registry->unregister(self::CURRENT_IMPO_KEY);
    }

    /**
     * @param array $types
     * @return bool
     */
    public function clearCache(array $types): bool
    {
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cachePool as $cache) {
            $cache->getBackend()->clean();
        }
        return true;
    }
}
