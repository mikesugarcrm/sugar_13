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
describe('View.Fields.Base.RepeatUntilField', function() {
    var app, field, createFieldProperties, sandbox,
        module = 'Meetings';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'date');
        SugarTest.testMetadata.set();
        createFieldProperties = {
            client: 'base',
            name: 'repeat_until',
            type: 'repeat-until',
            viewName: 'edit',
            module: module
        };
        field = SugarTest.createField(createFieldProperties);
        field.view = {
            getField: function(){}
        };
        sinon.stub(field.view, 'getField').callsFake(function() {
            return {
                label: 'foo'
            }
        });
    });

    afterEach(function() {
        sandbox.restore();
        if (field) {
            field.dispose();
        }
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    using('different repeat until and start date values', [
        {
            expectation: 'should error when repeat until is before start date',
            action: 'edit',
            repeatUntil: '2014-11-17',
            startDate: '2014-11-18T13:00:00-05:00',
            isErrorExpected: true
        },
        {
            expectation: 'should not error when repeat until is same as start date',
            action: 'edit',
            repeatUntil: '2014-11-18',
            startDate: '2014-11-18T13:00:00-05:00',
            isErrorExpected: false
        },
        {
            expectation: 'should not error when repeat until is after start date',
            action: 'edit',
            repeatUntil: '2014-11-19',
            startDate: '2014-11-18T13:00:00-05:00',
            isErrorExpected: false
        },
        {
            expectation: 'should not error when repeat until is not set',
            action: 'edit',
            repeatUntil: '',
            startDate: '2014-11-18T13:00:00-05:00',
            isErrorExpected: false
        },
        {
            expectation: 'should not error when not in edit mode',
            action: 'detail',
            repeatUntil: '2014-11-17',
            startDate: '2014-11-18T13:00:00-05:00',
            isErrorExpected: false
        }
    ], function(value) {
        it(value.expectation, function() {
            var errors = {};

            field.action = value.action;
            field.model.set('repeat_until', value.repeatUntil, {silent: true});
            field.model.set('date_start', value.startDate, {silent: true});
            field._doValidateRepeatUntil(null, errors, $.noop);

            expect(!_.isEmpty(errors)).toBe(value.isErrorExpected);
        });
    });
});
