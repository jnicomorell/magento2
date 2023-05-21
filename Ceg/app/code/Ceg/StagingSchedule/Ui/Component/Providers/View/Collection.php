<?php
declare(strict_types=1);

namespace Ceg\StagingSchedule\Ui\Component\Providers\View;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    /**
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('entity_id', 'main_table.staging_id');
        parent::_initSelect();
    }
}
