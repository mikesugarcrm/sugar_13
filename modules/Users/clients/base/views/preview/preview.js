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
 * @class View.Views.Base.Users.PreviewView
 * @alias SUGAR.App.view.views.BaseUsersPreviewView
 * @extends View.Views.Base.PreviewView
 */
({
    extendsFrom: 'PreviewView',

    /**
     * Extend the parent function to add editability checking for IDM
     *
     * @param {Array} options
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        _.each(this.meta.panels, function(panel) {
            app.utils.setIDMEditableFields(panel.fields, 'record');
        });
    },

    /**
     * @inheritdoc
     */
    _previewifyMetadata: function(meta) {
        let formattedMeta = this._super('_previewifyMetadata', [meta]);

        if (formattedMeta && formattedMeta.panels) {
            formattedMeta.panels = formattedMeta.panels.filter((item) =>
                !['downloads_tab_panel', 'access_tab_user_role_panel'].includes(item.name));
        }

        return formattedMeta;
    },

    /**
     * @inheritdoc
     */
    _renderHtml: function() {
        this.meta = this._previewifyMetadata(this.meta);

        this._super('_renderHtml');
    },

    /**
     * @inheritdoc
     *
     * Handles IDM alert messaging
     */
    handleEdit: function() {
        this._super('handleEdit');

        if (app.config.idmModeEnabled) {
            let message = app.lang.get('LBL_IDM_MODE_NON_EDITABLE_FIELDS_FOR_REGULAR_USER', this.module);

            // Admin users should see a link to the SugarIdentity user edit page
            if (app.user.get('type') === 'admin') {
                let link = decodeURI(this.meta.cloudConsoleEditUserLink);
                let linkTemplate = Handlebars.compile(link);
                let url = linkTemplate({
                    record: encodeURIComponent(app.utils.createUserSrn(this.model.get('id')))
                });

                message = app.lang.get('LBL_IDM_MODE_NON_EDITABLE_FIELDS_FOR_ADMIN_USER', this.module);
                message = message.replace('%s', url);
            }

            app.alert.show('edit-user-record', {
                level: 'info',
                autoClose: false,
                messages: app.lang.get(message)
            });
        }
    },
})
