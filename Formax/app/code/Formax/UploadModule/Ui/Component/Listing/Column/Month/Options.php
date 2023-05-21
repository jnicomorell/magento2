<?php

namespace Formax\UploadModule\Ui\Component\Listing\Column\Month;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;

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
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper)
    {
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
        $result = [];
        $result[] = ['value' => ' ', 'label' => 'Select...'];

        $result[] = ['value' => '1', 'label' => 'Enero'];
        $result[] = ['value' => '2', 'label' => 'Febrero'];
        $result[] = ['value' => '3', 'label' => 'Marzo'];
        $result[] = ['value' => '4', 'label' => 'Abril'];
        $result[] = ['value' => '5', 'label' => 'Mayo'];
        $result[] = ['value' => '6', 'label' => 'Junio'];
        $result[] = ['value' => '7', 'label' => 'Julio'];
        $result[] = ['value' => '8', 'label' => 'Agosto'];
        $result[] = ['value' => '9', 'label' => 'Septiembre'];
        $result[] = ['value' => '10', 'label' => 'Octubre'];
        $result[] = ['value' => '11', 'label' => 'Noviembre'];
        $result[] = ['value' => '12', 'label' => 'Diciembre'];
        return $result;
    }
}
