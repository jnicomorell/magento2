<?php

namespace Formax\Campaigns\Ui\Component\Listing\Column\Campaign;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use Formax\Campaigns\Model\CampaignFactory;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var CampaignFactory
     */
    protected $fileFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $currentOptions = [];

    /**
     * Constructor
     *
     * @param CampaignFactory $fileFactory
     * @param Escaper $escaper
     */
    public function __construct(CampaignFactory $fileFactory, Escaper $escaper)
    {
        $this->fileFactory = $fileFactory;
        $this->escaper = $escaper;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->options = $this->getAvailableCampaigns();

        return $this->options;
    }

    /**
     * Prepare Campaigns
     *
     * @return array
     */
    private function getAvailableCampaigns()
    {
        $collection = $this->fileFactory->create()->getCollection();
        $result = [];
        $result[] = ['value' => ' ', 'label' => 'Select...'];
        foreach ($collection as $file) {
            $result[] = ['value' => $file->getId(), 'label' => $this->escaper->escapeHtml($file->getTitle())];
        }
        return $result;
    }
}
