<?php
namespace Ceg\BitrixIntegration\Observer;

use Magento\Framework\Event\Observer;
use Ceg\BitrixIntegration\Helper\DataFactory as HelperFactory;
use Ceg\BitrixIntegration\Api\IntegrationRepositoryInterfaceFactory;
use Ceg\BitrixIntegration\Model\Integration\QueueElementFactory;

class SaveOrderInQueue implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var HelperFactory
     */
    private $helperFactory;

    /**
     * @var IntegrationRepositoryInterfaceFactory
     */
    private $intRepoFactory;

    /**
     * @var QueueElementFactory
     */
    private $elementFactory;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @param HelperFactory $helperFactory
     * @param IntegrationRepositoryInterfaceFactory $intRepoFactory
     * @param QueueElementFactory $elementFactory
     */
    public function __construct(
        HelperFactory $helperFactory,
        IntegrationRepositoryInterfaceFactory $intRepoFactory,
        QueueElementFactory $elementFactory
    ) {
        $this->helperFactory = $helperFactory;
        $this->intRepoFactory = $intRepoFactory;
        $this->elementFactory = $elementFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $helper = $this->helperFactory->create();
        if ($helper->isActive()) {
            $element = $this->elementFactory->create();
            $integrationRepo = $this->intRepoFactory->create();

            $element->setModel($quote);
            if (is_object($element)) {
                $element->setType(\Ceg\BitrixIntegration\Model\Queue::ELEMENT_TYPE_ORDER);
            }
            $integrationRepo->sendData($element);
        }
    }
}
