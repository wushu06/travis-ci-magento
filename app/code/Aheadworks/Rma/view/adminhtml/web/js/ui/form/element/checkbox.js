/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/form/element/single-checkbox'
], function (_, Checkbox) {
    'use strict';

    return Checkbox.extend({
        /**
         * {@inheritdoc}
         */
        onCheckedChanged: function (newChecked) {
            var self = this;

            _.each(this.rows().elems(), function(record, recordIndex) {
                _.each(record.elems(), function(elem, index) {
                    if (elem.index === self.index && elem.uid !== self.uid) {
                        elem.clear();
                    }
                });
            });

            this._super(newChecked);
        }
    });
});
