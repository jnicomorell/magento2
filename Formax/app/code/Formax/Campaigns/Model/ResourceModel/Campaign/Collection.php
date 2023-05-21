<?php
namespace Formax\Campaigns\Model\ResourceModel\Campaign;

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
        $this->_init('Formax\Campaigns\Model\Campaign', 'Formax\Campaigns\Model\ResourceModel\Campaign');
    }

    public function getCampaign($rut)
    {
        $this->campaign_table = "main_table";
        $this->formax_campaigns_type_table = $this->getTable("formax_campaigns_type");
        $this->getSelect()
            ->join(array('type' => $this->formax_campaigns_type_table), $this->campaign_table . '.type_id= type.id'
        );
        $this->getSelect()->where("rut='".$rut."'");
        $this->getSelect()->where($this->campaign_table . '.status= 1');
        $this->getSelect()->where('type.status= 1');
    }
}
