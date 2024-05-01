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

describe('pmse_Emails_Temapltes.Base.Fields.subject', function() {
    var app;
    var field;
    var sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        sinonSandbox = sinon.createSandbox();

        field = SugarTest.createField(
            'base',
            'subject',
            'subject',
            null,
            {},
            'pmse_Emails_Templates',
            null,
            context,
            true
        );

        sinonSandbox.stub(field, '_dispose');

    });

    afterEach(function() {
        sinonSandbox.restore();
        field = null;
        context = null;
        app = null;
    });

    describe('buildPlaceholders()', function() {
        it('should build the corresponding placeholder for a current value', function() {
            var attributes = {
                        'id': 'account_type',
                        'process_et_field_type': 'future',
                        'name': 'Type',
                        'rhs_module': 'Accounts',
                        '_module': 'Accounts',
                    };
            var model = app.data.createBean('pmse_Emails_Templates', attributes, null);
            var recipients = [model];

            var result = field.buildPlaceholders(recipients);

            expect(result).toBe('{::Accounts::account_type::}');
        });

        it('should build the corresponding placeholder for an old value', function() {
            var attributes = {
                'id': 'account_type',
                'process_et_field_type': 'old',
                'name': 'Type',
                'rhs_module': 'Accounts',
                '_module': 'Accounts',
            };
            var model = app.data.createBean('pmse_Emails_Templates', attributes, null);
            var recipients = [model];

            var result = field.buildPlaceholders(recipients);

            expect(result).toBe('{::Accounts::account_type::old::}');
        });
    });
});
