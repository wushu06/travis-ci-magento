define([
    'jquery',
    'matchMedia',
    'mage/tabs',
    'domReady!'
], function ($, mediaCheck) {
    'use strict';

    mediaCheck({
        media: '(min-width: 768px)',

        /**
         * Switch to Desktop Version.
         */
        entry: function () {

        }
    });
});
