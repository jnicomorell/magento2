<?php

namespace Perficient\FinancialAid\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Search\Model\QueryFactory;

/**
 * Search helper
 *
 * The helper has become a general tool for many classes to call utility methods.
 * I suggest you make it an API and give it an interface.
 */
class Data extends AbstractHelper
{

    const SFID_QUERY_VAR_NAME = "sfid";
    const SFID_QUERY_VAR_NAME_MINI = "i";
    const SCHOOL_NAME_QUERY_VAR_NAME = "n";
    const STORE_CODE_QUERY_VAR_NAME = "sc";
    const SCHOOL_URL_MONICKER = "school";
    const REWRITE_REQUEST_SCHOOL_PATH_ALIAS = 'rewrite_request_school_path';
    const MY_MADE_UP_SF_OWNER_ID_FIELD_NAME = 'salesforce_id_owner';
    const FIELD_NAME_CLASH_BUCKET_NAME = 'fields_that_clash';

    /**
     * @var \Vhl\Central\Helper\Data
     */
    protected $centralHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;


    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layoutInterface;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var \Vhl\Salesforce\Api\SchoolRepositoryInterface
     */
    protected $schoolRepositoryInterface;

    /**
     * @var \Vhl\Salesforce\Model\ResourceModel\PackageGroup\CollectionFactory
     */
    protected $packageGroupCollectionFactory;

    /**
     * @var \Vhl\Salesforce\Model\ResourceModel\Package\CollectionFactory
     */
    protected $packageCollectionFactory;

    /**
     * @var \Vhl\Salesforce\Model\AccountRepository
     */
    protected $salesForceAccountRepository;

    /**
     * @var \Vhl\Salesforce\Model\UserRepository
     */
    protected $salesForceUserRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Vhl\Salesforce\Api\Data\SchoolInterface
     */
    protected $school;

    /**
     * Data constructor.
     *
     * @param \Vhl\Central\Helper\Data $centralHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\Form\FormKey $formKeyModel
     * @param \Vhl\Salesforce\Model\AccountRepository $salesForceAccountRepository
     * @param \Vhl\Salesforce\Model\UserRepository $salesForceUserRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\View\LayoutInterface $layoutInterface
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Vhl\Salesforce\Api\SchoolRepositoryInterface $schoolRepositoryInterface
     * @param \Vhl\Salesforce\Model\ResourceModel\PackageGroup\CollectionFactory $packageGroupCollectionFactory
     * @param \Vhl\Salesforce\Model\ResourceModel\Package\CollectionFactory $packageCollectionFactory
     */
    public function __construct(
        \Vhl\Central\Helper\Data $centralHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Form\FormKey $formKeyModel,
        \Vhl\Salesforce\Model\AccountRepository $salesForceAccountRepository,
        \Vhl\Salesforce\Model\UserRepository $salesForceUserRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\LayoutInterface $layoutInterface,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Vhl\Salesforce\Api\SchoolRepositoryInterface $schoolRepositoryInterface,
        \Vhl\Salesforce\Model\ResourceModel\PackageGroup\CollectionFactory $packageGroupCollectionFactory,
        \Vhl\Salesforce\Model\ResourceModel\Package\CollectionFactory $packageCollectionFactory
    ) {
        $this->centralHelper = $centralHelper;
        $this->registry = $registry;
        $this->salesForceAccountRepository = $salesForceAccountRepository;
        $this->salesForceUserRepository = $salesForceUserRepository;
        $this->storeManager = $storeManager;
        $this->catalogSession = $catalogSession;
        $this->customerSession = $customerSession;
        $this->request = $context->getRequest();
        $this->layoutInterface = $layoutInterface;
        $this->blockFactory = $blockFactory;
        $this->schoolRepositoryInterface = $schoolRepositoryInterface;
        $this->packageGroupCollectionFactory = $packageGroupCollectionFactory;
        $this->packageCollectionFactory = $packageCollectionFactory;

        parent::__construct($context);
        $this->school = $this->initializeSchool();
    }

    /**
     * Initializes school
     * @return \Vhl\Salesforce\Api\Data\SchoolInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function initializeSchool()
    {
        $school = null;
        if ($this->catalogSession->getVhlSchoolSalesforceId()) {
            $school = $this->schoolRepositoryInterface->get($this->catalogSession->getVhlSchoolSalesforceId());
        } else {
            try {
                $identifier = trim($this->_getRequest()->getOriginalPathInfo(), '/');
                $schoolUrl = $this->findSchoolUrlKey($identifier);
                $school = $this->schoolRepositoryInterface->getByUrlKey($schoolUrl);
            } catch (\Exception $e) {
                // Do nothing
            }
        }

        return $school;
    }


    /**
     * @return int
     */
    public function getStoreId()
    {
        $stores = $this->storeManager->getStore();
        return $stores->getId();
    }

    /**
     * @param null $query
     * @return array
     */
    public function getStores($query = null)
    {
        $storesResult = [];
        $website = $this->storeManager->getWebsite();
        foreach ($website->getGroups() as $group) {
            if (!$group instanceof \Magento\Store\Model\Group) {
                $group = $this->_storeManager->getGroup($group);
            }
            $stores = $group->getStores();
            foreach ($stores as $store) {
                $storesResult[$store->getCode()] = [
                    'name' => $store->getName(),
                    'url' => $this->getResultUrl($query, $store),
                    'storeId' => $store->getId()
                ];
            }
        }

        return $storesResult;
    }

    /**
     * Get possible search types.
     *
     * @return array
     */
    public function getSearchTypes()
    {
        return ['isbn' => 'ISBN', 'school' => 'School', 'title' => 'Title'];
    }

    /**
     * Retrieve result page url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @param null $query
     * @param null $store
     * @return string
     */
    public function getResultUrl($query = null, $store = null)
    {
        if ($store !== null) {
            $resultUrl = $store->getBaseUrl() . 'vhl_sp/schools/result';
        } else {
            $resultUrl = $this->_getUrl(
                'vhl_sp/schools/result',
                [
                    '_query' => [QueryFactory::QUERY_VAR_NAME => $query],
                    '_secure' => $this->_request->isSecure()
                ]
            );
        }

        return $resultUrl;
    }

    /**
     * @return string
     */
    public function getSchoolsSearchGuessUrl()
    {
        $theUrlYouSeek = $this->centralHelper->getSchoolSearchUrl([], $this->centralHelper->getSslStatus());
        return $theUrlYouSeek;
    }

    /**
     * @param null $salesforceId
     * @param string $schoolName
     * @return string
     */
    public function getSchoolPackageGroupsUrl($salesforceId = null, $schoolName = "")
    {
        $urlPath = 'vhl_sp/packages/fetch';
        $queryParams = [
            '_query' => [
                self::SFID_QUERY_VAR_NAME => $salesforceId,
                self::SCHOOL_NAME_QUERY_VAR_NAME => $schoolName
            ],
            '_secure' => $this->_request->isSecure()
        ];


        try {
            if ($salesforceId) {
                $school = $this->schoolRepositoryInterface->get($salesforceId);
            } else {
                $school = $this->school;
            }

            if ($school) {
                $schoolUrlKey = $school->getUrlKey();
                if (is_string($schoolUrlKey)) {
                    if (strlen($schoolUrlKey) > 0) {
                        $urlPath = self::SCHOOL_URL_MONICKER . '/' . $schoolUrlKey;
                        $queryParams = ['_secure' => $this->_request->isSecure()];
                    }
                }
            }
        } catch (\Exception $e) {

        }

        return $this->_getUrl($urlPath, $queryParams);
    }

    /**
     * @return string
     */
    public function getSchoolInfoSubmitUrl()
    {
        $resultUrl = $this->_getUrl(
            'vhl_sp/schools/info',
            [
                '_secure' => $this->_request->isSecure()
            ]
        );
        return $resultUrl;
    }

    /**
     * @return string
     */
    public function getSchoolName()
    {
        $schoolName = "";
        if ($this->isSchool()) {
            $schoolName = $this->school->getName();
        }

        return $schoolName;
    }


    /**
     * @return string
     */
    public function getLastSearchTerm()
    {
        $searchTerm = $this->catalogSession->getVhlLastSearchString();
        return $searchTerm ?: "";
    }

    /**
     * And all because the breadcrumbs need to be able to go back to the search results page
     * So much for a stateless web
     *
     * @return string
     */
    public function getLastSearchUrl()
    {
        $salesforceId = $this->catalogSession->getVhlLastSearchSfid();
        $searchString = $this->catalogSession->getVhlLastSearchString();
        $searchSchoolName = $this->catalogSession->getVhlLastSearchSchoolName();

        $queryParams = [];
        if (true || $salesforceId) {
            $queryParams[self::SFID_QUERY_VAR_NAME_MINI] = $salesforceId;
        }
        if ($searchString) {
            $queryParams[QueryFactory::QUERY_VAR_NAME] = $searchString;
        }
        if ($searchSchoolName) {
            $queryParams[self::SCHOOL_NAME_QUERY_VAR_NAME] = $searchSchoolName;
        }

        $url = trim($this->getResultUrl(), "/") . "?" . http_build_query($queryParams);
        return $url;
    }

    /**
     * @param string $template
     * @return string
     */
    public function spawnSchoolsSearch($template = "")
    {
        $suffix = str_replace(".", "_", "_" . \microtime(true));
        $name = "school_search_" . $suffix;
        $widget = $this->layoutInterface->createBlock(\Vhl\SchoolsPackages\Block\Widget\Search::class, $name, []);
        if ($template) {
            $widget->setTemplate($template);
        }
        if (!$widget instanceof \Magento\Widget\Block\BlockInterface) {
            return "";
        }
        return $widget->toHtml();
    }

    /**
     * @return bool
     */
    public function isSchool()
    {
        $isSchool = false;

        $rewriteAlias = $this->request->getAlias(self::REWRITE_REQUEST_SCHOOL_PATH_ALIAS);
        if ($rewriteAlias) {
            $pathObject = $this->getPathDetails($rewriteAlias);
            if ($pathObject && array_key_exists('schools_bit', $pathObject) && array_key_exists('school_url_key', $pathObject)) {
                if ($pathObject['schools_bit'] && $pathObject['school_url_key']) {
                    switch ($pathObject['schools_bit']) {
                        case "school":
                        case "schools":
                        case "store":
                        case "stores":
                            $isSchool = true;
                            break;
                        default:
                            //leave it.
                    }
                }
            }
        }

        return $isSchool;
    }

    /**
     * @param string $schoolUrlKey
     * @return string
     */
    public function getSchoolsPath($schoolUrlKey = "")
    {
        $schoolsPath = "";

        if ($schoolUrlKey) {
            $schoolsPath = 'school' . "/" . $schoolUrlKey;
        } elseif ($this->isSchool()) {
            $sessionSchoolUrlKey = $this->school->getUrlKey();
            $rewriteAlias = $this->request->getAlias(self::REWRITE_REQUEST_SCHOOL_PATH_ALIAS);
            $schoolUrlMonicker = self::SCHOOL_URL_MONICKER;
            if ($sessionSchoolUrlKey) {
                $schoolsPath = $schoolUrlMonicker . "/" . $this->school->getUrlKey();
            } elseif ($rewriteAlias) {
                $pathObject = $this->getPathDetails($rewriteAlias);
                if ($pathObject && array_key_exists('schools_bit', $pathObject) && array_key_exists('school_url_key', $pathObject)) {
                    if ($pathObject['schools_bit'] && $pathObject['school_url_key']) {
                        switch ($pathObject['schools_bit']) {
                            case "school":
                            case "schools":
                            case "store":
                            case "stores":
                            case $schoolUrlMonicker:
                                $schoolsPath = $schoolUrlMonicker . "/" . $pathObject['school_url_key'];
                                break;
                            default:
                                //leave it.
                        }
                    }
                }
            }
        }

        return $schoolsPath;
    }

    /**
     * @return string
     */
    public function getSchoolsUrl()
    {
        return $this->_getUrl($this->getSchoolsPath());
    }

    /**
     * @param $path
     * @return null
     */
    public function getPathDetails($path)
    {

        $split = parse_url("http://www.dev.com/" . $path);
        if (!$split) {
            return null;
        }

        $splitPath = [];
        if ($split && array_key_exists('path', $split)) {
            $splitPath = explode("/", trim($split['path'], "/"));
        }

        if (count($splitPath) > 1) {
            $pathObject['schools_bit'] = $splitPath[0];
            $pathObject['school_url_key'] = $splitPath[1];
            //query
            if (array_key_exists('query', $split)) {
                $pathObject['query'] = $split['query'];
            } else {
                $pathObject['query'] = "";
            }
            //hash
            if (array_key_exists('hash', $split)) {
                $pathObject['hash'] = $split['hash'];
            } else {
                $pathObject['hash'] = "";
            }
            //the rest (ie whatever is left in the middle)(without leading or trailing slash)
            $pathObject['the_rest'] = trim(str_replace([$splitPath[0] . "/", $splitPath[0], "?" . $pathObject['query'], "#" . $pathObject['hash']], $path, ""), "/");
        } else {
            return null; //we could not split it into the correct format so just ignore it
        }

        return $pathObject;
    }

    /**
     * @return mixed
     */
    public function getKnownSchoolSalesforceId()
    {
        if ($this->school) {
            return $this->school->getSalesforceId();
        } else {
            return null;
        }
    }

    /**
     * @return mixed
     */
    public function getKnownSchoolId()
    {
        return $this->catalogSession->getVhlSchoolId();
    }

    /**
     * @return mixed
     */
    public function getKnownSchoolName()
    {
        if ($this->school) {
            return $this->school->getName();
        } else {
            return null;
        }
    }

    /**
     * Please note it is the SalesForce ID, not the DB table row id
     *
     * @param null $schoolId SalesForceId
     * @return null|\Vhl\Salesforce\Api\Data\SchoolInterface
     */
    public function getSchool($schoolId = null)
    {
        $school = null;
        try {
            $schoolId = $schoolId ?: $this->getKnownSchoolSalesforceId();
            if ($schoolId) {
                $school = $this->schoolRepositoryInterface->get($schoolId);
            }
        } catch (\Exception $e) {

        }

        return $school;
    }


    /**
     * Returns the url key from the original request
     *
     * @param $identifier
     * @return mixed|string|null
     */
    protected function findSchoolUrlKey($identifier)
    {
        $a[] = "^(schools{0,1}\\/[^\\/]+?)\\/{0,1}$"; //schools/uscupstate/
        $a[] = "^(schools{0,1}\\/.+?)\\/(.+)\\/{0,1}$"; //schools/uscupstate/some_product.html
        $a[] = "^.*?\\/(schools{0,1}\\/[^\\/]+?)\\/{0,1}$"; //mystorefolder/schools/uscupstate/
        $a[] = "^.*?\\/(schools{0,1}\\/.+?)\\/(.+)\\/{0,1}$"; //mystorefolder/schools/uscupstate/some_product.html
        $a[] = "^(stores{0,1}\\/[^\\/]+?)\\.html{0,1}$"; //store/uscupstate/
        $a[] = "^(stores{0,1}\\/.+?)\\/(.+)\\/{0,1}$"; //store/uscupstate/some_product.html
        $a[] = "^.*?\\/(stores{0,1}\\/[^\\/]+?)\\.html{0,1}$"; //mystorefolder/store/uscupstate/
        $a[] = "^.*?\\/(stores{0,1}\\/.+?)\\/(.+)\\/{0,1}$"; //mystorefolder/store/uscupstate/some_product.html

        $regexp = implode("|", $a);
        $dummyReturn = preg_match("#" . $regexp . "#", $identifier, $matches);

        if ($dummyReturn === 0 || $dummyReturn === false) {
            return null;
        } else {
            $matches = array_values(array_filter($matches)); //remove falsey entries *and* reset the numerical keys
            $schoolUrlKeyFull = $matches[1];
            $anVariable = explode('/', $schoolUrlKeyFull);
            $schoolUrlKey = array_pop($anVariable); //ASSUME the last one is the school
        }
        if (!$schoolUrlKey) {
            return null;
        }

        return $schoolUrlKey;
    }

    /**
     * Returns an Magento Framework Data Object of school specific product data
     * *if* the requesting URL represents a school
     * *and* if there is school specific data for this product
     *
     * @param $product  A product that represents a package
     * @param $school A school salesforce id or a school model object
     * @param $packageGroupId The salesforce ID of a package group
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSchoolSpecificProductData($product, $school = null, $packageGroupId = null)
    {
        $specificData = new \Magento\Framework\DataObject();
        if (!$school && !$this->isSchool()) {
            return $specificData;
        }

        $school = $school ?: $this->getSchool();
        if ($school instanceof \Vhl\Salesforce\Api\Data\SchoolInterface) {
            $schoolId = $school->getId();
        } else {
            $schoolId = $school;
            if (is_string($school)) {
                $isSfidLike = (strlen(preg_replace("#[^0-9]#", "", $school)));
                if ($isSfidLike) {
                    $schoolId = $this->getSchool($school)->getId();
                }
            }
        }

        //get a product id (aka package id)
        $productId = $product->getId();
        //We need the packageGroupId to uniquely identify the package-group-package
        if (!$packageGroupId) {
            //if the product id is a group then get school specific data
            $packageGroup = null;
            $packageGroups = $this->packageGroupCollectionFactory->create();
            $packageGroups->addFieldToFilter('product_id', $productId);
            $packageGroups->addFieldToFilter('school_id', $schoolId);

            //$product_id is not unique amongst package-groups-per-school.
            //so if there is more than one returned then we need to know also the packageGroupId
            if ($packageGroups->count() > 1) {
                if (!$packageGroupId) {
                    $packageGroupId = $this->getPackageGroupId();
                }

                if ($packageGroupId) {
                    //noting there are unlikely to be more than two or three
                    foreach ($packageGroups as $candidatePackageGroup) {
                        if ($candidatePackageGroup->getId == $packageGroupId) {
                            $packageGroup = $candidatePackageGroup;
                            break;
                        }
                    }
                } else {
                    $packageGroup = $packageGroups->getFirstItem();
                }
            } else {
                $packageGroup = $packageGroups->getFirstItem();
            }
            if ($packageGroup) {
                $packageGroupId = $packageGroup->getId();
            }
        }

        if ($packageGroupId && $productId && $schoolId) {
            $prefix = ($product->getTypeId() === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) ? 'g' : 'main_table';
            //if the product id is a package then get the school specific data
            $packages = $this->packageCollectionFactory->create();
            $packages->getSelect()
                ->join(
                    ['g' => 'vhl_salesforce_package_group'],
                    'main_table.package_group_id = g.id',
                    ['school_id' => 'school_id', 'product_group_id' => 'product_id', 'product_group_name' => 'name', 'product_group_description' => 'description']
                )->where($prefix.'.product_id = ?', $productId)->where('g.id = ?', $packageGroupId)->where('g.school_id = ?', $schoolId); //THIS IS REDUNDANT. [$packageGroupId AND $productId] form a unique recordset


            $package = $packages->getFirstItem();
            if ($package) {
                $specificData->setData($package->getData());
            }
        }

        return $specificData;
    }


    /**
     * Returns an Magento Framework Data Object of school specific product data
     * *if* the requesting URL represents a school
     * *and* if there is school specific data for this product
     *
     * @param null $school
     * @return \Magento\Framework\DataObject
     */
    public function getSchoolSpecificSalesForceData($school = null)
    {
        $specificData = new \Magento\Framework\DataObject();
        if (!$school && !$this->isSchool()) {
            return $specificData;
        }

        //get a schoolID
        if (!$school) {
            $school = $this->getSchool();
        }
        $schoolId = $school->getSalesforceId();
        try {
            $salesForceAccount = $this->salesForceAccountRepository->get($schoolId);
        } catch (\Exception $e) {
            $salesForceAccount = new \Magento\Framework\DataObject();
        }

        if ($salesForceAccount) {
            $specificData->setData($salesForceAccount->getData());
        }

        $ownerId = $salesForceAccount->getData('web_store_owner_id');
        $salesForceUser = null;

        if ($ownerId) {
            try {
                $salesForceUser = $this->salesForceUserRepository->get($ownerId);
            } catch (\Exception $e) {
                $salesForceUser = new \Magento\Framework\DataObject();
            }
        }

        if ($salesForceUser) {
            $hasFieldNameClash = false;
            $clashedFieldNames = [];
            foreach ($salesForceUser->getData() as $key => $value) {
                if ($specificData->getData($key)) {
                    $hasFieldNameClash = true;
                    $clashedFieldNames[$key] = $value;
                } else {
                    $specificData->setData($key, $value);
                }
            }

            if ($salesForceUser->getData('salesforce_id')) {
                $specificData->setData(static::MY_MADE_UP_SF_OWNER_ID_FIELD_NAME, $salesForceUser->getData('salesforce_id'));
            }
            if ($hasFieldNameClash) {
                $specificData->setData(static::FIELD_NAME_CLASH_BUCKET_NAME, $clashedFieldNames);
            }
        }

        return $specificData;
    }


    /**
     * A little wrapper function that gets the school specific data
     * and returns only the 'appropriate_for' text.
     *
     * @param $product
     * @param null $school
     * @return mixed
     */
    public function getAppropriateFor($product, $school = null)
    {
        return $this->getSchoolSpecificProductData($product, $school, $this->getPackageGroupId())->getData('appropriate_for');
    }

    /**
     * A little wrapper function that gets the school specific data
     * and returns only the 'name' text.
     *
     * @param $product
     * @param null $school
     * @return mixed
     */
    public function getPackageGroupName($product, $school = null)
    {
        return $this->getSchoolSpecificProductData($product, $school, $this->getPackageGroupId())->getData('product_group_name');
    }

    /**
     * A little wrapper function that gets the school specific data
     * and returns only the 'description' text.
     *
     * @param $product
     * @param null $school
     * @return mixed
     */
    public function getPackageGroupDescription($product, $school = null)
    {
        return $this->getSchoolSpecificProductData($product, $school, $this->getPackageGroupId())->getData('product_group_description');
    }

    /**
     * A little wrapper function that gets the school specific data
     * and returns only the 'description' text.
     *
     * @param $product
     * @param null $school
     * @return mixed
     */
    public function getPackagePrice($product, $school = null)
    {
        return $this->getSchoolSpecificProductData($product, $school, $this->getPackageGroupId())->getData('sales_price');
    }

    /**
     * Actually, we really must know the packageGroupId
     *
     * @return mixed
     */
    public function getPackageGroupId()
    {
        //a query string has top priority:
        $packageGroupId = $this->request->getParam('pgi', null);
        $packageGroupId = $packageGroupId ?: $this->catalogSession->getLastRequestedPgi();
        $packageGroupId = $packageGroupId ?: $this->registry->registry('last-requested-pgi');
        $packageGroupId = $packageGroupId ?: $this->request->getCookie('pgi', null);

        return $packageGroupId;
    }
}
