<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ceg\CatalogPermissions\Rewrite\Magento\Checkout\Controller\Cart;

use Ceg\CatalogPermissions\Helper\Data as HelperData;
use Ceg\Quote\Rewrite\Magento\Quote\Model\Quote;
use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Post update shopping cart.
 */
class UpdatePost extends \Magento\Checkout\Controller\Cart implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var RequestQuantityProcessor
     */
    private $quantityProcessor;

    /**
     * @var HelperData
     */
    private $helper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param HelperData $helper
     * @param RequestQuantityProcessor $quantityProcessor
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        HelperData $helper,
        RequestQuantityProcessor $quantityProcessor = null
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->helper = $helper;
        $this->quantityProcessor = $quantityProcessor ?: $this->_objectManager->get(RequestQuantityProcessor::class);
    }

    /**
     * Empty customer's shopping cart
     *
     * @return void
     */
    protected function _emptyShoppingCart()
    {
        try {
            $this->cart->truncate()->save();
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, __('We can\'t update the shopping cart.'));
        }
    }

    /**
     * Update customer's shopping cart
     *
     * @return void
     */
    protected function _updateShoppingCart()
    {
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                if (!$this->cart->getCustomerSession()->getCustomerId() && $this->cart->getQuote()->getCustomerId()) {
                    $this->cart->getQuote()->setCustomerId(null);
                }
                $cartData = $this->quantityProcessor->process($cartData);
                $cartData = $this->cart->suggestItemsQty($cartData);
                $this->cart->updateItems($cartData)->save();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            //TODO remove object manager
            $this->messageManager->addErrorMessage(
                $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the shopping cart.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
    }

    /**
     * Update shopping cart data action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $redirectUrl = $this->statusRedirect();
        if (!empty($redirectUrl)) {
            return $redirectUrl;
        }
        $quote = $this->cart->getQuote();
        $items = $quote->getAllVisibleItems();
        if (count($items) == 1) {
            $cartItems = $this->getRequest()->getParam("cart");
            $itemId = array_key_first($cartItems);
            if ((int)$cartItems[$itemId]["qty"] == 0 && $this->cart->getQuote()->cancelParentQuote($itemId)) {
                $this->_emptyShoppingCart();
            }
            $updateAction = (string)$this->getRequest()->getParam('update_cart_action');
            $this->getUpdateAction($updateAction);
            return $this->_goBack();
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }

    private function getUpdateAction($updateAction) {
        switch ($updateAction) {
            case 'empty_cart':
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                $this->_updateShoppingCart();
                break;
            default:
                $this->_updateShoppingCart();
        }
    }

    protected function statusRedirect()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $quoteStatus = $this->cart->getQuote()->getStatus();
        if ($quoteStatus) {
            switch ($quoteStatus) {
                case Quote::STATUS_APPROVED:
                    $this->messageManager->addErrorMessage(__($this->helper->getApprovedCartMessage()));
                    $action = $this->_goBack();
                    break;
                case Quote::STATUS_CLOSED:
                    $this->messageManager->addErrorMessage(__($this->helper->getClosedMessage()));
                    $action = $this->_goBack();
                    break;
                case Quote::STATUS_CLONED:
                    $this->cart->getQuote()->reopen();
                    break;
                case Quote::STATUS_NEW:
                    $this->cart->getQuote()->open();
                    break;
                default:
                    $action = false;
            }
        }

        return $action;
    }
}
