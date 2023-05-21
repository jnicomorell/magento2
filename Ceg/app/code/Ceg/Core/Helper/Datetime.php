<?php
declare(strict_types=1);

namespace Ceg\Core\Helper;

use DateTimeZone;
use Exception;
use DateTime as DTime;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;

class Datetime extends AbstractHelper
{
    /** @var TimezoneInterface */
    public $timezone;

    /**
     * @param Context           $context
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Context $context,
        TimezoneInterface $timezone
    ) {
        parent::__construct($context);
        $this->timezone = $timezone;
    }

    /**
     * @param string $modifier
     * @return string
     * @throws Exception
     */
    public function getNowUtc($modifier = '+0 Hour')
    {
        $nowModified = $modifier . ' now';
        $utcTimezone = new DateTimeZone($this->timezone->getDefaultTimezone());
        return new DTime($nowModified, $utcTimezone);
    }

    /**
     * @param        $datetime
     * @param        $websiteId
     * @return string
     * @throws Exception
     */
    public function convertDateFromWebsiteToUtc($datetime, $websiteId)
    {
        $utcTimezoneStr = $this->timezone->getDefaultTimezone();
        $websiteTimezoneStr = $this->timezone->getConfigTimezone(ScopeInterface::SCOPE_WEBSITE, $websiteId);
        return $this->convertDate($datetime, $websiteTimezoneStr, $utcTimezoneStr);
    }

    /**
     * @param        $datetime
     * @param        $websiteId
     * @return string
     * @throws Exception
     */
    public function convertDateFromUtcToWebsite($datetime, $websiteId)
    {
        $utcTimezoneStr = $this->timezone->getDefaultTimezone();
        $websiteTimezoneStr = $this->timezone->getConfigTimezone(ScopeInterface::SCOPE_WEBSITE, $websiteId);
        return $this->convertDate($datetime, $utcTimezoneStr, $websiteTimezoneStr);
    }

    /**
     * @param $datetime
     * @return string
     * @throws Exception
     */
    public function convertStringToDatetime($datetime)
    {
        $utcTimezoneStr = $this->timezone->getDefaultTimezone();
        $utcTzElement = new DateTimeZone($utcTimezoneStr);

        return new DTime($datetime, $utcTzElement);
    }

    /**
     * @param $datetime
     * @param $fromTimezone
     * @param $toTimezone
     * @return \DateTime
     * @throws Exception
     */
    private function convertDate($datetime, $fromTimezone, $toTimezone)
    {
        $fromTzElement = new DateTimeZone($fromTimezone);
        $toTzElement = new DateTimeZone($toTimezone);

        if(is_string($datetime)) {
            $datetime = $this->convertStringToDatetime($datetime);
        }

        $datetimeStr = $datetime->format('m/d/Y H:i:s');
        $value = new DTime($datetimeStr, $fromTzElement);
        $value->setTimezone($toTzElement);

        return $value;
    }
}
