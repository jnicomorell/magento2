<?php
declare(strict_types=1);

namespace Ceg\Impo\Block\Adminhtml\Publication\Edit;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\BlockInterface;
use Ceg\Impo\Block\Adminhtml\Publication\Edit\Tab\RelatedProducts as RelatedProductsTab;
use Ceg\Impo\Model\ResourceModel\Impo\RelatedProductFactory;
use Ceg\Impo\Helper\DataFactory as HelperFactory;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class RelatedProducts extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Ceg_Impo::publication/form/related_products.phtml';

    /**
     * @var RelatedProductsTab
     */
    protected $blockGrid;

    /**
     * @var HelperFactory
     */
    protected $helperFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var RelatedProductFactory
     */
    protected $productFactory;

    /**
     * @param Context $context
     * @param RelatedProductFactory $productFactory
     * @param HelperFactory $helperFactory
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Context $context,
        RelatedProductFactory $productFactory,
        HelperFactory $helperFactory,
        Json $json,
        array $data = []
    ) {
        $this->json = $json;
        $this->helperFactory = $helperFactory;
        $this->productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return BlockInterface
     * @throws LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                RelatedProductsTab::class,
                'impo.product.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return string
     */
    public function getProductsJson()
    {
        $productIds = [];
        $helper = $this->helperFactory->create();
        $currentImpo = $helper->getCurrentImpo();
        if ($currentImpo) {
            $relatedProduct = $this->productFactory->create();
            $productIds = $relatedProduct->getRelatedProductIds($currentImpo);
        }
        return $this->json->serialize($productIds);
    }
}
