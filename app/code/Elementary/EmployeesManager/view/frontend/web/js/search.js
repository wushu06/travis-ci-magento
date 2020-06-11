
define([
    'jquery',
    'ko',
    'uiComponent'
], function ($, ko, Component) {
    'use strict';
    var searchUrl,self;
    return Component.extend({
        defaults: {

        },
        initialize: function() {
            this._super();
            self = this;
            searchUrl = this.searchUrl;
            self.finish  = ko.observable(true);
            self.search_name = ko.observable("");
            self.employees = ko.observableArray();
            self.search_name.subscribe(function (newValue) {
                if(newValue.length > 1 && self.finish()){
                    var data = {
                        "searchCriteria":
                            {
                                "current_page": 1,
                                "page_size": 10,
                                "sortOrders": [{
                                    'field': 'entity_id',
                                    'direction' : 'DESC'
                                }]
                            }
                    };
                    data["searchCriteria"]["filter_groups"] = [
                        {
                            "filters":[
                                {
                                    "field": "name",
                                    'value' : `%${newValue}%`,
                                    'condition_type' : 'like'
                                }
                            ]
                        }
                    ];

                    console.log(data);
                    $.ajax(searchUrl,
                        {
                            data: data,
                            method: 'GET',
                            beforeSend: function(){
                                self.employees( [] )
                            },
                            success: function (data) {
                                self.employees.push(data.items)
                            }
                        });
                }
                if(newValue.length === 0 && self.finish() ){
                    self.employees( [] )
                }
            });



        },
        pickEmployee: function(item){
            $('#autocomplete').val(item.name)
            $('#employeesSelector').val(item.name).change();
            self.employees([])
        },
        loadJsAfterKoRender: function(){
            $('#autocomplete').on('blur', function () {
                setTimeout(function () {
                    self.employees([])
                }, 1000)

            });
        }
    });
});
