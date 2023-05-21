<?php

namespace Formax\UploadGeneral\Controller\Adminhtml\File;

use Magento\Framework\Controller\ResultFactory;

class UploadFile extends \Magento\Backend\App\Action
{
    /**
     * Image Uploader
     *
     * @var \Formax\UploadGeneral\Model\File\FileUploader
     */
    protected $fileUploader;

    /**
     * UploadFile constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Formax\UploadGeneral\Model\File\FileUploader $fileUploader
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Formax\UploadGeneral\Model\File\FileUploader $fileUploader
    ) {
        parent::__construct($context);
        $this->fileUploader = $fileUploader;
    }

    /**
     * Authorization level of a basic admin session
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Formax_UploadGeneral::file_read') || $this->_authorization->isAllowed('Formax_UploadGeneral::file_create');
    }

    /**
     * Upload File controller action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $fileId = $this->_request->getParam('param_name', 'file');
        try {
            $result = $this->fileUploader->saveFileToTmpDir($fileId);

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
