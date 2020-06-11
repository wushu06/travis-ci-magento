/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'uiComponent',
    'underscore'
], function (UiComponent, _) {
    'use strict';

    return UiComponent.extend({
        defaults: {
            template: 'Aheadworks_Rma/ui/show-hide-container',
            visible: true,
            additionalClasses: {}
        },

        /**
         * {@inheritdoc}
         */
        initialize: function () {
            return this._super()
                ._setClasses();
        },

        /**
         * {@inheritdoc}
         */
        initObservable: function () {
            return this._super()
                .observe([
                    'visible'
                ]);
        },

        /**
         * Show element
         *
         * @returns {ShowHideContainer} Chainable
         */
        show: function () {
            this.visible(true);

            return this;
        },

        /**
         * Hide element
         *
         * @returns {ShowHideContainer} Chainable
         */
        hide: function () {
            this.visible(false);

            return this;
        },

        /**
         * Extends 'additionalClasses' object
         *
         * @returns {ShowHideContainer} Chainable
         */
        _setClasses: function () {
            var addtional = this.additionalClasses,
                classes;

            if (_.isString(addtional)) {
                addtional = this.additionalClasses.split(' ');
                classes = this.additionalClasses = {};

                addtional.forEach(function (name) {
                    classes[name] = true;
                }, this);
            }

            return this;
        }
    });
});
