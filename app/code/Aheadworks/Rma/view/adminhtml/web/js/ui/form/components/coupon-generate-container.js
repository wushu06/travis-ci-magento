/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/button',
    'Aheadworks_Rma/js/ui/form/action/generate-coupon',
    'Aheadworks_Rma/js/ui/form/action/copy-to-clipboard'
], function (Component, couponGenerator, clipboardAction) {
    'use strict';

    return Component.extend({

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super()
                .observe({
                    couponCode: ''
                });
            return this;
        },

        /**
         * Generate a coupon action
         *
         * @returns {Object} Chainable
         */
        generate: function () {
            var params = {
                    data: {
                        recipient_email: this.source.data.customer_email,
                        rule_id: this.source.data.ccg_rule_id,
                        send_email_to_recipient: this.source.data.ccg_send_email_to_recipient
                    },
                    url: this.generateUrl,
                    onSuccess: this.onGenerateSuccess.bind(this)
                };

            couponGenerator.generate(params);
            return this;
        },

        /**
         * On generate success handler
         *
         * @param {String} couponCode
         */
        onGenerateSuccess: function(couponCode) {
            this.couponCode(couponCode);
        },

        /**
         * Copy to clipboard
         */
        copyToClipboard: function(){
            clipboardAction.copy();
        }
    });
});
