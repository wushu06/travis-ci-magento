/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery'
], function ($) {
    "use strict";

    return function () {
        $.validator.addMethod(
            'aw-rma__order-item-required',
            function (value) {
                return !$.mage.isEmpty(value);
            },
            $.mage.__('Please select order item(s).')
        );
        return $.mage.validation;
    }
});
