<?php

namespace Formax\Campaigns\Block\Adminhtml\Campaign\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class GenericButton
 */
class GenericButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @param Context $context
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(
        Context $context,
        \Magento\Framework\AuthorizationInterface $authorization
    ) {
        $this->context = $context;
        $this->_authorization = $authorization;
    }

    /**
     * Return the Campaign ID
     *
     * @return int
     */
    public function getCampaignId()
    {
        return (int)$this->context->getRequest()->getParam('id');
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [];
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
