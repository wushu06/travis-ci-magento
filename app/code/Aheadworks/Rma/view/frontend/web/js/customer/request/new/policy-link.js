/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert',
    'mageUtils'
], function($, modal, alert, utils){
    'use strict';

    $.widget("awrma.awRmaPolicyLink", {
        options: {
            popupSelector: '#aw-rma-policy-popup',
        },

        /**
         * Initialize widget
         */
        _create: function() {
            var options = {
                'type': 'popup',
                'modalClass': '',
                'responsive': true,
                'innerScroll': true,
                'buttons': []
            };

            this._bind();
            modal(options, $(this.options.popupSelector));
        },

        /**
         * Event binding
         */
        _bind: function() {
            this._on({
                'click': '_onClick'
            });
            $(document).on('click', $.proxy(this._onClickDocument, this));
        },

        /**
         * Click event handler
         *
         * @param {Object} event
         */
        _onClick: function (event) {
            event.preventDefault();
            $(this.options.popupSelector).modal('openModal');
        },

        /**
         * Click on document
         */
        _onClickDocument: function(e) {
            var popupContent = $(this.options.popupSelector),
                popupData = popupContent.data('mageModal');

            if (!utils.isEmpty(popupData) && popupData.options.isOpen
                && !popupContent.is(e.target) && popupContent.has(e.target).length === 0
                && $(e.target).data('role') !== this.element.data('role')
            ) {
                popupContent.modal('closeModal');
            }
        }
    });

    return $.awrma.awRmaPolicyLink;
});