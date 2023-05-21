<?php
declare(strict_types=1);

namespace Ceg\Quote\Block\Adminhtml\Detail\View;

use Ceg\Quote\ViewModel\QuoteData;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\DataObject;

class Items extends Template
{
    const COLUMN_KEY_TITLE = 'title';
    const COLUMN_KEY_CLASS = 'class';
    const COLUMN_KEY_FORMAT = 'format';

    /**
     * @var QuoteData
     */
    protected $quoteData = null;

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->quoteData = $data['quote_data'];
        parent::__construct($context, $data);
    }

    public function getItemsCollection()
    {
        $quote = $this->quoteData->getQuote();
        return $quote->getItemsCollection();
    }

    public function getColumns()
    {
        return array_key_exists('columns', $this->_data) ? $this->_data['columns'] : [];
    }

    public function getColumnTitle($column)
    {
        return array_key_exists(self::COLUMN_KEY_TITLE, $column) ? $column[self::COLUMN_KEY_TITLE] : '';
    }

    public function getColumnClass($column)
    {
        return array_key_exists(self::COLUMN_KEY_CLASS, $column) ? $column[self::COLUMN_KEY_CLASS] : '';
    }

    public function getColumnFormat($column)
    {
        return array_key_exists(self::COLUMN_KEY_FORMAT, $column) ? $column[self::COLUMN_KEY_FORMAT] : '';
    }

    public function getFieldHtml(DataObject $item, $columnName, $columnFormat)
    {
        $html = '';
        switch ($columnName) {
            case 'product':
                $elementId = substr('order_item_' . $item->getId() . '_', 0, -1);
                $html .= '<div id="' . $elementId . '">';
                $html .= $item->getData('name');
                $html .= '</div>';
                break;

            case 'qtyinbox':
                $html .= '<span>';
                $html .= $item->getData($columnName) . ' ' . __('Units');
                $html .= '</span>';
                break;

            case 'qty':
                $html .= '<span>';
                $html .= $item->getData($columnName) . ' ' . __('Ordered');
                $html .= '</span>';
                break;

            default:
                switch ($columnFormat) {
                    case 'price':
                        $html = $this->quoteData->formatPrice($item->getData($columnName));
                        break;

                    case 'price strong':
                        $html .= '<strong>';
                        $html .= $this->quoteData->formatPrice($item->getData($columnName));
                        $html .= '</strong>';
                        break;

                    default:
                        $html = $item->getData($columnName);
                }
        }
        return $html;
    }
}
