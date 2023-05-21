<?php
declare(strict_types=1);

namespace Ceg\Impo\Block\Adminhtml\Publication\Edit\Button;

class Delete extends AbstractButton
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->isAllowed(\Ceg\Impo\Controller\Adminhtml\Publication\Delete::ADMIN_RESOURCE)) {
            if ($this->getCurrentImpo() && $this->getCurrentImpo()->getId()) {
                $message = __('Are you sure you want to do this?');
                $action = $this->getDeleteUrl();
                $data = [
                    'label' => __('Delete'),
                    'class' => 'delete',
                    'on_click' => "deleteConfirm('" . $message . "', '" . $action . "')",
                    'sort_order' => 20,
                ];
            }
        }
        return $data;
    }

    /**
     * @return string
     */
    private function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['id' => $this->getCurrentImpo()->getId()]);
    }
}
