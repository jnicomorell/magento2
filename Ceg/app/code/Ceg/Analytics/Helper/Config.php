<?php
declare(strict_types=1);

namespace Ceg\Analytics\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\State;

class Config extends AbstractHelper
{
    const CONFIG_PATH = 'ceg_analytics';
    const ACTIVE = 'active';
    const SUCCESS_PAGE = 'active_successpage';

    protected $serialize;

    protected $appMode;

    /**
     * @param Json    $serialize
     * @param State   $appMode
     * @param Context $context
     */
    public function __construct(
        Json $serialize,
        State $appMode,
        Context $context
    ) {
        $this->serialize = $serialize;
        $this->appMode = $appMode;
        parent::__construct($context);
    }

    public function isActive($code)
    {
        return (bool)$this->scopeConfig->getValue(self::CONFIG_PATH.'/'.$code.'_settings/'. self::ACTIVE);
    }

    public function getConfig($code)
    {
        if (!$this->isActive($code)) {
            return false;
        }

        return $this->getConfigCode($code);
    }

    protected function getConfigCode($code)
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH.'/'.$code.'_settings/code');
    }

    public function getConfigSuccess(){
        $isActive = $this->scopeConfig->getValue(self::CONFIG_PATH.'/gtm_settings/'.self::SUCCESS_PAGE);
        return ($isActive)
            ? $this->scopeConfig->getValue(self::CONFIG_PATH.'/gtm_settings/code') : null;
    }

    public function getEventsConfig(){
        $config = $this->scopeConfig->getValue('ceg_analytics/ga_settings/ga_events/events_config');

        $unserializeConfig = $this->serialize->unserialize($config);
        $arrayConfig = [];

        foreach($unserializeConfig as $row){
            $arrayConfig[] = $row;
        }

        return $arrayConfig;
    }

    public function getFbConfigSuccess()
    {
        $isActive = $this->scopeConfig->getValue(self::CONFIG_PATH.'/fb_settings/'.self::SUCCESS_PAGE);
        return ($isActive)
            ? $this->scopeConfig->getValue(self::CONFIG_PATH.'/fb_settings/code') : null;
    }

    public function getFbToken()
    {
        $isActive = $this->scopeConfig->getValue(self::CONFIG_PATH.'/fb_settings/'.self::SUCCESS_PAGE);
        return ($isActive)
            ? $this->scopeConfig->getValue(self::CONFIG_PATH.'/fb_settings/token') : null;
    }

    public function isDebug(){
        return  (!$this->isProduction()) ?
                (bool)$this->scopeConfig->getValue('ceg_analytics/ga_settings/ga_events/event_debug'):
                false;
    }

    protected function isProduction(){
        $mode = $this->appMode->getMode();
        return ($mode !== 'developer') ? true : false;
    }

}
