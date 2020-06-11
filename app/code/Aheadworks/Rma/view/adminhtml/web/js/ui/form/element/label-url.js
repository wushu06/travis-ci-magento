/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            elementTmpl: 'Aheadworks_Rma/ui/form/element/label_url',
            links: {
                elementLabel: '${ $.provider }:${ $.dataScope }' + '_label',
                elementUrl: '${ $.provider }:${ $.dataScope }' + '_url',
                elementAfter: '${ $.provider }:${ $.dataScope }' + '_after'
            }
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {LabelUrl} Chainable
         */
        initObservable: function () {
            this._super()
                .track(['elementLabel', 'elementUrl', 'elementAfter']);

            return this;
        }
    });
});
