<?php
namespace Formax\Formularios\Controller\Haztesocio;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $helperData;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Formax\Formularios\Helper\Data $helperData)
	{
		$this->_pageFactory = $pageFactory;
		$this->helperData = $helperData;
		return parent::__construct($context);
	}

	public function execute()
	{
		$resultPage = $this->_pageFactory->create();
		$title = $this->helperData->getHazteSocioConfig('general/title');
        $resultPage->getConfig()->getTitle()->set($title);

		return $resultPage;
	}
}