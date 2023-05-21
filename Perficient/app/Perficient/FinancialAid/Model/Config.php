<?php

declare(strict_types=1);

namespace Perficient\FinancialAid\Model;

/**
 * Class Config
 * @package Perficient\FinancialAid\Model
 */
class Config
{
    const XML_PATH_CONFIG_FINANCIAL_ENABLED = 'financial_aid/financial_aid_conf/enabled';
    const XML_PATH_CONFIG_FINANCIAL_EMAILS = 'financial_aid/financial_aid_conf/financial_aid_emails';
    const XML_PATH_CONFIG_FINANCIAL_SENDER = 'financial_aid/financial_aid_conf/sender';
    const XML_PATH_CONFIG_FINANCIAL_CONS_TITLE = 'financial_aid/financial_form/consent_title';
    const XML_PATH_CONFIG_FINANCIAL_CONS_TEXT = 'financial_aid/financial_form/consent_text';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    private $scopeConfig;

    /**
     * Config constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_FINANCIAL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return string
     */
    public function getEmails()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_FINANCIAL_EMAILS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getConsentTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_FINANCIAL_CONS_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return string
     */
    public function getConsentText()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_FINANCIAL_CONS_TEXT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG_FINANCIAL_SENDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
