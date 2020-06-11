/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiElement',
    'uiLayout',
    'rjsResolver',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, _, utils, Element, layout, resolver, modal, alert, $t) {
    'use strict';

    return Element.extend({
        defaults: {
            mapExcludeAfterError: [],
            switcherConfig: {
                component: 'Magento_Ui/js/form/switcher',
                name: '${ $.name }_switcher',
                target: '${ $.name }',
                property: 'downloadState'
            },
            imports: {
                orderData: '${ $.provider }:data.sales_order_selected',
                isErrorAfterSave: '${ $.provider }:data.error_after_save'
            },
            listens: {
                orderData: 'updateOrderData'
            }
        },

        /**
         * {@inheritdoc}
         */
        initialize: function () {
            this._super()
                .initSwitcher();

            return this;
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {OrderDataDownloader} Chainable
         */
        initObservable: function () {
            this._super()
                .observe({'downloadState': false});

            return this;
        },

        /**
         * Initializes switcher element instance
         *
         * @returns {OrderDataDownloader} Chainable
         */
        initSwitcher: function () {
            if (this.switcherConfig.enabled) {
                layout([this.switcherConfig]);
            }

            return this;
        },

        /**
         * Update order data
         */
        updateOrderData: function () {
            resolver(this.prepareAndSendData, this);
        },

        /**
         * Prepare and send data
         */
        prepareAndSendData: function () {
            var requestData = {'form_key': window.FORM_KEY};

            if (_.isArray(this.orderData) && this.orderData.length > 0 && _.isObject(this.orderData[0])) {
                requestData['entity_id'] = this.orderData[0].entity_id;
                this._sendRequest(requestData);
            } else {
                alert({content: $t('Incorrect selected order data. Please try again.')});
            }
        },

        /**
         * Send request
         *
         * @param requestData
         * @private
         */
        _sendRequest: function (requestData) {
            var self = this;

            $('body').trigger('processStart');
            $.ajax({
                url: self.url,
                type: 'POST',
                dataType: 'json',
                data: requestData,

                /**
                 * Success callback
                 *
                 * @param {Object} response
                 * @returns {Boolean}
                 */
                success: function(response) {
                    if (response.error) {
                        self.onError(response.message);
                        return true;
                    } else {
                        self.onSuccess(response);
                    }
                    return false;
                },

                /**
                 * Complete callback
                 */
                complete: function () {
                    $('body').trigger('processStop');
                }
            });
        },

        /**
         * Ajax request error handler
         *
         * @param {String} errorMessage
         */
        onError: function (errorMessage) {
            alert({content: errorMessage});
        },

        /**
         * Ajax request success handler
         *
         * @param {Object} response
         */
        onSuccess: function (response) {
            if (response.error) {
                alert({content: response.message});
            } else {
                this._addResponseDataToSource(response.data);
                resolver(this.onSuccessDataDownload, this);
            }
        },

        /**
         * Data download success handler
         */
        onSuccessDataDownload: function () {
            this.downloadState(true);
        },

        /**
         * Add response data to source
         *
         * @param newData
         * @private
         */
        _addResponseDataToSource: function (newData) {
            var value;

            newData = newData || {};
            _.each(this.map, function (prop, index) {
                value = newData[prop];
                if (!_.isUndefined(value) && !this._isExcludeFromMapping(index)) {
                    this.source.set('data.' + index, value);
                }
            }, this);
        },

        /**
         * Check if exclude from mapping
         *
         * @param {String} index
         * @return {Boolean}
         * @private
         */
        _isExcludeFromMapping: function (index) {
            if (this.mapExcludeAfterError.length === 0) {
                return false;
            }
            if (_.indexOf(this.mapExcludeAfterError, index) === -1) {
                return false;
            }

            return !((this.isErrorAfterSave && this._isValueEmpty(index))
                || (!this.isErrorAfterSave && this._isValueEmpty(index)));
        },

        /**
         * Check if value empty
         *
         * @param index
         * @return {Bboolean}
         * @private
         */
        _isValueEmpty: function (index) {
            var value, isValueEmpty;

            value = this.source.get('data.' + index);
            isValueEmpty = _.isNull(value) || _.isUndefined(value)
                || ((!_.isNull(value) || !_.isUndefined(value)) && value.length === 0);

            return isValueEmpty;
        }
    });
});
