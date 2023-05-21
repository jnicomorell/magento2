<?php
declare(strict_types=1);

namespace Ceg\Pages\Setup\Patch\Data;

use Magento\Cms\Model\PageFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CMSTermsAndConditionsInstaller implements DataPatchInterface
{
    /**
     * @var PageFactory
     */
    private $pageFactory;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * CMSTermsAndConditionsInstaller constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param PageFactory $pageFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        PageFactory $pageFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->pageFactory = $pageFactory;
    }

    /**
     * @return CMSTermsAndConditionsInstaller|void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $page = $this->pageFactory->create();
        $page->setTitle('Términos y Condiciones')
            ->setIdentifier('terminos-y-condiciones')
            ->setIsActive(true)
            ->setPageLayout('1column')
            ->setStores([0])
            ->save();

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}
