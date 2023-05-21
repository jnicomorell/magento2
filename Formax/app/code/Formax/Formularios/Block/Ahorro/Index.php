<?php
namespace Formax\Formularios\Block\Ahorro;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $helperData;
    protected $productName;

	public function __construct(
        \Formax\Formularios\Helper\Data $helperData,
        \Magento\Framework\View\Element\Template\Context $context,
        \Formax\Formularios\Controller\Ahorro\Index $productName
    ) {
        $this->helperData = $helperData;
        $this->productName = $productName;
		parent::__construct($context);
    }
    
    public function getTitle()
    {
        return $this->helperData->getAhorroConfig('general/title');
    }

    public function getTextDownload()
    {
        return $this->helperData->getAhorroConfig('general/text_download');
    }

    public function getLegalTerms()
    {
        return $this->helperData->getAhorroConfig('general/legal_terms');
    }

    public function getProductName()
    {
        return $this->productName->getProductName();
    }
}