<?php
declare(strict_types=1);

namespace Ceg\Setup\Setup\Patch\Data;

/**
 * Class InstallData
 * @package Ceg\Setup\Setup\Patch\Data
 */
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;

class WebsiteStoresInstaller implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var Group
     */
    private $groupResourceModel;

    /**
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * @var Store
     */
    private $storeResourceModel;

    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var Website
     */
    private $websiteResourceModel;

    /**
     * InstallData constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Group $groupResourceModel
     * @param GroupFactory $groupFactory
     * @param ManagerInterface $eventManager
     * @param Store $storeResourceModel
     * @param StoreFactory $storeFactory
     * @param Website $websiteResourceModel
     * @param WebsiteFactory $websiteFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Group $groupResourceModel,
        GroupFactory $groupFactory,
        ManagerInterface $eventManager,
        Store $storeResourceModel,
        StoreFactory $storeFactory,
        Website $websiteResourceModel,
        WebsiteFactory $websiteFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eventManager = $eventManager;
        $this->groupFactory = $groupFactory;
        $this->groupResourceModel = $groupResourceModel;
        $this->storeFactory = $storeFactory;
        $this->storeResourceModel = $storeResourceModel;
        $this->websiteFactory = $websiteFactory;
        $this->websiteResourceModel = $websiteResourceModel;
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

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $defaultAttributes = [
            [
                'website_code' => 'argentina',
                'website_name' => 'Argentina Website',
                'group_name' => 'Argentina Store',
                'store_code' => 'ar_es',
                'store_name' => 'Argentina Store View',
                'root_category_id' => '2',
                'is_active' => '1',
            ],
            [
                'website_code' => 'brazil',
                'website_name' => 'Brazil Website',
                'group_name' => 'Brazil Store',
                'store_code' => 'br_pt',
                'store_name' => 'Brazil Store View',
                'root_category_id' => '2',
                'is_active' => '1',
            ],
            [
                'website_code' => 'mexico',
                'website_name' => 'Mexico Website',
                'group_name' => 'Mexico Store',
                'store_code' => 'mx_es',
                'store_name' => 'Mexico Store View',
                'root_category_id' => '2',
                'is_active' => '1',
            ]
        ];

        foreach ($defaultAttributes as $attribute) {
            /** @var  \Magento\Store\Model\Store $store */
            $store = $this->storeFactory->create();
            $store->load($attribute['store_code']);

            if (!$store->getId()) {
                /** @var \Magento\Store\Model\Website $website */
                $website = $this->websiteFactory->create();
                $website->load($attribute['website_code']);
                $website = $this->setWebID($website, $attribute);

                /** @var \Magento\Store\Model\Group $group */
                $group = $this->groupFactory->create();
                $group->setWebsiteId($website->getWebsiteId());
                $group->setName($attribute['group_name']);
                $group->setCode($attribute['store_code']);
                $group->setRootCategoryId($attribute['root_category_id']);
                $this->groupResourceModel->save($group);

                $group = $this->groupFactory->create();
                $group->load($attribute['group_name'], 'name');
                $store->setCode($attribute['store_code']);
                $store->setName($attribute['store_name']);
                $store->setWebsite($website);
                $store->setGroupId($group->getId());
                $store->setData('is_active', $attribute['is_active']);
                $this->storeResourceModel->save($store);
                $this->storeFactory->create();
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @param \Magento\Store\Model\Website $website
     * @param array $attribute
     * @return \Magento\Store\Model\Website
     * @throws AlreadyExistsException
     */
    public function setWebID(\Magento\Store\Model\Website $website, array $attribute)
    {
        if (!$website->getId()) {
            $website->setCode($attribute['website_code']);
            $website->setName($attribute['website_name']);
            $this->websiteResourceModel->save($website);
        }

        return $website;
    }
}
