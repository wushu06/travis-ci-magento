/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'ko',
    'Magento_Ui/js/grid/columns/multiselect'
], function (ko, MultiSelect) {
    'use strict';

    return MultiSelect.extend({
        defaults: {
            headerTmpl: 'Aheadworks_Rma/ui/grid/columns/single-select',
            bodyTmpl: 'Aheadworks_Rma/ui/grid/columns/cells/single-select',
            preserveSelectionsOnFilter: true
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {SingleSelect} Chainable
         */
        initObservable: function () {
            this._super();

            this._selected = ko.pureComputed({
                read: function () {
                    return this.selected().first();
                },

                /**
                 * Validates input field prior to updating 'qty' property
                 */
                write: function (value) {
                    this.selected([value]);
                },

                owner: this
            });

            return this;
        }
    });
});
