<?php
declare(strict_types=1);

namespace Ceg\Impo\Controller\Adminhtml\Publication;

use Ceg\Impo\Api\ImpoRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;
use Ceg\Impo\Block\Adminhtml\Publication\Edit\Tab\RelatedProducts as RelatedProductsTab;
use Ceg\Impo\Helper\DataFactory as HelperFactory;

class Grid extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ceg_Impo::impo';

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var ImpoRepositoryInterface
     */
    protected $impoRepository;

    /**
     * @var HelperFactory
     */
    protected $helperFactory;

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     * @param ImpoRepositoryInterface $impoRepository
     * @param HelperFactory $helperFactory
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory,
        ImpoRepositoryInterface $impoRepository,
        HelperFactory $helperFactory
    ) {
        parent::__construct($context);
        $this->helperFactory = $helperFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->impoRepository = $impoRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $helper = $this->helperFactory->create();
        $helper->clearCurrentImpo();

        $impoId = $this->getRequest()->getParam('id');
        if ($impoId) {
            $impo = $this->impoRepository->getById($impoId);
            $helper->setCurrentImpo($impo);
        }

        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                RelatedProductsTab::class,
                'impo.product.grid'
            )->toHtml()
        );
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _isAllowed()
    {
        return true;
    }
}
