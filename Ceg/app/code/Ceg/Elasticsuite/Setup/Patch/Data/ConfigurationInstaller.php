<?php
declare(strict_types=1);

namespace Ceg\Elasticsuite\Setup\Patch\Data;

use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Smile\ElasticsuiteCatalog\Model\Layer\Filter\Category;
use Smile\ElasticsuiteCore\Client\ClientConfiguration;
use Smile\ElasticsuiteCore\Helper\AbstractConfiguration;
use Smile\ElasticsuiteCore\Helper\IndexSettings;

class ConfigurationInstaller implements DataPatchInterface, PatchRevertableInterface
{
    const CONFIG_ENGINE_PATH = Custom::XML_PATH_CATALOG_SEARCH_ENGINE;
    const ES_BASE_CONFIG_PATH = AbstractConfiguration::BASE_CONFIG_XML_PREFIX;
    const ES_INDICES_SETTINGS_PATH = IndexSettings::INDICES_SETTINGS_CONFIG_XML_PREFIX;
    const ES_CLIENT_PATH = ClientConfiguration::ES_CLIENT_CONFIG_XML_PREFIX;
    const ES_USE_URL_REWRITE_PATH = Category::XML_CATEGORY_FILTER_USE_URL_REWRITE;
    const ES_INDICES_PATH = self::ES_BASE_CONFIG_PATH . '/' . self::ES_INDICES_SETTINGS_PATH;
    
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

        $this->writerInterface->save(self::CONFIG_ENGINE_PATH, 'elasticsuite');
        $this->writerInterface->save(self::ES_CLIENT_PATH . '/servers', 'elasticsearch:9200');
        $this->writerInterface->save(self::ES_CLIENT_PATH . '/enable_https_mode', 0);
        $this->writerInterface->save(self::ES_CLIENT_PATH . '/http_auth_user', '');
        $this->writerInterface->save(self::ES_CLIENT_PATH . '/http_auth_pwd', '');
        $this->writerInterface->save(self::ES_CLIENT_PATH . '/enable_http_auth', 0);
        $this->writerInterface->save(self::ES_INDICES_PATH . '/alias', 'ceg-store');
        $this->writerInterface->save(self::ES_USE_URL_REWRITE_PATH, 0);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->writerInterface->save(self::CONFIG_ENGINE_PATH, 'elasticsearch7');
        $this->writerInterface->delete(self::ES_CLIENT_PATH . '/servers');
        $this->writerInterface->delete(self::ES_CLIENT_PATH . '/enable_https_mode');
        $this->writerInterface->delete(self::ES_CLIENT_PATH . '/http_auth_user');
        $this->writerInterface->delete(self::ES_CLIENT_PATH . '/http_auth_pwd');
        $this->writerInterface->delete(self::ES_CLIENT_PATH . '/enable_http_auth');
        $this->writerInterface->delete(self::ES_INDICES_PATH . '/alias');
        $this->writerInterface->delete(self::ES_USE_URL_REWRITE_PATH);

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
}
