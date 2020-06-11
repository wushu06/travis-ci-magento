/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/components/button',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert',
    'uiRegistry'
], function ($, _, Button, modal, alert, registry) {
    'use strict';

    /**
     * Retrieve wrapper selector
     *
     * @param {String} modalWrapperName
     */
    function getWrapperSelector(modalWrapperName) {
        return '[data-role="' + modalWrapperName +'"]';
    }

    return Button.extend({
        defaults: {
            modalWrapperName: 'popup-preview',
            modules: {
                record: '${ $.parentName }'
            }
        },

        /**
         * @inheritdoc
         */
        applyAction: function (action) {
            var self = this,
                requestData = {'form_key': window.FORM_KEY},
                recordData = this.source.get(this.record().dataScope),
                formData = this.source.get('data'),
                recordParent = registry.get(this.record().parentName);

            requestData['store_id'] = recordData.store_id;
            requestData['to_admin'] = recordParent.toAdmin;
            requestData = _.extend({}, requestData, formData);

            $('body').trigger('processStart');
            $.ajax({
                url: action.url,
                type: 'POST',
                dataType: 'json',
                data: {
                    request_data: requestData
                },

                /**
                 * Success callback.
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
            alert({
                content: errorMessage
            });
        },

        /**
         * Ajax request success handler
         *
         * @param {Object} response
         */
        onSuccess: function (response) {
            var
                wrapper = $('<div></div>'),
                wrapperSelector = getWrapperSelector(this.modalWrapperName),
                options = {
                    autoOpen: true,
                    responsive: true,
                    clickableOverlay: false,
                    innerScroll: true,
                    modalClass: 'email-preview-modal',
                    title: $.mage.__('Email Preview'),
                    buttons: [],
                    /**
                     * {@inheritdoc}
                     */
                    closed: function () {
                        $(wrapperSelector).remove();
                    }
                };

            wrapper
                .attr('data-role', this.modalWrapperName)
                .append(response.content)
                .hide();
            $('body').append(wrapper);

            modal(options, $(wrapperSelector), {closed : this.onTrigger});
        }
    });
});
