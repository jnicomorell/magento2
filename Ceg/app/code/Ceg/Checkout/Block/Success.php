<?php
declare(strict_types=1);

namespace Ceg\Checkout\Block;

use Ceg\Checkout\Helper\Config;
use Ceg\Quote\Rewrite\Magento\Quote\Model\Quote;
use Magento\Checkout\Model\Session as MagentoSession;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Order\Totals;
use Magento\Sales\Model\OrderFactory;
use Magento\Checkout\Model\Cart;

class Success extends Totals
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var MagentoSession
     */
    protected $checkoutSession;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var Config|null
     */
    private $configHelper;

    /**
     * Success constructor.
     * @param Cart $cart
     * @param MagentoSession $checkoutSession
     * @param Session $customerSession
     * @param OrderFactory $orderFactory
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     * @param Config|null $configHelper
     */
    public function __construct(
        Cart $cart,
        MagentoSession $checkoutSession,
        Session $customerSession,
        OrderFactory $orderFactory,
        Context $context,
        Registry $registry,
        array $data = [],
        Config $configHelper = null
    ) {
        parent::__construct($context, $registry, $data);
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->registry = $registry;
        $this->configHelper = $configHelper ?? ObjectManager::getInstance()->get(Config::class);
    }

    /**
     * @return mixed
     */
    public function getQuoteIncrementId()
    {
        return $this->checkoutSession->getLastReservedId();
    }

    /**
     * @return string
     */
    public function getSuccessMessage(): string
    {
        return $this->configHelper->getSuccessMessage();
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getContinueUrl()
    {
        return $this->_urlBuilder->getUrl('impo/view');
    }

    /**
     * @return \Magento\Sales\Model\Order|null
     */
    public function getOrder()
    {
        return  $this->_order = $this->_orderFactory->create()->loadByIncrementId(
            $this->checkoutSession->getLastRealOrderId()
        );
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customerSession->getCustomer()->getId();
    }

    /**
     * @return bool
     */
    public function isOrderUpdate()
    {
        $quote = $this->checkoutSession->getQuote();
        return !$quote->isParentFirstOrder();
    }

    /**
     * Retrieve current order model instance
     *
     * @return Quote;
     */
    public function getQuote()
    {
        return $this->cart->getQuote();
    }

}
