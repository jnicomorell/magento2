<?php
namespace Formax\FormCyber\Block;

class Comprobante extends \Magento\Framework\View\Element\Template
{
    protected $_storeManager;

	public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->_storeManager = $storeManager;
		parent::__construct($context);
    }

    public function getFormData()
    {
        $post = $this->getRequest()->getPostValue();

        $data = array(
            'name'           => $post['name'],
            'rut'            => $post['rut'],
            'phone'          => $post['code'] . $post['phone'],
            'email'          => $post['email'],
            'comment'        => $post['comment']
        );

        return $data;
    }


    public function getBaseMediaDir()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}