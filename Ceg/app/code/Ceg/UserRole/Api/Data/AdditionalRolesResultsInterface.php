<?php
declare(strict_types=1);

namespace Ceg\UserRole\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface AdditionalRolesResultsInterface extends SearchResultsInterface
{

    /**
     * @return \Ceg\UserRole\Api\Data\AdditionalRolesInterface[]
     */
    public function getItems();

    /**
     * @param array $items
     * @return AdditionalRolesResultsInterface
     */
    public function setItems(array $items);
}
