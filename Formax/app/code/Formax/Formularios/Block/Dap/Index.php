<?php
namespace Formax\Formularios\Block\Dap;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $helperData;

	public function __construct(
        \Formax\Formularios\Helper\Data $helperData,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->helperData = $helperData;
		parent::__construct($context);
    }
    
    public function getTitle()
    {
        return $this->helperData->getDapConfig('general/title');
    }

    public function getTextDownload()
    {
        return $this->helperData->getDapConfig('general/text_download');
    }

    public function getLegalTerms()
    {
        return $this->helperData->getDapConfig('general/legal_terms');
    }
}