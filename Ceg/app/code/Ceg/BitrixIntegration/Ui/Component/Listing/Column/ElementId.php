<?php
namespace Ceg\BitrixIntegration\Ui\Component\Listing\Column;

use Magento\Cron\Model\Schedule;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class ElementId extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @param UiComponentFactory $uiComponentFactory
     * @param ContextInterface   $context
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        UiComponentFactory $uiComponentFactory,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['element_id'] = $item['type'] .': '.  $item['element_id'];
            }
        }

        return $dataSource;
    }
}
