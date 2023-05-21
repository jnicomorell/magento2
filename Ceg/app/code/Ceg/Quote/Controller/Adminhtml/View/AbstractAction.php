<?php
declare(strict_types=1);

namespace Ceg\Quote\Controller\Adminhtml\View;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

abstract class AbstractAction extends Action
{
    const ADMIN_RESOURCE = '';

    /**
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Action\Context          $context
     * @param CartRepositoryInterface $quoteRepository
     * @param Registry                $coreRegistry
     * @param PageFactory             $resultPageFactory
     * @param LoggerInterface         $logger
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        Action\Context $context,
        CartRepositoryInterface $quoteRepository,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return CartInterface|false
     */
    protected function initQuote()
    {
        $quoteId = $this->getRequest()->getParam('id');
        try {
            $quote = $this->quoteRepository->get($quoteId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This quote no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage(__('This quote no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }

        $this->coreRegistry->register('current_quote', $quote);
        return $quote;
    }

    /**
     * @return bool
     */
    protected function isValidPostRequest()
    {
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        return ($formKeyIsValid && $isPost);
    }
}
