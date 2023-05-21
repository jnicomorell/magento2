<?php
declare(strict_types=1);

namespace Ceg\UserRole\Observer;

use Ceg\UserRole\Model\Repository\AdditionalRolesRepository as Repository;
use Ceg\UserRole\Model\AdditionalRolesFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveAdditionalRoleObserver implements ObserverInterface
{
    /**
     * @var AdditionalRolesFactory
     */
    private $additionalRolesFactory;
    /**
     * @var Repository
     */
    private $additionalRolesRepository;

    /**
     * UserIdAdditionalRoleObserver constructor.
     * @param Repository $additionalRolesRepository
     * @param AdditionalRolesFactory $additionalRolesFactory
     */
    public function __construct(
        Repository $additionalRolesRepository,
        AdditionalRolesFactory $additionalRolesFactory
    ) {
        $this->additionalRolesFactory = $additionalRolesFactory;
        $this->additionalRolesRepository = $additionalRolesRepository;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(Observer $observer)
    {
        $observerObject = $observer->getEvent()->getObject();
        $userAdditionalRoles = $observerObject->getData('additional_user_roles');
        $userId = (int) $observerObject->getData('user_id');

        $selectedRoles = ($userAdditionalRoles) ? implode(',', $userAdditionalRoles) : '';

        try {
            if ($selectedRoles && $userId) {
                $additionalRoles = $this->additionalRolesRepository->getByUserId($userId);
                $additionalRoles->setRoleIds($selectedRoles);

                $this->additionalRolesRepository->save($additionalRoles);
            }
        } catch (\Exception $e) {
            $additionalRoles = $this->additionalRolesFactory->create();
            $additionalRoles->setUserId((int) $userId);
            $additionalRoles->setRoleIds($selectedRoles);
            $this->additionalRolesRepository->save($additionalRoles);
        }
    }
}
