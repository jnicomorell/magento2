<?php

namespace Ceg\Backend\App\Action\Plugin;

use Magento\Backend\App\Action\Plugin\Authentication as Auth;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Authentication extends Auth
{
    /**
     * @var string[]
     */
    protected $_openActions = [
        'forgotpassword',
        'resetpassword',
        'resetpasswordpost',
        'logout',
        'refresh',// captcha refresh
        'auth0',
    ];
}
