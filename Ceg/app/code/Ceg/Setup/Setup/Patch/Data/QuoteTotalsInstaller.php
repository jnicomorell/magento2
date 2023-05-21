<?php

namespace Ceg\Setup\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteFactory;
use Ceg\Quote\Rewrite\Magento\Quote\Model\Quote as CegQuote;
use Magento\Sales\Model\Order;

class QuoteTotalsInstaller implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Order
     */
    protected $order;

    /**
     * QuoteTotalsInstaller constructor.
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param QuoteFactory                                      $quoteFactory
     * @param Order                                             $order
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        QuoteFactory $quoteFactory,
        Order $order
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->quoteFactory = $quoteFactory;
        $this->order = $order;
    }

    /**
     * @return QuoteTotalsInstaller|void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $quotes = $this->getClosedQuotes();

        foreach($quotes as $quote) {
            $orderFound = $this->order->loadByIncrementId($quote->getData('reserved_order_id'));
            $orderItems = $this->order->getAllItems();

            if (count($this->order->getAllVisibleItems()) == 0) {
                continue;
            }

            $productTaxes = $this->setProductTax($orderItems);
            $quoteItems =  $quote->getAllVisibleItems();
            $this->setQuoteTotals($quote, $quoteItems, $orderFound, $productTaxes);

        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    protected function getTax($productTaxes,$itemQuote)
    {
        return ( isset($productTaxes) )
                    ?  $productTaxes : $itemQuote;
    }
    /**
     * @return \Magento\Quote\Model\ResourceModel\Quote\Collection
     */
    protected function getClosedQuotes()
    {
        $lastUpdateDate = "2021-02-18 00:00:00";

        $quoteCollection = $this->quoteFactory->create();
        $quoteCollection->addFieldToSelect('*');
        $quoteCollection->addFieldToFilter('main_table.status', ['eq' => CegQuote::STATUS_CLOSED]);
        $quoteCollection->addFieldToFilter('main_table.updated_at', ['lteq' => $lastUpdateDate]);

        $quoteCollection->getSelect()
            ->joinLeft(
                ["order" => $quoteCollection->getTable('sales_order')],
                "main_table.reserved_order_id = order.increment_id",
                ["increment_id" => "order.increment_id"]
            )->columns(
                "order.increment_id"
            )->where("order.base_grand_total != main_table.base_grand_total");

        return $quoteCollection->load();
    }

    public static function setProductTax($orderItems)
    {
        $productTaxes = [];
        foreach($orderItems as $item) {
            if(!array_key_exists($item->getProductId(), $productTaxes )){
                $productTaxes[$item->getProductId()] = [
                    'tax_amount' => $item->getTaxAmount(),
                    'totalIncTaxes' => $item->getRowTotalInclTax()
                ];
            }
        }
        return $productTaxes;
    }

    public function setQuoteTotals($quote, $quoteItems, $orderFound, $productTaxes)
    {
        foreach($quoteItems as $itemQuote){
            $itemId = $itemQuote->getProductId();
            $taxAmount = $this->getTax($productTaxes[$itemId]['tax_amount'], $itemQuote->getTaxAmount());
            $totalTax = $this->getTax($productTaxes[$itemId]['totalIncTaxes'], $itemQuote->getRowTotalInclTax());
            $itemQuote->setTaxAmount($taxAmount);
            $itemQuote->setRowTotalInclTax($totalTax);
        }

        $orderTotal = $orderFound->getGrandTotal();
        $orderTaxAmount = $orderFound->getTaxAmount();
        $quote->setGrandTotal($orderTotal);
        $quote->setBaseGrandTotal($orderTotal);
        $quote->setSubtotal($orderTotal - $orderTaxAmount);
        $quote->setBaseSubtotal($orderTotal - $orderTaxAmount);
        $quote->setSubtotalWithDiscount($orderTotal - $orderTaxAmount);
        $quote->setBaseSubtotalWithDiscount($orderTotal - $orderTaxAmount);
        $quote->setFobTotal($orderFound->getFobTotal());
        $quote->save();
    }

    /**
     * @return array
     */
    public static function getDependencies():array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases():array
    {
        return [];
    }
}
