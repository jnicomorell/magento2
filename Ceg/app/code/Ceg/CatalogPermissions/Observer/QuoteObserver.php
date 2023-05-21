<?php
declare(strict_types=1);

namespace Ceg\CatalogPermissions\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ceg\CatalogPermissions\Helper\Data as Helper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class QuoteObserver implements ObserverInterface
{
    /**
     * Helper
     *
     * @var Helper
     */
    protected $_helper;

    /**
     * Initialize observer
     *
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Handler for product salable event
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helper->isAvailableAddToCart()) {
            throw new LocalizedException(
                __('You can not add products to cart.')
            );
        }
    }
}
