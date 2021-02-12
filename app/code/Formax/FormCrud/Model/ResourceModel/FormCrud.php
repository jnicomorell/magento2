<?php
namespace Formax\FormCrud\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;

class FormCrud extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('formax_form_crud_formcrud', 'id');
	}
	
}