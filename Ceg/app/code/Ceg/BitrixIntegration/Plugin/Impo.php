<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Plugin;

use Ceg\BitrixIntegration\Helper\DataFactory as HelperFactory;
use Ceg\BitrixIntegration\Api\IntegrationRepositoryInterfaceFactory;
use Ceg\BitrixIntegration\Model\Integration\QueueElementFactory;
use Ceg\Impo\Model\ImpoRepository;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Ceg\Core\Logger\Logger as CegLogger;
use Exception;
use Ceg\Core\Helper\Datetime as CegDateTime;

class Impo
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
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @var CegLogger
     */
    protected $logger;

    /**
     * @var CegDateTime
     */
    protected $cegDateTime;

    /**
     * @param HelperFactory                         $helperFactory
     * @param IntegrationRepositoryInterfaceFactory $intRepoFactory
     * @param QueueElementFactory                    $elementFactory
     * @param TimezoneInterface                     $timezoneInterface
     * @param CegLogger                             $logger
     * @param CegDateTime                             $cegDateTime
     */
    public function __construct(
        HelperFactory $helperFactory,
        IntegrationRepositoryInterfaceFactory $intRepoFactory,
        QueueElementFactory $elementFactory,
        TimezoneInterface $timezoneInterface,
        CegLogger $logger,
        CegDateTime $cegDateTime
    ) {
        $this->helperFactory = $helperFactory;
        $this->intRepoFactory = $intRepoFactory;
        $this->elementFactory = $elementFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->logger = $logger;
        $this->cegDateTime = $cegDateTime;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ImpoRepository $subject
     * @param                                $result
     * @return mixed
     */
    public function afterOpen(
        ImpoRepository $subject,
        $result
    ) {
        $helper = $this->helperFactory->create();
        try {
            if ($helper->isActive()) {
                $element = $this->elementFactory->create();
                $integrationRepo = $this->intRepoFactory->create();
                $element->setModel($result);
                $element->setType(\Ceg\BitrixIntegration\Model\Queue::ELEMENT_TYPE_IMPO);
                $integrationRepo->sendData($element);
            }
        }catch (Exception $exception){
            $this->logger->info($exception->getMessage());
        }

        return $result;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ImpoRepository $subject
     * @param $result
     * @return mixed
     */
    public function afterSave(
        ImpoRepository $subject,
        $result
    ) {
        $helper = $this->helperFactory->create();
        if ($helper->isActive()) {
            if ($this->isSendAvailable($result)) {
                $element = $this->elementFactory->create();
                $integrationRepo = $this->intRepoFactory->create();

                $element->setModel($result);
                if (is_object($element)) {
                    $element->setType(\Ceg\BitrixIntegration\Model\Queue::ELEMENT_TYPE_IMPO);
                }
                $integrationRepo->sendData($element);
            }
        }
        return $result;
    }

    /**
     * @param $model
     *
     * @return bool
     */
    private function isSendAvailable($model)
    {
        // Validation to avoid sending data before the Impo opens
        $today = (int)strtotime($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
        $dateStartAt = $model->getStartAt();
        if(is_string($dateStartAt)) {
            $dateStartAt = $this->cegDateTime->convertStringToDatetime($dateStartAt);
        }
        $startAt = (int)$dateStartAt->format('U');
        return ($today >= $startAt);
    }
}
