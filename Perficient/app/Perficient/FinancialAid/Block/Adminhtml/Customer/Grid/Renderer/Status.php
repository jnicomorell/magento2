<?php
 
namespace Perficient\FinancialAid\Block\Adminhtml\Customer\Grid\Renderer;
 
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Helper\Image;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use Perficient\FinancialAid\Model\ResourceModel\FinancialAid\CollectionFactory;
use Magento\Framework\App\DeploymentConfig\Reader;

class Status extends AbstractRenderer
{
    private $_storeManager;
    private $imageHelper;
    private $collectionFactory;
    protected $_configReader;

    public function __construct(
        Context $context,
        Image $imageHelper,
        StoreManagerInterface $storemanager,
        CollectionFactory $collectionFactory,
        Reader $reader,
        array $data = []
    ) {
        $this->_storeManager = $storemanager;
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
        $this->imageHelper = $imageHelper;
        $this->collectionFactory = $collectionFactory;
        $this->_configReader = $reader;
    }
 
    public function render(DataObject $row)
    {
        $options = ["New","Sent","Approved","Denied","Pending","Voided"];

        $selectName = 'items[' . $row->getId() . '][' . $this->getColumn()->getId() . ']';
        $html = '<select name="' . $selectName . '" class="admin__control-select required-entry status_select" data-id="'.$row->getId().'" data-save-url="/'.$this->getAdminBaseUrl().'financialaid/index/save">';
        $value = $row->getStatus();
        $html .= '<option value=""></option>';
        foreach ($options as $label) {
            $selected = $label == $value && $value !== null ? ' selected="selected"' : '';
            $html .= '<option value="' . $label . '"' . $selected . '>' . $label . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
    public function getAdminBaseUrl()
    {
        $config = $this->_configReader->load();
        $adminSuffix = $config['backend']['frontName'];
        return $this->getBaseUrl() . $adminSuffix . '/';
    }
}
