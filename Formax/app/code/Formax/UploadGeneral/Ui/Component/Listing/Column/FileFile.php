<?php

namespace Formax\UploadGeneral\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class FileFile extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'uploadgeneral/file/edit';

    /**
     * @var \Formax\UploadGeneral\Model\File
     */
    protected $file;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Formax\UploadGeneral\Model\File $file
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Formax\UploadGeneral\Model\File $file,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->file = $file;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $file = new \Magento\Framework\DataObject($item);
                $item[$fieldName . '_src'] = $this->file->getFileUrl($file['file']);
                $item[$fieldName . '_orig_src'] = $this->file->getFileUrl($file['file']);
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    self::URL_PATH_EDIT,
                    ['id' => $file['id']]
                );
                $item[$fieldName . '_alt'] = $file['name'];
            }
        }
        return $dataSource;
    }
}
