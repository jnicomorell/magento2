<?php
declare(strict_types=1);

namespace Ceg\Impo\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CollectTotals implements ObserverInterface
{
    /**
     * Handler for collect totals after event
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        //collect totals FOB
        $quote = $observer->getQuote();

        $fobTotal = 0;
        foreach ($quote->getAllItems() as $item) {
            $item->setFobSubtotal($item->getFobUnit() * $item->getQty());
            $item->save();
            $fobTotal += $item->getFobSubtotal();
        }

        $quote->setFobTotal($fobTotal);
    }
}
