<?php
namespace Formax\Formularios\Controller\Consumo;

class Comprobante extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $_logger;
	protected $_form;
	protected $_product;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Psr\Log\LoggerInterface $logger,
		\Formax\Formularios\Helper\Form $form,
		\Magento\Catalog\Model\ProductRepository $productRepository
	) {
		$this->_pageFactory = $pageFactory;
		$this->_logger = $logger;
		$this->_form = $form;
		$this->_productRepository = $productRepository;
		return parent::__construct($context);
	}

	public function execute()
	{
		$resultRedirect = $this->resultRedirectFactory->create();

		try {
			// Obtenemos parametros enviados desde el formulario
			$data = $this->getRequest()->getPostValue();

			if ( !empty($data) ) {
				
				$product = $this->_productRepository->get( $data['sku'] );
				$data['formato-id'] = 'credconsumo_001_simulador';
				$data['amount'] = $product->getMonto();

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
			return $resultRedirect->setPath('*/*/error');
			$this->_logger->error(__CLASS__. "_" .__FUNCTION__. ": data " . $e->getMessage());
		}
	}
}