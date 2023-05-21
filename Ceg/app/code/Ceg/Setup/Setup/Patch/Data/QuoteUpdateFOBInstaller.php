<?php

namespace Ceg\Setup\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteFactory;
use Ceg\Quote\Rewrite\Magento\Quote\Model\Quote as CegQuote;
use Psr\Log\LoggerInterface;

class QuoteUpdateFOBInstaller implements DataPatchInterface
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
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        QuoteFactory $quoteFactory,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->quoteFactory = $quoteFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $closedQuotes = $this->getClosedQuotes();

        $itemsWithBaseCost = [];

        foreach ($closedQuotes as $quote) {

            $items = $quote->getAllVisibleItems();

            $fobTotal = 0;

            foreach ($items as $item) {

                $itemCost = $item->getBaseCost();

                if (isset($itemCost)) {
                    if (!array_key_exists($item->getProductId(), $itemsWithBaseCost)) {
                        $itemsWithBaseCost[$item->getProductId()] = $itemCost;
                    }
                }

                $fobUnit = ( isset($itemsWithBaseCost[$item->getProductId()]) )
                    ? $itemsWithBaseCost[$item->getProductId()] : $item->getProduct()->getPrice();
                $fobSubtotal = ($fobUnit * $item->getQty());
                $fobTotal += $fobSubtotal;
                $item->setFobUnit($fobUnit);
                $item->setFobSubtotal($fobSubtotal);

            }
            $quote->setFobTotal($fobTotal);
            $quote->save();
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    protected function getClosedQuotes()
    {
        $lastUpdateDate = "2021-04-21 00:00:00";

        $quoteCollection = $this->quoteFactory->create();
        $quoteCollection->addFieldToSelect('*');
        $quoteCollection->addFieldToFilter('main_table.status', ['eq' => CegQuote::STATUS_CLOSED]);
        $quoteCollection->addFieldToFilter('main_table.updated_at', ['lteq' => $lastUpdateDate]);

        return $quoteCollection->load();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
