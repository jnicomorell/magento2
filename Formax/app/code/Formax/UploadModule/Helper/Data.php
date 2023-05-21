<?php

namespace Formax\UploadModule\Helper;

use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
use Formax\UploadModule\Model\FileFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_logger;
    protected $_storeManager;
    protected $_resource;
    protected $_fileFactory;
    

    public function __construct(
            LoggerInterface $logger,
            StoreManagerInterface $store,
            ResourceConnection $resource,
            FileFactory $fileFactory
    ) {
        $this->_logger = $logger;
        $this->_storeManager = $store;
        $this->_resource = $resource;
        $this->_fileFactory = $fileFactory;
    }

    public function getYearsCollection($type=null)
    {
        try {
            $collection=$this->_fileFactory->create()->getCollection();
            $storeId = $this->_storeManager->getStore()->getId();    
            $collection->addFieldToFilter('store_id', array('in'=> [$storeId,0]));
            $collection->addFieldToFilter('status', array('eq'=> 1));
            if($type==null || empty($type)){
                $type=1;
            }
            $collection->addFieldToFilter('type_id', array('eq'=> $type));

            $collection->getSelect()->group('year')->order('year DESC');

            return $collection;
        } catch (\categoriesId $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
    }

    

    public function getFileCollection($year=null, $type=null)
    {
        try {
            $collection=$this->_fileFactory->create()->getCollection();
            $storeId = $this->_storeManager->getStore()->getId();    
            $collection->addFieldToFilter('store_id', array('in'=> [$storeId,0]));
            $collection->addFieldToFilter('status', array('eq'=> 1));
            if($type==null || empty($type)){
                $type=1;
            }
            $collection->addFieldToFilter('type_id', array('eq'=> $type));

            /*if($year!=null && !empty($year)){
                $year=str_replace(' ', '', $year);
                $collection->addFieldToFilter('year', array('eq'=> $year));
                */
                $collection->setOrder(
                    "sort_order",
                    'asc'
                );
            /*}else{
                $collection->setOrder(
                    "year",
                    'desc'
                );
            }*/
            
            $collection->setPageSize(8)->setCurPage(1);
            
            return $collection;
        } catch (\categoriesId $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
    }

    public function getHomeFileCollection()
    {
        try {
            $collection=$this->_fileFactory->create()->getCollection();
            $storeId = $this->_storeManager->getStore()->getId();    
            $collection->addFieldToFilter('store_id', array('in'=> [$storeId,0]));
            $collection->addFieldToFilter('status', array('eq'=> 1));
            $collection->addFieldToFilter('home', array('eq'=> 1));
            $collection->setOrder(
                "type_id",
                'asc'
            );
            
            $collection->setPageSize(2)->setCurPage(1);
            
            return $collection;
        } catch (\categoriesId $ex) {
            $this->_logger->info(__CLASS__."/".__FUNCTION__.": ". $ex->getMessage());
        }
    }
    
    public function getFileImage($imageId) {
        return $this->_fileFactory->create()->getImageUrl($imageId);
    }
    
    public function getFileURL($fileId) {
        return $this->_fileFactory->create()->getFileUrl($fileId);
    }

}