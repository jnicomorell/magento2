<?php
namespace Formax\Formularios\Controller\Ahorro;

class Error extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$resultPage = $this->_pageFactory->create();
        $resultPage->getConfig()->getTitle()->set('Error de solicitud');

		return $resultPage;
	}
}