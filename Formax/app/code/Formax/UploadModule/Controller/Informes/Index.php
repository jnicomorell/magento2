<?php

namespace Formax\UploadModule\Controller\Informes;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $_resultJsonFactory;
    protected $_helper;
    protected $_logger;

    public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
            \Psr\Log\LoggerInterface $logger,
            \Formax\UploadModule\Helper\Data $helper
    )
    {
        $this->_resultJsonFactory = $jsonResultFactory;
        $this->_logger = $logger;
        $this->_helper = $helper;
        return parent::__construct($context);
    }

    public function execute()
    {
        
        if ($this->getRequest()->isAjax()) 
        {
            $year = $this->getRequest()->getParam('year');
            $type = $this->getRequest()->getParam('fileType');
            try {
                $data = $this->_helper->getFileCollection($year, $type);
                $files = $data->getData();
                foreach ($files as $key => $file){
                    $files[$key]["image"]=$this->_helper->getFileImage($file["image"]);
                    $files[$key]["file"]=$this->_helper->getFileURL($file["file"]);
                    $files[$key]["link_url"]=$files[$key]["link_url"]!=null && !empty($files[$key]["link_url"])?$files[$key]["link_url"]: $files[$key]["file"];
                }
                $files = (object) array('files' => $files);
                if ($files !== null) {
                    $resultJson = $this->_resultJsonFactory->create();
                    return $resultJson->setData($files);
                }
            } catch (\Exception $ex) {
                $this->_logger->error("Error in REST API Return : " . $ex->getMessage());
            }
        }
    }
}
