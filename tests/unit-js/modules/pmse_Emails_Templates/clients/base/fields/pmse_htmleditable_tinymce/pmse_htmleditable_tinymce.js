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

describe('pmse_Emails_Temapltes.Base.Fields.pmse_htmleditable_tinymce', function() {
    var app;
    var field;
    var sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        sinonSandbox = sinon.createSandbox();

        SugarTest.loadComponent('base', 'field', 'htmleditable_tinymce');

        field = SugarTest.createField(
            'base',
            'pmse_htmleditable_tinymce',
            'pmse_htmleditable_tinymce',
            null,
            {},
            'pmse_Emails_Templates',
            null,
            context,
            true
        );

        htmlEditor = {
            selection: {
                getBookmark: sinonSandbox.stub().returns('id="mce-0"'),
                moveToBookmark: sinonSandbox.stub(),
                setContent: sinonSandbox.stub()
            },
            getContent: sinonSandbox.stub()
        };

        field._htmleditor = htmlEditor;

        sinonSandbox.stub(field, '_dispose');

    });

    afterEach(function() {
        sinonSandbox.restore();
        field = null;
        context = null;
        app = null;
    });

    describe('buildVariablesString()', function() {
        var htmlEditor;
        var recipients;
        beforeEach(function() {
            htmlEditor = {
                selection: {
                    getBookmark: sinonSandbox.stub().returns('id="mce-0"'),
                    moveToBookmark: sinonSandbox.stub(),
                    setContent: sinonSandbox.stub()
                },
                getContent: sinonSandbox.stub()
            };

            field._htmleditor = htmlEditor;

            recipients =
            {
                'model':
                {
                    'attributes':
                    {
                        'id': 'account_type',
                        'process_et_field_type': 'future',
                        'name': 'Type',
                        'rhs_module': 'Accounts',
                        '_module': 'Accounts',
                    }
                }
            };
            sinonSandbox.stub(field, 'buildPlaceholders').returns('{::Accounts::account_type::}');
        });

        afterEach(function() {
            sinonSandbox.restore();
            htmlEditor = null;
            recipients = null;
        });

        it('Checks if all the functions have been called correctly.', function() {
            var result = field.buildVariablesString(recipients);

            expect(field.buildPlaceholders).toHaveBeenCalledOnce();
            expect(field.buildPlaceholders).toHaveBeenCalledWith(recipients);
            expect(field._htmleditor.selection.getBookmark).toHaveBeenCalledOnce();
            expect(field._htmleditor.selection.moveToBookmark).toHaveBeenCalledOnce();
            expect(field._htmleditor.selection.moveToBookmark).toHaveBeenCalledWith('id="mce-0"');
            expect(field._htmleditor.selection.setContent).toHaveBeenCalledOnce();
            expect(field._htmleditor.selection.setContent).toHaveBeenCalledWith('{::Accounts::account_type::}');
        });
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
