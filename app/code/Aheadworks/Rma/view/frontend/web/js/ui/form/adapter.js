/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    var buttons = {
        'cancel': '#aw-rma__cancel',
        'save': '#aw-rma__save',
        'updateRequest': '[data-role=aw-rma-update-request-form]'
    };

    /**
     * Initialize listener
     * @param {Function} callback
     * @param {String} action
     */
    function initListener(callback, action) {
        var selector = buttons[action],
            element = $(selector)[0];

        if (element) {
            if (action === 'updateRequest') {
                $(element).on('additional.validation', callback);
            } else {
                if (element.onclick) {
                    element.onclick = null;
                }
                $(element).off().on('click', callback);
            }
        }
    }

    return {
        /**
         * Calls callback when name event is triggered
         * @param  {Object} handlers
         */
        on: function (handlers) {
            _.each(handlers, initListener);
        }
    };
});
