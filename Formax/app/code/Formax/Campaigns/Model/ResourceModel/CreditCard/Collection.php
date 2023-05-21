<?php
namespace Formax\Campaigns\Model\ResourceModel\CreditCard;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Formax\Campaigns\Model\CreditCard', 'Formax\Campaigns\Model\ResourceModel\CreditCard');
    }

    public function filterCampaign($rut)
    {
        $this->credit_card_table = "main_table";
        $this->formax_campaigns_type_table = $this->getTable("formax_campaigns_type");
        $this->getSelect()
            ->join(array('type' => $this->formax_campaigns_type_table), $this->credit_card_table . '.type_id= type.id'
        );
        $this->getSelect()->where("rut='".$rut."'");
        $this->getSelect()->where($this->credit_card_table . '.status= 1');
        $this->getSelect()->where('type.status= 1');
    }
}
