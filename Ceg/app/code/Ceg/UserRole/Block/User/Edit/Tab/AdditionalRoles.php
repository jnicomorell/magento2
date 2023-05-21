<?php
declare(strict_types=1);

namespace Ceg\UserRole\Block\User\Edit\Tab;

use Ceg\UserRole\Model\Repository\AdditionalRolesRepository;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\User\Model\User;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as UserRolesCollectionFactory;

class AdditionalRoles extends Extended
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var UserRolesCollectionFactory
     */
    protected $_userRolesFactory;

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var AdditionalRolesRepository
     */
    private $additionalRolesRepository;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param EncoderInterface $jsonEncoder
     * @param UserRolesCollectionFactory $userRolesFactory
     * @param Registry $coreRegistry
     * @param array $data
     * @param AdditionalRolesRepository|null $additionalRolesRepository
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        EncoderInterface $jsonEncoder,
        UserRolesCollectionFactory $userRolesFactory,
        Registry $coreRegistry,
        array $data = [],
        AdditionalRolesRepository $additionalRolesRepository = null
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_userRolesFactory = $userRolesFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);

        $this->additionalRolesRepository = $additionalRolesRepository
            ?? ObjectManager::getInstance()->get(AdditionalRolesRepository::class);
    }

    /**
     * Class constructor
     *
     * @return void
     * @throws FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('permissionsAdditional UserRolesGrid');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('asc');
        $this->setTitle(__('User Roles Information'));
        $this->setUseAjax(true);
    }

    /**
     * Adds column filter to collection
     *
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        parent::_addColumnFilterToCollection($column);
        return $this;
    }

    /**
     * Prepares collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_userRolesFactory->create();
        $collection->setRolesFilter();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepares columns
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn(
            'additional_user_roles',
            [
                'header_css_class' => 'data-grid-actions-cell',
                'header' => __('Assigned'),
                'type' => 'checkbox',
                'html_name' => 'additional_user_roles[]',
                'field_name' => 'additional_user_roles[]',
                'values' => $this->getSelectedRoles(),
                'align' => 'center',
                'index' => 'role_id'
            ]
        );

        $this->addColumn('role_name', ['header' => __('Role'), 'index' => 'role_name']);
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl(): string
    {
        $userPermissions = $this->_coreRegistry->registry('permissions_user');
        return $this->getUrl('*/*/rolesGrid', ['user_id' => $userPermissions->getUserId()]);
    }

    /**
     * Gets selected roles
     *
     * @return array|string
     * @throws NoSuchEntityException
     */
    public function getSelectedRoles()
    {
        $additionalRoles = [];
        /* @var $user User */
        $user = $this->_coreRegistry->registry('permissions_user');

        try {
            $additionalRolesCollection = $this->additionalRolesRepository->getByUserId((int) $user->getUserId());
            foreach ($additionalRolesCollection as $additionalRole) {
                if ($additionalRole['role_ids']) {
                    $additionalRoles = explode(',', $additionalRole['role_ids']);
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }

        return $additionalRoles;
    }
}
