<?php

namespace Formax\UploadGeneral\Ui\Component\Listing\Column\File;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use Formax\UploadGeneral\Model\FileFactory;

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
     * @var FileFactory
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
     * @param FileFactory $fileFactory
     * @param Escaper $escaper
     */
    public function __construct(FileFactory $fileFactory, Escaper $escaper)
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

        $this->options = $this->getAvailableFiles();

        return $this->options;
    }

    /**
     * Prepare Files
     *
     * @return array
     */
    private function getAvailableFiles()
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
