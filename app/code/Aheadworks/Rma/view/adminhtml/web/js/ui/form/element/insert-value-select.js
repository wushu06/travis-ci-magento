/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'underscore',
    'jquery',
    'uiRegistry',
    'Aheadworks_Rma/js/ui/form/element/select'
], function (_, $, registry, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            queryTemplate: 'ns = ${ $.ns }, index = message'
        },

        /**
         * Init component with initialization of setHandlers method.
         */
        initialize: function () {
            this._super();
            this.setHandlers();

            return this;
        },

        /**
         * Set handler with registration on 'select' value changed event
         */
        setHandlers: function () {
            registry.get(this.queryTemplate, function (component) {
                this.on('value', this.insertText.bind(this, component));
            }.bind(this));
        },

        /**
         * Insert text to textarea
         *
         * @param {Object} component
         */
        insertText: function (component) {
            var targetElementId = '#' + component.uid,
                targetElementValue =  $(targetElementId).val(),
                cursorPosition = $(targetElementId).prop('selectionStart'),
                textBeforeInsert = targetElementValue.substring(0,  cursorPosition),
                textAfterInsert  = targetElementValue.substring(cursorPosition, targetElementValue.length),
                result;

            result = _.findWhere(this.options(), {
                value: this.value()
            });

            if (_.has(result, 'content')) {
                component.value(textBeforeInsert + result.content + textAfterInsert);
                $(targetElementId).prop({
                    'selectionStart': cursorPosition +result.content.replace(/\n|/g, "").length,
                    'selectionEnd': cursorPosition +result.content.replace(/\n|/g, "").length
                });
                $(targetElementId).focus();
                this.clear();
            }
        },

        /**
         * Filters 'initialOptions' property by 'field' and 'value' passed,
         * calls 'setOptions' passing the result to it
         *
         * @param {*} value
         * @param {String} field
         */
        filter: function (value, field) {
            var source = this.initialOptions,
                result;

            field = field || this.filterBy.field;

            result = _.filter(source, function (item) {
                return item[field] == value || item.value === '';
            });

            this.setOptions(result);
        },

        /**
         * Sets 'data' to 'options' observable array, if instance has
         * 'customEntry' property set to true, calls 'setHidden' method
         *  passing !options.length as a parameter
         *
         * @param {Array} data
         * @returns {Object} Chainable
         */
        setOptions: function (data) {
            var isVisible;

            this._super(data);

            isVisible = !!this.options().length;
            this.setVisible(isVisible);

            return this;
        }
    });
});
