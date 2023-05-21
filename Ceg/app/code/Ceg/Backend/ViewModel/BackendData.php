<?php

namespace Ceg\Backend\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Ceg\Backend\Helper\Config;

class BackendData implements ArgumentInterface
{

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function inDevMode()
    {
        return $this->config->inDevMode();
    }

    public function getLoginUrl()
    {
        return $this->config->getLoginUrl();
    }
}
