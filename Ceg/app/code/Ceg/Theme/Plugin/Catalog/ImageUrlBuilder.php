<?php

namespace Ceg\Theme\Plugin\Catalog;

use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\RequestInterface;

class ImageUrlBuilder
{

    /**
     * @var DirectoryList
     */
    protected $dir;

    /**
     * @var File
     */
    protected $fileDriver;

    /**
     * @var Repository
     */
    protected $assetImage;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * ImageFactory constructor.
     *
     * @param DirectoryList    $dir
     * @param File             $fileDriver
     * @param Repository       $assetImage
     * @param RequestInterface $request
     */
    public function __construct(
        DirectoryList $dir,
        File $fileDriver,
        Repository $assetImage,
        RequestInterface $request
    ) {
        $this->dir = $dir;
        $this->fileDriver = $fileDriver;
        $this->assetImage = $assetImage;
        $this->request = $request;
    }

    /**
     * @param \Magento\Catalog\Model\Product\Image\UrlBuilder $subject
     * @param $result
     * @return string
     */
    public function aftergetUrl(
        \Magento\Catalog\Model\Product\Image\UrlBuilder $subject,
        $result
    ) {
        $imageUrl = $result;
        $imageUrl = explode('/media/', $imageUrl);
        if (count($imageUrl) > 1) {
            $fileName = end($imageUrl);
            $fileName = $this->dir->getPath('media'). DS . $fileName;
            $fileName = preg_replace('/(\/cache\/)\w+/', '', $fileName);
            
            // if missing file in system
            if (!$this->fileDriver->isExists($fileName)) {
                $action = $this->request->getFullActionName();
                $type = ($action == 'catalog_product_view') ? 'image' : 'small_image';
                $result = $this->assetImage->getUrl("Magento_Catalog::images/product/placeholder/{$type}.jpg");
            }
        }

        return $result;
    }
}
