/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/file-uploader'
], function ($, _, Component) {
    'use strict';

    return Component.extend({
        lastenter: '',

        /**
         * {@inheritdoc}
         */
        initUploader: function (fileInput) {
            $(this.dropZone).on('dragenter', this.onDragEnter.bind(this));
            $(this.dropZone).on('dragleave', this.onDragLeave.bind(this));
            $(this.dropZone).on('drop', this.onDrop.bind(this));
            this._super();

            return this;
        },

        /**
         * Browse file
         */
        browseFile: function () {
            $('#' + this.uid).click();
        },

        /**
         * Handler for drag enter of the dropZone
         *
         * @param {Event} event - Event object
         */
        onDragEnter: function (event) {
            event.preventDefault();
            this.lastenter = event.target;
            $(this.dropZone).addClass('aw-rma__file-uploader-dragging');
        },

        /**
         * Handler for drag leave of the dropZone
         *
         * @param {Event} event - Event object
         */
        onDragLeave: function (event) {
            event.preventDefault();
            if (this.lastenter === event.target) {
                $(this.dropZone).removeClass('aw-rma__file-uploader-dragging');
            }
        },

        /**
         * Handler for drag leave of the dropZone
         *
         * @param {Event} event - Event object
         */
        onDrop: function (event) {
            event.preventDefault();
            $(this.dropZone).removeClass('aw-rma__file-uploader-dragging');
        },

        /**
         * Retrieve input name
         *
         * @param {String} inputName
         * @param {String} fileName
         * @return {String}
         */
        getInputName: function (inputName, fileName) {
            return this.inputName + '[' + fileName + '][' + inputName + ']';
        }
    });
});
