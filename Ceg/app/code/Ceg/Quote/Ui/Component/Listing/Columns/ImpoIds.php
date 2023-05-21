<?php
declare(strict_types=1);

namespace Ceg\Quote\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class ImpoIds extends Column
{
    /**
     * Column name
     */
    const NAME = 'column.impo_ids';

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        return $dataSource;
    }
}
