<?php

namespace Perficient\FinancialAid\Block;

/**
 * Class Product
 * @package Corra\GoogleCards\Block
 */
class Product extends \Magento\Framework\View\Element\Template
{
    public $_storeManager;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $_catalogSession;
    protected $catalogData;
    protected $categoryRepository;
    protected $_productRepository;
    protected $request;
    protected $customer;
    /**
     * Breadcrumb constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Customer\Model\Session $customer,
        \Magento\Directory\Model\Country $country,
        \Perficient\FinancialAid\Model\Config $financialAidConfig,
        array $data = []
    ) {
        $this->_catalogSession = $catalogSession;
        $this->catalogData = $catalogData;
        $this->_storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->_registry = $registry;
        $this->_productRepository = $productRepository;
        $this->request = $request;
        $this->customer = $customer;
        $this->country = $country;
        $this->financialAidConfig = $financialAidConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return false|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCrumbs()
    {
        $itemListElement = [];

        //Adding Home Page to BreadCrumb Item List

        $itemListElement[] = [
            '@type' => 'ListItem',
            'position' => 1,
            'item' => [
                '@id' => $this->getHomeUrl(),
                'name' => __('Home')
            ]
        ];
        $position = 2;
        $path = $this->getBreadcrumbPath();
        foreach ($path as $key => $category) {

            if (empty($category['link'])) {
                if (strpos($key, 'category') !== false) {
                    $catId = str_replace('category', '', $key);
                    $categoryLink = $this->getCategoryUrl($catId);
                } else {
                    continue;
                }

            }
            if (!empty($categoryLink)) {
                $category['link'] = $categoryLink;
            }

            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'item' => [
                    '@id' => $category['link'],
                    'name' => $category['label']
                ]
            ];
            $position++;
        }
        $currentPageXML = $this->request->getFullActionName();
        if ($currentPageXML == 'catalog_product_view') {
            $product = $this->_registry->registry('current_product');
            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'item' => [
                    '@id' => $product->getProductUrl(),
                    'name' => $product->getName()
                ]
            ];
        }
        $data = [
            '@context' => 'http://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElement
        ];
        return json_encode($data);
    }
    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHomeUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }
    /**
     * Retrieve Breadcrumb data from session
     * @return mixed
     */

    public function getBreadcrumbPath()
    {
        return $this->catalogData->getBreadcrumbpath();
    }
    /**
     * @param $categoryId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryUrl($categoryId)
    {
        $category = $this->categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());
        return $category->getUrl();
    }

    public function getProductById()
    {
        $id=$this->request->getParam('id');
        return $this->_productRepository->getById($id);
    }

    public function getCustomer()
    {
        return $this->customer->getCustomerData();
    }

    public function getSchoolName()
    {
        return $this->request->getParam('sn');
    }

    public function getRegionsOfCountry($countryCode)
    {
        $regionCollection = $this->country->loadByCode($countryCode)->getRegions();
        $regions = $regionCollection->loadData()->toOptionArray(false);
        return $regions;
    }

    public function getConsentTitle()
    {
        return $this->financialAidConfig->getConsentTitle();
    }

    public function getConsentText()
    {
        return $this->financialAidConfig->getConsentText();
    }
}
