<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ceg\Quote\Rewrite\Magento\Sales\Model\ResourceModel\Collection;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;

class ExpiredQuotesCollection extends \Magento\Sales\Model\ResourceModel\Collection\ExpiredQuotesCollection
{
    /**
     * @var int
     */
    private $secondsInDay = 86400;

    /**
     * @var string
     */
    private $quoteLifetime = 'checkout/cart/delete_quote_after';

    /**
     * @var string
     */
    private $lifetimeApproved = 'checkout/cart/delete_quote_approved_after';

    /**
     * @var CollectionFactory
     */
    private $quoteCollFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param ScopeConfigInterface $config
     * @param CollectionFactory $quoteCollFactory
     */
    public function __construct(
        ScopeConfigInterface $config,
        CollectionFactory $quoteCollFactory
    ) {
        $this->config = $config;
        $this->quoteCollFactory = $quoteCollFactory;
    }

    /**
     * Gets expired quotes
     *
     * Quote is considered expired if the latest update date
     * of the quote is greater than lifetime threshold
     *
     * @param StoreInterface $store
     * @return AbstractCollection
     */
    public function getExpiredQuotes(StoreInterface $store): AbstractCollection
    {
        $lifetime = $this->config->getValue(
            $this->quoteLifetime,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
        $lifetime *= $this->secondsInDay;

        /** @var $quotes Collection */
        $quotes = $this->quoteCollFactory->create();
        $quotes->addFieldToFilter('store_id', $store->getId());
        $quotes->addFieldToFilter('updated_at', ['to' => date("Y-m-d", time() - $lifetime)]);

        return $quotes;
    }

    /**
     * Gets approved quotes
     *
     * Quote is considered approved or closed if the latest update date
     * of the quote is greater than lifetime threshold
     *
     * @param StoreInterface $store
     * @return AbstractCollection
     */
    public function getApprovedQuotes(StoreInterface $store): AbstractCollection
    {
        $lifetime = $this->config->getValue(
            $this->lifetimeApproved,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
        $lifetime *= $this->secondsInDay;

        /** @var $quotes Collection */
        $quotes = $this->quoteCollFactory->create();
        $quotes->addFieldToFilter('store_id', $store->getId());
        $quotes->addFieldToFilter(
            ['status'],
            [
            ['eq' => "approved"],
            ['eq' => 'closed']
            ]
        );
        $quotes->addFieldToFilter('updated_at', ['to' => date("Y-m-d", time() - $lifetime)]);

        return $quotes;
    }
}
