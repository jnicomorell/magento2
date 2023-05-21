<?php
declare(strict_types=1);

namespace Ceg\Quote\Ui\Component\Providers\View;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Zend_Db_Expr;

class Collection extends SearchResult
{
    /**
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->addFilterToMap('is_active', 'is_active');
        $this->addFilterToMap('status', 'status');

        $this->addFieldToFilter('status', [
            \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_OPEN,
            \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_APPROVED,
            \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_REOPEN,
            \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_CLOSED,
            \Ceg\Quote\Rewrite\Magento\Quote\Model\Quote::STATUS_CANCELED
        ]);

        // TODO: Verificar casos de quotes con multiples IMPO IDs
        $cegImpoTable = $this->getTable('ceg_impo_entity');
        $subquery = new Zend_Db_Expr(sprintf('(SELECT entity_id, ceg_id FROM %s)', $cegImpoTable));
        $this->getSelect()->joinleft(
            ['impo' => $subquery],
            'main_table.impo_ids LIKE CONCAT(\'%"\', impo.entity_id, \'"%\')',
            ['impo_ids' => new Zend_Db_Expr('group_concat(`impo`.ceg_id)')]
        )->group('main_table.entity_id');
        $this->addFilterToMap('impo_ids', 'impo.ceg_id');

        parent::_initSelect();
    }
}
