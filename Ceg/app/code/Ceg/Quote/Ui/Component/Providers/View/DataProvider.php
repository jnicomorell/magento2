<?php
declare(strict_types=1);

namespace Ceg\Quote\Ui\Component\Providers\View;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as MagentoDataProvider;

class DataProvider extends MagentoDataProvider
{
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->searchResultToOutput($this->getSearchResult());
    }
}
