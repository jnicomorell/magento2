<?php
declare(strict_types=1);

namespace Ceg\Impo\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Model\Cart;

class Data extends \Magento\Framework\App\Action\Action
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
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $quote = $this->cart->getQuote();
        $data = [
            'quote' => [
                'id' => $quote->getId(),
                'status' => $quote->getStatus(),
                'need_to_approve' => $quote->needToApprove(),
                'order_id' => $quote->getParentOrderId(),
            ]
        ];

        $resultJson->setData($data);
        return $resultJson;
    }
}
