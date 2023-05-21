<?php
declare(strict_types=1);

namespace Ceg\Impo\Controller\View;

use Ceg\Impo\Helper\Data;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\Attribute\LayoutUpdateManager;
use Magento\Catalog\Model\Design;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Catalog\Model\Session;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * View a category on storefront. Needs to be accessible by POST because of the store switching.
 */
class Index extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * Catalog session
     *
     * @var Session
     */
    protected $catalogSession;

    /**
     * Catalog design
     *
     * @var Design
     */
    protected $catalogDesign;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryUrlPathGenerator
     */
    protected $catUrlGenerator;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var ToolbarMemorizer
     */
    private $toolbarMemorizer;

    /**
     * @var LayoutUpdateManager
     */
    private $customLayoutManager;

    /**
     * @var CategoryHelper
     */
    private $categoryHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $impoHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Design $catalogDesign
     * @param Session $catalogSession
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     * @param CategoryUrlPathGenerator $catUrlGenerator
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $impoHelper
     * @param ToolbarMemorizer|null $toolbarMemorizer
     * @param LayoutUpdateManager|null $layoutUpdateManager
     * @param CategoryHelper $categoryHelper
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Design $catalogDesign,
        Session $catalogSession,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        CategoryUrlPathGenerator $catUrlGenerator,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $impoHelper,
        ToolbarMemorizer $toolbarMemorizer = null,
        ?LayoutUpdateManager $layoutUpdateManager = null,
        CategoryHelper $categoryHelper = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->catalogDesign = $catalogDesign;
        $this->catalogSession = $catalogSession;
        $this->coreRegistry = $coreRegistry;
        $this->catUrlGenerator = $catUrlGenerator;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->layerResolver = $layerResolver;
        $this->categoryRepository = $categoryRepository;
        $this->impoHelper = $impoHelper;
        $this->toolbarMemorizer = $toolbarMemorizer ?: ObjectManager::getInstance()->get(ToolbarMemorizer::class);
        $this->customLayoutManager = $layoutUpdateManager
            ?? ObjectManager::getInstance()->get(LayoutUpdateManager::class);
        $this->categoryHelper = $categoryHelper ?: ObjectManager::getInstance()
            ->get(CategoryHelper::class);
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(LoggerInterface::class);
    }

    /**
     * Initialize requested category object
     *
     * @return Category|bool
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _initCategory()
    {
        $categoryId = $this->impoHelper->getGeneralConfig('layer_category_id');
        try {
            $category = $this->categoryRepository->get($categoryId, $this->storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }
        if (!$this->categoryHelper->canShow($category)) {
            return false;
        }
        $this->catalogSession->setLastVisitedCategoryId($category->getId());
        $this->coreRegistry->register('current_category', $category);
        $this->toolbarMemorizer->memorizeParams();
        try {
            $this->_eventManager->dispatch(
                'catalog_controller_category_init_after',
                ['category' => $category, 'controller_action' => $this]
            );
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            return false;
        }

        return $category;
    }

    /**
     * Category view action
     *
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $result = null;

        if ($this->_request->getParam(ActionInterface::PARAM_NAME_URL_ENCODED)) {
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
        }
        $category = $this->_initCategory();
        if ($category) {
            $this->layerResolver->create(Resolver::CATALOG_LAYER_CATEGORY);
            $settings = $this->catalogDesign->getDesignSettings($category);

            if ($settings->getCustomDesign()) {
                $this->catalogDesign->applyCustomDesign($settings->getCustomDesign());
            }

            $this->catalogSession->setLastViewedCategoryId($category->getId());

            $page = $this->resultPageFactory->create();
            if ($settings->getPageLayout()) {
                $page->getConfig()->setPageLayout($settings->getPageLayout());
            }

            $pageType = $this->getPageType($category);

            if (!$category->hasChildren()) {
                $parentPageType = strtok($pageType, '_');
                $page->addPageLayoutHandles(['type' => $parentPageType], null, false);
            }
            $page->addPageLayoutHandles(['type' => $pageType], null, false);
            // $page->addPageLayoutHandles(['displaymode' => strtolower($category->getDisplayMode())], null, false);
            $page->addPageLayoutHandles(['id' => $category->getId()]);

            $this->applyLayoutUpdates($page, $settings);

            $page->getConfig()->addBodyClass('page-products')
                ->addBodyClass('categorypath-' . $this->catUrlGenerator->getUrlPath($category))
                ->addBodyClass('category-' . $category->getUrlKey());

            return $page;
        } elseif (!$this->getResponse()->isRedirect()) {
            $result = $this->resultForwardFactory->create()->forward('noroute');
        }
            return $result;
    }

    /**
     * Get page type based on category
     *
     * @param Category $category
     * @return string
     */
    private function getPageType(Category $category): string
    {
        $hasChildren = $category->hasChildren();
        if ($category->getIsAnchor()) {
            return $hasChildren ? 'layered' : 'layered_without_children';
        }

        return $hasChildren ? 'default' : 'default_without_children';
    }

    /**
     * Apply custom layout updates
     *
     * @param Page $page
     * @param DataObject $settings
     * @return void
     */
    private function applyLayoutUpdates(
        Page $page,
        DataObject $settings
    ) {
        $layoutUpdates = $settings->getLayoutUpdates();
        if ($layoutUpdates && is_array($layoutUpdates)) {
            foreach ($layoutUpdates as $layoutUpdate) {
                $page->addUpdate($layoutUpdate);
                $page->addPageLayoutHandles(['layout_update' => sha1($layoutUpdate)], null, false);
            }
        }

        if ($settings->getPageLayoutHandles()) {
            $page->addPageLayoutHandles($settings->getPageLayoutHandles());
        }
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _isAllowed()
    {
        return true;
    }
}
