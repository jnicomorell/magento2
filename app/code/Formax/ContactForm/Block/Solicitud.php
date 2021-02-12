<?php
namespace Formax\ContactForm\Block;

class Solicitud extends \Magento\Framework\View\Element\Template
{
	protected $_storeManager;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Formax\Crypto\Helper\Data $cryptohelper,
		\Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
		$this->_cryptohelper = $cryptohelper;
		$this->_storeManager = $storeManager;
		parent::__construct($context);
	}

	public function getFormData()
    {
        $post = $this->getRequest()->getPostValue();
        //$post=$this->_cryptohelper->decryptPost($post);

        $data = array(
            'name'       => $post['name'],
            'rut'        => $post['rut'],
            'phone'      => $post['code'] . $post['phone'],
            'email'      => $post['email'],
            'formato-id' => $post['formato-id'],
            'comment'    => $post['comment']
        );

        return $data;
	}
	
	public function getBaseMediaDir()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}