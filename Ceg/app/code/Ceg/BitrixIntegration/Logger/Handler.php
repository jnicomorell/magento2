<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    protected $loggerType = Logger::INFO;
    protected $fileName = '/var/log/bitrixIntegration.log';
}
