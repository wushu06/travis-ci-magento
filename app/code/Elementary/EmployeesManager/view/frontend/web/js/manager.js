/*
 * list employees
 * search employees by name
 * add employee popup -> delegate to form .js
 * delete employee
 */
define([
    'jquery',
    'ko',
    'uiComponent',
    'mage/url',
    'Magento_Ui/js/modal/modal',
    'underscore',
    'Magento_Customer/js/customer-data',
    'pagination',
    'domReady!'
], function ($, ko, Component, urlBuilder, modal, _, customerData, pagination) {
    'use strict';
    var searchUrl, formUrl, orderUrl,  currentPage = 1, size= 10, id= null, _this;
    return Component.extend({
        defaults: {

        },
        initialize: function() {
            this._super();
            var customer = customerData.get('customer');
            if(customer().fullname){
                $('.page-title').text('Welcome, '+customer().fullname)

            }
            var self = this;
            _this = this;
            searchUrl = this.searchUrl;
            formUrl =  '/m2/rest/V1/elementary-employeesmanager/customeremployee';
            orderUrl = this.orderUrl;
            var isPopup = this.popup;
            self.finish  = ko.observable(true);
            self.error  = ko.observable();
            self.name  = ko.observable();
            self.comment = ko.observable();
            self.printed = ko.observable();
            self.profileTitle = ko.observable('New Employee');
            self.shouldShowMessage = ko.observable(!isPopup);
            self.search_name = ko.observable("");
            self.disable = ko.observable(false);
            self.url = ko.observable(urlBuilder.build('/employees/customeremployee/view/id/'));
            self.status =  [
                {'id': 1 , 'title' : 'Full time'},
                {'id': 2 , 'title' : 'Part time'}
            ];
            self.area =  [
                {'id': 1 , 'title' : 'Male'},
                {'id': 2 , 'title' : 'Female'}
            ];
            self.employees = ko.observableArray()
            self.total = ko.observable();

            if(window.location.hash) {
                var current = window.location.hash
                currentPage = Number(current.split('-')[1])
            }
            var storageSize = window.localStorage.getItem('size')
            if(storageSize && storageSize !== '') {
                size = storageSize
            }

            self.ajaxRequest()
            window.addEventListener('triggerAjax', function (e) {
                self.ajaxRequest()
            });

            self.total.subscribe(function(newValue) {
                self.initPagination(newValue)
            });

            self.search_name.subscribe(function (newValue) {
                if(newValue.length > 2 && self.finish()){
                    _this.ajaxRequest(newValue)

                }
                if(newValue.length === 0 && self.finish() ){
                    _this.ajaxRequest(newValue)
                }
            });
        },
        loadJsAfterKoRender: function(){
            size &&  $('.size-' + size).addClass('active').siblings().removeClass('active')

        },
        initPagination: function(total){
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
                displayedPages: 1,
                prevText: "<i class=\"arrow_to_left\"></i>",
                nextText: "<i class=\"arrow_to_right\"></i>",
                edges: 0,
                currentPage: currentPage,
                onPageClick: function (pageNumber) {
                    currentPage = pageNumber
                    if(_self.search_name().length > 2){
                        // formAjax(searchUrl, 'search', null, pageNumber, size).then(data => {
                        //     _self.viewEmployee(data.data)
                        // })
                        return;
                    }
                    currentPage = pageNumber
                    _self.ajaxRequest()
                    _this.addTotalToPagination()

                }
            });
            _this.addTotalToPagination()
        },
        addTotalToPagination: function(){
            var current  = $('.simple-pagination').find('li');
            current[1].append('/'+Math.ceil(_this.total()/ size ))


        },
        sameAs: function(e, event){
            if($(event.target).is(':checked')){
                $('#printed').val($('#name').val())
            }else{
                $('#printed').val('')
            }
        },
        addEmployeeModal : function() {
            this.profileTitle('New Employee');
            $('#addEmployee').show()
            if( $('#cloneForm').is(':empty') ) {
                var form = $('#employeeProfile').clone().removeAttr('id');
                $('#cloneForm').html(form);
            }

            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                buttons: []
            };

            var popup = modal(options, $('#employeeProfile'));
            $("#employeeProfile").modal("openModal");

        },
        deleteEmployee : function(item) {
            _this.deletePopup(item)
        },
        deletePopup: function(item){
            $('.employee_delete_wrapper').show()
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                buttons: [{
                    text: $.mage.__('Delete'),
                    class: 'employee_modal_delete',
                    click: function () {
                        var url = `${formUrl}/${item.entity_id}`
                        $.ajax(url,
                            {
                                method: 'DELETE',
                                beforeSend: function () {
                                    $('.loader').show()
                                },
                                success: function (data) {
                                    $("#popup-modal-delete").modal("closeModal");
                                    _this.ajaxRequest()
                                },
                                error: function () {
                                    $('.loader').hide()
                                }
                            });
                    }
                }]
            };

            var popup = modal(options, $('#popup-modal-delete'));
            $("#popup-modal-delete").modal("openModal");
        },
        showOrder: function(_this, item, event) {
            $('#item' + item.id).fadeToggle();
            var btn = $(event.target)
            if (btn.text() === 'Close') {
                btn.text('View Order')
            } else {
                btn.text('Close')
            }
        },
        showEmployee: function(){
            var el = $(event.target);
            size = el.text();
            window.localStorage.setItem('size', size)
            el.addClass('active').siblings().removeClass('active');
            this.ajaxRequest()

        },
        ajaxRequest: function (searchTerm = '') {
            //search?searchCriteria[requestName]=quick_search_container&searchCriteria[filterGroups][0][filters][0][field]=name&searchCriteria[filterGroups][0][filters][0][value]=Test

            var data = {
                "searchCriteria":
                    {
                        "current_page": currentPage,
                        "page_size": size,
                        "sortOrders": [{
                            'field': 'entity_id',
                            'direction' : 'DESC'
                        }]
                    }
            };
            if(searchTerm !== ''){
                data["searchCriteria"]["filter_groups"] = [
                    {
                        "filters":[
                            {
                                "field": "name",
                                'value' : `%${searchTerm}%`,
                                'condition_type' : 'like'
                            }
                        ]
                    }
                ];
            }
            console.log(data);
            $.ajax(searchUrl,
                {
                    data: data,
                    method: 'GET',
                    success: function (data) {
                        console.log(data);
                        _this.employees( [] )
                        _this.employees.push(data.items)
                        _this.total(data.total_count)
                        $('.loader').hide()
                    }
                });
        }

    });
});
