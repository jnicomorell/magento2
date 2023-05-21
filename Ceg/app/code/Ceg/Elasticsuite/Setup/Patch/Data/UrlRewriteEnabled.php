<?php
declare(strict_types=1);

namespace Ceg\Elasticsuite\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class UrlRewriteEnabled implements DataPatchInterface, PatchRevertableInterface
{
    const XML_PATH_ELASTICSUITE_ENABLE_URL_REWRITE = 'smile_elasticsuite_catalogsearch_settings/catalogsearch/category_filter_use_url_rewrites';

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
        $this->setConfigData(self::XML_PATH_ELASTICSUITE_ENABLE_URL_REWRITE, 1);
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->setConfigData(self::XML_PATH_ELASTICSUITE_ENABLE_URL_REWRITE, 0);
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
