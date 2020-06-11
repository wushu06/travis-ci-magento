/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'mageUtils',
    'Magento_Ui/js/form/element/region',
    'uiLayout'
], function (utils, Region, layout) {
    'use strict';

    var inputNode = {
        parent: '${ $.$data.parentName }',
        component: 'Magento_Ui/js/form/element/abstract',
        template: '${ $.$data.template }',
        provider: '${ $.$data.provider }',
        name: '${ $.$data.index }_input',
        dataScope: '${ $.$data.customEntry }',
        customScope: '${ $.$data.customScope }',
        sortOrder: {
            after: '${ $.$data.name }'
        },
        displayArea: 'body',
        label: '${ $.$data.label }',
        additionalClasses: '${ $.$data.customClasses }'
    };

    return Region.extend({
        /**
         * {@inheritdoc}
         */
        initInput: function () {
            layout([utils.template(inputNode, this)]);

            return this;
        }
    });
});
