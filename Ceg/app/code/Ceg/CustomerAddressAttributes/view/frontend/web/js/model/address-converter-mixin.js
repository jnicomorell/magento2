/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @api
 */
define([
    'jquery',
    'Magento_Checkout/js/model/new-customer-address',
    'Magento_Customer/js/customer-data',
    'mage/utils/objects',
    'underscore',
    'mage/utils/wrapper'
], function (
    $,
    address,
    customerData,
    mageUtils,
    _,
    wrapper
) {
    'use strict';
    return function (target) {
        var countryData = customerData.get('directory-data');
        var formAddressDataToQuoteAddress = wrapper.wrap(target.formAddressDataToQuoteAddress, function (_super, formData) {

                // clone address form data to new object
                var addressData = $.extend(true, {}, formData),
                    region,
                    regionName = addressData.region;

                if (mageUtils.isObject(addressData.street)) {
                    addressData.street = this.objectToArray(addressData.street);
                }

                addressData.region = {
                    'region_id': addressData['region_id'],
                    'region_code': addressData['region_code'],
                    region: regionName
                };

                addressData.region['region'] = $('select[name=region_id] option:selected').text();

                if (addressData['region_id'] &&
                    countryData()[addressData['country_id']] &&
                    countryData()[addressData['country_id']].regions
                ) {
                    region = countryData()[addressData['country_id']].regions[addressData['region_id']];

                    if (region) {
                        addressData.region['region_id'] = addressData['region_id'];
                        addressData.region['region_code'] = region.code;
                        addressData.region.region = region.name;
                    }
                } else if (
                    !addressData['region_id'] &&
                    countryData()[addressData['country_id']] &&
                    countryData()[addressData['country_id']].regions
                ) {
                    addressData.region['region_code'] = '';
                    addressData.region.region = '';
                }
                delete addressData['region_id'];

                if (addressData['custom_attributes']) {
                    addressData['custom_attributes'] = _.map(
                        addressData['custom_attributes'],
                        function (value, key) {
                            return {
                                'attribute_code': key,
                                'value': value
                            };
                        }
                    );
                }
                return address(addressData);
        });

        target.formAddressDataToQuoteAddress = formAddressDataToQuoteAddress;
        return target;
    }
});
