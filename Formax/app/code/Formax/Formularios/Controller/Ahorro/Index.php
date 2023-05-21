<?php
namespace Formax\Formularios\Controller\Ahorro;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $helperData;
    protected $redirect;
    protected $_productCollectionFactory;
    protected $_productName;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
        \Formax\Formularios\Helper\Data $helperData,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    )
	{
		$this->_pageFactory = $pageFactory;
        $this->helperData = $helperData;
        $this->redirect = $redirect;
        $this->_productCollectionFactory = $productCollectionFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$resultPage = $this->_pageFactory->create();
		$title = $this->helperData->getAhorroConfig('general/title');
        $resultPage->getConfig()->getTitle()->set($title);
        $resultRedirect = $this->resultRedirectFactory->create();

        if ( $this->getProductName() ) {
            return $resultPage;
        } else {
            return $resultRedirect->setPath('');
        }
    }

    public function getProductName()
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $ref = explode("/", $_SERVER['HTTP_REFERER']);
            $referer = end($ref);
            $referer = str_replace(".html", "", $referer);

            $productCollection = $this->getProductCollection($referer);
            if ($productCollection->count()) {
                foreach ($productCollection as $product) {
                    $this->_productName = $product->getName();
                }
                return $this->_productName;
            } else
                return false;
        } else {
            return false;
        }
    }

    protected function getProductCollection($ref)
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('name', 'url_key');
        $collection->addFieldToFilter('url_key', array('eq' => $ref));
        $collection->setPageSize(1);
        return $collection;
    }
    
}