<?php
namespace Formax\News\Block\Main;

class News extends \Magento\Framework\View\Element\Template
{
    protected $_newsId;
    protected $_newsConfiguration;
    protected $_helperData;
    protected $_storeManager;

    /**
     * @param \Formax\News\Helper\Data $helperData
     */

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Formax\News\Helper\Data $helperData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    )
	{
        $this->_helperData = $helperData;
        $this->_storeManager = $storeManager;
        $this->setTemplate('main/news.phtml');
		parent::__construct($context, $data);
    }
    
    public function getMainNewsConfig()
    {
        $newsId = $this->getData('main_news_id');
        $storeId = $this->getStoreId();

        if ($this->_newsId != $newsId) {
            $this->_newsId = $newsId;
        }

        if (is_null($this->_newsConfiguration)) {

            $this->_newsConfiguration = $this->_helperData->getMainNewsData($this->_newsId, $storeId);
        }

        return $this->_newsConfiguration;
    }

    public function getNewsConfiguration($newsIds)
    {
        $storeId = $this->getStoreId();
        return $this->_helperData->getNewsData($newsIds, $storeId);
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}
