<?php
declare(strict_types=1);

namespace Ceg\ImportExport\Block\Adminhtml;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ImportButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Import'),
            'class' => 'save primary',
            'sort_order' => 10
        ];
    }
}
