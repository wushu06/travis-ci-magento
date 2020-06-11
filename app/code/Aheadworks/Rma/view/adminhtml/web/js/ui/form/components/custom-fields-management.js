/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'uiElement',
    'uiRegistry',
    'rjsResolver',
    'underscore'
], function (Element, registry, resolver, _) {
    'use strict';

    return Element.extend({
        defaults: {
            queryTemplate: 'ns = ${ $.ns }, requestFieldType = customField',
            imports: {
                storeId: '${ $.provider }:data.store_id'
            }
        },

        /**
         * Show element
         */
        showIsAvailable: function () {
            resolver(this._checkAndShowHideFields, this);
        },

        /**
         * Check and show/hide custom fields
         *
         * @private
         */
        _checkAndShowHideFields: function () {
            var customFields = registry.filter(this.queryTemplate);

            _.each(customFields, function (elem) {
                elem.visible(false);
                if (_.indexOf(elem.visibleOnStoreIds, this.storeId) !== -1) {
                    elem.visible(true);
                }
            }, this);
        }
    });
});
