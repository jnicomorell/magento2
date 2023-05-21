<?php
declare(strict_types=1);

namespace Ceg\Impo\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Ceg\Impo\Model\ResourceModel\Impo\CollectionFactory as ImpoCollectionFactory;

class UpdateImpoStatus implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var ImpoCollectionFactory
     */
    protected $impoCollection;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ImpoCollectionFactory    $collectionFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ImpoCollectionFactory $collectionFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->impoCollection = $collectionFactory;
    }

    /**
     * @return UpdateImpoStatus|void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        try {
            // Set past impo as closed
            $impoCollection = $this->impoCollection->create()->getItems();
            foreach ($impoCollection as $impo) {
                if ($impo->getFinishAt() < date("Y/m/d")) {
                    $impo->setData('status', \Ceg\Impo\Ui\Component\Providers\Status::STATUS_CLOSED);
                }
                $impo->save();
            }

        } catch (\Exception $e) {
            // @codingStandardsIgnoreStart
            echo $e->getMessage();
            // @codingStandardsIgnoreEnd
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array
     */
    public static function getDependencies():array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases():array
    {
        return [];
    }
}
