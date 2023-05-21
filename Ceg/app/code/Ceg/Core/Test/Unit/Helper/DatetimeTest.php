<?php

namespace Ceg\Core\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\Time;
use PHPUnit\Framework\TestCase;
use Ceg\Core\Helper\Datetime;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use DateTimeZone;
use DateTime as DTime;

class DatetimeTest extends TestCase
{
    /**
     * @var Datetime
     */
    protected $datetimeMock;

    /**
     * @var Timezone
     */
    protected $timezoneMock;

    /**
     * @var DateTimeZone
     */
    protected $dateTimezoneMock;

    /**
     * @var DTime
     */
    protected $dTimeMock;

    protected function setUp(): void
    {
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->timezoneMock = $this->getMockBuilder(Timezone::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dateTimezoneMock = $this->getMockBuilder(DateTimeZone::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dTimeMock = $this->getMockBuilder(DTime::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $arguments = [$contextMock, $this->timezoneMock];
        $this->datetimeMock = $this->getMockBuilder(Datetime::class)
            ->disableOriginalConstructor()
            ->setConstructorArgs($arguments)
            ->getMock();
    }

    public function testDatetimeInstance()
    {
        $this->assertInstanceOf(Datetime::class, $this->datetimeMock);
    }

    public function testGetNowUtc()
    {
        $this->assertInstanceOf(DTime::class, $this->dTimeMock);
    }

    public function testResultGetNowUtc($modifier = '+0 Hour')
    {
        $nowModified = $modifier . ' now';
        $utcTimezone = new DateTimeZone('UTC');
        $expectedDate = new DTime($nowModified, $utcTimezone);
        $dateTime = $this->datetimeMock->getNowUtc();
        $this->assertEquals($expectedDate, $dateTime);
    }
}
