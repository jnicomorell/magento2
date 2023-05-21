<?php
declare(strict_types=1);

namespace Ceg\CatalogPermissions\Rewrite\Magento\Checkout\Controller\Cart;

use Ceg\CatalogPermissions\Helper\Data as HelperData;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Controller\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Zend_Filter_LocalizedToNormalized;

/**
 * Controller for processing add to cart action.
 */
class Add extends Cart implements HttpPostActionInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var HelperData
     */
    private $helper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Data
     */
    private $jsonHelper;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param HelperData $helper
     * @param LoggerInterface $logger
     * @param ResolverInterface $resolver
     * @param Escaper $escaper
     * @param Data $jsonData
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        HelperData $helper,
        LoggerInterface $logger,
        ResolverInterface $resolver,
        Escaper $escaper,
        Data $jsonData
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->resolver = $resolver;
        $this->escaper = $escaper;
        $this->jsonHelper = $jsonData;
    }

    /**
     * Initialize product instance from request data
     *
     * @return Product|false
     * @throws NoSuchEntityException
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        $impoId = $this->getRequest()->getParam('impoid');
        $qty = $this->getRequest()->getParam('qty');
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productId, false, $storeId);
                $product->setFobSubtotal($product->getFob() * $qty);
                $product->setFobUnit($product->getFob());
                $product->setImpoId($impoId);
                return $product;
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Add product to shopping cart action
     *
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $product = $this->_initProduct();
        $redirectUrl = !$product ? $this->goBack() : $this->statusRedirect($product);
        if (!empty($redirectUrl)) {
            return $redirectUrl;
        }

        try {
            $filter = new Zend_Filter_LocalizedToNormalized(
                ['locale' => $this->resolver->getLocale()]
            );
            $params = $paramsQty = $this->getRequest()->getParams();
            $paramsQty['qty'] = $filter->filter($params['qty']);
            $params = isset($params['qty']) ? $paramsQty : $params;

            $this->cart->addProduct($product, $params);
            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                $action = $this->cartRedirect($product);
            }
        } catch (LocalizedException $e) {
            $action = $this->addNotificationMessages($e);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to your shopping cart right now.')
            );
            $this->logger->critical($e);

            $action = $this->goBack();
        }

        return $action;
    }

    /**
     * @param $product
     *
     * @return $this|Redirect
     */
    protected function cartRedirect($product)
    {
        if (!$this->cart->getQuote()->getHasError()) {
            $message = __('You added %1 to your shopping cart.', $product->getName());
            ($this->shouldRedirectToCart())
                ? $this->messageManager->addSuccessMessage($message)
                : $this->messageManager->addComplexSuccessMessage(
                'addCartSuccessMessage',
                [
                    'product_name' => $product->getName(),
                    'cart_url' => $this->getCartUrl(),
                ]
            );
        }
        return $this->goBack(null, $product);
    }

    /**
     * @param $e
     *
     * @return $this|Redirect
     */
    protected function addNotificationMessages($e)
    {
        if ($this->_checkoutSession->getUseNotice(true)) {
            $message = $this->escaper->escapeHtml($e->getMessage());
            $this->messageManager->addNoticeMessage($message);
        }
        if (!$this->_checkoutSession->getUseNotice(true)) {
            $messages = array_unique(explode("\n", $e->getMessage()));
            foreach ($messages as $message) {
                $this->messageManager->addErrorMessage(
                    $this->escaper->escapeHtml($message)
                );
            }
        }

        $url = $this->_checkoutSession->getRedirectUrl(true);
        $url = (!$url) ? $url = $this->_redirect->getRedirectUrl($this->getCartUrl()) : $url;

        return $this->goBack($url);
    }

    /**
     * @param $product
     *
     * @return $this|false|Redirect
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function statusRedirect($product)
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')
            );
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $is_enabled = $this->helper->isCatalogRestrictionEnable();
        if ($is_enabled && !$this->helper->isCatalogAvailable()) {
            $message = $this->helper->getCustomerRestrictionCartErrorMessage();
            $this->messageManager->addErrorMessage(__($message));
            return $this->goBack();
        }

        $quoteStatus = $this->cart->getQuote()->getStatus();
        if ($quoteStatus) {
            switch ($quoteStatus) {
                case \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_APPROVED:
                    $this->messageManager->addErrorMessage(__($this->helper->getApprovedCartMessage()));
                    return $this->goBack(null, $product);

                case \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_CLOSED:
                    $this->messageManager->addErrorMessage(__($this->helper->getClosedMessage()));
                    return $this->goBack(null, $product);

                case \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_CLONED:
                    $this->cart->getQuote()->reopen();
                    break;

                case \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_NEW:
                    $this->cart->getQuote()->open();
                    break;
            }
        }

        return false;
    }

    /**
     * Resolve response
     *
     * @param string $backUrl
     * @param Product $product
     * @return $this|Redirect
     */
    protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return parent::_goBack($backUrl);
        }

        $backUrlR = $productR = [];
        $backUrlR['backurl'] = !empty($backUrl) ? $backUrl : $this->getBackUrl();
        $productR['product'] = ($product && !$product->getIsSalable()) ? ['statusText' => __('Out of stock')] : '';

        $result = !empty($backUrlR['backurl']) ? $backUrlR : $productR;
        $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($result)
        );
    }

    /**
     * Returns cart url
     *
     * @return string
     */
    private function getCartUrl()
    {
        return $this->_url->getUrl('checkout/cart', ['_secure' => true]);
    }

    /**
     * Is redirect should be performed after the product was added to cart.
     *
     * @return bool
     */
    private function shouldRedirectToCart()
    {
        return $this->_scopeConfig->isSetFlag(
            'checkout/cart/redirect_to_cart',
            ScopeInterface::SCOPE_STORE
        );
    }

}
