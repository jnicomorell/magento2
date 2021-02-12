<?php
namespace Formax\SimulatorHipotecario\Controller\Hipotecario;

class Resultado extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $_logger;
	protected $_form;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Formax\Crypto\Helper\Data $cryptohelper,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Psr\Log\LoggerInterface $logger,
		\Formax\Formularios\Helper\Form $form
	) {
		$this->_pageFactory = $pageFactory;
		$this->_logger = $logger;
		$this->_cryptohelper = $cryptohelper;
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

				if ( isset($data['more_pie']) && $data['more_pie'] == "si" )
				{
					$data['comment'] = 'Plazo ' . $data['plazo'] . ' años, tengo el 20% de financiamiento: Si';
				} else
				{
					$data['comment'] = 'Plazo ' . $data['plazo'] . ' años, tengo el 20% de financiamiento: No, tengo ' . $data['pie'];
				}
				
				$data['formato-id'] = 'credito_simulador_hipotecario_001';

				$response = $this->_form->validar($data);

				if ($response) {
					$resultPage = $this->_pageFactory->create();
            		$resultPage->getConfig()->getTitle()->set(__('Resultado de simulación'));

					return $resultPage;
				} else {
					return $resultRedirect->setPath('*/*/error');
				}

			} else {
				return $resultRedirect->setPath('*/*/error');
			}
		} catch (\Exception $e) {
			$this->_logger->error(__CLASS__. "_" .__FUNCTION__. ": " . $e->getMessage());
			return $resultRedirect->setPath('*/*/error');
		}
	}
}