/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'jquery',
        'underscore',
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'uiRegistry',
        'mage/translate',
        'Magento_Checkout/js/model/shipping-rate-service'
    ], function (
    $,
    _,
    Component,
    ko,
    customer,
    addressList,
    addressConverter,
    quote,
    createShippingAddress,
    selectShippingAddress,
    shippingRatesValidator,
    formPopUpState,
    shippingService,
    selectShippingMethodAction,
    rateRegistry,
    setShippingInformationAction,
    stepNavigator,
    modal,
    checkoutDataResolver,
    checkoutData,
    registry,
    $t
    ) {
        'use strict';

        return function (target) {
            return target.extend({
                setShippingInformation: function () {
                    if (this.validateDeliveryDate()) {
                        this._super();
                    }
                },
                isSelected: ko.computed(function () {
                    return quote.shippingMethod() ?
                        quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] :
                        null;
                }),
                selectShippingMethod: function (shippingMethod) {
                    selectShippingMethodAction(shippingMethod);
                    checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);

                    return true;
                },

                validateDeliveryDate: function() {
                    this.source.set('params.invalid', false);
                    this.source.trigger('delivery_date.data.validate');

                    if (this.source.get('params.invalid')) {
                        return false;
                    }

                    return true;
                }
            });
        }
    }
);