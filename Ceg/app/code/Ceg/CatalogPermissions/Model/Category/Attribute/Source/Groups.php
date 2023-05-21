<?php
declare(strict_types=1);

namespace Ceg\CatalogPermissions\Model\Category\Attribute\Source;

use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Groups extends AbstractSource
{
    public $options;

    /**
     * @var Collection
     */
    private $groups;

    /**
     * Groups constructor.
     * @param Collection $groups
     */
    public function __construct(
        Collection $groups
    ) {
        $this->groups = $groups;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->options === null) {
            $groups = $this->groups->toOptionArray();
            $this->options = $groups;
        }
        return $this->options;
    }
}
