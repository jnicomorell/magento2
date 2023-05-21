<?php
declare(strict_types=1);

namespace Ceg\UserRole\Plugin;

use Ceg\UserRole\Model\Repository\AdditionalRolesRepository as AdditionalRolesRepository;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Authorization\PolicyInterface;
use Psr\Log\LoggerInterface;

class Authorization
{
    /**
     * @var AdditionalRolesRepository
     */
    private $additionalRolesRepository;

    /**
     * @var Session
     */
    private $authSession;
    /**
     * @var PolicyInterface
     */
    private $aclPolicy;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Authorization constructor.
     * @param PolicyInterface $aclPolicy
     * @param AdditionalRolesRepository $additionalRolesRepository
     * @param Session $authSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        PolicyInterface $aclPolicy,
        AdditionalRolesRepository $additionalRolesRepository,
        Session $authSession,
        LoggerInterface $logger
    ) {
        $this->additionalRolesRepository = $additionalRolesRepository;
        $this->authSession = $authSession;
        $this->aclPolicy = $aclPolicy;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Authorization $subject
     * @param $result
     * @param string $resource
     * @param string|null $privilege
     * @return mixed
     */
    public function afterIsAllowed(
        \Magento\Framework\Authorization $subject,
        $result,
        $resource,
        $privilege = null
    ) {
        try {
            $user = $this->authSession->getUser();
            if ($user && is_object($user)) {
                $additionalRoles = $this->additionalRolesRepository->getByUserId((int)$user->getId());
                $explodedAdditionalRoles = explode(',', $additionalRoles->getRoleIds());

                foreach ($explodedAdditionalRoles as $additionalRoleId) {
                    $isAllowed = $this->aclPolicy->isAllowed($additionalRoleId, $resource, $privilege);
                    if ($isAllowed) {
                        return $isAllowed;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->debug(__($e->getMessage()));
        }

        return $result;
    }
}
