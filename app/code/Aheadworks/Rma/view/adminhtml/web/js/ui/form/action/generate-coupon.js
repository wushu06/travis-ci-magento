/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
], function ($) {
    'use strict';

    return {

        /**
         * Make a request to generate a coupon
         *
         * @param {Array} params
         */
        generate: function (params) {
            $.ajax({
                url: params.url,
                type: "POST",
                data: params.data,
                showLoader: true,
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        alert(response.message)
                    } else {
                        params.onSuccess(response.couponCode);
                    }
                }
            });
        },
    }
});
