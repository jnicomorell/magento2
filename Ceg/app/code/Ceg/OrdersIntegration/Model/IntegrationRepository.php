<?php
declare(strict_types=1);

namespace Ceg\OrdersIntegration\Model;

use Ceg\Impo\Api\ImpoRepositoryInterfaceFactory;
use Ceg\OrdersIntegration\Api\IntegrationRepositoryInterface;
use Ceg\OrdersIntegration\Api\QueueRepositoryInterfaceFactory;
use Ceg\OrdersIntegration\Helper\DataFactory as HelperFactory;
use Ceg\OrdersIntegration\Helper\Data as Helper;
use Ceg\OrdersIntegration\Model\Integration\Provider;
use Ceg\OrdersIntegration\Api\Data\Integration\ResultInterfaceFactory;
use Ceg\OrdersIntegration\Api\Data\Integration\ResultInterface;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Customer\Api\CustomerRepositoryInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Datetime;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IntegrationRepository implements IntegrationRepositoryInterface
{
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const DEFAULT_STATE = 'processing';

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var QueueRepositoryInterfaceFactory
     */
    protected $queueRepoFactory;

    /**
     * @var CustomerRepositoryInterfaceFactory
     */
    protected $cusRepoFactory;

    /**
     * @var ProductRepositoryInterfaceFactory
     */
    protected $prodRepoFactory;

    /**
     * @var ImageFactory
     */
    protected $imageHelperFactory;

    /**
     * @var ImpoRepositoryInterfaceFactory
     */
    protected $impoRepoFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Provider
     */
    protected $provider;

    /**
     * @var ResultInterfaceFactory
     */
    protected $resultFactory;

    /**
     * @var $timeZone
     */
    protected $timeZone;

    /**
     * @param HelperFactory                      $helperFactory
     * @param QueueRepositoryInterfaceFactory    $queueRepoFactory
     * @param CustomerRepositoryInterfaceFactory $cusRepoFactory
     * @param ProductRepositoryInterfaceFactory  $prodRepoFactory
     * @param ImageFactory                       $imageHelperFactory
     * @param ImpoRepositoryInterfaceFactory     $impoRepoFactory
     * @param Json                               $json
     * @param Provider                           $provider
     * @param ResultInterfaceFactory             $resultFactory
     * @param TimezoneInterface                  $timeZone
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        HelperFactory $helperFactory,
        QueueRepositoryInterfaceFactory $queueRepoFactory,
        CustomerRepositoryInterfaceFactory $cusRepoFactory,
        ProductRepositoryInterfaceFactory $prodRepoFactory,
        ImageFactory $imageHelperFactory,
        ImpoRepositoryInterfaceFactory $impoRepoFactory,
        Json $json,
        Provider $provider,
        ResultInterfaceFactory $resultFactory,
        TimezoneInterface $timeZone
    ) {
        $this->helper = $helperFactory->create();
        $this->queueRepoFactory = $queueRepoFactory;
        $this->cusRepoFactory = $cusRepoFactory;
        $this->prodRepoFactory = $prodRepoFactory;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->impoRepoFactory = $impoRepoFactory;
        $this->json = $json;
        $this->provider = $provider;
        $this->resultFactory = $resultFactory;
        $this->timeZone = $timeZone;
    }

    /**
     * {@inheritdoc}
     */
    public function sendOrder($quote)
    {
        return $this->process($quote, null, false);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteOrder($quote)
    {
        return $this->process($quote, null, true);
    }

    /**
     * {@inheritdoc}
     */
    public function resendOrder($queue, $quote)
    {
        $isDelete = ($queue->getAction() == self::ACTION_DELETE);
        return $this->process($quote, $queue, $isDelete);
    }

    /**
     * {@inheritdoc}
     */
    public function setFinalQuantityBulk($orders)
    {
        $result = $this->resultFactory->create();
        $hasSuccess = false;
        $hasError = false;
        foreach ($orders as $order) {
            try {
                $itemResult = $order->processQuantity();
                if ($itemResult->isSuccess()) {
                    $result->setSuccessStatus();
                    $hasSuccess = true;
                }
                if ($itemResult->isError()) {
                    $result->addMessage($itemResult->getMessages());
                    $result->setErrorStatus();
                    $hasError = true;
                }
            } catch (\Exception $exception) {
                $message = __('Could not save the Order # %1: $2', $order->getOrderId(), $exception->getMessage());
                $result->addMessage($message);
                $result->setErrorStatus();
                $hasError = true;
            }
        }

        if ($hasError && $hasSuccess) {
            $result->setPartialSuccessStatus();
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setFinalQuantity($order)
    {
        return $order->processQuantity();
    }

    /**
     * @param $quote
     * @param $queue
     * @param $isDelete
     * @return ResultInterface
     */
    public function process($quote, $queue, $isDelete)
    {
        if ($queue === null) {
            $queueRepository = $this->queueRepoFactory->create();
            $queue = $queueRepository->getByQuoteId($quote->getId());
        }

        $action = $this->getAction($queue, $isDelete);
        $result = $this->send($quote, $action);

        $queueRepository = $this->queueRepoFactory->create();
        $queue->setQuoteId($quote->getId());
        $queue->setAction($action);
        $queue->setStatus(($result->isError() ? Queue::STATUS_PENDING : Queue::STATUS_SENDED));
        $queue->setMessage($result->getMessage());
        $queueRepository->save($queue);

        return $result;
    }

    /**
     * @param $quote
     * @param $action
     * @return ResultInterface
     */
    public function send($quote, $action)
    {
        $result = $this->resultFactory->create();
        if (!$this->helper->isSendEnabled()) {
            $result->setErrorStatus();
            $result->addMessage('Send disabled');
            return $result;
        }

        try {
            $apiUrl = $this->getActionUrl($action, $quote->getStoreId());
            $data = $this->getActionRequestData($action, $quote);
            $this->provider->send($apiUrl, $data, $quote->getStoreId());
            $result->setSuccessStatus();

        } catch (\Exception $exception) {
            $result->setErrorStatus();
            $result->addMessage($exception->getMessage());
        }
        return $result;
    }

    /**
     * @param $queue
     * @param $isDelete
     * @return string
     */
    public function getAction($queue, $isDelete)
    {
        if ($isDelete) {
            return self::ACTION_DELETE;
        }
        if ($queue->getId()) {
            if ($queue->getStatus() == Queue::STATUS_PENDING) {
                return $queue->getAction();
            }
            if ($queue->getStatus() == Queue::STATUS_SENDED) {
                return self::ACTION_UPDATE;
            }
        }
        return self::ACTION_CREATE;
    }

    /**
     * @param $action
     * @param $storeId
     * @return mixed|string
     */
    public function getActionUrl($action, $storeId)
    {
        switch ($action) {
            case self::ACTION_CREATE:
            case self::ACTION_UPDATE:
            case self::ACTION_DELETE:
                $url = $this->helper->getCreateApiUrl($storeId);
                break;
            default:
                $url = '';
        }
        return $url;
    }

    /**
     * @param $action
     * @param $quote
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getActionRequestData($action, $quote)
    {
        switch ($action) {
            case self::ACTION_DELETE:
            case self::ACTION_CREATE:
            case self::ACTION_UPDATE:
            default:
                $data = $this->createData($quote, $action);
        }
        return $data;
    }

    protected function createData($quote, $action)
    {
        $shippingAddress = $quote->getShippingAddress();
        $countryCode = strtolower($shippingAddress->getCountryId());

        $street = $shippingAddress->getStreet()[0] ?? ' ';
        $streetnumber = $shippingAddress->getData('numero');
        $street .= $streetnumber !== null ? ' '.$streetnumber : '';

        $colonia = $shippingAddress->getData('colonia');
        $colonia = $colonia !== null ? $colonia : '';

        $city = $shippingAddress->getCity();
        $postalcode = $shippingAddress->getPostcode();
        $state = $shippingAddress->getRegion();

        $adicionales = $shippingAddress->getData('observaciones');
        $adicionales = $adicionales !== null ? $adicionales : '';

        $stringAddress = $street .', '. $colonia;
        $stringAddress .= ', '.$city.', '.$postalcode.', '.$state.' - ';
        $stringAddress .= $adicionales;

        $customerRepository = $this->cusRepoFactory->create();
        $customer = $customerRepository->getById($quote->getCustomerId());

        $companyId = is_object($attr = $customer->getCustomAttribute('company_id')) ? $attr->getValue() : '' ;
        $companyTin = is_object($attr = $customer->getCustomAttribute('rfc')) ? $attr->getValue() : '' ;
        $companyName = is_object($attr =  $customer->getCustomAttribute('company_name'))? $attr->getValue() :'';
        $dateCreated = $this->formatDate($quote->getCreatedAt(), 'Y-m-d\TH:i:s\Z');
        return [
            'order_code' => $quote->getReservedOrderId() ?? '',
            'company_id' => (int)$companyId,
            'company_tin' => $companyTin,
            'company_name' => $companyName,
            'country_code' => $countryCode,
            'imported_at' => $dateCreated,
            'date_created' =>  $this->formatDate($quote->getTosAt(), 'Y-m-d\TH:i:s\Z'),
            'state' => self::DEFAULT_STATE,
            'delivery_address' => $stringAddress,
            'receiver_name' => $shippingAddress->getLastName() .', '. $shippingAddress->getFirstName(),
            'products' => $this->getProductImpoData($quote, $action)
        ];
    }

    /**
     * @param $quote
     * @return array
     * @throws NoSuchEntityException
     */
    public function getImposData($quote)
    {
        $item = [];
        $quoteImpoIds = $quote->getImpoIds() ? $quote->getImpoIds() : '[]';
        $impoIds = $this->json->unserialize($quoteImpoIds);
        $impoRepository = $this->impoRepoFactory->create();
        foreach ($impoIds as $impoId) {
            $impo = $impoRepository->getById($impoId);
            $item = [
                'impo_code' => $impo->getCegId(),
                'ceg_id' => $impo->getCegId(),
                'init_date' => $this->formatDate($impo->getStartAt(), 'Y-m-d\TH:i:s\Z'),
                'end_date' => $this->formatDate($impo->getFinishAt(), 'Y-m-d\TH:i:s\Z'),
                'fob' => (int)$impo->getFreeOnBoard(),
            ];
        }
        return $item ?? [];
    }

    /**
     * @param $quote
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getProductImpoData($quote, $action)
    {
        if ($action === self::ACTION_DELETE) {
            return [];
        }

        $result = [];
        $quoteItems = $quote->getAllVisibleItems();

        if (!empty($quoteItems)) {
            $productRepository = $this->prodRepoFactory->create();
            $imageHelper = $this->imageHelperFactory->create();
            foreach ($quoteItems as $productItem) {
                $product = $productRepository->getById($productItem->getProductId());
                $urlImg = $imageHelper->init($product, 'product_thumbnail_image')->getUrl();
                $productCode = $product->getModel();

                $item = [
                    'item_id' => (int)$productItem->getItemId() ?? '',
                    'product_id' => (int)$productItem->getProductId() ?? '',
                    'product_code' => $productCode ?? '',
                    'sku' => $productItem->getSku() ?? '',
                    'name' => $productItem->getName() ?? '',
                    'image_url' => $urlImg ?? '',
                    'quantity' => (int)$productItem->getQty() ?? '',
                    'quantity_package' => (int)$productItem->getQtyinbox() ?? '',
                    'unit_price_package' => (int)$productItem->getBaseCost() ?? '',
                    'unit_tax_price_package' => (int)$productItem->getPriceInclTax() ?? '',
                    'unit_fob_price_package' => (int)$productItem->getFobUnit() ?? '',
                    'state' => self::DEFAULT_STATE,
                    'impo' => $this->getImposData($quote),
                ];
                array_push($result, $item);
            }

        }

        return $result;
    }

    /**
     * @param $date
     * @param $format
     *
     * @return string
     * @throws \Exception
     */
    protected function formatDate($date, $format)
    {
        return $this->timeZone->date(new DateTime($date))->format($format);
    }
}
