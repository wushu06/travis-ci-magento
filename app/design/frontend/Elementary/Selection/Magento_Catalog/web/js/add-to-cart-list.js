define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Ui/js/modal/modal',
    'underscore',
    'pagination',
    'domReady!'
], function ($, ko, Component, modal,_, pagination) {
    'use strict';
    var _list;
    return Component.extend({
        defaults: {

        },
        initialize: function() {
            this._super();
            _list = this;
            _list.productId = ko.observable();
            _list.itemId = ko.observable();
            _list.totalQty = ko.observable(0);
            _list.totalPrice = ko.observable(0);
            _list.items = ko.observableArray();

        },
        value_changed: function(id, optionId, _this,  event){
            var option = event.target
            var price = $(option).data('product-price');
            var qty = parseInt(option.value);
            var allQty = 0;
            var total = 0;
            var qtyParent = $(option).parents('.grouped-wrapper');

            $(qtyParent).each(function () {
                $(this).find('.input-text.qty').each(function () {
                    var currentQty = parseInt($(this).val());
                    allQty = allQty + currentQty;
                    if(currentQty > 0){
                        total = total + parseInt($(this).data('product-price'));
                    }

                })

            })

            _list.totalPrice(parseFloat(total.toFixed(2)))
            _list.totalQty( allQty  )

        },
        submitHandler: function(id){
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                buttons: []
            };

            $('#popup-modal-'+id).show()
            var popup = modal(options, $('#popup-modal-'+id));

            $("#popup-modal-"+id).modal("openModal").on('modalclosed', function() {
                _list.totalPrice(0)
                _list.totalQty(0)
            });

        }

    });
});
