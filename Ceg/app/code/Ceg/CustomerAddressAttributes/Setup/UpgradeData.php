<?php
namespace Ceg\CustomerAddressAttributes\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var WriterInterface
     */
    private $writerInterface;

    /**
     * @param WriterInterface $writerInterface
     */
    public function __construct(
        WriterInterface $writerInterface
    ) {
        $this->writerInterface = $writerInterface;
    }

    /**
     * Method upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.1') < 0) {
            $setup->startSetup();
            $this->writerInterface->save('customer/address/street_lines', 1);
            $setup->endSetup();
        }
    }
}
