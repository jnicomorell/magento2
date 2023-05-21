<?php

namespace Perficient\FinancialAid\Block\Rewrite\Product\View;

use Perficient\FinancialAid\Model\Config;

/**********************
 * Class GroupedSimpleLayout
 * Block methods for helping to display the simple products of a grouped product,
 *
 * @package Perficient\FinancialAid\Block\Rewrite\Product\View
 *
 */
class GroupedSimpleLayout extends \Vhl\Catalog\Block\Product\View\GroupedSimpleLayout
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layoutInterface;

    /**
     * @var \Magento\Catalog\Block\Product\Context
     */
    protected $context;

    /**
     * @var \Perficient\FinancialAid\Helper\Financialaid $FinancialAid
     */
    private $financialAid;


    /**
     * GroupedSimpleLayout constructor.
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Perficient\FinancialAid\Helper\Financialaid $financialAid,
        Config $faConfig,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->context = $context;
        $this->layoutInterface = $this->context->getLayout();
        $this->financialAid = $financialAid;
        $this->registry = $registry;
        $this->faConfig = $faConfig;

        parent::__construct(
            $blockFactory,
            $context,
            $arrayUtils,
            $data
        );
    }

    /**
     * @param $product
     * @param null $template
     * @return string
     */
    public function getComponentsAsHtml($product, $template = null)
    {
        //get the block instance
        $probablyUniqueSuffix = str_replace(".", "_", "_" . \microtime(true));
        $name = "product_components_" . $product->getId() . "_" . $probablyUniqueSuffix;
        $type = "Vhl\\Catalog\\Block\\Product\\View\\Components";
        $block = $this->layoutInterface->createBlock($type, $name, []);
        $block->setProduct($product);

        //set the template
        if ($template) {
            $block->setTemplate($template);
        } else {
            $block->setTemplate("Magento_Catalog::product/view/content-package-component.phtml");
        }
        //turn the handle
        $componentsAsHtml = $block->toHtml();
        return $componentsAsHtml;
    }
    
    /**
     * @return bool
     */
    public function getFinancialAid()
    {
        $product = $this->getProduct();
        return $this->financialAid->getFinancialAid($product);
    }

    /**
     * @return bool
     */
    public function hasContents()
    {
        return $this->getFinancialAid();
    }

    public function getAllowedSchools()
    {
        return explode(",", $this->faConfig->getAllowedSchools());
    }
}
