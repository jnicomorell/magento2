<?php
namespace Ceg\Checkout\Setup;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

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
        if ($context->getVersion() && version_compare($context->getVersion(), '0.1.1') < 0) {
            $setup->startSetup();

            $path = \Ceg\CatalogPermissions\Helper\Data::XML_CONFIG_CHECKOUT_REOPENED_CART_MSG;
            $value = 'Su carrito ha sido reabierto. Si ha realizado modificaciones, deberá volver a tramitar el pedido y aceptar los términos y condiciones.';//phpcs:ignore
            $this->writerInterface->save($path, $value);

            $setup->endSetup();
        }
    }
}
