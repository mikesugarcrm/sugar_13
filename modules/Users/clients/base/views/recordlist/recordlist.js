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
 * @class View.Views.Base.Users.RecordlistView
 * @alias SUGAR.App.view.views.BaseUsersRecordlistView
 * @extends View.Views.Base.RecordlistView
 */
({
    extendsFrom: 'RecordlistView',

    /**
     * Extend the parent function to add editability checking for IDM
     */
    parseFields: function() {
        _.each(this.meta.panels, function(panel) {
            app.utils.setIDMEditableFields(panel.fields, 'recordlist');
        }, this);

        return this._super('parseFields');
    },
})
