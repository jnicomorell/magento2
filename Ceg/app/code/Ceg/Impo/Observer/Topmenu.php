<?php

namespace Ceg\Impo\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Event\ObserverInterface;

class Topmenu implements ObserverInterface
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Initialize observer
     *
     * @param \Magento\Framework\View\Element\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Framework\Data\Tree\Node $menu */
        $menu = $observer->getMenu();
        $tree = $menu->getTree();
        $catalogNode = [];
        foreach ($menu->getChildren() as $child) {
            $catalogNode[] = $child;
            $menu->removeChild($child);
        }
        $url = $this->urlBuilder->getUrl('impo/view');
        $url = str_replace('://', '', $url);
        $url = explode('/', $url);
        if (strpos($url[0], '.') !== false) {
            $url[0] = '';
        }
        $data = [
            'name'      => __('Importation'),
            'id'        => 'ceg-impo-section',
            'url'       => implode('/', $url),
            'is_active' => false,
            'class'     => 'ceg-impo-menu',
        ];
        $node = new Node($data, 'id', $tree, $menu);
        $menu->addChild($node);
        foreach ($catalogNode as $item) {
            $menu->addChild($item);
        }
        return $this;
    }
}
