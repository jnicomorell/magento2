<?php

namespace Ceg\Setup\Setup\Patch\Data;



use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 */
class AnalyticsConfigInstaller implements DataPatchInterface
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
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WriterInterface $writerInterface
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $writerInterface
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->writerInterface = $writerInterface;
    }
    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->setConfigData(
            'ceg_analytics/ga_settings/ga_events/events_config',
            '{"_1627911783283_283":{"event":"system_action","category":"shopp_results_import","action_ga":"pageview-shopp-main-import","to_class":"\/store\/mx\/impo\/view\/","label":""},"_1627929224212_212":{"event":"user_action","category":"shopp_results_import","action_ga":"click-cluster-image","to_class":".product-image-photo","label":""},"_1627929414728_728":{"event":"user_action","category":"shopp_results_import","action_ga":"click-cluster-cart-add-subtract","to_class":".impoPlusQty","label":"add"},"_1627929439081_81":{"event":"user_action","category":"shopp_results_import","action_ga":"click-cluster-cart-add-subtract","to_class":".impoMinusQty","label":"subtract"},"_1627929763246_246":{"event":"user_action","category":"shopp_results_import","action_ga":"click-cluster-cart-submit","to_class":".impo-add-to-cart","label":""},"_1627930267058_58":{"event":"user_action","category":"shopp_detail_import","action_ga":"click-detail-add-subtract","to_class":".minusQtyImpo","label":"subtract"},"_1627930302188_188":{"event":"user_action","category":"shopp_detail_import","action_ga":"click-detail-add-subtract","to_class":".plusQtyImpo","label":"add"},"_1627930457797_797":{"event":"user_action","category":"shopp_detail_import","action_ga":"click-detail-submit","to_class":".catalogAdd","label":""},"_1627932947100_100":{"event":"user_action","category":"shopp_detail_import","action_ga":"click-detail-image","to_class":".gallery-placeholder","label":""},"_1627932977174_174":{"event":"system_action","category":"shopp_results_catalog","action_ga":"pageview-shopp-catalog","to_class":"\/store\/mx\/productos.html","label":""},"_1627936525947_947":{"event":"user_action","category":"shopp_results_catalog","action_ga":"click-price-filter","to_class":".actions-primary","label":""},"_1627993090258_258":{"event":"system_action","category":"checkout_cart","action_ga":"pageview-checkout-cart","to_class":"\/store\/mx\/checkout\/cart\/","label":""},"_1627993124596_596":{"event":"user_action","category":"checkout_cart","action_ga":"click-cluster-cart-add-subtract","to_class":".plusCart","label":"add"},"_1627993856537_537":{"event":"user_action","category":"checkout_cart","action_ga":"click-cluster-cart-add-subtract","to_class":".minusCart","label":"subtract"},"_1627993928241_241":{"event":"user_action","category":"checkout_cart","action_ga":"click-update-units-buton","to_class":".update-cart","label":""},"_1627993997015_15":{"event":"user_action","category":"checkout_cart","action_ga":"click-cluster-eliminate-product","to_class":".action-delete-item","label":""},"_1627994191449_449":{"event":"system_action","category":"checkout_cart_shipping","action_ga":"pageview-checkout-addresses","to_class":"\/store\/mx\/checkout\/#shipping","label":""},"_1627994946591_591":{"event":"user_action","category":"checkout_cart_shipping","action_ga":"click-add-addresses-link","to_class":".click-add-addresses-link","label":""},"_1627994966591_591":{"event":"user_action","category":"checkout_cart_shipping","action_ga":"click-add-addresses-buton","to_class":".action-save-address","label":""},"_1627995138962_962":{"event":"user_action","category":"checkout_cart_shipping","action_ga":"click-add-billing-address-buton","to_class":".shipping-address-add","label":""},"_1627995255658_658":{"event":"system_action","category":"checkout_cart_billing","action_ga":"pageview-checkout-billing-address","to_class":"\/store\/mx\/checkout\/#payment","label":""},"_1627995342276_276":{"event":"user_action","category":"checkout_cart_billing","action_ga":"click-add-addresses-link","to_class":".new-address-popup-billing","label":""},"_1627995523331_331":{"event":"user_action","category":"checkout_cart_billing","action_ga":"checkout-cart-billing","to_class":".confirm-order","label":""},"_1627995601560_560":{"event":"system_action","category":"thanks_page","action_ga":"pageview-thanks-page","to_class":"\/store\/mx\/checkout\/onepage\/success\/","label":""},"_1628052195871_871":{"event":"user_action","category":"shopp_results_catalog","action_ga":"click-cluster-detail","to_class":".product-item-link","label":""},"_1628056557395_395":{"event":"user_action","category":"checkout_cart_shipping","action_ga":"click-add-addresses-link","to_class":".action-save-address","label":""},"_1628082241251_251":{"event":"user_action","category":"checkout_cart","action_ga":"click-continue-order-buton","to_class":".checkout","label":""},"_1628085063851_851":{"event":"user_action","category":"checkout_cart_shipping","action_ga":"click-add-addresses-link","to_class":".new-address-popup","label":""},"_1628094705829_829":{"event":"user_action","category":"checkout_cart_billing","action_ga":"click-add-addresses-link","to_class":".payment-method-billing-address","label":""}}'
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @param $path
     * @param $value
     */
    public function setConfigData($path, $value)
    {
        $this->writerInterface->save($path, $value);
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
