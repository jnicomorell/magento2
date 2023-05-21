<?php
namespace Formax\Formularios\Controller\Hipotecario;

class Comprobante extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $_logger;
	protected $_form;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Psr\Log\LoggerInterface $logger,
		\Formax\Formularios\Helper\Form $form
	) {
		$this->_pageFactory = $pageFactory;
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

			if ( !empty($data) ) {

				if ( isset($data['check-down-payment-yes']) )
				{
					$data['comment'] = 'Valor de la propiedad ' . $data['amount'] . ', SI, tengo ' . $data['down-payment'] . ' de pie';
					$data['down-payment'] = 'Si, tengo ' . $data['down-payment'];
				} else
				{
					$data['comment'] = 'Valor de la propiedad ' . $data['amount'];
					$data['down-payment'] = 'No';
				}

				$data['formato-id'] = 'credito_hipotecario_001';
				$this->getRequest()->setPostValue('down-payment', $data['down-payment']);

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
			$this->_logger->error("Error in controller Formularios Hipotecario Comprobante : " . $e->getMessage());
		}
	}
}