<?php

namespace Ceg\Sales\Block\Order;

use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;

/**
 * Sales order link
 *
 * @api
 */
class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->registry = $registry;
    }

    /**
     * Retrieve current order model instance
     *
     * @return Order
     */
    private function getQuote()
    {
        return $this->registry->registry('current_quote');
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl($this->getPath(), ['quote_id' => $this->getQuote()->getId()]);
    }

    /**
     * @inheritdoc
     *
     * @return string
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _toHtml()
    {
        if ($this->hasKey()
            && method_exists($this->getQuote(), 'has' . $this->getKey())
            && !$this->getQuote()->{'has' . $this->getKey()}()
        ) {
            return '';
        }
        return parent::_toHtml();
    }
}
