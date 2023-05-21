<?php
namespace Ceg\Impo\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Product\Action;
use Magento\Store\Model\StoreManagerInterface;


class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Action
     */
    private $productAction;

    /**
     * @var Collection
     */
    private $productCollection;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var WriterInterface
     */
    private $writerInterface;

    /**
     * @var Json
     */
    private $json;

    /**
     * Method __construct
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param WriterInterface $writerInterface
     * @param Json $json
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        WriterInterface $writerInterface,
        Json $json,
        Collection $productCollection,
        Action $productAction,
        StoreManagerInterface $storeManager
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->writerInterface = $writerInterface;
        $this->json = $json;
        $this->productCollection = $productCollection;
        $this->productAction = $productAction;
        $this->storeManager = $storeManager;

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
        $setup->startSetup();
        $versions = ['1.0.2','1.1.4','1.1.5','1.1.6','1.1.7','1.1.8','1.1.9','1.2.0','1.2.1','1.2.2','1.2.3','1.2.4','1.2.5','1.2.6'];
        if ($context->getVersion()) {
            foreach ($versions as $version) {
                if (version_compare($context->getVersion(), $version) < 0) {
                    $callfunc = str_replace(".", "", $version);
                    $callfunc = 'apply'.$callfunc;
                    $this->$callfunc($setup);
                }
            }
        }
        $setup->endSetup();
    }

    public function apply102($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'model',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Model',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple',
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'brand',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Brand',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple',
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'sellby',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 210,
                'label' => 'Sell by',
                'input' => 'select',
                'class' => '',
                'source' => \Ceg\Impo\Model\Source\Sellby::class,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '1',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'apply_to' => ''
            ]
        );
    }

    public function apply114($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'qtyinbox',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 220,
                'label' => 'Quantity in box',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple',
            ]
        );
    }

    public function apply115($setup)
    {
        $this->writerInterface->delete('impo/general/impo_date');
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->removeAttribute(Product::ENTITY, 'impo_active');
    }

    public function apply116($setup)
    {
        $connection = $setup->getConnection();

        $cegImpoTable = $setup->getTable('ceg_impo_entity');
        $quoteTable = $setup->getTable('quote');
        $quoteItemTable = $setup->getTable('quote_item');
        $orderTable = $setup->getTable('sales_order');
        $orderItemTable = $setup->getTable('sales_order_item');

        $query = $connection->select()
            ->from(
                ['item_table' => $quoteItemTable],
                ['quote_id', 'item_id', 'impo_id']
            )
            ->joinInner(
                ['ceg_impo' => $cegImpoTable],
                'item_table.impo_id = ceg_impo.ceg_id',
                ['ceg_impo.entity_id AS impo_entity_id']
            )
            ->order('quote_id');

        $qImpoIds = [];
        $currentParentId = '';
        foreach ($connection->fetchAll($query) as $row) {
            if ($currentParentId != $row['quote_id']) {
                if ($currentParentId !== '') {
                    // If Header Id change, then update last Header with all Impo Ids
                    $qImpoIdsJson = $this->json->serialize($qImpoIds);
                    $bind = ['impo_ids' => $qImpoIdsJson];
                    $quoteWhere = ['entity_id = ?' => $currentParentId];
                    $orderWhere = ['quote_id = ?' => $currentParentId];
                    $connection->update($quoteTable, $bind, $quoteWhere);
                    $connection->update($orderTable, $bind, $orderWhere);
                }
                // Reset to collect data for next header
                $currentParentId = $row['quote_id'];
                $qImpoIds = [];
            }

            // Collect Impo Ids from Items
            if (!in_array($row['impo_entity_id'], $qImpoIds)) {
                array_push($qImpoIds, $row['impo_entity_id']);
            }

            // Update Item
            $bind = ['impo_id' => $row['impo_entity_id']];
            $quoteWhere = ['item_id = ?' => $row['item_id']];
            $orderWhere = ['quote_item_id = ?' => $row['item_id']];
            $connection->update($quoteItemTable, $bind, $quoteWhere);
            $connection->update($orderItemTable, $bind, $orderWhere);
        }
    }

    public function apply117($setup)
    {
        $connection = $setup->getConnection();
        $cegImpoTable = $setup->getTable('ceg_impo_entity');
        $quoteTable = $setup->getTable('quote');
        $quoteItemTable = $setup->getTable('quote_item');
        $orderTable = $setup->getTable('sales_order');
        $orderItemTable = $setup->getTable('sales_order_item');

        $query = $connection->select()
            ->from(
                ['item_table' => $quoteItemTable],
                ['quote_id', 'item_id', 'impo_id']
            )
            ->joinInner(
                ['ceg_impo' => $cegImpoTable],
                'item_table.created_at between ceg_impo.start_at and ceg_impo.finish_at',
                ['ceg_impo.entity_id AS impo_entity_id']
            )
            ->where("item_table.impo_id = ''")
            ->order('quote_id');

        $quotesData = [];
        foreach ($connection->fetchAll($query) as $row) {
            $bind = ['impo_id' => $row['impo_entity_id']];
            $quoteWhere = ['item_id = ?' => $row['item_id']];
            $orderWhere = ['quote_item_id = ?' => $row['item_id']];
            $connection->update($quoteItemTable, $bind, $quoteWhere);
            $connection->update($orderItemTable, $bind, $orderWhere);

            if (empty($quotesData[$row['quote_id']])) {
                $quotesData[$row['quote_id']] = [];
            }
            array_push($quotesData[$row['quote_id']], $row['impo_entity_id']);
        }

        foreach ($quotesData as $quoteId => $impoIds) {
            $impoIdsJson = $this->json->serialize(array_unique($impoIds));
            $bind = ['impo_ids' => $impoIdsJson];
            $quoteWhere = ['entity_id = ?' => $quoteId];
            $orderWhere = ['quote_id = ?' => $quoteId];
            $connection->update($quoteTable, $bind, $quoteWhere);
            $connection->update($orderTable, $bind, $orderWhere);
        }
    }

    public function apply118($setup)
    {
        $connection = $setup->getConnection();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $attributeCode = 'qtyinbox';
        $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
        $attributeId = $eavSetup->getAttributeId($entityTypeId, $attributeCode);

        $productTable = $setup->getTable('catalog_product_entity');
        $textTable = $setup->getTable('catalog_product_entity_text');
        $intTable = $setup->getTable('catalog_product_entity_int');

        $valuesToRestore = [];
        $query = $connection->select()
            ->from(
                ['products' => $productTable],
                ['entity_id']
            )
            ->joinLeft(
                ['textAtt' => $textTable],
                sprintf('textAtt.entity_id = products.entity_id AND textAtt.attribute_id = %s', $attributeId),
                ['IFNULL(value, 1) AS value', 'IFNULL(textAtt.store_id, 0) AS store_id']
            );
        foreach ($connection->fetchAll($query) as $data) {
            $data['value'] = (is_numeric($data['value']) ? (int)$data['value'] : 1);
            $valuesToRestore[] = [
                'attribute_id' => $attributeId,
                'store_id' => $data['store_id'],
                'entity_id' => $data['entity_id'],
                'value' => $data['value'],
            ];
        }

        if (!empty($valuesToRestore)) {
            try {
                $connection->insertMultiple($intTable, $valuesToRestore);
                $connection->delete($textTable, sprintf('attribute_id = %s', $attributeId));
            } catch (\Exception $e) {
                $e->getMessage();
            }
        }

        $eavSetup->updateAttribute(
            $entityTypeId,
            $attributeCode,
            [
                'type' => 'int',
                'backend_type' => 'int',
                'frontend_class' => 'validate-digits validate-not-negative-number',
            ]
        );
    }
    public function apply119($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'is_in_impo',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Is in Impo',
                'input' => 'boolean',
                'class' => '',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );
    }

    public function apply120($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'is_in_impo', 'filtrable', true);
    }

    public function apply121($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'unit_price',
            [
                'type' => 'decimal',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 220,
                'label' => 'Unit Price',
                'input' => 'price',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => true,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple',
                'used_for_sort_by' => '1',
                'attribute_set_id' => 4,
            ]
        );
        $attrGroup = 'General'; // Attribute Group Name
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $allAttributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
        foreach ($allAttributeSetIds as $attributeSetId) {
            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $attrGroup,
                'unit_price',
                null
            );
        }

        $collection = $this->productCollection->addAttributeToSelect('*');
        $storeId = $this->storeManager->getStore()->getId();

        $connection = $setup->getConnection();
        $select = $connection->select()->from(
            'catalog_product_entity_tier_price'
        )->order(['entity_id','qty']);

        $productTierprices = [];
        foreach ($connection->fetchAssoc($select) as $row) {
            $productTierprices[$row['entity_id']] = $row;
        }

        foreach ($collection as $product){
            $pid = $product->getId();
            if (isset($productTierprices[$pid]) && empty($product->getUnitPrice())) {
                $tierprice = $productTierprices[$pid];
                $qtyInBox= (int)$product->getQtyinbox() > 0 ? (int)$product->getQtyinbox() : 1;
                $unitPrice = number_format($tierprice['value'] / $qtyInBox, 2);
                $this->productAction->updateAttributes([$pid], ['unit_price' => $unitPrice], $storeId);
            }
        }
    }

    public function apply122($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'unit_price', 'filtrable', false);
    }

    public function apply123($setup){
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,
         'unit_price');
        $this->apply121($setup);
    }

    public function apply124($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'qtyinbox',
            [
                'type' => 'int',
                'default' => 1,
                'frontend_class' => 'validate-digits validate-greater-than-zero'
            ]
        );
    }

    public function apply125($setup) {
        $collection = $this->productCollection->addAttributeToSelect('*');
        $storeId = $this->storeManager->getStore()->getId();

        $connection = $setup->getConnection();
        $select = $connection->select()->from(
            'catalog_product_entity_tier_price'
        )->order(['entity_id','qty']);

        $productTierprices = [];
        foreach ($connection->fetchAssoc($select) as $row) {
            $productTierprices[$row['entity_id']] = $row;
        }

        foreach ($collection as $product){
            $pid = $product->getId();
            if (isset($productTierprices[$pid]) && empty($product->getUnitPrice())) {
                $tierprice = $productTierprices[$pid];
                $qtyInBox= (int)$product->getQtyinbox() > 0 ? (int)$product->getQtyinbox() : 1;
                $unitPrice = number_format($tierprice['value'] / $qtyInBox, 2);
                $this->productAction->updateAttributes([$pid], ['unit_price' => $unitPrice], $storeId);
            }
        }
    }

    public function apply126($setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'qtyinbox',
            [
                'type' => 'int',
                'default' => 1,
                'frontend_class' => 'validate-digits validate-greater-than-zero required-entry'
            ]
        );
    }
}
