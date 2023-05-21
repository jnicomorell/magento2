<?php
declare(strict_types=1);

namespace Ceg\Impo\Block\Adminhtml\Publication\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\AuthorizationInterface;
use Ceg\Impo\Api\Data\ImpoInterface;
use Ceg\Impo\Helper\DataFactory as HelperFactory;

class AbstractButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var HelperFactory
     */
    protected $helperFactory;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @param Context $context
     * @param HelperFactory $helperFactory
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        Context $context,
        HelperFactory $helperFactory,
        AuthorizationInterface $authorization
    ) {
        $this->context = $context;
        $this->helperFactory = $helperFactory;
        $this->authorization = $authorization;
    }

    /**
     * @param string $route
     * @param array  $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrl($route, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [];
    }

    /**
     * @return ImpoInterface
     */
    public function getCurrentImpo()
    {
        $helper = $this->helperFactory->create();
        return $helper->getCurrentImpo();
    }

    /**
     * @param   string $resource
     * @return  boolean
     */
    public function isAllowed($resource)
    {
        return $this->authorization->isAllowed($resource);
    }
}
