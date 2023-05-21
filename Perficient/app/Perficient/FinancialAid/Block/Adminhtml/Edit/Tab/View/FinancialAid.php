<?php
 
namespace Perficient\FinancialAid\Block\Adminhtml\Edit\Tab\View;
 
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Registry;
use Perficient\FinancialAid\Model\ResourceModel\FinancialAid\CollectionFactory;
 
class FinancialAid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;
 
    protected $_collectionFactory;

    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }
 
    protected function _construct()
    {
        parent::_construct();
        $this->setId('view_custom_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }
    
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        $collection->addFieldToFilter('customer_id', ['in' => $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)]);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    
    protected function _prepareColumns()
    {

        $this->addColumn(
            'fname',
            [
                'header' => __('First Name'),
                'index' => 'fname',
            ]
        );
        $this->addColumn(
            'lname',
            [
                'header' => __('Last Name'),
                'index' => 'lname',
            ]
        );
        $this->addColumn(
            'email',
            [
                'header' => __('Email Address'),
                'index' => 'email',
            ]
        );
        $this->addColumn(
            'school',
            [
                'header' => __('School name'),
                'index' => 'school',
            ]
        );
        $this->addColumn(
            'isbn',
            [
                'header' => __('ISBN'),
                'index' => 'isbn',
            ]
        );
        $this->addColumn(
            'description',
            [
                'header' => __('Description'),
                'index' => 'description',
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created Date'),
                'index' => 'created_at',
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'sortable' => true,
                'index' => 'status',
                'renderer' => 'Perficient\FinancialAid\Block\Adminhtml\Customer\Grid\Renderer\Status'
            ]
        );
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'sortable' => true,
                'index' => 'pdf',
                'renderer' => 'Perficient\FinancialAid\Block\Adminhtml\Customer\Grid\Renderer\Pdf'
 
            ]
        );

        return parent::_prepareColumns();
    }
    
    public function getHeadersVisibility()
    {
        return $this->getCollection()->getSize() >= 0;
    }
    
    /**
     * Get Url to action
     *
     * @param  string $action action Url part
     * @return string
     */
    protected function _getControllerUrl($action = '')
    {
        return '*/*/' . $action;
    }

    /**
     * Retrieve row url
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $row->getPdf();
    }
}
