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
    'domReady!'
], function ($, ko, Component, urlBuilder) {
    'use strict';
    var formUrl, id, isPopup = false;
    return Component.extend({

        initialize: function() {
            this._super();
            var self = this;
            isPopup = this.popup;
            formUrl = '/m2/rest/V1/elementary-employeesmanager/customeremployee';
            self.error  = ko.observable();
            self.name  = ko.observable();
            self.comment = ko.observable();
            self.printed_name = ko.observable('');
            self.display_area = ko.observable('');
            self.status = ko.observable('');
            self.profileTitle = ko.observable('Employee Profile');
            self.shouldShowMessage = ko.observable(!isPopup);
            self.button = ko.observable(!isPopup ? 'Save' : 'Add Employee');
            self.disable = ko.observable(false);
            self.status_options =  [
                {'id': 1 , 'title' : 'Full time'},
                {'id': 2 , 'title' : 'Part time'}
            ];
            self.area =  [
                {'id': 1 , 'title' : 'Male'},
                {'id': 2 , 'title' : 'Female'}
            ];
            if(!isPopup){
                var pageURL = window.location.href;
                var lastURLSegment = pageURL.substr(pageURL.lastIndexOf('/') + 1);
                if(lastURLSegment ){
                    id = lastURLSegment;
                    self.button('<i style="margin-right: 5px" class="fas fa-circle-notch fa-spin"></i>loading...')
                    self.disable(true)
                    var url = `${formUrl}/${id}`;
                    $.ajax(url,
                        {
                            method: 'GET',
                            success: function (data) {
                                self.viewEmployee(data)
                                $('.loader').hide()
                            }
                        });
                }
            }
            console.log(formUrl);

        },
        sameAs: function(e, event){
            if($(event.target).is(':checked')){
                $('#printed_name').val($('#name').val())
            }else{
                $('#printed_name').val('')
            }
        },
        addEmployee : function() {

            this.disable(true)
            var url = !isPopup ?  `${formUrl}/${id}`
                           : formUrl;
            var unindexed_array = $('#employeeManager').serializeArray();
            var customerEmployee= {};
            $.map(unindexed_array, function(n, i){
                customerEmployee[n['name']] = n['value'];
            });
            var obj = {
                customerEmployee
            };
            var _this = this
            $.ajax(url,
                {
                    method: !isPopup ? 'PUT' : 'POST',
                    dataType: "json",
                    contentType: "application/json;charset=utf-8",
                    data: JSON.stringify(obj),
                    beforeSend: function () {
                        _this.disable(true)
                        _this.button('<i style="margin-right: 5px" class="fas fa-circle-notch fa-spin"></i>loading...')
                        $('.loader').show()
                    },
                    success: function (data) {
                        if(isPopup) {
                            _this.button('Add Employee')
                            _this.disable(false)
                            $("#employeeProfile").modal("closeModal");
                            var evt = new CustomEvent('triggerAjax', {detail: 'save'});
                            window.dispatchEvent(evt);
                        }else{
                            _this.viewEmployee(data)
                        }
                        $('.loader').hide()
                    },
                    error: function () {
                        $('.loader').hide()
                    }
                });

        },
        viewEmployee : function(item) {
            if(item && item.name && item.name.length > 0) {
                this.name( item.name )
                $('#status').val(item.status)
                $('#display_area').val(item.display_area)
                $('#printed_name').val(item.printed_name)
                $('#editEmployee').show()
                $('#backToManager').show()
               // $('#addEmployee').hide()
                this.comment( item.comment)
            }
            this.button('save')
            this.disable(false)

        }

    });
});
