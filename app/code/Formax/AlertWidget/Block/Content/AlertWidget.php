<?php

namespace Formax\AlertWidget\Block\Content;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Formax\AlertWidget\Api\AlertWidgetInterface;
use Magento\Framework\UrlInterface;

class AlertWidget extends Template
{

    private const BASE_PATH = 'header_coopeuch';

    private $_scopeConfigInterface;

    private $scopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

    protected $_storeManagerInterface;

    private $_alertWidgetInterface;

    const SUCCESS = 1;
    const INFO = 2;
    const WARNING = 3;
    const ERROR = 4;
    /**
     * __construct
     *
     * @return void
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManagerInterface,
        AlertWidgetInterface $alertWidgetInterface,
        array $data = []
    ) {
        $this->_scopeConfigInterface = $context->getScopeConfig();
        $this->_storeManagerInterface = $storeManagerInterface;
        $this->_alertWidgetInterface = $alertWidgetInterface;
        parent::__construct($context, $data);
    }

    /**
     * isActive
     *
     * @return void
     */
    public function isActive()
    {
        return $this->getConfig('alert_widget/enable');
    }


    /**
     * getAlertType
     *
     * @return void
     */
    public function getAlertType()
    {
        $config = $this->getConfig('alert_widget/alert_type');
        return $this->getAlertTypeClass($config);
    }

    /**
     * getAlertIcon
     *
     * @return array $type
     */
    public function getAlertTypeClass($type)
    {
        switch ($type) {
            case self::SUCCESS:
                $icon = $this->getConfig('alert_widget/alert_type_success_icon');
                if (empty($icon)) {
                    return null;
                }
                return [
                    'class' => 'success',
                    'icon' => $this->mediaUrl(UrlInterface::URL_TYPE_MEDIA) . 'alert_type_success_icon/' . $icon
                ];
            case self::INFO:
                $icon = $this->getConfig('alert_widget/alert_type_info_icon');
                if (empty($icon)) {
                    return null;
                }
                return [
                    'class' => 'info',
                    'icon' => $this->mediaUrl(UrlInterface::URL_TYPE_MEDIA) . 'alert_type_info_icon/' . $icon
                ];
            case self::WARNING:
                $icon = $this->getConfig('alert_widget/alert_type_warning_icon');
                if (empty($icon)) {
                    return null;
                }
                return [
                    'class' => 'warning',
                    'icon' => $this->mediaUrl(UrlInterface::URL_TYPE_MEDIA) . 'alert_type_warning_icon/' . $icon
                ];
            case self::ERROR:
                $icon = $this->getConfig('alert_widget/alert_type_error_icon');
                if (empty($icon)) {
                    return null;
                }
                return [
                    'class' => 'error',
                    'icon' => $this->mediaUrl(UrlInterface::URL_TYPE_MEDIA) . 'alert_type_error_icon/' . $icon
                ];
        }
    }

    /**
     * getInitialDate
     *
     * @return void
     */
    public function getInitialDate()
    {
        return $this->getConfig('alert_widget/initial_date');
    }

    /**
     * getEndingDate
     *
     * @return void
     */
    public function getEndingDate()
    {
        return $this->getConfig('alert_widget/ending_date');
    }

    /**
     * getTitle
     *
     * @return void
     */
    public function getTitle()
    {
        return $this->getConfig('alert_widget/title');
    }

    /**
     * getAlertContent
     *
     * @return void
     */
    public function getAlertContent()
    {
        return $this->getConfig('alert_widget/alert_content');
    }

    /**
     * getEnableExternalUrl
     *
     * @return void
     */
    public function getEnableExternalUrl()
    {
        return $this->getConfig('alert_widget/enable_external_url');
    }

    /**
     * getExternalUrl
     *
     * @return void
     */
    public function getExternalUrl()
    {
        return $this->getConfig('alert_widget/external_url');
    }

    /**
     * getExternalUrlLabel
     *
     * @return void
     */
    public function getExternalUrlLabel()
    {
        return $this->getConfig('alert_widget/external_url_label');
    }


    /**
     * checkAlertCookie
     *
     * @return void
     */
    public function checkAlertCookie()
    {

        $currentCookie = $this->_alertWidgetInterface->get();

        $contentHash = hash('md5', $this->getAlertContent());

        if (($currentCookie !== $contentHash) && $this->validateAlertWidgetDate()) {
            return [
                'hash' => $contentHash,
                'cookie' => $currentCookie,
                'closeUrl' => $this->mediaUrl(UrlInterface::URL_TYPE_WEB) . 'alert/alert/set?hash=' . $contentHash,
            ];
        }

        return null;
    }

    /**
     * validateAlertWidgetDate
     *
     * @return void
     */
    public function validateAlertWidgetDate()
    {
        $currentDate = date('Y-m-d');
        $currentDate = date('Y-m-d', strtotime($currentDate));
        $initialDate = date('Y-m-d', strtotime($this->getInitialDate()));
        $endingDate = date('Y-m-d', strtotime($this->getEndingDate()));

        if (($currentDate >= $initialDate) && ($currentDate <= $endingDate)) {
            return true;
        }

        return false;
    }

    /**
     * setNewCookie
     *
     * @param  mixed $contentHash
     *
     * @return void
     */
    public function setNewCookie($contentHash)
    {
        return $this->_alertWidgetInterface->set($contentHash, 86400);
    }

    /**
     * getConfig
     *
     * @param  mixed $key
     *
     * @return void
     */
    public function getConfig($key)
    {
        return $this->_scopeConfigInterface->getValue(self::BASE_PATH . '/' . $key, $this->scopeStore);
    }

    /**
     * mediaUrl
     *
     * @return void
     */
    private function mediaUrl($type)
    {
        $currentStore = $this->_storeManagerInterface->getStore()->getBaseUrl($type);

        return $currentStore;
    }
}
