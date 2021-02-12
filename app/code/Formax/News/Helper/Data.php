<?php

namespace Formax\News\Helper;

use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Formax\News\Model\Article;
use Formax\News\Model\ArticleFactory;
use Formax\News\Model\ResourceModel\Article\CollectionFactory as ArticleCollection;
use Formax\News\Model\CategoryFactory;
use Formax\News\Model\Homenews;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_logger;
    protected $_storeManager;
    protected $_article;
    protected $_articleFactory;
    protected $_articleCollection;
    protected $_categoryFactory;
    protected $_homenewsModel;
    protected $_resource;
    protected $scopeConfig;
    
    const XML_PATH_HELPCENTER = 'news';

    public function __construct(
            LoggerInterface $logger,
            StoreManagerInterface $store,
            Article $article,
            ArticleFactory $articleFactory,
            CategoryFactory $categoryFactory,
            Homenews $homenewsModel,
            ArticleCollection $articleCollection,
            ScopeConfigInterface $scopeConfig,
            \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_logger = $logger;
        $this->_storeManager = $store;
        $this->_articleFactory = $articleFactory;
        $this->_articleCollection = $articleCollection;
        $this->_categoryFactory = $categoryFactory;
        $this->_homenewsModel = $homenewsModel;
        $this->_resource = $resource;
        $this->scopeConfig = $scopeConfig;
    }


    public function getCategoriesByArticle($articleId = null)
    {
        try {
            $resource = $this->_resource->getConnection();

            if ($articleId !== null && !empty($articleId)) {
                $query = "SELECT category_id FROM formax_news_category_article WHERE article_id='$articleId' ";
            } else {
                $query = "SELECT category_id FROM formax_news_category_article ";
            }
            $categories = $resource->fetchAll($query);

            $categoriesId=[];
            foreach ($categories as $categoriId) {
                $categoriesId[]=$categoriId['category_id'];
            }

            $categoryCollection=$this->_categoryFactory->create()->getCollection();
            $storeId = $this->_storeManager->getStore()->getId();    
            $categoryCollection->addFieldToFilter('store_id', array('eq'=> $storeId))
                            ->addFieldToFilter('id', $categoriesId)
                            ->addFieldToFilter('status', array('eq'=> 1));

            return $categoryCollection;
        } catch (\categoriesId $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
    }


    public function insertCategoryArticle($category,$articleId){

        $this->_logger->info(__CLASS__."/".__FUNCTION__." /CATEGORIA:". $category);
        $this->_logger->info(__CLASS__."/".__FUNCTION__." /QUESTION:". $articleId);

        $insertQuery = $this->_resource->getConnection();
                //Your custom sql query
        $saveQuery = "INSERT INTO formax_news_category_article (category_id, article_id) VALUES ('$category','$articleId')"; 

        $save = $insertQuery->query($saveQuery);
    }

    public function deleteCategoryArticleByArticle($articleId){

        try {
            if ($articleId !== null && !empty($articleId)) {
                $resource = $this->_resource->getConnection();
                $query = "DELETE FROM formax_news_category_article WHERE article_id='$articleId' "; 
                $delete = $resource->query($query);
            }
        } catch (\categoriesId $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
    }

    public function getArticleByID($articleId)
    {
        try {
            $resource = $this->_resource->getConnection();

            if ($articleId == null || empty($articleId)) 
                return;
            
            $query = "SELECT article, answer FROM formax_news_article WHERE id='$articleId' ";
            
            $articles = $resource->fetchAll($query);

            foreach ($articles as $cuestion) {
                return $cuestion;
            }
            return [];
        } catch (\categoriesId $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
    }

    public function getArticle($articleId=null)
    {
        try {
            if($articleId!=null && !empty($articleId)){
                $collection=$this->_articleFactory->create()->getCollection();
                $storeId = $this->_storeManager->getStore()->getId();    
                $collection->addFieldToFilter('store_id', array('eq'=> $storeId));
                $collection->addFieldToFilter('status', array('eq'=> 1));
                $collection->addFieldToFilter('id', array('eq'=> $articleId));
                if($collection->getSize()){
                    $article=$collection->getFirstItem();
                    return $article;
                }
                return null;
            }else{
                return null;
            }

        } catch (\categoriesId $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
    }

    public function getArticlesidByCategory($categoryID)
    {
        try {
            $resource = $this->_resource->getConnection();

            if ($categoryID !== null && !empty($categoryID)) {
                $query = "SELECT article_id FROM formax_news_category_article WHERE category_id='$categoryID' ";
            } 
            $categories = $resource->fetchAll($query);

            $articlesId=[];
            foreach ($categories as $categoriId) {
                $articlesId[]=$categoriId['article_id'];
            }
            return $articlesId;
        } catch (\categoriesId $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
    }

    public function getAllArticleCollection($category=null, $year=null)
    {
        try {
            $collection=$this->_articleFactory->create()->getCollection();
            $storeId = $this->_storeManager->getStore()->getId();    
            $collection->addFieldToFilter('store_id', array('eq'=> $storeId))
                            ->addFieldToFilter('status', array('eq'=> 1));

            if($year!=null && !empty($year)){
                $year=str_replace(' ', '', $year);
                $collection->addFieldToFilter('year', array('eq'=> $year));
            }

            if($category!=null && !empty($category)){
                $category=str_replace(' ', '', $category);
                $articlesId=$this->getArticlesidByCategory($category);
                if (count($articlesId)>0){
                    $collection->addFieldToFilter('id', $articlesId);
                }else{
                    $collection->addFieldToFilter('id', array('eq'=> '0'));
                }
            }
            $collection->setOrder(
                "sort_order",
                'asc'
            );

            return $collection;

        } catch (\categoriesId $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
    }

    public function getArticleCollectionByCategory($categoryID)
    {
        try {
            $articlesId=$this->getArticlesidByCategory($categoryID);
            
            if (count($articlesId)>0){
                $articleCollection=$this->_articleFactory->create()->getCollection();
                $storeId = $this->_storeManager->getStore()->getId();    
                $articleCollection->addFieldToFilter('store_id', array('eq'=> $storeId))
                                ->addFieldToFilter('id', $articlesId)
                                ->addFieldToFilter('status', array('eq'=> 1));
                return $articleCollection;
            }
            return [];

        } catch (\categoriesId $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
    }

    public function getCategoryById($categoryId)
    {
        try {

            $categoryCollection=$this->_categoryFactory->create()->getCollection();
            $storeId = $this->_storeManager->getStore()->getId();    
            $categoryCollection->addFieldToFilter('store_id', array('eq'=> $storeId))
                            ->addFieldToFilter('id', $categoryId)
                            ->addFieldToFilter('status', array('eq'=> 1));
            if($categoryCollection->getSize()){
                $category=$categoryCollection->getFirstItem();
                return $category;
            }
            return null;

        } catch (\categoriesId $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
    }

    public function getArticleImage($imageId) {
        return $this->_articleFactory->create()->getImageUrl($imageId);
    }

    public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
    }
    
    public function getGeneralConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_HELPCENTER .'/'. $code, $storeId);
    }

    public function getMainNewsData($mainNewsId)
    {
        $mainNews = $this->_homenewsModel->load($mainNewsId)->getData();
        return $mainNews;
    }

    public function getNewsData($newsIds)
    {
        $collection = [];
        $newsCollection = $this->getNewsCollection();
        $arrayCollection = [];
        $i = 0;

        if ( count($newsCollection->getData()) > 0 ) {
            foreach($newsCollection as $item) {
                $arrayCollection[$i] = $item->getData();
                $arrayCollection[$i]['image_url'] = $item->getImageUrl();
                if ($i == 2) break; $i++;
            }
        }
    
        foreach ($newsIds as $key => $id) {
            try {
                if ($id != 0) {
                    $new = $this->getNewsById($id);
            
                    if ( count($new->getData()) > 0 ) {
                        foreach($new as $item) {
                            $collection[$key] = $item->getData();
                            $collection[$key]['image_url'] = $item->getImageUrl();
                        }
                    } else {
                        if ( isset($arrayCollection[0]) ) {
                            $collection[$key] = $arrayCollection[0];
                            $arrayCollection = array_slice($arrayCollection, 1);
                        }
                    }
                } else {
                    if ( isset($arrayCollection[0]) ) {
                        $collection[$key] = $arrayCollection[0];
                        $arrayCollection = array_slice($arrayCollection, 1);
                    }
                }
            } catch (\Exception $ex) {
                $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
            }
        }

        return $collection;
    }

    public function getNewsById($newsId)
    {
        try {
            $news = $this->_articleFactory->create()->getCollection();
            $storeId = $this->_storeManager->getStore()->getId();
            $news->addFieldToFilter('store_id', array('eq'=> $storeId))
                        ->addFieldToFilter('status', array('eq'=> 1))
                        ->addFieldToFilter('id', array('eq'=> $newsId));
            return $news;
        } catch (\Exception $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
    }

    public function getNewsCollection()
    {
        try {
            $newsCollection = $this->_articleFactory->create()->getCollection();
            $storeId = $this->_storeManager->getStore()->getId();
            $newsCollection->addFieldToFilter('store_id', array('eq'=> $storeId))
                       ->addFieldToFilter('status', array('eq'=> 1))
                       ->setOrder('created_at', 'desc');
        return $newsCollection;
        } catch (\Exception $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
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

    public function getYearsCollection($type=null)
    {
        try {
            $collection=$this->_articleFactory->create()->getCollection();
            $storeId = $this->_storeManager->getStore()->getId();    
            $collection->addFieldToFilter('store_id', array('eq'=> $storeId));
            $collection->addFieldToFilter('status', array('eq'=> 1));

            $collection->getSelect()->group('year')->order('year DESC');

            return $collection;
        } catch (\categoriesId $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
    }

    public function getCollectionRelatedNews($newsIds)
    {
        try {
            $news = $this->_articleFactory->create()->getCollection();
            $storeId = $this->_storeManager->getStore()->getId();
            $news->addFieldToFilter('store_id', array('eq'=> $storeId))
                        ->addFieldToFilter('status', array('eq'=> 1))
                        ->addFieldToFilter('id', array('in'=> $newsIds));
            return $news;
        } catch (\Exception $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
    }
    
}
