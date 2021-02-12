<?php
namespace Formax\Formularios\Block\Hipotecario;

class Comprobante extends \Magento\Framework\View\Element\Template
{
    protected $helperData;
    protected $_storeManager;

	public function __construct(
        \Formax\Formularios\Helper\Data $helperData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->helperData = $helperData;
        $this->_storeManager = $storeManager;
		parent::__construct($context);
    }

    public function getFormData()
    {
        $post = $this->getRequest()->getPostValue();

        $data = array(
            'name'           => $post['name'],
            'lastname'       => $post['lastname'],
            'rut'            => $post['rut'],
            'phone'          => $post['code'] . $post['phone'],
            'email'          => $post['email'],
            'monthly-income' => $post['monthly-income'],
            'amount'         => $post['amount'],
            'down-payment'   => $post['down-payment']
        );

        return $data;
    }
    
    public function getTitle()
    {
        return $this->helperData->getCredHipotecarioConfig('title');
    }

    public function getButtonText()
    {
        return $this->helperData->getCredHipotecarioVoucher('button_text');
    }

    public function getButtonLink()
    {
        return $this->helperData->getCredHipotecarioVoucher('button_link');
    }

    public function getLegalTerms()
    {
        return $this->helperData->getCredHipotecarioConfig('legal_terms');
    }

    public function getBaseMediaDir()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}