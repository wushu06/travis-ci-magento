var _self;
define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Ui/js/modal/modal',
    'underscore',
    'pagination',
    'Magento_Customer/js/customer-data',
    'selectric',
    'domReady!'
], function ($, ko, Component, modal,_, pagination,customerData, selectric) {
    'use strict';
    return Component.extend({
        defaults: {

        },
        initialize: function() {
            this._super();
            _self = this;
            _self.itemsArray = ko.observableArray();
            _self.items = ko.observable();
            _self.total = ko.observable(0);
            _self.options = ko.observableArray();
            _self.optionId = ko.observable();
            _self.qty = ko.observableArray();
            _self.product = ko.observable();
            _self.error = ko.observable();
            _self.button = ko.observable('Add to cart');
            _self.toggle = ko.observable(false);
            _self.quantity = ko.observable();
            _self.formAction = ko.observable();
            _self.employee = ko.observable();
            _self.disable = ko.observable(false);
           // $('select').selectric('refresh');

        },
        getTotal: function(price, qty){
            let total = _self.total();

            if (qty > 0 && price > 0) {
                total = total + (Number(price) * qty)
            }
            if (qty <= 0 && total >= Number(price)) {
                total = total - Number(price)
            }
            _self.total(parseFloat(total.toFixed(2)))

        },
        qtyWatcher: function(id, optionId, _this,  event){

            var qty = event.target.value

            if(qty < 0 ){
                return;
            }
            var price;
            if(optionId && id && _self.items()[id]){
                _self.items()[id][optionId].qty =  qty
                price = _self.items()[id][optionId].price
                _self.itemsArray([])
                _self.itemsArray.push(_self.items());
                _self.getTotal(price, qty)
            }


        },
        optionWatcher: function(id, optionId, _this,  event ){

            var option = event.target
            if(option === ''){
                return;
            }
            _self.itemsArray([])
            _self.toggle( true );
            _self.optionId( optionId );
            var emptyValue = false
            if($(option).attr('type') === 'checkbox') {
                emptyValue = $(option).is(':checked');
            }else{
                emptyValue =  $(option).val() !== '';
            }
            if(!emptyValue){
                var price;
                if(_.has(_self.items(), id)) {
                    if (_.has(_self.items()[id], optionId)) {
                        price = _self.items()[id][optionId].price
                        delete _self.items()[id][optionId];
                    }
                }
                _self.itemsArray.push(_self.items());
                _self.getTotal(price, 0)
                return;
            }

            var qty = $('#qty-'+optionId).val();
            var selected = option.options ? option.options[option.selectedIndex].text : $(option).siblings('label').text()
            var priceFull = selected.split('+')[1];
            var price = priceFull.split('£')[1];
            if(_.has(_self.items(), id)){
                if(_.has(_self.items()[id], optionId)){
                    _self.items()[id][optionId].option = option.value
                    _self.items()[id][optionId].name = selected
                    _self.items()[id][optionId].priceFull= priceFull
                    _self.items()[id][optionId].qty = qty
                }else {
                    _self.items()[id] = {..._self.items()[id],
                        [optionId]: {
                            option: option.value,
                            name: selected,
                            priceFull: priceFull,
                            price: price,
                            productId: id,
                            qty: qty
                        }

                    }

                }

            }else{
                _self.items({..._self.items(), [id]: {
                        [optionId]: {
                            option: option.value,
                            name: selected,
                            priceFull: priceFull,
                            price: price,
                            productId: id,
                            qty: qty
                        }
                    }
                });
            }

            _self.itemsArray.push(_self.items());
            qty > 0 && _self.getTotal(price, qty)
          //  console.log(_self.itemsArray());
        },
        addToCart: function(){

            _self.error( '')
            var data = {data: _self.items() }

            if($('.employee-name').val() === ''){
                _self.error('Select an employee!')
                return;
            }else{
                data.employee = $('.employee-name').val()
            }
            _self.employee( $('.employee-name').val() )

            var self = this

            _.mapObject(Object.values(_self.items), function(items) {
                _.mapObject(Object.values(items), function (item) {
                    _.mapObject(Object.values(item), function (val, key) {
                        if (!val.qty || val.qty <= 0) {
                            self.error(val.name + ' quantity is missing!');
                        }
                    })
                })
            });

            if(_self.error() !==''){
                return;
            }
            data.form_key = $('input[name="form_key"]').val()

            _self.button('<i class="fas fa-circle-notch fa-spin"></i>adding...')
            _self.disable(true)
            $.post(
                $('#formAction').val(),
                data
            ).done(function (response) {
                _self.total(0)
                _self.items()
                _self.itemsArray([])
                _self.disable(false)
                self.button('Add To Cart')
                var sections = ['cart'];
                customerData.invalidate(sections);
                customerData.reload(sections, true)
                console.log(response);

                // window.location.reload();
            }).complete(function (response) {
                window.scrollTo(0,0);
               // $('[data-block="minicart"]').find('[data-role="dropdownDialog"]').dropdownDialog("open");


            })
            .fail(function (response) {
                _self.disable(false)
                self.button('Add To Cart')
                console.log(response);
            });

        },
        toggleHeader: function () {
            _self.toggle(!_self.toggle())
        }

    });
});
