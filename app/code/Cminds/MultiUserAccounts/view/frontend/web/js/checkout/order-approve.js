define(
    [
        'jquery',
        'Magento_Customer/js/model/authentication-popup',
        'Magento_Customer/js/customer-data'
    ],
    function ($, authenticationPopup, customerData) {
        'use strict';

        return function (config, element) {
            $(element).click(function (event) {
                var cart = customerData.get('cart'),
                    customer = customerData.get('customer');

                event.preventDefault();

                if (!customer().firstname && !cart().isGuestCheckoutAllowed) {
                    authenticationPopup.showModal();

                    return false;
                }
                location.href = config.requestOrderApprovalUrl;
            });

        };
    }
);
