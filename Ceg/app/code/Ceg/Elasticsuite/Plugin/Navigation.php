<?php
declare(strict_types=1);

namespace Ceg\Elasticsuite\Plugin;

class Navigation
{
    public function afterGetDisplayedFilters(
        \Smile\ElasticsuiteCatalog\Block\Navigation $subject,
        $result
    ) {
        return array_values($subject->getFilters());
    }
}
