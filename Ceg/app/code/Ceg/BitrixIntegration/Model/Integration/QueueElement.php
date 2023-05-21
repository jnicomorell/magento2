<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Model\Integration;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ceg\BitrixIntegration\Logger\BitrixIntegrationLogger;

class QueueElement extends AbstractElement
{
    const CONFIG_API_IMPO_ENABLE = 'ceg_bitrixintegration/api/send/enable_impo';
    const CONFIG_API_ORDER_ENABLE = 'ceg_bitrixintegration/api/send/enable_order';
    const CONFIG_API_IMPO_USE_ACCESS_TOKEN = 'ceg_bitrixintegration/api/send/use_access_token';
    const CONFIG_API_IMPO_URL = 'ceg_bitrixintegration/api/send/impo_url';
    const CONFIG_API_URL = 'ceg_bitrixintegration/api/send/%type%_url';

    /**
     * @var String
     */
    protected $type;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * Logging instance
     * @var \Ceg\BitrixIntegration\Logger\BitrixIntegrationLogger
     */
    protected $bitrixIntegrationlogger;


    /**
     * @param ScopeConfigInterface  $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface     $timezoneInterface
     * @param LoggerInterfaceFactory $loggerFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TimezoneInterface $timezoneInterface,
        BitrixIntegrationLogger $bitrixIntegrationlogger
    ) {
        parent::__construct($scopeConfig, $storeManager);
        $this->timezoneInterface = $timezoneInterface;
        $this->bitrixIntegrationlogger = $bitrixIntegrationlogger;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getId()
    {
        return $this->model->getId();
    }

    public function getWebsiteId()
    {
        return $this->model->getWebsiteId();
    }

    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            constant('self::CONFIG_API_'.strtoupper($this->getType()).'_ENABLE'),
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $this->getWebsiteId()
        );
    }

    public function useAccessToken()
    {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_API_IMPO_USE_ACCESS_TOKEN,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $this->getWebsiteId()
        );
    }

    public function getUrl()
    {
        $configPath = str_replace('%type%', strtolower($this->getType()), self::CONFIG_API_URL);
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $this->getWebsiteId()
        );
    }

    public function getRequestData()
    {
        $request = 'get'.ucfirst(strtolower($this->getType())).'RequestData';
        return $this->{$request}();
    }

    public function getImpoRequestData()
    {
        $publicationId = $this->getPublicationId();
        $startAt = $this->getIsoFormat($this->model->getStartAt());
        $finishAt = $this->getIsoFormat($this->model->getFinishAt());
        $fob = (float)$this->model->getFreeOnBoard();
        $title = $this->model->getTitle();
        $cegId = $this->model->getCegId();
        $countryCode = $this->getCountryCode();
        $open = $this->isOpen();

        return [
            'store_publication_id' => $publicationId,
            'start_datetime' => $startAt,
            'end_datetime' => $finishAt,
            'objective_fob' => $fob,
            'title' => $title,
            'ceg_id' => $cegId,
            'open' => $open,
            'country' => $countryCode
        ];
    }

    public function getOrderRequestData()
    {
        $email = $this->model->getCustomerEmail();

        $countryCode = $this->model->getStore()->getCode();
        $codeSplit = explode('_', $countryCode);
        $country = strtoupper($codeSplit[0]);

        $contactId = "No existe el contactId";
        if ($this->model->getCustomer()->getCustomAttribute('contact_id')) {
            $contactId = $this->model->getCustomer()->getCustomAttribute('contact_id')->getValue();
        }

        $companyId = "No existe el companyId";
        if ($this->model->getCustomer()->getCustomAttribute('company_id')) {
            $companyId = $this->model->getCustomer()->getCustomAttribute('company_id')->getValue();
        }

        $replace = ['"', '[', ']'];
        $impoIds = str_replace($replace, '', $this->model->getImpoIds());

        $order_id = $this->model->getParentOrderId();

        $items = [];
        $quoteItems = $this->model->getAllVisibleItems();
        $i = 0;
        $tax_total = 0;
        foreach ($quoteItems as $item) {
            $items[$i] = [
                "item_id" => $item->getItemId(),
                "model" => $item->getProduct()->getModel(),
                "brand" => $item->getProduct()->getBrand(),
                "name" => $item->getName(),
                "qty" => $item->getQty(),
                "price" => $item->getPrice(),
                "tax" => $item->getTaxAmount()
            ];
            $tax_total += $item->getTaxAmount();
            $i++;
        }
        $grand_total = $this->model->getGrandTotal();
        $terms_conditions_date = $this->model->getTosAt();

        $orderData = [
          'email' => $email,
          'country' => $country,
          'contact_id' => $contactId,
          'company_id' => $companyId,
          'store_publication_id' => $impoIds,
          'order_id' => $order_id,
          'items' => $items,
          'tax_total' => $tax_total,
          'grand_total' => $grand_total,
          'terms_conditions_date' => $terms_conditions_date
        ];

        return $orderData;
    }

    public function validateResponse($response)
    {
        $request = 'validate'.ucfirst(strtolower($this->getType())).'Response';
        return $this->{$request}($response);
    }

    public function validateImpoResponse($response)
    {
        if (!is_array($response) || empty($response)) {
            throw new LocalizedException(__('Could not get data from Bitrix API. Response bad format'));
        }

        $currentPublicationId = $this->getPublicationId();
        if ($response['store_publication_id'] != $currentPublicationId) {
            throw new LocalizedException(__('Bitrix API response validation. Difference in store_publication_id'));
        }
    }

    public function validateOrderResponse($response)
    {
        $this->bitrixIntegrationlogger->info(json_encode($response));
    }

    private function getPublicationId()
    {
        return (int)$this->model->getId();
    }

    private function getIsoFormat($value)
    {
        $datetime = $this->strToTime($value);
        return date(DATE_ISO8601, $datetime);
    }

    private function getCountryCode(): string
    {
        $code = '';
        $website = $this->storeManager->getWebsite($this->getWebsiteId());
        $storeCodes = $website->getStoreCodes();
        if (!empty($storeCodes)) {
            $firstIndex = array_key_first($storeCodes);
            $codeSplit = explode('_', $storeCodes[$firstIndex]);
            $code = strtoupper($codeSplit[0]);
        }
        return $code;
    }

    private function isOpen()
    {
        $isActive = (bool)$this->model->getIsActive();
        $today = strtotime($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
        $startAt = $this->strToTime($this->model->getStartAt());
        $finishAt = $this->strToTime($this->model->getFinishAt());

        return ($isActive && ($today >= $startAt && $today <= $finishAt));
    }

    private function strToTime($value)
    {
        return is_object($value) ? $value->getTimestamp() : strtotime($value);
    }
}
