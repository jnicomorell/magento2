<?php

declare(strict_types=1);

namespace Perficient\FinancialAid\Block;

/**
 * Class FinancialAid
 * @package Perficient\FinancialAid\Block\FinancialAid
 */
class FinancialAid extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Perficient\FinancialAid\Helper\Financialaid $FinancialAid
     */
    private $financialAid;

    /**
     * BookAlert constructor.
     * @param \Perficient\FinancialAid\Helper\Financialaid $FinancialAid
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Perficient\FinancialAid\Helper\Financialaid $financialAid,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->financialAid = $financialAid;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function getFinancialAid()
    {
        $product = $this->getCurrentProduct();
        return $this->financialAid->getFinancialAid($product);
    }

    /**
     * @return bool
     */
    public function hasContents()
    {
        return $this->getFinancialAid();
    }

    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }
}
