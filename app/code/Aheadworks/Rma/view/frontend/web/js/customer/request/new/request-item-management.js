/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery'
], function($) {
    'use strict';

    $.widget("awrma.awRmaRequestItemManagement", {
        options: {
            itemReturnContainerSelector: '[data-role=item-return-container]'
        },

        /**
         * Initialize widget
         */
        _create: function() {
            this._bind();
            this._hideItemContainer();
        },

        /**
         * Event binding
         */
        _bind: function() {
            this._on({
                'change': '_onChange'
            });
        },

        /**
         * On change event handler
         *
         * @param {Object} event
         */
        _onChange: function (event) {
            event.preventDefault();

            if ($(this.element).is(':checked')) {
                this._showItemContainer();
            } else {
                this._hideItemContainer();
            }
        },

        /**
         * Hide item container
         */
        _hideItemContainer: function () {
            var itemReturnContainer = $(this.element)
                    .closest('.aw-rma__return-item-container')
                    .find(this.options.itemReturnContainerSelector),
                tickCheckboxMsg = $(this.element)
                    .closest('.aw-rma__return-item-container')
                    .find('.tick-checkbox');

            tickCheckboxMsg.show();
            itemReturnContainer.hide();
            itemReturnContainer.find('input, select, textarea').prop('disabled', true);
        },

        /**
         * Show item container
         */
        _showItemContainer: function () {
            var itemReturnContainer = $(this.element)
                    .closest('.aw-rma__return-item-container')
                    .find(this.options.itemReturnContainerSelector),
                tickCheckboxMsg = $(this.element)
                    .closest('.aw-rma__return-item-container')
                    .find('.tick-checkbox');

            tickCheckboxMsg.hide();
            itemReturnContainer.show();
            itemReturnContainer.find('input, select, textarea').prop('disabled', false);
        }
    });

    return $.awrma.awRmaRequestItemManagement;
});
