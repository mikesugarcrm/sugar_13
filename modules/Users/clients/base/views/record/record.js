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
 * @class View.Views.Base.Users.RecordView
 * @alias SUGAR.App.view.views.BaseUsersRecordView
 * @extends View.Views.Base.RecordView
 */
({
    extendsFrom: 'RecordView',

    /**
     * Extend the parent function to add editability checking for IDM
     *
     * @param {Array} options
     */
    initialize: function(options) {
        this.plugins = _.union(this.plugins || [], ['HistoricalSummary']);
        this._super('initialize', [options]);

        _.each(this.meta.panels, function(panel) {
            app.utils.setIDMEditableFields(panel.fields, 'record');
        });

        this._initUserTypeViews();
    },

    /**
     * @inheritdoc
     */
    _afterInit: function() {
        this._super('_afterInit');

        // Get a list of names of all the user preference fields on the view
        let userPreferenceFields = [];
        let viewFields = _.flatten(_.pluck(this.meta.panels, 'fields'));
        _.each(viewFields, function(field) {
            if (field.name) {
                let fieldDef = this.model.fields[field.name];
                if (fieldDef && fieldDef.user_preference) {
                    userPreferenceFields.push(field.name);
                }
            }
        }, this);

        // Make sure all user preference fields are added to the options of
        // fields to fetch
        let contextFields = this.context.get('fields') || [];
        contextFields = contextFields.concat(userPreferenceFields);
        this.context.set('fields', _.uniq(contextFields));
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this.listenTo(this.context, 'button:reset_preferences:click', this.resetPreferencesClicked);
        this._super('bindDataChange');
    },

    /**
     * @inheritdoc
     *
     * Handles IDM alert messaging
     */
    editClicked: function() {
        this._super('editClicked');

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

    /**
     * Reset all preferences for this user
     */
    resetPreferencesClicked: function() {
        app.alert.show('reset_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('LBL_RESET_PREFERENCES_WARNING_USER', this.module),
            onConfirm: _.bind(function() {
                let url = app.api.buildURL(this.module, `${this.model.get('id')}/resetPreferences`);
                app.api.call('update', url, null, {
                    success: _.bind(function() {
                        this.context.reloadData();
                        app.alert.show('reset_success', {
                            level: 'success',
                            messages: app.lang.get('LBL_RESET_PREFERENCES_SUCCESS_USER', this.module),
                            autoClose: true,
                        });
                    }, this),
                });
            }, this),
            onCancel: function() {
                return;
            }
        });
    },

    /**
     * Sets up functionality to support special views based on the User type
     *
     * @private
     */
    _initUserTypeViews() {
        // Always fetch is_group and portal_only so we can determine if we need
        // to show their special views
        let contextFields = this.context.get('fields') || [];
        contextFields.push('is_group', 'portal_only');
        this.context.set('fields', _.uniq(contextFields));

        this._checkUserType();
        this.listenTo(this.model, 'change:is_group change:portal_only', this._checkUserType);
    },

    /**
     * Fetches new metadata and re-renders to show special views based on the
     * User type if necessary
     *
     * @private
     */
    _checkUserType: function() {
        let viewType = this.model.get('is_group') ? 'group' :
            this.model.get('portal_only') ? 'portalapi' :
                false;

        if (['group', 'portalapi'].includes(viewType)) {
            this.meta = _.extend({}, app.metadata.getView(null, 'record'),
                app.metadata.getView(this.module, `record-${viewType}`));
            this.render();
            this.handleActiveTab();
        }
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        this._super('_dispose');
        this.stopListening();
    }
})
