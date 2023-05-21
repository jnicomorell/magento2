<?php
declare(strict_types=1);

namespace Ceg\ImportExport\Controller\Adminhtml\Import;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\Exception;
use Ceg\ImportExport\Helper\Data as ColumnsData;
use Ceg\Quote\Rewrite\Magento\Quote\Model\Quote\Item;
use Magento\Backend\App\Action as MagentoAction;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Psr\Log\LoggerInterface;

class Action extends MagentoAction implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var LocalizedException
     */
    protected $localizedException;

    /**
     * @var Exception
     */
    protected $exception;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var FilterGroup
     */
    private $filterGroup;

    /**
     * @var SearchCriteriaInterface
     */
    private $searchCriteria;

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
     * @var Csv
     */
    private $csv;

    /**
     * @var Int
     */
    protected $quotesEdited;

    /**
     * ExportAction constructor.
     * @param Context $context
     * @param LocalizedException $localizedException
     * @param Exception $exception
     * @param PageFactory $resultPageFactory
     * @param Csv $csv
     * @param CartRepositoryInterface $cartRepository
     * @param Filter $filter
     * @param FilterGroup $filterGroup
     * @param SearchCriteriaInterface $searchCriteria
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        LocalizedException $localizedException,
        Exception $exception,
        PageFactory $resultPageFactory,
        Csv $csv,
        CartRepositoryInterface $cartRepository,
        Filter $filter,
        FilterGroup $filterGroup,
        SearchCriteriaInterface $searchCriteria,
        Filesystem $filesystem,
        FileFactory $fileFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->localizedException = $localizedException;
        $this->exception = $exception;
        $this->resultPageFactory = $resultPageFactory;
        $this->cartRepository = $cartRepository;
        $this->filter = $filter;
        $this->filterGroup = $filterGroup;
        $this->searchCriteria = $searchCriteria;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->fileFactory = $fileFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->csv = $csv;
    }

    /**
     * Index action
     * @throws Exception
     */
    public function execute()
    {
        try {
            $csv = $this->csv;
            $csv->setDelimiter(';');
            $files = $this->getRequest()->getFiles();

            $csvData = $this->getCsvData($files, $csv);

            $quote = null;
            $quoteItems = [];
            $this->quotesEdited = 0;
            array_shift($csvData); // remove headers row
            foreach ($csvData as $row => $data) {
                $quoteIdIdx = array_search(ColumnsData::ID_QUOTE, ColumnsData::FILE_HEADERS);
                $quoteItemQtyIdx = array_search(ColumnsData::QTY, ColumnsData::FILE_HEADERS);
                $quoteItemIdIdx = array_search(ColumnsData::ITEM_ID, ColumnsData::FILE_HEADERS);

                $this->validateCsvRow($row, $data);

                $quoteId = $data[$quoteIdIdx];
                $quoteItemQty = $data[$quoteItemQtyIdx];
                $quoteItemId = $data[$quoteItemIdIdx];

                // process last quote before next quote is loaded
                $this->processQuote($quote, $quoteId, $quoteItems);

                // load new quote
                if (!is_object($quote) || ((int)$quote->getId() != (int)$quoteId)) {
                    $quote = $this->cartRepository->get($quoteId);
                    $quoteItems = $quote->getAllVisibleItems();
                }

                // process only approved quotes
                if (!$quote->isApproved()) {
                    continue;
                }

                // process quote items
                $quoteItems = $this->processQuoteItems($quote, $quoteItems, $quoteItemId, $quoteItemQty);
                $quote->setItems($quoteItems);
            }
            // quoteId + 1 ensures that last quote will be processed
            $this->processQuote($quote, $quoteId + 1, $quoteItems);

            $this->messageManager
                ->addSuccessMessage(__('%1 orders have been processed and marked as "closed".', $this->quotesEdited));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $this->_redirect->redirect($this->_response, '*/*');
    }

    /**
     * @param $files
     * @param $csv
     *
     * @return mixed
     * @throws LocalizedException
     */
    protected function getCsvData($files, $csv)
    {
        if (!$files['quote_file']['tmp_name']) {
            throw new LocalizedException('You need to select a file!');
        }

        $csvData = $csv->getData($files['quote_file']['tmp_name']);

        $rows = count($csvData);
        if ($rows <= 0) {
            throw new LocalizedException('File is empty.');
        }

        return $csvData;
    }

    /**
     * @param $row
     * @param $data
     *
     * @throws LocalizedException
     */
    protected function validateCsvRow($row, $data)
    {
        if (count($data) <= 1) {
            $msg = __('Wrong field delimiter. You need to use a semicolon as delimiter.');
            throw new LocalizedException($msg);
        }

        if (count($data) != count(ColumnsData::FILE_HEADERS)) {
            $msg = __('The number of columns received in row %1 does not match what is expected.', $row);
            throw new LocalizedException($msg);
        }
    }

    protected function processQuoteItems($quote, $quoteItems, $quoteItemId, $quoteItemQty)
    {
        foreach ($quoteItems as $idx => $quoteItem) {
            /** @var $quoteItem Item */
            if ($quoteItem->getItemId() == $quoteItemId) {
                if ($quoteItemQty > 0) {
                    $quoteItem->setFobSubtotal($quoteItemQty * $quoteItem->getFobUnit());
                    $quoteItem->setQty($quoteItemQty);
                }
                if ($quoteItemQty == 0) {
                    unset($quoteItems[$idx]);
                    $quote->deleteItem($quoteItem);
                }
            }
        }
        return $quoteItems;
    }

    /**
     * @param $quote
     * @param $quoteId
     * @param $quoteItems
     */
    protected function processQuote($quote, $quoteId, $quoteItems)
    {
        $quoteFobTotal = 0;
        // reworked due poor performance code, it was loading the whole quote for every item
        if (is_object($quote) && ((int)$quote->getId() != (int)$quoteId) && $quote->isApproved()) {
            // enters here after first quote on every first item, closes previous quote if already approved
            if (empty($quoteItems)) {
                $quote->cancel();
                return;
            }
            foreach ($quoteItems as $item) {
                $quoteFobTotal += $item->getFobSubtotal();
            }
            $quote->setFobTotal($quoteFobTotal);
            $this->cartRepository->save($quote);
            $quote->collectTotals()->save();
            $quote->close();
            $this->quotesEdited++;
        }
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _isAllowed()
    {
        return true;
    }
}
