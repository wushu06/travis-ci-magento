/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/textarea'
], function (Textarea) {
    'use strict';

    return Textarea.extend({
        defaults: {
            elementTmpl: 'Aheadworks_Rma/ui/form/element/message',
            imports: {
                isInternal: '${ $.provider }:data.thread_message.is_internal'
            }
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {Abstract} Chainable
         */
        initObservable: function () {
            this._super()
                .observe({isInternal: false});

            return this;
        }
    });
});
