<?php
 
namespace Perficient\FinancialAid\Block\Adminhtml\Customer\Grid\Renderer;
 
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Helper\Image;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use Perficient\FinancialAid\Model\ResourceModel\FinancialAid\CollectionFactory;
 
class Pdf extends AbstractRenderer
{
    private $_storeManager;
    private $imageHelper;
    private $collectionFactory;
 
    public function __construct(
        Context $context,
        Image $imageHelper,
        StoreManagerInterface $storemanager,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_storeManager = $storemanager;
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
        $this->imageHelper = $imageHelper;
        $this->collectionFactory = $collectionFactory;
    }
 
    public function render(DataObject $row)
    {
        if ($row->getPdf()) {
            $link = '<a href="'.$row->getPdf().'" download>Download Pdf</a>';
        } else {
            $link = '';
        }
        return $link;
    }
}
