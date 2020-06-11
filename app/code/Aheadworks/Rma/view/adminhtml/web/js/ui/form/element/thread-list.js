/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'ko',
    'underscore',
    'Magento_Ui/js/lib/spinner',
    'rjsResolver',
    'uiLayout',
    'uiCollection'
], function (ko, _, loader, resolver, layout, Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'Aheadworks_Rma/ui/form/element/thread-list',
            imports: {
                rows: '${ $.provider }:data.thread_list'
            }
        },

        /**
         * Initializes observable properties
         *
         * @returns {ThreadList} Chainable
         */
        initObservable: function () {
            this._super()
                .track({rows: []});

            return this;
        }
    });
});
