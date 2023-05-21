<?php
declare(strict_types=1);

namespace Ceg\Impo\Controller\Adminhtml\Publication;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Model\ProductRepository;
use Ceg\Impo\Api\Data\ImpoInterface;
use Ceg\Impo\Api\Data\ImpoInterfaceFactory;
use Ceg\Impo\Api\ImpoRepositoryInterfaceFactory;
use Ceg\Impo\Helper\DataFactory as HelperFactory;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * Custom Date format
     */
    const FORMAT_DATE = 'd M, Y';

    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ceg_Impo::impo_save';

    /**
     * @var PageFactory|null
     */
    protected $resultPageFactory = null;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ImpoInterfaceFactory
     */
    protected $impoFactory;

    /**
     * @var ImpoRepositoryInterfaceFactory
     */
    protected $impoRepoFactory;

    /**
     * @var HelperFactory
     */
    protected $helperFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $session
     * @param ImpoRepositoryInterfaceFactory $impoRepoFactory
     * @param ImpoInterfaceFactory $impoFactory
     * @param HelperFactory $helperFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $session,
        ImpoRepositoryInterfaceFactory $impoRepoFactory,
        ImpoInterfaceFactory $impoFactory,
        HelperFactory $helperFactory,
        ProductRepository $productRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $session;
        $this->impoRepoFactory = $impoRepoFactory;
        $this->impoFactory = $impoFactory;
        $this->helperFactory = $helperFactory;
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $impoRepository = $this->impoRepoFactory->create();
        $helper = $this->helperFactory->create();

        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $impoId = $this->getRequest()->getParam(ImpoInterface::ID);
            $impo = $this->impoFactory->create();
            if ($impoId) {
                $impo = $impoRepository->getById($impoId);
                if (!$impo->getId()) {
                    $this->messageManager->addErrorMessage(__('This Impo no longer exists.'));
                    $helper->clearCurrentImpo();
                    return $resultRedirect->setPath('*/*/');
                }
            }

            if (empty($data['entity_id'])) {
                $data['entity_id'] = null;
            }
            $impo->setData($data);

            if (isset($data['grid_products']) && is_string($data['grid_products'])) {
                $products = json_decode($data['grid_products'], true);
                $impo->setRelatedProductIds($products);
            }

            try {
                $impoRepository->save($impo);
                $this->messageManager->addSuccessMessage(__('You saved the Impo %1.', $impo->getTitle()));
                $this->session->setFormData(false);
                return $resultRedirect->setPath('*/*/');
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->session->setFormData($data);
                $returnParams = ['id' => $impo->getId()];
                return $resultRedirect->setPath('*/*/edit', $returnParams);
            }
        }
        return $resultRedirect->setPath('*/*/');
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
