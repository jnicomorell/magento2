<?php
namespace Ceg\Analytics\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Ceg\Analytics\Block\Adminhtml\Form\Field\EventColumn;
use Ceg\Analytics\Block\Adminhtml\Form\Field\CategoryColumn;
/**
 * Class Events
 */
class Events extends AbstractFieldArray
{
    /**
     * @var EventColumn
     */
    private $eventRenderer;

    /**
     * @var CategoryColumn
     */
    private $categoryRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('event', [
            'label' => __('Event'),
            'renderer' => $this->getEventRenderer()
        ]);
        $this->addColumn('category', [
            'label' => __('Category'),
            'renderer' => $this->getCategoryRenderer()
        ]);
        $this->addColumn('action_ga', ['label' => __('GA Action'), 'class' => 'required-entry']);
        $this->addColumn('to_class', ['label' => __('Class')]);
        $this->addColumn('label', ['label' => __('Label')]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $tax = $row->getTax();
        if ($tax !== null) {
            $options['option_' . $this->getTaxRenderer()->calcOptionHash($tax)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return TaxColumn
     * @throws LocalizedException
     */
    private function getEventRenderer()
    {
        if (!$this->eventRenderer) {
            $this->eventRenderer = $this->getLayout()->createBlock(
                EventColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->eventRenderer;
    }

    private function getCategoryRenderer()
    {
        if (!$this->categoryRenderer) {
            $this->categoryRenderer = $this->getLayout()->createBlock(
                CategoryColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->categoryRenderer;
    }
}
