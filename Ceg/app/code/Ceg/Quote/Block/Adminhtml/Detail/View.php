<?php
declare(strict_types=1);

namespace Ceg\Quote\Block\Adminhtml\Detail;

use Ceg\Quote\ViewModel\QuoteData;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class View extends Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Ceg_Quote';

    /**
     * @var QuoteData
     */
    protected $quoteData = null;

    /**
     * @param Context $context
     * @param array $data
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->quoteData = $data['quote_data'];
        parent::__construct($context, $data);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_objectId = 'entity_id';
        $this->_controller = 'adminhtml_detail';
        $this->_mode = 'view';

        parent::_construct();

        $this->removeButton('delete');
        $this->removeButton('reset');
        $this->removeButton('save');
        $this->setId('quote_view_detail');

        $quote = $this->quoteData->getQuote();
        if (!$quote) {
            return;
        }

        $isAllowedAction = $this->_authorization->isAllowed('Ceg_Core::quote_cancel');
        if ($isAllowedAction && $quote->isApproved()) {
            $url = parent::getUrl('quote/view/cancel', ['id' => $quote->getId()]);
            $this->addButton(
                'quote_cancel',
                [
                    'label' => __('Cancel'),
                    'class' => 'save primary',
                    'id' => 'quote-view-cancel-button',
                    'onclick' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $url . '\', {data: {}})'
                ]
            );
        }
    }
}
