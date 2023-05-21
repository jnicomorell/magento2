<?php
namespace Formax\News\Block;

use Psr\Log\LoggerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Formax\News\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

class NewsPortal extends Template {

    protected $_logger;
    protected $_dataHelper;
    protected $_categoryId;
    protected $_subcategoryId;
    protected $_articleId;
    protected $_categoryFactory;
    protected $_articleFactory;
    protected $_storeManager;
    protected $_dateTime;
    protected $_category;
    protected $_sanitize;

    public function __construct(
            Context $context,
            LoggerInterface $logger,
            Data $dataHelper,
            \Formax\News\Model\CategoryFactory $categoryFactory,
            \Formax\News\Model\ArticleFactory $articleFactory,
            StoreManagerInterface $store,
            \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
            \Formax\HelpCenter\Helper\Sanitize $sanitize
            //\DateTime $dateTime
    ) {
        $this->_logger = $logger;
        $this->_dataHelper = $dataHelper;
        $this->_categoryFactory = $categoryFactory;
        $this->_articleFactory = $articleFactory;
        $this->_storeManager = $store;
        $this->_dateTime = $dateTime;
        $this->_sanitize = $sanitize;
        parent::__construct($context);
        $this->init();
    }

    public function init()
	{
        $this->_category = 0;
        try {
            $values = $this->getRequest()->getParams();	
            if ($values !== null && is_array($values)){
                if (isset($values['category'])) {
                    $this->_category = $this->_sanitize->clean($values['category']);
                    if (!is_numeric($this->_category))
                        return false;
                } else {
                    return false;
                }
            }
        } catch (\Exception $e) {
            $this->_logger->error("Error getParams category: " . $e->getMessage());
        }
    }

    public function getCategory(){
        return $this->_category;
    }

    public function getCategories()
    {
        $categoryCollection = $this->_categoryFactory->create()->getCollection();
		$storeId = $this->_storeManager->getStore()->getId();
        $categoryCollection->addFieldToFilter('store_id', array('eq'=> $storeId))
                           ->addFieldToFilter('status', array('eq' => 1));
		return $categoryCollection;
    }

    public function getFirstCategoryId(){
        $categoryCollection = $this->_categoryFactory->create()->getCollection();
		$storeId = $this->_storeManager->getStore()->getId();
		$categoryCollection->addFieldToFilter('store_id', array('eq'=> $storeId));

        if($categoryCollection->getSize()){
            $category=$categoryCollection->getFirstItem();
            return $category->getId();
        }
        return null;
    }

    public function getFirstCategory(){
        $categoryCollection = $this->_categoryFactory->create()->getCollection();
		$storeId = $this->_storeManager->getStore()->getId();
		$categoryCollection->addFieldToFilter('store_id', array('eq'=> $storeId));

        if($categoryCollection->getSize()){
            $category=$categoryCollection->getFirstItem();
            return $category;
        }
        return null;
    }

    public function getAllArticles(){
		return $this->_dataHelper->getAllArticleCollection(); 
    }

    public function getArticles($categoryId){
		return $this->_dataHelper->getArticleCollectionByCategory($categoryId); 
    }


    public function getCategoryById($categoryId){
        return $this->_dataHelper->getCategoryById($categoryId);
    }

    public function getCategoryId(){
        return $this->_categoryId;
    }
    public function getSubcategoryId(){
        return $this->_subcategoryId;
    }
    public function getArticleId(){
        return $this->_articleId;
    }

    public function getArticle($articleId){
        return $this->_dataHelper->getArticle($articleId);
    }

    public function getStoreName()
    {
        return $this->_storeManager->getStore()->getName();
    }

    public function getArticleImage($imageId)
    {
        return $this->_dataHelper->getArticleImage($imageId);
    }

    public function limitTxt($txt, $limit){
        if(strlen($txt)>$limit){
            return substr($txt, 0, ($limit-3) )."...";
        }
        return $txt;
    }

    public function getformatDay($date){
        date_default_timezone_set('America/Santiago');
        $ph = (int)explode(":", date('P', strtotime($date)))[0];
        $time = date("j", strtotime($date." $ph hour")) ;

        return $time;
    }
    public function getformatMoth($date){
        $months=array(
            "January"=>"Enero",
            "February"=>"Febrero",
            "March"=>"Marzo",
            "April"=>"Abril",
            "May"=>"Mayo",
            "June"=>"Junio",
            "July"=>"Julio",
            "August"=>"Agosto",
            "September"=>"Septiembre",
            "October"=>"Octubre",
            "November"=>"Noviembre",
            "December"=>"Diciembre"
        );
        date_default_timezone_set('America/Santiago');
        $ph = (int)explode(":", date('P', strtotime($date)))[0];
         $time = date("F", strtotime($date." $ph hour"));
         $time =$months[$time];
         return $time;
    }
    public function getformatYear($date){
        date_default_timezone_set('America/Santiago');
        $ph = (int)explode(":", date('P', strtotime($date)))[0];
        $time = date("Y", strtotime($date." $ph hour"));

        return $time;
    }
    public function getformatTime($date){
        date_default_timezone_set('America/Santiago');
        $ph = (int)explode(":", date('P', strtotime($date)))[0];
        $time=date('g:i a', strtotime($date." $ph hour"));
        return $time;
    }

    public function getCategoriesByArticle($articleId = null){
        return $this->_dataHelper->getCategoriesByArticle($articleId);
    }

    public function getYearsCollection($type=null)
	{	
		return $this->_dataHelper->getYearsCollection();
    }

    public function getYear()
	{	
        return date("Y");
    }

}
