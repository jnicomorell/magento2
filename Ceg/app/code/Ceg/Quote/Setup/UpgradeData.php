<?php
namespace Ceg\Quote\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
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
            $this->apply101($setup);
            $setup->endSetup();
        }
    }

    public function apply101($setup)
    {
        $bind = ['is_active' => 0];
        $where = ['status = ?' => \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_CLOSED];
        $setup->getConnection()->update($setup->getTable('quote'), $bind, $where);
    }
}
