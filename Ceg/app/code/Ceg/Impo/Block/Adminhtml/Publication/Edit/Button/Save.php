<?php
declare(strict_types=1);

namespace Ceg\Impo\Block\Adminhtml\Publication\Edit\Button;
use Ceg\Impo\Ui\Component\Providers\Status;

class Save extends AbstractButton
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $helper = $this->helperFactory->create();
        $currentImpo = $helper->getCurrentImpo();
        $status = is_object($currentImpo) ? $currentImpo->getStatus() : '';
        $data = [];
        if ($status != Status::STATUS_CLOSED) {
            if ($this->isAllowed(\Ceg\Impo\Controller\Adminhtml\Publication\Save::ADMIN_RESOURCE)) {
                $data = [
                    'label' => __('Save'),
                    'class' => 'save primary',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'save'],
                        ],
                    ],
                    'sort_order' => 30,
                ];
            }
        }
        return $data;
    }
}
