<?php

namespace Formax\UploadGeneral\Controller\Adminhtml\File;

use Magento\Framework\Controller\ResultFactory;

class UploadImage extends \Magento\Backend\App\Action
{
    /**
     * Image Uploader
     *
     * @var \Formax\UploadGeneral\Model\File\ImageUploader
     */
    protected $imageUploader;

    /**
     * UploadImage constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Formax\UploadGeneral\Model\File\ImageUploader $imageUploader
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Formax\UploadGeneral\Model\File\ImageUploader $imageUploader
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
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
     * Upload Image controller action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $imageId = $this->_request->getParam('param_name', 'image');
        try {
            $result = $this->imageUploader->saveFileToTmpDir($imageId);

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
