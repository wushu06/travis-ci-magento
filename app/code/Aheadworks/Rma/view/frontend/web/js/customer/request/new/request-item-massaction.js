/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
], function($) {
    'use strict';

    $.widget("awrma.awRmaRequestItemMassAction", {
        _actionType: 0,
        options: {
            selectAllSelector: '[data-role=select-all]',
            deselectAllSelector: '[data-role=deselect-all]',
            itemSelectSelector: '[data-role=item-return-select]'
        },

        /**
         * Initialize widget
         */
        _create: function() {
            this._bind();
        },

        /**
         * Event binding
         */
        _bind: function() {
            var handlers = {};

            handlers['click ' + this.options.selectAllSelector] = this._onSelectAllClick;
            handlers['click ' + this.options.deselectAllSelector] = this._onDeselectAllClick;
            this._on(handlers);
        },

        /**
         * Click select all event handler
         *
         * @param {Object} event
         */
        _onSelectAllClick: function (event) {
            event.preventDefault();
            $(this.options.itemSelectSelector).prop('checked', true).trigger('change');
        },

        /**
         * Click deselect all event handler
         *
         * @param {Object} event
         */
        _onDeselectAllClick: function (event) {
            event.preventDefault();
            $(this.options.itemSelectSelector).prop('checked', false).trigger('change');
        }
    });

    return $.awrma.awRmaRequestItemMassAction;
});
