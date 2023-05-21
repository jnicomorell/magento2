<?php
declare(strict_types=1);

namespace Ceg\Checkout\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Model\Cart;
use Ceg\Quote\Rewrite\Magento\Quote\Model\Quote;

class Delete extends \Magento\Framework\App\Action\Action
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
        $res = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            try {
                // We should set Totals to be recollected once more because of Cart model as usually is loading
                // before action executing and in case when triggerRecollect setted as true recollecting will
                // executed and the flag will be true already.
                if ($this->cart->getQuote()->getStatus() == Quote::STATUS_CLONED) {
                    $this->cart->getQuote()->setStatus(Quote::STATUS_REOPEN);
                }
                if ($this->cart->getQuote()->cancelParentQuote($id) == false) {
                    $this->cart->removeItem($id);
                }
                $this->cart->getQuote()->setTotalsCollectedFlag(false);
                $this->cart->save();
                $res->setData(["success" => true]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('We can\'t remove the item.'));
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);

            }
        }
        return $res;
    }
}
