<?php
declare(strict_types=1);

namespace Ceg\Setup\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class LoginAsCustomerConfigurationsInstaller implements DataPatchInterface, PatchRevertableInterface
{
    const XML_PATH_SHOPPING_ASSISTANCE_CHBOX_TITLE = 'login_as_customer/general/shopping_assistance_checkbox_title';
    const XML_PATH_SHOPPING_ASSISTANCE_CHBOX_TOOLTIP = 'login_as_customer/general/shopping_assistance_checkbox_tooltip';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var WriterInterface
     */
    private $writerInterface;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WriterInterface $writerInterface
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $writerInterface
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->writerInterface = $writerInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->setConfigData(
            self::XML_PATH_SHOPPING_ASSISTANCE_CHBOX_TITLE,
            "Permitir asistencia de compra remota"
        );

        $this->setConfigData(
            self::XML_PATH_SHOPPING_ASSISTANCE_CHBOX_TOOLTIP,
            "Esto permite a un asesor asistirle en la compra o modificar datos de su cuenta."
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->setConfigData(
            self::XML_PATH_SHOPPING_ASSISTANCE_CHBOX_TITLE,
            "Allow remote shopping assistance"
        );

        $this->setConfigData(
            self::XML_PATH_SHOPPING_ASSISTANCE_CHBOX_TOOLTIP,
            'This allows merchants to "see what you see"'
            .' and take actions on your behalf in order to provide better assistance.'
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies() :array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases() :array
    {
        return [];
    }

    /**
     * @param $path
     * @param $value
     */
    public function setConfigData($path, $value)
    {
        $this->writerInterface->save($path, $value);
    }
}
