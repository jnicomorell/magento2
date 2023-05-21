<?php
declare(strict_types=1);

namespace Ceg\Theme\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;

class ThemeMvpConfig implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var WriterInterface
     */
    private $writerInterface;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var ThemeProviderInterface
     */
    private $themeInterface;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WriterInterface $writerInterface
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param ThemeProviderInterface $themeInterface
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $writerInterface,
        WebsiteRepositoryInterface $websiteRepository,
        ThemeProviderInterface $themeInterface
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->writerInterface = $writerInterface;
        $this->websiteRepository = $websiteRepository;
        $this->themeInterface = $themeInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $codes = ['base' => '', 'argentina' => '','brazil' => '','mexico' => ''];
        foreach (array_keys($codes) as $code) {
            $website = $this->websiteRepository->get($code);
            $codes[$code] = (int)$website->getId();
        }

        $websiteConfigs = $this->getWebsiteConfigs();

        foreach ($websiteConfigs as $code => $config) {
            $scope = 'default';
            $scopeId = 0;
            if (isset($codes[$code])) {
                $scope = 'websites';
                $scopeId = $codes[$code];
            }
            foreach ($config as $path => $value) {
                $this->setConfigData($path, $value, $scope, $scopeId);
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array
     */
    public function getWebsiteConfigs()
    {
        return [
            'default' => [
                'web/cookie/cookie_lifetime' => '86000',
                'admin/security/session_lifetime' => '7200',
                'web/default/front' => 'catalog/category/view/id/3',
                'design/footer/copyright' => 'Copyright © 2020 Comprandoengrupo.net. Todos los derechos reservados.',
                'catalog/layered_navigation/display_category' => '1',
                'catalog/navigation/max_depth' => '2',
                'checkout/cart_link/use_qty' => '0',
                'payment/checkmo/active' => '0',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

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
     * @param $scope
     * @param $scopeId
     */
    public function setConfigData($path, $value, $scope = 'default', $scopeId = 0)
    {
        $this->writerInterface->save($path, $value, $scope, $scopeId);
    }
}
