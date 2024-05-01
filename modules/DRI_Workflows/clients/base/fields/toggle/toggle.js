/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Fields.Base.DriWorkflows.ToggleField
 * @alias SUGAR.App.view.fields.DriWorkflows.BaseToggleField
 * @extends View.Fields.Base.Toggle
 */
 ({
    extendsFrom: 'ToggleField',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
    },

    /**
     * @inheritdoc
     */
    toggleValue: function() {
        if (this.model.get(this.name)) {
            $(`[id ="cj-widget-config-toggle-label-right"]`).addClass('font-bold');
        } else {
            $(`[id ="cj-widget-config-toggle-label-left"]`).addClass('font-bold');
        }
    },

    /**
     * @inheritdoc
     *
     * @param {number} value
     * @return {string}
     */
    format: function(value) {
        if (_.isNull(value)) {
            return value;
        }

        if (this.action === 'detail') {
            if (value == false) {
                return app.lang.get('LBL_CUSTOMER_JOURNEY_STAGE_NUMBER_SHOW');
            } else {
                return app.lang.get('LBL_CUSTOMER_JOURNEY_STAGE_NUMBER_HIDE');
            }
        } else if (this.action === 'edit') {
            if (value == false) {
                $(`[id ="cj-widget-config-toggle-label-left"]`).addClass('font-bold');
                $(`[id ="cj-widget-config-toggle-label-right"]`).removeClass('font-bold');
            } else {
                $(`[id ="cj-widget-config-toggle-label-right"]`).addClass('font-bold');
                $(`[id ="cj-widget-config-toggle-label-left"]`).removeClass('font-bold');
            }
            return value;
        }
    },
});
