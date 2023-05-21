<?php
namespace Ceg\CustomerAddressAttributes\Plugin\Checkout;

class LayoutProcessor
{
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        $jsLayout = $this->setShippingAttr($jsLayout);
        $jsLayout = $this->setBillingAttr($jsLayout);

        return $jsLayout;
    }

    public function setShippingAttr($jsLayout)
    {
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['numero'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [],
                'id' => 'numero'
            ],
            'dataScope' => 'shippingAddress.custom_attributes.numero',
            'label' => 'Número',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => [
                'required-entry' => true,
                'validate-zero-or-greater' => true
            ],
            'sortOrder' => 80,
            'id' => 'numero'
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['colonia'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [],
                'id' => 'colonia'
            ],
            'dataScope' => 'shippingAddress.custom_attributes.colonia',
            'label' => 'Colonia',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => [],
            'sortOrder' => 81,
            'id' => 'colonia'
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['observaciones'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [],
                'id' => 'observaciones'
            ],
            'dataScope' => 'shippingAddress.custom_attributes.observaciones',
            'label' => 'Indicaciones adicionales',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => [],
            'sortOrder' => 250,
            'id' => 'observaciones'
        ];
        return $jsLayout;
    }

    public function setBillingAttr($jsLayout)
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'])) {
            foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'] as $key => $paymentLayout) {
                if (isset($paymentLayout['children']['form-fields'])) {
                    $paymentLayout['children']['form-fields']['children']['numero'] = [
                        'component'  => 'Magento_Ui/js/form/element/abstract',
                        'config'     => [
                            'customScope' => $paymentLayout['dataScopePrefix'] . 'custom_attributes',
                            'template'    => 'ui/form/field',
                            'elementTmpl' => 'ui/form/element/input',
                            'options'     => [],
                            'id'          => 'numero'
                        ],
                        'dataScope'  => $paymentLayout['dataScopePrefix'] . '.custom_attributes.numero',
                        'label'      => 'Número',
                        'provider'   => 'checkoutProvider',
                        'visible'    => true,
                        'validation' => [
                            'required-entry' => true,
                            'validate-zero-or-greater' => true
                        ],
                        'sortOrder'  => 80,
                        'id'         => 'numero'
                    ];

                    $paymentLayout['children']['form-fields']['children']['colonia'] = [
                        'component'  => 'Magento_Ui/js/form/element/abstract',
                        'config'     => [
                            'customScope' => $paymentLayout['dataScopePrefix'] . 'custom_attributes',
                            'template'    => 'ui/form/field',
                            'elementTmpl' => 'ui/form/element/input',
                            'options'     => [],
                            'id'          => 'colonia'
                        ],
                        'dataScope'  => $paymentLayout['dataScopePrefix'] . '.custom_attributes.colonia',
                        'label'      => 'Colonia',
                        'provider'   => 'checkoutProvider',
                        'visible'    => true,
                        'validation' => [],
                        'sortOrder'  => 81,
                        'id'         => 'colonia'
                    ];

                    $paymentLayout['children']['form-fields']['children']['observaciones'] = [
                        'component'  => 'Magento_Ui/js/form/element/abstract',
                        'config'     => [
                            'customScope' => $paymentLayout['dataScopePrefix'] . '.custom_attributes',
                            'template'    => 'ui/form/field',
                            'elementTmpl' => 'ui/form/element/input',
                            'options'     => [],
                            'id'          => 'observaciones'
                        ],
                        'dataScope'  => $paymentLayout['dataScopePrefix'] . '.custom_attributes.observaciones',
                        'label'      => 'Indicaciones adicionales',
                        'provider'   => 'checkoutProvider',
                        'visible'    => true,
                        'validation' => [],
                        'sortOrder'  => 250,
                        'id'         => 'observaciones'
                    ];

                    if (isset($paymentLayout['children']['form-fields']['children']['telephone'])) {
                        $telephone = $paymentLayout['children']['form-fields']['children']['telephone'];
                        $telephone['config']['tooltip'] = false;
                        $telephone['config']['notice'] = 'Ej. 222 5549747';
                        $telephone['validation'] = ["validate-number"=>true];
                        $paymentLayout['children']['form-fields']['children']['telephone'] = $telephone;
                    }

                    if (isset($paymentLayout['children']['form-fields']['children']['region_id'])) {
                        $regionId = $paymentLayout['children']['form-fields']['children']['region_id'];
                        $regionId['sortOrder'] = 117;
                        $paymentLayout['children']['form-fields']['children']['region_id'] = $regionId;
                    }
                }
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key] = $paymentLayout;
            }
        }
        return $jsLayout;
    }
}
