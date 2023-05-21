<?php
namespace Ceg\OrdersIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ceg\OrdersIntegration\Model\IntegrationRepository;
use Ceg\Core\Logger\Logger as CegLogger;
use Exception;

class AfterCancelParentQuote implements ObserverInterface
{

    /*
     * @var Ceg\OrdersIntegration\Model\IntegrationRepository
     */
    private $integrationRepo;

    /**
     * @var CegLogger
     */
    private $cegLogger;

    /**
     * @param IntegrationRepository $integrationRepo
     * @param CegLogger             $cegLogger
     */
    public function __construct(
        IntegrationRepository $integrationRepo,
        CegLogger $cegLogger
    ) {
        $this->integrationRepo = $integrationRepo;
        $this->cegLogger = $cegLogger;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        try {
            $quote = $observer->getData('quote');
            $this->integrationRepo->send($quote, IntegrationRepository::ACTION_DELETE);
        } catch (Exception $exception) {
            $this->cegLogger->info($exception->getMessage());
        }

        return $this;
    }
}
