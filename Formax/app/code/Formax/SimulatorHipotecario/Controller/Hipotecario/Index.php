<?php
namespace Formax\SimulatorHipotecario\Controller\Hipotecario;

use Formax\SimulatorHipotecario\Helper\Data;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $_data;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		Data $data)
	{
		$this->_pageFactory = $pageFactory;
		$this->_data=$data;
		return parent::__construct($context);
	}

	public function execute()
	{
		$resultPage = $this->_pageFactory->create();
		$resultPage->getConfig()->getTitle()->set(__('Credito Hipotecario'));
		return $resultPage;
	}
	
}