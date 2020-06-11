/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function (_, dynamicRows) {
    'use strict';

    return dynamicRows.extend({
        /**
         * {@inheritdoc}
         */
        initHeader: function () {
            var data;

            if (!this.labels().length) {
                _.each(this.childTemplate.children, function (cell) {
                    data = this.createHeaderTemplate(cell.config);
                    cell.config.labelVisible = false;
                    _.extend(data, {
                        label: cell.config.label,
                        name: cell.name,
                        required: cell.config.required,
                        additionalClasses: cell.config.columnsHeaderClasses,
                        sortOrder: cell.config.sortOrder
                    });

                    this.labels.push(data);
                }, this);
                this.labels(_.sortBy(this.labels(), 'sortOrder'));
            }
        }
    });
});
