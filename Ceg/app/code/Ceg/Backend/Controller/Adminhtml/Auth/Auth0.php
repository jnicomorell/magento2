<?php
namespace Ceg\Backend\Controller\Adminhtml\Auth;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Controller\Adminhtml\Auth;
use Magento\User\Model\UserFactory;
use Ceg\Backend\Helper\Config;
use Magento\Backend\Model\Auth\Session;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ceg\Core\Logger\Logger;
use Exception;

class Auth0 extends Auth implements
    \Magento\Framework\App\Action\HttpGetActionInterface,
    \Magento\Framework\App\Action\HttpPostActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Config
     */
    protected $helper;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var AdminSessionsManager
     */
    protected $adminSession;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var bool
     */
    protected $hasErrors = false;

    /**
     * @var UserFactory
     */
    protected $admin;

    /**
     * @var Logger
     */
    protected $cegLogger;

    /**
     * @param Context              $context
     * @param PageFactory          $resultPageFactory
     * @param Config               $helper
     * @param UserFactory          $userFactory
     * @param Session              $session
     * @param AdminSessionsManager $adminSession
     * @param DateTime             $dateTime
     * @param Logger               $cegLogger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Config $helper,
        UserFactory $userFactory,
        Session $session,
        AdminSessionsManager $adminSession,
        DateTime $dateTime,
        Logger $cegLogger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->userFactory = $userFactory;
        $this->session = $session;
        $this->adminSession = $adminSession;
        $this->dateTime = $dateTime;
        $this->cegLogger = $cegLogger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|
     * \Magento\Framework\Controller\Result\Redirect|
     * \Magento\Framework\Controller\ResultInterface|
     * \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->getRedirect($this->_backendUrl->getStartupPageUrl());
        }

        $params = $this->getRequest()->getParams();
        $adminEmail = $params['login']['username'];

        $authData = $this->getAuth0Data($params);
        $result = $this->resultPageFactory->create();

        $this->validateEmail($adminEmail);
        $this->compareData($authData);

        if (is_array($authData) && !$this->hasErrors) {

            $this->admin = $this->loadAdmin($adminEmail);

            try {
                $this->loginOrCreateAdmin($this->admin, $params, $authData);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(reset($this->errors));
                return $result;
            }
            $result = $this->getRedirect($this->_backendUrl->getStartupPageUrl());
        }

        if ($this->hasErrors) {
            $this->messageManager->addErrorMessage(reset($this->errors));
        }

        return $result;
    }

    /**
     * @param $admin
     * @param $params
     * @param $auth0
     */
    private function loginOrCreateAdmin($admin, $params, $auth0)
    {

        if (is_object($admin) && empty($admin->getData()) && !$this->hasErrors) {
            $this->createAdmin($params, $auth0);
        }

        if ($admin->getIsActive() && !$this->hasErrors) {
            $this->setAdminAsLoggedIn($admin, $params);
        }
    }

    /**
     * @param $params
     * @param $adminData
     */
    private function createAdmin($params, $adminData)
    {

        $user = $params['login']['username'];
        $pass = $params['login']['password'];
        $firstname = $adminData['metadata']['first_name'];
        $lastname = $adminData['metadata']['last_name'];

        $adminInfo = [
            'username'  => $user,
            'firstname' => $firstname,
            'lastname'    => $lastname,
            'email'     => $user,
            'password'  => $pass,
            'auth0_hash' => $this->helper->hashPassword($pass, true),
            'interface_locale' => 'en_US',
            'is_active' => 1
        ];

        try {
            $this->adminData($adminInfo, true);
            $this->loginOrCreateAdmin($this->loadAdmin($user), $params, null);
        } catch (Exception $exception) {
            $this->cegLogger->info($exception->getMessage());
        }
    }

    /**
     * @param $admin
     *
     * @return bool|void
     */
    private function setAdminAsLoggedIn($admin, $params)
    {
        if ($admin->getId() && !$this->hasErrors) {
            $this->session->setUser($admin);
            $this->adminSession->getCurrentSession()->load($this->session->getSessionId());
            $sessionInfo = $this->adminSession->getCurrentSession();
            $sessionInfo->setUpdatedAt($this->dateTime->gmtTimestamp());
            $sessionInfo->setStatus($sessionInfo::LOGGED_IN);
            $this->adminSession->processLogin();
            $this->_eventManager->dispatch(
                'backend_auth_user_login_success',
                ['user' => $admin]
            );
            $this->isVerified = true;
            try {
                $pass = $params['login']['password'];
                $admin->setData('auth0_hash', $this->helper->hashPassword($pass, true));
                $admin->save();
            } catch (Exception $e) {
                $this->cegLogger->info($e->getMessage());
            }
            return true;
        }
    }

    /**
     * @param $email
     *
     * @return \Magento\User\Model\User
     */
    private function loadAdmin($email)
    {
        return $this->userFactory->create()->load($email, 'email');
    }

    /**
     * @param $auth0Data
     *
     * @return bool
     */
    private function compareData($auth0Data)
    {
        $adminData = [];
        $adminData['role']['auth0'] = $auth0Data['role'] ?? null;
        $adminData['role']['config'] = $this->helper->getCompanyRole() ?? null;

        foreach ($adminData as $attCode => $attr) {
            if ($attr['config'] != $attr['auth0']) {
                $this->cegLogger->addInfo(' Metadata Error '. $attCode);
                $this->errors[] = __('Auth0 Metadata Error');
                $this->hasErrors = true;
            }
        }

        $adminData['first_name'] = $auth0Data['metadata']['first_name'] ?? null;
        $adminData['last_name'] = $auth0Data['metadata']['last_name'] ?? null;
        if (!isset($adminData['first_name']) || !isset($adminData['last_name'])) {
            $this->errors[] =  __('Error Firstname & Lastname cannot be null');
            $this->hasErrors = true;
        }

        return $this->hasErrors;
    }

    /**
     * @param $params
     *
     * @return array|false|void
     */
    private function getAuth0Data($params)
    {
        $user = $params['login']['username'];
        $pass = $params['login']['password'];
        $auth0Data = $this->helper->getUserInfo($user, $pass);

        if ($auth0Data === false) {
            $this->hasErrors = true;
            $this->messageManager->addErrorMessage(
                __('The account sign-in was incorrect or your account is disabled temporarily.'
                .'Please wait and try again later.')
            );
            return false;
        }

        return $auth0Data;
    }

    /**
     * @param $email
     */
    private function validateEmail($email)
    {
        $companyEmail = $this->helper->getCompanyEmail();
        if (!strpos($email, $companyEmail) !== false) {
            $this->hasErrors = true;
            $this->errors[] = __('Unauthorized email');
        }
    }

    /**
     * @param       $data
     * @param false $create
     */
    protected function adminData($data, $create)
    {
        $userModel = $this->userFactory->create();
        $userModel->setData($data);
        if ($create) {
            $adminRole = $this->helper->getBackendRole();
            $userModel->setRoleId($adminRole);
        }
        try {
            $userModel->save();
        } catch (Exception $e) {
            $this->cegLogger->info($e->getMessage());
        }
    }

    /**
     * @param $path
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function getRedirect($path)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($path);
    }
}
