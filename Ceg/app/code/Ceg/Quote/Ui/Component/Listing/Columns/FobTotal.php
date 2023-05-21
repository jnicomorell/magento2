<?php
declare(strict_types=1);

namespace Ceg\Quote\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class FobTotal extends Column
{
    /**
     * Column name
     */
    const NAME = 'column.fob_total';

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $item[$fieldName] = $item[$fieldName];
                }
            }
        }
        return $dataSource;
    }
}
