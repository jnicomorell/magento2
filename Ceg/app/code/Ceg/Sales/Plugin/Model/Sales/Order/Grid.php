<?php


namespace Ceg\Sales\Plugin\Model\Sales\Order;


class Grid
{

    public static $table = 'sales_order_grid';
    public static $leftJoinTable = 'sales_order';

    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {
            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);
            $collection
                ->getSelect()
                ->joinLeft(
                    ['co'   =>  $leftJoinTableName],
                    "co.entity_id = main_table.entity_id",
                    [
                        'entity_id' => 'co.entity_id',
                    ]
                );
            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);
        }
        return $collection;


    }


}
