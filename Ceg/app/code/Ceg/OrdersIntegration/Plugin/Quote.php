<?php
namespace Ceg\OrdersIntegration\Plugin;

use Ceg\OrdersIntegration\Helper\DataFactory as HelperFactory;
use Ceg\OrdersIntegration\Api\IntegrationRepositoryInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;

class Quote
{
    /**
     * @var HelperFactory
     */
    private $helperFactory;

    /**
     * @var CartRepositoryInterfaceFactory
     */
    private $cartRepoFactory;

    /**
     * @var IntegrationRepositoryInterfaceFactory
     */
    private $intRepoFactory;

    /**
     * @param HelperFactory $helperFactory
     * @param CartRepositoryInterfaceFactory $cartRepoFactory
     * @param IntegrationRepositoryInterfaceFactory $intRepoFactory
     */
    public function __construct(
        HelperFactory $helperFactory,
        CartRepositoryInterfaceFactory $cartRepoFactory,
        IntegrationRepositoryInterfaceFactory $intRepoFactory
    ) {
        $this->helperFactory = $helperFactory;
        $this->cartRepoFactory = $cartRepoFactory;
        $this->intRepoFactory = $intRepoFactory;
    }

    public function afterApprove(
        \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote $subject,
        $result
    ) {
        $helper = $this->helperFactory->create();
        if ($helper->isActive()) {
            $cartRepository = $this->cartRepoFactory->create();
            $quote = $cartRepository->get($subject->getId());
            $integrationRepo = $this->intRepoFactory->create();
            $integrationRepo->sendOrder($quote);
        }
        return $result;
    }
}
