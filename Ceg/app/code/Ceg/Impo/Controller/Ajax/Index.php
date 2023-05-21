<?php
declare(strict_types=1);

namespace Ceg\Impo\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Model\Cart;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @param Context $context
     * @param Cart $cart
     */
    public function __construct(
        Context $context,
        Cart $cart
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->cart = $cart;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $wholeData = $this->context->getRequest()->getParams();
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData(["quoteid" => $this->getQuoteId($wholeData['productid']), "success" => true]);
        return $resultJson;
    }

    /**
     * Method getQuoteId
     *
     * @param int
     * @return int
     */
    public function getQuoteId($productId)
    {
        $items = $this->cart->getQuote()->getAllItems();
        foreach ($items as $cartItem) {
            if ($cartItem->getProduct()->getId() == $productId) {
                return $cartItem->getId();
            }
        }
        return 0;
    }
}
