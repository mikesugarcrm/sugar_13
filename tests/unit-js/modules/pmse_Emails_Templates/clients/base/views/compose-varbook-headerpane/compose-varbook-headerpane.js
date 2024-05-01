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
describe('pmse_Emails_Temapltes.Base.Views.compose-varbook-headerpane',
    function() {
    var app;
    var view;
    var sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        sinonSandbox = sinon.createSandbox();

        view = SugarTest.createView(
            'base',
            'pmse_Emails_Templates',
            'compose-varbook-headerpane',
            null,
            null,
            true
        );
    });

    afterEach(function() {
        sinonSandbox.restore();
        view = null;
        context = null;
        app = null;
    });

    describe('selectList()', function() {
        it('Checks if the result is correct when ' +
            'process_et_field_type has `none` as a value.',
            function() {
            var models = [];
            var child =
            {
                'attributes':
                {
                    'id': 'account_type',
                    'process_et_field_type': 'none',
                    'name': 'Type',
                    'rhs_module': 'Accounts',
                    '_module': 'Accounts',
                }
            };
            models.push(child);

            var result = view.selectList(models);
            expect(result.length).toBe(0);
        });
        it('Checks if the result is correct when ' +
            'process_et_field_type has `future` as a value.',
            function() {
            var models = [];
            var child =
            {
                'attributes':
                {
                    'id': 'account_type',
                    'process_et_field_type': 'future',
                    'name': 'Type',
                    'rhs_module': 'Accounts',
                    '_module': 'Accounts',
                }
            };
            models.push(child);

            var result = view.selectList(models);
            expect(result.length).toBe(1);
        });
        it('Checks if the result is correct when ' +
            'process_et_field_type has `old` as a value.',
            function() {
            var models = [];
            var child =
            {
                'attributes':
                {
                    'id': 'account_type',
                    'process_et_field_type': 'old',
                    'name': 'Type',
                    'rhs_module': 'Accounts',
                    '_module': 'Accounts',
                }
            };
            models.push(child);

            var result = view.selectList(models);
            expect(result.length).toBe(1);
        });
        it('Checks if the result is correct when ' +
            'process_et_field_type has `both` as a value.',
            function() {
            var models = [];
            var child =
            {
                'attributes':
                {
                    'id': 'account_type',
                    'process_et_field_type': 'both',
                    'name': 'Type',
                    'rhs_module': 'Accounts',
                    '_module': 'Accounts',
                }
            };
            child.clone = sinonSandbox.stub().returns(child);
            models.push(child);

            var result = view.selectList(models);

            expect(result.length).toBe(2);
        });
    });
});
