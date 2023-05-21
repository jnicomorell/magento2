<?php
namespace Perficient\FinancialAid\Controller\Index;
 
use Zend\Log\Filter\Timestamp;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Dompdf\Dompdf;
use Perficient\FinancialAid\Model\FinancialAidFactory;
use Magento\Framework\View\Asset\Repository;

class Post extends \Magento\Framework\App\Action\Action
{
     
    protected $_inlineTranslation;
    protected $_transportBuilder;
    protected $_scopeConfig;
    protected $_logLoggerInterface;
    protected $storeManager;
    protected $filesystem;
    protected $layout;
    protected $financialAidFactory;
    protected $customer;
    /**
     * @var Repository
     */
    private $assetRepository;
     
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Perficient\FinancialAid\Helper\Email $email,
        \Psr\Log\LoggerInterface $loggerInterface,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        FinancialAidFactory $financialAidFactory,
        \Magento\Customer\Model\Session $customer,
        Repository $assetRepository,
        \Perficient\FinancialAid\Model\Config $financialAidConfig,
        array $data = []
    ) {
        $this->email = $email;
        $this->_logLoggerInterface = $loggerInterface;
        $this->messageManager = $context->getMessageManager();
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->layout = $layout;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->financialAidFactory = $financialAidFactory;
        $this->customer = $customer;
        $this->assetRepository = $assetRepository;
        $this->financialAidConfig = $financialAidConfig;

        parent::__construct($context);
    }
     
    public function execute()
    {
        $mediaPath = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
        if ($post = $this->getRequest()->getPost()) {
            $post = $this->getRequest()->getPost();
            $model = $this->financialAidFactory->create();
            $json  = json_encode($post);
            $arraydata = json_decode($json, true);

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productpdf = $this->layout->createBlock('\Perficient\FinancialAid\Block\View', "", ['data' => ['form_data' => $post,'consenttitle' => $this->getConsentTitle(),'consenttext' => $this->getConsentText()]])->setData('area', 'frontend')->setTemplate('Perficient_FinancialAid::pdf/view/form.phtml')->toHtml();

            $processor = $objectManager->create('Magento\Cms\Model\Template\FilterProvider');
            $finalpdf = $processor->getBlockFilter()->filter($productpdf);

            $dompdf = new Dompdf();
                        
            $dompdf->load_html($finalpdf);
            $dompdf->set_option('isRemoteEnabled', true);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $output = $dompdf->output();
            $time = date("Y-m-d");
            $name = str_replace([" "], '-', $post['fname'].$post['lname']);
            $stid = $post['studentid'];
            
            $pdfFile = file_put_contents($mediaPath . '/'.$name.'-'.$time.'-'.$stid.'.pdf', $output);

            $result = $this->resultJsonFactory->create();
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $downloadPath = $mediaPath.'/'.$name.'-'.$time.'-'.$stid.'.pdf';
            $arraydata['customer_id']=$this->customer->getId();
            $arraydata['pdf']='/media'.'/'.$name.'-'.$time.'-'.$stid.'.pdf';

            $model->setData($arraydata)->save();


        }
        try {
            // Send Mail
            $this->email->sendEmail($post);
            $this->messageManager->addSuccess('Form submitted successfully. A confirmation has been emailed to you.');
            $this->_redirect('financialaid/product/access/id/'.$arraydata['prid']);
                 
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_logLoggerInterface->debug($e->getMessage());
        }
    }
    /**
     * Get Image full path from view directory
     */
    public function getImageFullPath()
    {
        $fileId = 'Perficient_FinancialAid::images/logo.jpg';

        $params = [
            'area' => 'frontend' //for admin area its backend
        ];

        $asset = $this->assetRepository->createAsset($fileId, $params);

        $imageFullPath = null;
        try {
            $imageFullPath = $asset->getSourceFile();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $imageFullPath;
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
