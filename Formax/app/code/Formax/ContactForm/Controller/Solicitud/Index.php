<?php
namespace Formax\ContactForm\Controller\Solicitud;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $_logger;
	protected $_form;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Formax\Crypto\Helper\Data $cryptohelper,
		\Psr\Log\LoggerInterface $logger,
		\Formax\Formularios\Helper\Form $form
	) {
		$this->_pageFactory = $pageFactory;
		$this->_cryptohelper = $cryptohelper;
		$this->_logger = $logger;
		$this->_form = $form;
		return parent::__construct($context);
	}

	public function execute()
	{
		$resultRedirect = $this->resultRedirectFactory->create();

		try {
			// Obtenemos parametros enviados desde el formulario
			$data = $this->getRequest()->getPostValue();
			//$data=$this->_cryptohelper->decryptPost($data);

			if ( !empty($data) ) {

				$response = $this->_form->validar($data);

				if ($response) {
					$resultPage = $this->_pageFactory->create();
            		$resultPage->getConfig()->getTitle()->set(__('Comprobante de solicitud'));

					return $resultPage;
				} else {
					$this->_logger->error(__CLASS__. "_" .__FUNCTION__. ": responde is false");
					return $resultRedirect->setPath('*/*/error');
				}

			} else {
				$this->_logger->error(__CLASS__. "_" .__FUNCTION__. ": data is empty");
				return $resultRedirect->setPath('*/*/error');
			}
		} catch (\Exception $e) {
			$this->_logger->error(__CLASS__. "_" .__FUNCTION__. ": data " . $e->getMessage());
			return $resultRedirect->setPath('*/*/error');
		}
	}
}