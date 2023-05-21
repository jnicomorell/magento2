<?php
declare(strict_types=1);

namespace Ceg\Impo\Model;

use Exception;
use Zend_Db_Expr;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Catalog\Model\ResourceModel\ProductFactory as ProductResourceFactory;


use Ceg\Impo\Model\ImpoFactory as ImpoModelFactory;
use Ceg\Impo\Model\ResourceModel\ImpoFactory as ImpoResourceFactory;
use Ceg\Impo\Api\ImpoRepositoryInterfaceFactory;

use Ceg\Impo\Api\Data\Integration\ImpoInterface;
use Ceg\Impo\Api\IntegrationRepositoryInterface;

class IntegrationRepository implements IntegrationRepositoryInterface
{
    /**
     * @var ImpoModelFactory
     */
    protected $impoFactory;

    /**
     * @var ImpoResourceFactory
     */
    protected $impoResourceFactory;

    /**
     * @var ProductResourceFactory
     */
    protected $productResFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var ImpoRepositoryInterfaceFactory
     */
    protected $impoRepoFactory;

    /**
     * @param Json $json
     * @param ImpoModelFactory $impoFactory
     * @param ImpoResourceFactory $impoResourceFactory
     * @param ProductResourceFactory $productResFactory
     * @param ImpoRepositoryInterfaceFactory $impoRepoFactory
     */
    public function __construct(
        Json $json,
        ImpoModelFactory $impoFactory,
        ImpoResourceFactory $impoResourceFactory,
        ProductResourceFactory $productResFactory,
        ImpoRepositoryInterfaceFactory $impoRepoFactory
    ) {
        $this->json = $json;
        $this->impoFactory = $impoFactory;
        $this->impoResourceFactory = $impoResourceFactory;
        $this->productResFactory = $productResFactory;
        $this->impoRepoFactory = $impoRepoFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getImpo($cegId)
    {
        $result = [];
        try {
            $impoRepository = $this->impoRepoFactory->create();
            $impo = $impoRepository->getByCegId($cegId);
            $products = [];
            foreach ($impo->getRelatedProducts() as $relatedProduct) {
                $products[] = [
                    'id' => $relatedProduct->getId(),
                    'sku' => $relatedProduct->getSku(),
                    'name'=> $relatedProduct->getName()
                ];
            }
            $impo->setRelatedProducts($products);
            $result['result'] = $impo->getData();
        } catch (Exception $exception) {
            $result['result'] = (string) __('Could not get the impo: %1.', $exception->getMessage());
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function saveImpo(ImpoInterface $impo)
    {
        $result = [];
        try {
            $impoModel = $this->impoFactory->create();
            $impoResource = $this->impoResourceFactory->create();
            $impoResource->load($impoModel, $impo->getCegId(), ImpoInterface::CEG_ID);

            $result['result'] = 'Success';
            $isNewimpo = !$impoModel->getId();
            if ($isNewimpo) {
                $result['messagges'][] = __('New Impo, successfully created.');
            }
            if (!$isNewimpo) {
                $result['messagges'][] = __('Impo %1, successfully updated.', $impo->getCegId());
            }

            $impoModel->setCegId($impo->getCegId());
            $impoModel->setWebsiteId($impo->getWebsiteId());
            $impoModel->setIsActive($impo->getIsActive());
            $impoModel->setFreeOnBoard($impo->getFob());
            $impoModel->setTitle($impo->getTitle());
            $impoModel->setStartAt($impo->getStartAt());
            $impoModel->setFinishAt($impo->getFinishAt());

            $productIds =[];
            $products = $impo->getProducts();
            if (!empty($products)) {
                $productResource = $this->productResFactory->create();
                foreach ($products as $product) {
                    $sku = $product->getSku();
                    $productId = $productResource->getIdBySku($sku);
                    if (!$productId) {
                        $result['messagges'][] = __('We can\'t find the product: %1.', $sku);
                        continue;
                    }
                    $productIds[] = $productId;
                }
            }
            $impoModel->setRelatedProductIds($productIds);

            $impoRepository = $this->impoRepoFactory->create();
            $impoRepository->save($impoModel);

        } catch (Exception $exception) {
            $result['result'] = 'Failed';
            $result['errors'][] = __('Could not save the impo: %1.', $exception->getMessage());
        }
        return $result;
    }
}
