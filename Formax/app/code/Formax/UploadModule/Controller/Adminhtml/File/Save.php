<?php

namespace Formax\UploadModule\Controller\Adminhtml\File;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Inspection\Exception;
use Formax\UploadModule\Model\File\ImageUploader;
use Formax\UploadModule\Model\File\FileUploader;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var ImageUploader
     */
    protected $imageUploader;

    /**
     * @var FileUploader
     */
    protected $fileUploader;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param ImageUploader $imageUploader
     *  @param FileUploader $fileUploader
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        ImageUploader $imageUploader,
        FileUploader $fileUploader
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->imageUploader = $imageUploader;
        $this->fileUploader = $fileUploader;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        
        if ($data) {
            $id = $this->getRequest()->getParam('id');

            if (empty($data['id'])) {
                $data['id'] = null;
            }

            $imageName = '';
            if (!empty($data['image'])) {
                $imageName = $data['image'][0]['name'];
                $data['image'] = $imageName;
            } else {
                $data['image'] = '0';
            }

            $fileName = '';
            if (!empty($data['file'])) {
                $fileName = $data['file'][0]['name'];
                $data['file'] = $fileName;
            } else {
                $data['file'] = '0';
            }

            /** @var \Formax\UploadModule\Model\File $model */
            $model = $this->_objectManager->create('Formax\UploadModule\Model\File')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This file no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setData($data);

            try {
                $model->save();
                if ($imageName) {
                    $this->imageUploader->moveFileFromTmp($imageName);
                }
                if ($fileName) {
                    $this->fileUploader->moveFileFromTmp($fileName);
                }
                $this->messageManager->addSuccess(__('You saved the file.'));
                $this->dataPersistor->clear('file');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the file.'));
            }

            $this->dataPersistor->set('file', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Authorization level of a basic admin session
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Formax_UploadModule::file_update') || $this->_authorization->isAllowed('Formax_UploadModule::file_create');
    }
}
