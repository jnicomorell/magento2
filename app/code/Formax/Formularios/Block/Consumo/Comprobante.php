<?php
namespace Formax\Formularios\Block\Consumo;

class Comprobante extends \Magento\Framework\View\Element\Template
{
    protected $_productRepository;
    protected $_storeManager;
    protected $_product;
    protected $priceHelper;
    protected $timezone;

	public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->_productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
        $this->timezone = $timezone;
		parent::__construct($context);
    }

    public function getFormData()
    {
        $post = $this->getRequest()->getPostValue();
        $_product = $this->_productRepository->get( $post['sku'] );
        $costoTotal = !is_null($_product->getSpecialPrice()) ? $_product->getSpecialPrice() : $_product->getPrice();

        $data = array(
            'monto'        => $_product->getMonto(),
            'cuotas'       => $_product->getNumeroCuotas(),
            'valor-cuota'  => $_product->getCuotas(),
            'fecha'        => $this->timezone->date()->format('d/m/Y'),
            'tasa-mensual' => $_product->getTasaOnline(),
            'tasa-anual' => $_product->getTasaInteresAnual(),
            'desgravamen'  => $_product->getSeguroDesgravamen(),
            'costo-total'  => $this->getPrice($costoTotal),
            'cae'          => $_product->getCae(),
            'notaria'      => $_product->getGastosNotariales(),
            'monto-bruto'  => $_product->getMontoBrutoCredito(),
            'garantias'    => $_product->getGarantiasAsociadas()
        );

        return $data;
    }

    private function getPrice($price){
        return $this->priceHelper->currency($price, true, false);
    }

    public function getBaseMediaDir()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}