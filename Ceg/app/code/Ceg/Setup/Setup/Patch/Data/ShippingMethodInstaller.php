<?php
declare(strict_types=1);

namespace Ceg\Setup\Setup\Patch\Data;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class ShippingMethodInstaller implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var WriterInterface
     */
    private $writerInterface;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteria;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WriterInterface $writerInterface
     * @param CartRepositoryInterface $cartRepository
     * @param SearchCriteriaBuilder $searchCriteria
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $writerInterface,
        CartRepositoryInterface $cartRepository,
        SearchCriteriaBuilder $searchCriteria
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->writerInterface = $writerInterface;
        $this->cartRepository = $cartRepository;
        $this->searchCriteria = $searchCriteria;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->writerInterface->save('carriers/freeshipping/title', 'Entrega directa sin costo adicional');

        $criteria = $this->searchCriteria->create();
        $quotes = $this->cartRepository->getList($criteria);

        foreach ($quotes->getItems() as $quote) {
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress
                ->setShippingDescription('Entrega directa sin costo adicional')
                ->save();
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies() :array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases() :array
    {
        return [];
    }
}
