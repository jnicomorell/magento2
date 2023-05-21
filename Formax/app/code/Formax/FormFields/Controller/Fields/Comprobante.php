<?php
namespace Formax\FormFields\Controller\Fields;

class Comprobante extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $_sanitize;
	protected $_logger;
	protected $_helper;
	protected $_form;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Formax\HelpCenter\Helper\Sanitize $sanitize,
		\Psr\Log\LoggerInterface $logger,
		\Formax\CoreApiCoopeuch\Helper\Data $helper,
		\Formax\Formularios\Helper\Form $form
	) {
		$this->_pageFactory = $pageFactory;
		$this->_sanitize = $sanitize;
		$this->_logger = $logger;
		$this->_helper = $helper;
		$this->_form = $form;
		return parent::__construct($context);
	}

	public function execute()
	{
		$resultRedirect = $this->resultRedirectFactory->create();

		try {
			// Obtenemos parametros enviados desde el formulario
			$data = $this->getRequest()->getPostValue();

			if ( !empty($data) ) {

				

				$data['formato-id'] = 'credconsumo_001_simulador';

				$response = $this->_form->validar($data);

				if ($response) {
					$resultPage = $this->_pageFactory->create();
            		$resultPage->getConfig()->getTitle()->set(__('Comprobante de solicitud'));

					return $resultPage;
				} else {
					return $resultRedirect->setPath('*/*/error');
				}

			} else {
				return $resultRedirect->setPath('*/*/error');
			}
		} catch (\Exception $e) {
			$this->_logger->error("Error in controller Formularios Cyber Comprobante : " . $e->getMessage());
		}
	}
}