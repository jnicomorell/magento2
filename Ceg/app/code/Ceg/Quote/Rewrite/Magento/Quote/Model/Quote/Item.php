<?php
declare(strict_types=1);

namespace Ceg\Quote\Rewrite\Magento\Quote\Model\Quote;

use Magento\Quote\Model\Quote\Item as MagentoQuoteItem;

class Item extends MagentoQuoteItem
{
    /**
     * @param float $qty
     * @param bool $validate
     * @return $this|Item
     */
    public function setQty($qty)
    {
        $qty = $this->_prepareQty($qty);
        $oldQty = $this->_getData(self::KEY_QTY);
        $this->setData(self::KEY_QTY, $qty);

        if (!empty($this->getQuote())) {
            if ($this->getQuote()->getStatus() !== \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_CLOSED) {
                $this->_eventManager->dispatch('sales_quote_item_qty_set_after', ['item' => $this]);
            }
        }

        if ($this->getQuote() && $this->getQuote()->getIgnoreOldQty()) {
            return $this;
        }

        if ($this->getUseOldQty()) {
            $this->setData(self::KEY_QTY, $oldQty);
        }

        return $this;
    }
}
