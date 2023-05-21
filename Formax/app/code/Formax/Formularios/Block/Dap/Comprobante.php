<?php
namespace Formax\Formularios\Block\Dap;

class Comprobante extends \Magento\Framework\View\Element\Template
{
    protected $helperData;
    protected $_storeManager;

	public function __construct(
        \Formax\Formularios\Helper\Data $helperData,
		\Formax\Crypto\Helper\Data $cryptohelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->helperData = $helperData;
        $this->_storeManager = $storeManager;
		$this->_cryptohelper = $cryptohelper;
		parent::__construct($context);
    }

    public function getFormData()
    {
        $post = $this->getRequest()->getPostValue();
		$post=$this->_cryptohelper->decryptPost($post);

        $data = array(
            'name'           => $post['name'],
            'lastname'       => $post['lastname'],
            'rut'            => $post['rut'],
            'phone'          => $post['code'] . $post['phone'],
            'email'          => $post['email']
        );

        return $data;
    }
    
    public function getTitle()
    {
        return $this->helperData->getDapConfig('general/title');
    }

    public function getButtonText()
    {
        return $this->helperData->getDapConfig('voucher/button_text');
    }

    public function getButtonLink()
    {
        return $this->helperData->getDapConfig('voucher/button_link');
    }

    public function getLegalTerms()
    {
        return $this->helperData->getDapConfig('general/legal_terms');
    }

    public function getBaseMediaDir()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}