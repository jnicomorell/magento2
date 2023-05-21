<?php
declare(strict_types=1);

namespace Ceg\Theme\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;

class ThemeBaseInstaller implements DataPatchInterface, PatchRevertableInterface
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
        $themeIds = $this->getThemeIds();

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
            if (isset($themeIds[$code])) {
                $path = 'design/theme/theme_id';
                $value = $themeIds[$code];
                $this->setConfigData($path, $value, $scope, $scopeId);
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array
     */
    public function getThemeIds()
    {
        $themes = [
            'frontend/Ceg/base' => 'default',
            'frontend/Ceg/mex' => 'mexico',
            'frontend/Ceg/brz' => 'brazil',
            'frontend/Ceg/arg' => 'argentina',
        ];
        foreach ($themes as $theme => $website) {
            $themes[$website] = (int)$this->themeInterface->getThemeByFullPath($theme)->getId();
        }
        return $themes;
    }

    /**
     * @return array
     */
    public function getWebsiteConfigs()
    {
        return [
            'default' => [
                'general/store_information/name' => 'Comprandoengrupo.net',
                'web/unsecure/base_url' => 'https://local.sales.com/',
                'web/unsecure/base_link_url' => 'https://local.sales.com/',
                'web/secure/base_url' => 'https://local.sales.com/',
                'web/secure/base_link_url' => 'https://local.sales.com/',
                'web/cookie/cookie_lifetime' => '86000',
                'catalog/review/active' => '0',
                'catalog/price/scope' => '0',
                'catalog/layered_navigation/display_product_count' => '0',
                'cataloginventory/options/can_subtract' => '0',
                'cataloginventory/item_options/manage_stock' => '0',
                'cataloginventory/item_options/max_sale_qty' => '100000',
                'sales/reorder/allow' => '0',
                'sales/instant_purchase/active' => '0',
                'checkout/options/guest_checkout' => '0',
                'checkout/options/enable_agreements' => '1',
                'checkout/sidebar/display' => '0',
                'checkout/cart/enable_clear_shopping_cart' => '1',
                'carriers/freeshipping/active' => '1',
                'carriers/freeshipping/title' => 'Shipping costs (will be calculated after delivery)',
                'admin/security/session_lifetime' => '7200',
                'admin/security/password_is_forced' => '0',
                'dev/debug/template_hints_storefront' => '0',
                'dev/debug/template_hints_admin' => '0',
                'dev/debug/template_hints_blocks' => '0',
            ],

            'base' => [
                'general/country/default' => 'AR',
                'general/locale/timezone' => 'America/Argentina/Buenos_Aires',
                'general/locale/code' => 'es_AR',
                'general/locale/weight_unit' => 'kgs',
                'general/store_information/name' => 'Comprandoengrupo.net',
                'general/store_information/country_id' => 'AR',
                'web/unsecure/base_url' => 'https://local.sales.com/',
                'web/unsecure/base_link_url' => 'https://local.sales.com/',
                'web/secure/base_url' => 'https://local.sales.com/',
                'web/secure/base_link_url' => 'https://local.sales.com/',
            ],

            'mexico' => [
                'general/country/default' => 'MX',
                'general/locale/timezone' => 'America/Chihuahua',
                'general/locale/code' => 'es_MX',
                'general/locale/weight_unit' => 'kgs',
                'general/store_information/name' => 'Comprandoengrupo.net - Mexico',
                'general/store_information/country_id' => 'MX',
                'web/unsecure/base_url' => 'https://local.sales.mx/',
                'web/unsecure/base_link_url' => 'https://local.sales.mx/',
                'web/secure/base_url' => 'https://local.sales.mx/',
                'web/secure/base_link_url' => 'https://local.sales.mx/',
            ],

            'brazil' => [
                'general/country/default' => 'BR',
                'general/locale/timezone' => 'America/Sao_Paulo',
                'general/locale/code' => 'pt_BR',
                'general/locale/weight_unit' => 'kgs',
                'general/store_information/name' => 'Comprandoengrupo.net - Brazil',
                'general/store_information/country_id' => 'BR',
                'web/unsecure/base_url' => 'https://local.sales.br/',
                'web/unsecure/base_link_url' => 'https://local.sales.br/',
                'web/secure/base_url' => 'https://local.sales.br/',
                'web/secure/base_link_url' => 'https://local.sales.br/',
            ],

            'argentina' => [
                'general/country/default' => 'AR',
                'general/locale/timezone' => 'America/Argentina/Buenos_Aires',
                'general/locale/code' => 'es_AR',
                'general/locale/weight_unit' => 'kgs',
                'general/store_information/name' => 'Comprandoengrupo.net - Argentina',
                'general/store_information/country_id' => 'AR',
                'web/unsecure/base_url' => 'https://local.sales.ar/',
                'web/unsecure/base_link_url' => 'https://local.sales.ar/',
                'web/secure/base_url' => 'https://local.sales.ar/',
                'web/secure/base_link_url' => 'https://local.sales.ar/',
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
