var config = {
    "map": {
        "*": {
            'Magento_Checkout/js/model/shipping-save-processor/default':
                'Elementary_DeliveryDate/js/model/shipping-save-processor/default'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Elementary_DeliveryDate/js/mixin/shipping-mixin': true
            }
        }
    }
};