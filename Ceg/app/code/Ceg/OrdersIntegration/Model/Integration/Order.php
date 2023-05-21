<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Model\Integration;

use Ceg\OrdersIntegration\Api\Data\Integration\OrderInterface;
use Ceg\OrdersIntegration\Api\Data\Integration\ResultInterfaceFactory;
use Ceg\OrdersIntegration\Api\Data\Integration\ResultInterface;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Order extends AbstractModel implements OrderInterface
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var ResultInterfaceFactory
     */
    protected $resultFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param QuoteFactory $quoteFactory
     * @param ResultInterfaceFactory $resultFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        QuoteFactory $quoteFactory = null,
        ResultInterfaceFactory $resultFactory = null
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->quoteFactory = $quoteFactory ?? ObjectManager::getInstance()->get(QuoteFactory::class);
        $this->resultFactory = $resultFactory ?? ObjectManager::getInstance()->get(ResultInterfaceFactory::class);
    }

    /**
     * @inheritdoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setOrderId($value)
    {
        $this->setData(self::ORDER_ID, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProducts()
    {
        return $this->getData(self::PRODUCTS);
    }

    /**
     * @inheritdoc
     */
    public function setProducts($value)
    {
        $this->setData(self::PRODUCTS, $value);
        return $this;
    }

    public function processQuantity()
    {
        $result = $this->resultFactory->create();
        try {
            $reservedOrderId = $this->getOrderId();
            $products = $this->getProducts();

            $quote = $this->quoteFactory->create();
            $quote->load($reservedOrderId, 'reserved_order_id');
            $quoteItems = $quote->getAllItems();
            foreach ($products as $product) {
                foreach ($quoteItems as $quoteItem) {
                    if ($quoteItem->getItemId() == $product->getItemId()) {
                        $quoteItem->setQty($product->getQuantity());
                    }
                }
            }
            $quote->setItems($quoteItems);
            $quote->close();
            $quote->save();
            $quote->collectTotals();
            $result->setSuccessStatus();

        } catch (\Exception $exception) {
            $result->setErrorStatus();
            $result->addMessage($exception->getMessage());
        }
        return $result;
    }
}
