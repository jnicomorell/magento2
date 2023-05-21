<?php

namespace Ceg\Backend\Model;

use Magento\Framework\Exception\AuthenticationException;
use Magento\User\Model\User as MagentoUser;

class User extends MagentoUser
{

    /**
     * @return mixed|null
     */
    public function getAuth0Hash()
    {
        $auth0Hash = $this->_getData('auth0_hash');
        return !empty($auth0Hash) ? $auth0Hash : 'String';
    }

    /**
     * @param string $password
     *
     * @return bool
     * @throws AuthenticationException
     */
    public function verifyIdentity($password)
    {
        $result = false;

        if ($this->_encryptor->validateHash($password, $this->getPassword()) ||
            $this->_encryptor->validateHash($password, $this->getAuth0Hash())) {

            if ($this->getIsActive() != '1') {
                throw new AuthenticationException(
                    __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    )
                );
            }
            if (!$this->hasAssigned2Role($this->getId())) {
                throw new AuthenticationException(__('More permissions are needed to access this.'));
            }
            $result = true;
        }
        return $result;
    }
}
