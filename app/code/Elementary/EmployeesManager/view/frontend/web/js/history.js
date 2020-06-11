define([
    'jquery',
    'ko',
    'uiComponent',
    'mage/url',
    'Magento_Ui/js/modal/modal',
    'pagination',
    'domReady!'
], function ($, ko, Component,urlBuilder, modal, pagination) {
    'use strict';
    var formUrl, orderUrl, self, currentPage = 1, size= 10, id= null;

    return Component.extend({
        defaults: {

        },
        initialize: function() {
            this._super();

            self = this;
            formUrl = this.formUrl;
            orderUrl = this.orderUrl;
            self.finish  = ko.observable(true);
            self.search_order = ko.observable("");
            self.orders = ko.observableArray()
            self.ordersData = ko.observableArray()
            self.total = ko.observable(0);
            self.test = ko.observable('some');

            if(window.location.hash) {
                var current = window.location.hash
                currentPage = Number(current.split('-')[1])
            }
            var storageSize = window.localStorage.getItem('size')
            if(storageSize && storageSize !== '') {
                size = storageSize
                $(window).on('load', function() {
                    console.log($('.active'));
                })
            }
            var pageURL = window.location.href;
            var lastURLSegment = pageURL.substr(pageURL.lastIndexOf('/') + 1);
            if(lastURLSegment ){
                id = lastURLSegment;
                this.getOrder('order', currentPage);
            }
            self.search_order.subscribe(function (newValue) {
                if(newValue.length === 0 && self.finish() ){
                    self.getOrder('order', currentPage, size);
                }
            });
            self.total.subscribe(function(newValue) {
                self._initPagination(newValue)
            });

        },
        loadJsAfterKoRender: function(){
            size &&  $('.size-' + size).addClass('active').siblings().removeClass('active')

        },
        _initPagination: function(total){
            var perPage = 10;
            var _self = this
            var $_pagination = $('#pagination-container');
            if(total <= perPage){
                $_pagination.hide()
                return ;
            }
            $_pagination.show()
            $_pagination.pagination({
                items: total,
                itemsOnPage: perPage,
                prevText: "&laquo;",
                nextText: "&raquo;",
                currentPage: currentPage,
                onPageClick: function (pageNumber) {
                    self.getOrder('order', pageNumber);
                }
            });
        },
        showOrder: function(_this, item, event){
            var url = urlBuilder.build('/sales/order/view/order_id/' + item.id);

            $('#item'+item.id).fadeToggle();
            var btn = $(event.target)
            if(btn.text() === 'Close'){
                btn.text('View Order')
            }else{
                btn.text('Close')
                console.log($('#itemDetails'+item.id).length);
                console.log(url);

                $.ajax({
                    url: url,
                    beforeSend: function () {
                        $('.loader').show()
                    },
                    success: function(response) {
                        var $result = $(response).find('.table-wrapper');
                        console.log($result);
                        $('#itemDetails'+item.id).empty().html($result)
                        $('.loader').hide()
                    },
                    error: function (err) {
                        console.log(err);
                        $('.loader').hide()
                    }
                })
            }
        },
        searchOrderKey: function(data , event){
            self.getOrder()
        },
        searchOrder: function(){
            self.getOrder('search')
        },
        getOrder: function (action = 'order', pageNumber = 1, size = 10) {
            var _self = this;
            var data = $.param({ 'action': action})
                +'&'+$.param({ 'order_id': self.search_order() })+
                '&'+$.param({ 'id': id })+
                '&'+$.param({ 'pageNumber': pageNumber })+
                '&'+$.param({ 'size': size });
            self.ordersData( [] )
            $.ajax({
                url: orderUrl,
                dataType: 'json',
                type: 'post',
                data: data,
                beforeSend: function () {
                    $('.loader').show()
                },
                success: function (data) {
                    console.log(data)
                    _self.ordersData.push(data.data)
                    _self.total(data.total)
                    $('.loader').hide()
                },
                error: function (err) {
                    console.log(err);
                    $('.loader').hide()
                }
            });
        }

    });
});
