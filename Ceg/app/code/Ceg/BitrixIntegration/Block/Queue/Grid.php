<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Block\Queue;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Ceg\BitrixIntegration\Model\QueueRepositoryFactory;

class Grid extends Template
{
    /**
     * @var QueueRepositoryFactory
     */
    protected $queueRepoFactory;

    /**
     * @param Context                $context
     * @param QueueRepositoryFactory $queueRepoFactory
     * @param array                  $data
     */
    public function __construct(
        Context $context,
        QueueRepositoryFactory $queueRepoFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->queueRepoFactory = $queueRepoFactory;
    }

    public function getQueues()
    {
        $queueRepository = $this->queueRepoFactory->create();
        return $queueRepository->getAllQueues();
    }

    public function getConfgLink()
    {
        return $this->_urlBuilder->getUrl(
            'adminhtml/system_config/edit',
            ['section' => 'ceg_bitrixintegration']
        );
    }

    public function isWarningStatus($status)
    {
        if ($status == \Ceg\BitrixIntegration\Api\Data\QueueInterface::STATUS_PENDING) {
            return true;
        }
        return false;
    }
}
