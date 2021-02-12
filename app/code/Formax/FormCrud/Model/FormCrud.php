<?php

namespace Formax\FormCrud\Model;

use Magento\Store\Model\StoreManagerInterface;

class FormCrud extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Term cache tag
     */
    const CACHE_TAG = 'formax_form_crud_formcrud';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Formax\FormCrud\Model\ResourceModel\FormCrud');
    }

    public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}

}
