<?php
namespace Ceg\Auth0Integration\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Ceg\Auth0Integration\Block\Adminhtml\Form\Field\CountryColumns;

/**
 * Class Ranges
 */
class Countries extends AbstractFieldArray
{

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('website_code', ['label' => __('Website Code'), 'class' => 'required-entry']);
        $this->addColumn('auth0_code', ['label' => __('Auth0 Code'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
