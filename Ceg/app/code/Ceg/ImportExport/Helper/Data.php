<?php
declare(strict_types=1);

namespace Ceg\ImportExport\Helper;

use Exception;
use DateTime;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;
use Ceg\Impo\Model\ImpoRepository;
use Magento\Customer\Api\AddressRepositoryInterface;

class Data extends AbstractHelper
{
    const ID_QUOTE = 'ID';
    const ID_ORDEN = 'ID Orden';
    const ORDER_DATE = 'Fecha Orden';
    const TERM_DATE = 'Fecha TyC';
    const ORDER_STATE = 'Estado Orden';
    const ORDER_TOTAL_NETO = 'Total Neto Orden';
    const ORDER_TOTAL_IMP = 'Total Imp Orden';
    const ORDER_TOTAL_C_IMP = 'Total c/Imp. Orden';
    const ORDER_TOTAL_FOB = 'FOB Total orden';
    const COMPANY_ID = 'ID Cliente';
    const RFC = 'RFC Cliente';
    const COMPANY_NAME = 'Razón Social Cliente';
    const COMPANY_EMAIL = 'Email Cliente';
    const ITEM_ID = 'ID Item';
    const PRODUCT_ID = 'ID Producto';
    const PRODUCT_CODE = 'Código Producto';
    const PRODUCT_SKU = 'SKU';
    const PRODUCT_NAME = 'Nombre Producto';
    const PRODUCT_IMAGE = 'Imagen Producto';
    const QTY = 'Cantidad de Paquetes';
    const QTY_UNIT_TOTAL = 'Cantidad de unidades';
    const PRICE_NETO = 'Precio Neto Unitario del paquete';
    const PRICE_IMP = 'Precio Imp Unitario del paquete';
    const PRICE_TOTAL = 'Precio Total Unitario del paquete';
    const TOTAL_NETO = 'Neto Total';
    const TAX_TOTAL = 'Tax Total';
    const TOTAL_C_IMP = 'Total c/Imp';
    const IMPORT_ID = 'ID Importación';

    const COST_ITEM = 'Costo Presupuestado';
    const FOB_ITEM = 'FOB unitario';
    const FOB_SUBTOTAL = 'FOB total paquetes';
    const CUS_PHONE = 'Customer Telefono';
    const SHP_STREET = 'Dirección Calle';
    const SHP_NUMBER = 'Dirección Número';
    const SHP_COLONIA = 'Dirección Colonia';
    const SHP_CITY = 'Dirección Ciudad';
    const SHP_POSTALCODE = 'Dirección Código Postal';
    const SHP_STATE = 'Dirección Estado';
    const SHP_INDADIC = 'Indicaciones Adicionales';

    const FILE_HEADERS = [
        self::ID_QUOTE,
        self::ID_ORDEN,
        self::ORDER_DATE,
        self::TERM_DATE,
        self::ORDER_STATE,
        self::ORDER_TOTAL_NETO,
        self::ORDER_TOTAL_IMP,
        self::ORDER_TOTAL_C_IMP,
        self::ORDER_TOTAL_FOB,
        self::COMPANY_ID,
        self::RFC,
        self::COMPANY_NAME,
        self::COMPANY_EMAIL,
        self::ITEM_ID,
        self::PRODUCT_ID,
        self::PRODUCT_CODE,
        self::PRODUCT_SKU,
        self::PRODUCT_NAME,
        self::PRODUCT_IMAGE,
        self::QTY,
        self::QTY_UNIT_TOTAL,
        self::PRICE_NETO,
        self::PRICE_IMP,
        self::PRICE_TOTAL,
        self::TOTAL_NETO,
        self::TAX_TOTAL,
        self::TOTAL_C_IMP,
        self::IMPORT_ID,

        self::COST_ITEM,
        self::FOB_ITEM,
        self::FOB_SUBTOTAL,
        self::CUS_PHONE,
        self::SHP_STREET,
        self::SHP_NUMBER,
        self::SHP_COLONIA,
        self::SHP_CITY,
        self::SHP_POSTALCODE,
        self::SHP_STATE,
        self::SHP_INDADIC
    ];

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $directory;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var string
     */
    private $dateToStringName;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ImpoRepository
     */
    protected $impoRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    protected $line = [];

    /**
     * ExportAction constructor.
     *
     * @param Context                     $context
     * @param CartRepositoryInterface     $cartRepository
     * @param SearchCriteriaBuilder       $searchCriteriaBuilder
     * @param Filesystem                  $filesystem
     * @param FileFactory                 $fileFactory
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param LoggerInterface             $logger
     * @param PriceCurrencyInterface      $priceCurrency
     * @param ProductRepositoryInterface  $productRepository
     * @param Image                       $imageHelper
     * @param ImpoRepository              $impoRepository
     * @param AddressRepositoryInterface  $addressRepository
     *
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        CartRepositoryInterface $cartRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Filesystem $filesystem,
        FileFactory $fileFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        LoggerInterface $logger,
        PriceCurrencyInterface $priceCurrency,
        ProductRepositoryInterface $productRepository,
        Image $imageHelper,
        ImpoRepository $impoRepository,
        AddressRepositoryInterface $addressRepository
    )
    {
        parent::__construct($context);

        $this->cartRepository = $cartRepository;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->fileFactory = $fileFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->priceCurrency = $priceCurrency;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->impoRepository = $impoRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @param $status
     *
     * @return int|ResponseInterface|void
     * @throws Exception
     */


    public function getExportFile($fromDate, $toDate, $status, $cegId)
    {
        $result = $this->getImpoData($fromDate, $toDate, $status, $cegId);

        if (empty($result)) {
            return 0;
        }

        $exportData = [];
        $resultItems = $result->getItems();

        foreach ($resultItems as $quote) {
            $quoteItems = $quote->getAllVisibleItems();

            if (!empty($quoteItems)) {
                try {
                    $customer = $this->customerRepositoryInterface->getById($quote->getCustomerId());

                    foreach ($quoteItems as $item) {

                        $this->createQuoteOrderStatus($quote);

                        $this->createOrderTotals($quoteItems, $quote->getFobTotal());

                        $this->createCustomerData($customer, $quote->getCustomerEmail());

                        $this->createProductData($item);

                        $this->createTaxData($item);

                        $this->line[self::IMPORT_ID] = (string)$item->getImportId();

                        $this->createFobData($item);

                        $this->createShippingData($quote);

                        $exportData[] = join(';', $this->line);
                    }
                } catch (NoSuchEntityException $e) {
                    $this->logger->critical(__('Customer in Quote not found: ' . $e->getMessage()));
                } catch (LocalizedException $e) {
                    $this->logger->critical(__('Customer in Quote not found: ' . $e->getMessage()));
                }
            }
        }

        return $this->writeAndDownloadCsv($exportData);
    }

    protected function getImpoData($fromDate, $toDate, $status, $cegId)
    {
        $status = !empty($status) ? $status : \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_APPROVED;
        $quoteFromDate = DateTime::createFromFormat('m/d/Y', $fromDate)->format('Y-m-d');
        $quoteToDate = DateTime::createFromFormat('m/d/Y', $toDate)->format('Y-m-d');
        $impoId = $this->getImpoIdByCegId($cegId);

        $this->dateToStringName = $quoteFromDate . '__' . $quoteToDate;

        $searchCriteriaBuilder = $this->searchCriteriaBuilder;
        if ($impoId > 0) {
            $searchCriteriaBuilder->addFilter('impo_ids', '%"' . $impoId . '"%', 'like');
        }
        $searchCriteriaBuilder->addFilter('status', $status);
        $searchCriteriaBuilder->addFilter('updated_at', $quoteFromDate . ' 00:00:00', 'gteq');
        $searchCriteriaBuilder->addFilter('updated_at', $quoteToDate . ' 23:59:59', 'lteq');
        $searchCriteria = $searchCriteriaBuilder->create();

        return $this->cartRepository->getList($searchCriteria);
    }

    protected function createQuoteOrderStatus($quote)
    {
        $this->line[self::ID_QUOTE] = $quote->getId() ?? '';
        $this->line[self::ID_ORDEN] = $quote->getReservedOrderId() ?? '';
        $this->line[self::ORDER_DATE] = $quote->getUpdatedAt() ?? '';
        $this->line[self::TERM_DATE] = $quote->getData('tos_at') ?? '';
        $this->line[self::ORDER_STATE] = $quote->getStatus() ?? '';
    }

    protected function createOrderTotals($quoteItems, $fobTotal)
    {
        $quoteTaxAmount = 0;
        $quoteTotalTaxAmount = 0;
        foreach ($quoteItems as $item) {
            $quoteTaxAmount += $item->getTaxAmount();
            $quoteTotalTaxAmount += $item->getRowTotalInclTax();
        }
        $baseTotal = $quoteTotalTaxAmount - $quoteTaxAmount;
        $this->line[self::ORDER_TOTAL_NETO] = number_format((float)$baseTotal, 2, '.', '');
        $this->line[self::ORDER_TOTAL_IMP] = number_format((float)$quoteTaxAmount, 2, '.', '');
        $this->line[self::ORDER_TOTAL_C_IMP] = number_format((float)$quoteTotalTaxAmount, 2, '.', '');
        $this->line[self::ORDER_TOTAL_FOB] = number_format((float)$fobTotal, 2, '.', '');
    }

    protected function createCustomerData($customer, $customerQuoteEmail)
    {
        $rfc = $customer->getCustomAttribute('rfc');
        $companyId = $customer->getCustomAttribute('company_id');
        $companyName = $customer->getCustomAttribute('company_name');

        $this->line[self::COMPANY_ID] = !empty($companyId) ? $companyId->getValue() : '';
        $this->line[self::RFC] = !empty($rfc) ? $rfc->getValue() : '';
        $this->line[self::COMPANY_NAME] = !empty($companyName) ? $companyName->getValue() : '';
        $this->line[self::COMPANY_EMAIL] = $customerQuoteEmail;

    }

    protected function createProductData($item)
    {
        $product = $this->productRepository->getById($item->getProductId());
        $urlImg = $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl();

        $this->line[self::ITEM_ID] = $item->getItemId() ?? '';
        $this->line[self::PRODUCT_ID] = $item->getProductId() ?? '';
        $this->line[self::PRODUCT_CODE] = $product->getModel() ?? '';
        $this->line[self::PRODUCT_SKU] = $item->getSku() ?? '';
        $this->line[self::PRODUCT_NAME] = str_replace([',', ';'], '', $item->getName()) ?? '';
        $this->line[self::PRODUCT_IMAGE] = $urlImg ?? '';
    }

    protected function createTaxData($item)
    {
        $unitTax = $item->getPriceInclTax() - $item->getPrice();
        $this->line[self::QTY] = $item->getQty() ?? '';
        $this->line[self::QTY_UNIT_TOTAL] = $item->getQty() * $item->getQtyinbox();
        $this->line[self::PRICE_NETO] = number_format((float)$item->getPrice(), 2, '.', '');
        $this->line[self::PRICE_IMP] = number_format((float)$unitTax, 2, '.', '');
        $this->line[self::PRICE_TOTAL] = number_format((float)$item->getPriceInclTax(), 2, '.', '');
        $this->line[self::TOTAL_NETO] = number_format((float)$item->getRowTotal(), 2, '.', '');
        $this->line[self::TAX_TOTAL] = number_format((float)$item->getTaxAmount(), 2, '.', '');
        $this->line[self::TOTAL_C_IMP] = number_format((float)$item->getRowTotalInclTax(), 2, '.', '');
    }

    protected function createFobData($item){
        $this->line[self::COST_ITEM] = number_format((float)$item->getBaseCost(), 2, '.', '');
        $this->line[self::FOB_ITEM] = number_format((float)$item->getFobUnit(), 2, '.', '');
        $this->line[self::FOB_SUBTOTAL] = number_format((float)$item->getFobSubtotal(), 2, '.', '');
    }

    protected function createShippingData($quote){
        $shippingAddressId = $quote->getShippingAddress();
        $telephone = $shippingAddressId->getTelephone();
        $street = $shippingAddressId->getStreet();
        $streetnumber = $shippingAddressId->getData('numero');
        $streetnumber = $streetnumber !== null ? $streetnumber : '';
        $colonia = $shippingAddressId->getData('colonia');
        $colonia = $colonia !== null ? $colonia : '';
        $city = $shippingAddressId->getCity();
        $postalcode = $shippingAddressId->getPostcode();
        $state = $shippingAddressId->getRegion();
        $adicionales = $shippingAddressId->getData('observaciones');
        $adicionales = $adicionales !== null ? $adicionales : '';

        $this->line[self::CUS_PHONE] = str_replace([',', ';'], '', $telephone) ?? '';
        $this->line[self::SHP_STREET] = str_replace([',', ';'], '', $street[0]) ?? '';
        $this->line[self::SHP_NUMBER] = str_replace([',', ';'], '', $streetnumber) ?? '';
        $this->line[self::SHP_COLONIA] = str_replace([',', ';'], '', $colonia) ?? '';
        $this->line[self::SHP_CITY] = str_replace([',', ';'], '', $city) ?? '';
        $this->line[self::SHP_POSTALCODE] = str_replace([',', ';'], '', $postalcode) ?? '';
        $this->line[self::SHP_STATE] = str_replace([',', ';'], '', $state) ?? '';
        $this->line[self::SHP_INDADIC] = str_replace([',', ';'], '', $adicionales) ?? '';

    }
    /**
     * @param $exportData
     * @return int|ResponseInterface|void
     * @throws Exception
     */
    private function writeAndDownloadCsv($exportData)
    {
        try {
            $filepath = 'export/quotes_'.$this->dateToStringName.'.csv';
            $this->directory->create('export');

            $stream = $this->directory->openFile($filepath, 'w+');
            $stream->lock();

            $stream->writeCsv(self::FILE_HEADERS, ';');
            foreach ($exportData as $exportDatum) {
                $stream->write($exportDatum);
                $stream->write(PHP_EOL);
            }
            $content = [];
            $content['type'] = 'filename';
            $content['value'] = $filepath;
            $content['rm'] = 1;

            return $this->fileFactory->create($filepath, $content, DirectoryList::VAR_DIR);
        } catch (FileSystemException $e) {
            $this->logger->critical(__('Something went wrong while with the file: ' . $e->getMessage()));
        }
    }

    /**
     * @param $cegId
     * @return int
     */
    protected function getImpoIdByCegId($cegId)
    {
        $impoId = 0;
        if (!empty($cegId)) {
            try {
                $impo = $this->impoRepository->getByCegId($cegId);
                $impoId = (int)$impo->getEntityId();
            } catch (NoSuchEntityException $e) {
                $this->logger->critical(__($e->getMessage()));
            }
        }

        return $impoId;
    }
}
