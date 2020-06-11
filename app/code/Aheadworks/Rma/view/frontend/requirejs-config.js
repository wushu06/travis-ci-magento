/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

var config = {
    map: {
        '*': {
            awRmaSelectOrderForm:       'Aheadworks_Rma/js/customer/request/new/select-order-form',
            awRmaRequestItemMassAction: 'Aheadworks_Rma/js/customer/request/new/request-item-massaction',
            awRmaRequestItemManagement: 'Aheadworks_Rma/js/customer/request/new/request-item-management',
            awRmaPolicyLink:            'Aheadworks_Rma/js/customer/request/new/policy-link',
            awRmaButtonControl:         'Aheadworks_Rma/js/button-control'
        }
    },
    config: {
        mixins: {
            'mage/validation': {
                'Aheadworks_Rma/js/validation-mixin': true
            }
        }
    }
};
