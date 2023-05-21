<?php
declare(strict_types=1);

namespace Ceg\StagingSchedule\Model;

use Ceg\StagingSchedule\Api\Data\StagingScheduleInterface;
use Ceg\StagingSchedule\Api\Data\StagingScheduleInterfaceFactory;
use Ceg\StagingSchedule\Model\ResourceModel\StagingSchedule\Collection;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class StagingSchedule extends AbstractModel
{
    protected $_eventPrefix = 'staging_schedule';
    protected $scheduleFactory;
    protected $dataObjectHelper;

    /**
     * @param Context                         $context
     * @param Registry                        $registry
     * @param StagingScheduleInterfaceFactory $scheduleFactory
     * @param DataObjectHelper                $dataObjectHelper
     * @param ResourceModel\StagingSchedule   $resource
     * @param Collection                      $resourceCollection
     * @param array                           $data
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StagingScheduleInterfaceFactory $scheduleFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceModel\StagingSchedule $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve staging_schedule model with staging_schedule data
     * @return StagingScheduleInterface
     */
    public function getDataModel()
    {
        $scheduleData = $this->getData();

        $schedule = $this->scheduleFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $schedule,
            $scheduleData,
            StagingScheduleInterface::class
        );

        return $schedule;
    }
}
