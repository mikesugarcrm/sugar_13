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
 * @class View.Views.Base.Users.MassupdateView
 * @alias SUGAR.App.view.views.BaseUsersMassupdateView
 * @extends View.Views.Base.MassupdateView
 */
({
    extendsFrom: 'MassupdateView',

    /**
     * @inheritdoc
     *
     * Extends the parent function to also check for fields that are not
     * editable while in IDM mode
     */
    checkFieldAvailability: function(field) {
        let available = this._super('checkFieldAvailability', [field]);
        let idmProtected = app.config.idmModeEnabled && field.idm_mode_disabled;
        let isPreferenceField = field.user_preference;

        return available && !idmProtected && !isPreferenceField;
    }
})
