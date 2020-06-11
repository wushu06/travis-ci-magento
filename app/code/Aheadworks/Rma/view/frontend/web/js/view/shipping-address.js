/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/form',
    'Aheadworks_Rma/js/ui/form/adapter',
    'uiRegistry',
    'Magento_Ui/js/modal/alert',
    'Magento_Customer/js/customer-data'
], function ($, _, Component, adapter, registry, alert, customerData) {
    'use strict';
    var countryData = customerData.get('directory-data');

    return Component.extend({
        defaults: {
            selectorPrefix: 'body',
            scopeId: 'shippingAddress.print_label',
            requestIdSelector: '[data-role=aw-rma-request-id]',
            address: {},
            showEditAddress: false,
            showForm: false
        },

        /**
         * {@inheritdoc}
         */
        initialize: function () {
            var self = this;

            this._super()._checkFormKey();

            this.responseData.subscribe(function (data) {
                if (!data.error) {
                    this.onSuccess(data);
                }
                alert({
                    content: data.message
                });
            }, this);

            registry.async('awRmaAddressProvider')(function (addressProvider) {
                self.address(addressProvider.get(self.scopeId));
            });
        },

        /**
         * {@inheritdoc}
         */
        initObservable: function () {
            this._super().observe(['showForm', 'address']);

            return this;
        },

        /**
         * {@inheritdoc}
         */
        initAdapter: function () {
            adapter.on({
                'cancel': this.cancel.bind(this),
                'save': this.save.bind(this, true, {}),
                'updateRequest': this.validateAddress.bind(this)
            }, this.selectorPrefix, this.eventPrefix);

            return this;
        },

        /**
         * {@inheritdoc}
         */
        destroyAdapter: function () {
            adapter.off([
                'cancel',
                'save',
                'updateRequest'
            ], this.eventPrefix);

            return this;
        },

        /**
         * {@inheritdoc}
         */
        validate: function () {
            this.source.set('params.invalid', false);
            if (this.showForm()) {
                this.source.trigger(this.scopeId + '.data.validate');

                if (this.source.get(this.scopeId + '.custom_attributes')) {
                    this.source.trigger(this.scopeId + '.custom_attributes.data.validate');
                }
            }
        },

        /**
         * {@inheritdoc}
         */
        save: function (redirect, data) {
            var addressData;

            this.validate();
            if (!this.source.get('params.invalid')) {
                addressData = this.source.get('shippingAddress');
                addressData['id'] = $(this.requestIdSelector).val();

                addressData = _.extend(addressData, data);
                this.setAdditionalData(addressData)
                    .submit(redirect);
            } else {
                this.focusInvalid();
            }
        },

        /**
         * On cancel address click event handler
         */
        cancel: function () {
            this.reset();
            this.showForm(false);
        },

        /**
         * Validate address before update request
         *
         * @return {boolean}
         */
        validateAddress: function () {
            this.validate();
            if (!this.source.get('params.invalid')) {
                return true;
            } else {
                this.focusInvalid();
            }

            return false;
        },

        /**
         * On edit address click event handler
         */
        onEditAddressClick: function () {
            this.showForm(true);
        },

        /**
         * Ajax request success handler
         *
         * @param {Object} data
         */
        onSuccess: function (data) {
            this.overload();
            this.showForm(false);
        },

        /**
         * Retrieve country name
         *
         * @param {Number} countryId
         * @return {String}
         */
        getCountryName: function(countryId) {
            return (countryData()[countryId] !== undefined) ? countryData()[countryId].name : '';
        },

        /**
         * Format street
         *
         * @param {Array} street
         * @return {String}
         */
        formatStreet: function (street) {
            return _.values(street).filter(Boolean).join(', ');
        },

        /**
         * Check if form key exists in window object
         */
        _checkFormKey: function () {
            if (!window.FORM_KEY) {
                window.FORM_KEY = $.mage.cookies.get('form_key');
            }
        }
    });
});
