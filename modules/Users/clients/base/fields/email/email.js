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
 * @class View.Fields.Base.Users.EmailField
 * @alias SUGAR.App.view.fields.BaseUsersEmailField
 * @extends View.Fields.Base.EmailField
 */
({
    extendsFrom: 'EmailField',

    /**
     * Get HTML for email input field.
     * override the parent method
     * @param {Object} email
     * @return {Object}
     * @private
     */
    _buildEmailFieldHtml: function(email) {
        let editEmailFieldTemplate = app.template.getField(
            'email',
            'edit-email-field',
            'Users'
        );
        let emails = this.model.get(this.name);
        let index = _.indexOf(emails, email);
        let disabledPrimary = app.config.idmModeEnabled;
        let disabled = app.config.idmModeEnabled && email.primary_address;

        return editEmailFieldTemplate({
            max_length: this.def.len,
            index: index === -1 ? emails.length - 1 : index,
            email_address: email.email_address,
            primary_address: email.primary_address,
            opt_out: email.opt_out,
            invalid_email: email.invalid_email,
            reply_to_address: email.reply_to_address,
            disabledPrimary,
            disabled
        });
    }
})
