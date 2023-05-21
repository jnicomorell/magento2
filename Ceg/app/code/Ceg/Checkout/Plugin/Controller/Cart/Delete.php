<?php

namespace Ceg\Checkout\Plugin\Controller\Cart;

use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;

class Delete
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var string
     */
    protected $_productName;

    /**
     * @param Context $context
     * @param Cart $cart
     */
    public function __construct(
        Context $context,
        Cart $cart
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->cart = $cart;
    }

    public function beforeExecute(
        \Magento\Checkout\Controller\Cart\Delete $subject
    ) {
        $id = (int)$subject->getRequest()->getParam('id');
        $product = $this->cart->getQuote()->getItemById($id);
        $this->_productName = $product->getName();
        return [];
    }

    public function afterExecute(
        \Magento\Checkout\Controller\Cart\Delete $subject,
        $result
    ) {
        if (!is_a(
            $this->messageManager->getMessages()->getLastAddedMessage(),
            \Magento\Framework\Message\Error::class,
            true
        )
        ) {
            $this->messageManager->addSuccessMessage(
                __('You deleted %1 from your shopping cart.', $this->_productName)
            );
        }
        return $result;
    }
}
