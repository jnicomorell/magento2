<?php
namespace Formax\News\Controller\Articulo;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $_sanitize;
	protected $_logger;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Formax\HelpCenter\Helper\Sanitize $sanitize,
		\Psr\Log\LoggerInterface $logger
	) {
		$this->_pageFactory = $pageFactory;
		$this->_sanitize = $sanitize;
		$this->_logger = $logger;
		return parent::__construct($context);
	}

	public function execute()
	{
		try {
			$art = $this->getRequest()->getParam('article');
			$art = $this->_sanitize->clean($art);

			if (is_numeric($art))
			{
				$this->getRequest()->setParam('article', $art);
				
				$resultPage = $this->_pageFactory->create();

				return $resultPage;

			} else {
				return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
			}

		} catch (\Exception $e) {
			$this->_logger->error("Error getParam article: " . $e->getMessage());
			return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
		}
	}
}
