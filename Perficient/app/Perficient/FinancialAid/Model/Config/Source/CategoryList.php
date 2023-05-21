<?php

namespace Perficient\FinancialAid\Model\Config\Source;

use Vhl\Salesforce\Model\SchoolFactory;
use Vhl\Salesforce\Model\ResourceModel\School\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;
use Perficient\FinancialAid\Model\Config;

class CategoryList implements ArrayInterface
{
    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * CategoryList constructor.
     *
     * @param CategoryFactory $categoryFactory
     * @param CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        SchoolFactory $categoryFactory,
        CollectionFactory $categoryCollectionFactory,
        Config $config
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->config = $config;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray()
    {
        $arr = $this->toArray();
        $ret = [];

        foreach ($arr as $key => $value) {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $ret;
    }


    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toArray()
    {
        $categories = $this->getCategoryCollection();

        $categoryList = [];
        foreach ($categories as $category) {
            $categoryList[$category->getId()] = [
                'name' => $category->getName(),
                'cat_id' => $category->getId()
            ];
        }

        $catagoryArray = [];
        foreach ($categoryList as $k => $v) {
                $catagoryArray[$k] = '[' . $v['cat_id'] . '] -- ' . $v['name'];
        }

        asort($catagoryArray);
        return $catagoryArray;
    }


    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryCollection()
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addFieldToFilter('salesforce_id', ['gt' => 100000]);

        return $collection;
    }
}
