<?php

namespace Formax\News\Controller\Adminhtml\Article;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Inspection\Exception;
use Formax\News\Model\Article\ImageUploader;
use Formax\News\Helper\Data;

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
     * @var Data
     */
    protected $_helperData;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        ImageUploader $imageUploader,
        Data $helperData
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->imageUploader = $imageUploader;
        $this->_helperData = $helperData;
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
                $data['year'] = date("Y");
            }else if($data['year']==null || empty($data['year'])){
                $data['year'] = date("Y");
            }
            

            $imageName = '';
            if (!empty($data['image'])) {
                $imageName = $data['image'][0]['name'];
                $data['image'] = $imageName;
            } else {
                $data['image'] = '0';
            }

            $categories=[];
            if (!empty($data['categories'])) {
                $categories=$data['categories'];
                $data['categories']= implode(",", $data['categories']);
            }

            $relatedNews = [];
            if (!empty($data['related_news'])) {
                $relatedNews = $data['related_news'];
                $data['related_news'] = implode(",", $data['related_news']);
            }

            /** @var \Formax\News\Model\Article $model */
            $model = $this->_objectManager->create('Formax\News\Model\Article')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This article no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setData($data);

            try {
                $model->save();
                if ($imageName) {
                    $this->imageUploader->moveFileFromTmp($imageName);
                }
                if (!empty($categories)) {
                    $this->_helperData->deleteCategoryArticleByArticle( $model->getId() );
                    foreach ($categories as $category) {
                        $this->_helperData->insertCategoryArticle($category, $model->getId());
                    }
                }

                $this->messageManager->addSuccess(__('You saved the article.'));
                $this->dataPersistor->clear('article');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the article.'));
            }

            $this->dataPersistor->set('article', $data);
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
        return $this->_authorization->isAllowed('Formax_News::article_update') || $this->_authorization->isAllowed('Formax_News::article_create');
    }
}
