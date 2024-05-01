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
describe('View.Fields.Base.RepeatDowField', function() {
    var app, field, createFieldProperties, sandbox,
        module = 'Meetings';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.testMetadata.set();
        createFieldProperties = {
            client: 'base',
            name: 'repeat_dow',
            type: 'repeat-dow',
            viewName: 'edit',
            module: module
        };
        field = SugarTest.createField(createFieldProperties);
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

    it('should default value on edit to current day of week', function() {
        var appDate = sandbox.stub(app, 'date');
        appDate.returns({
            isoWeekday: function() {
                return 3; // Wednesday - iso === Sugar API form
            }
        });
        field.dispose();
        field = SugarTest.createField(createFieldProperties);
        expect(field.def['default']).toEqual('3');
    });

    it('should translate Sunday value when retrieving day of week default', function() {
        var appDate = sandbox.stub(app, 'date'),
            defaultValue;
        appDate.returns({
            isoWeekday: function() {
                return 7; // Sunday - needs to be translated to 0 for Sugar API
            }
        });
        defaultValue = field.getDefaultDayOfWeek();
        expect(defaultValue).toEqual('0');
    });

    using('values on the model',[
        {
            inputValue: '312',
            expected: ['1','2','3']
        },
        {
            inputValue: '',
            expected: []
        },
        {
            inputValue: undefined,
            expected: undefined
        }
    ], function (value) {
        it('should format value on model to sorted array for select2', function() {
            var actual = field.format(value.inputValue);
            expect(actual).toEqual(value.expected);
        });
    });

    using('values on the DOM',[
        {
            inputValue: ['4','6','2'],
            expected: '246'
        },
        {
            inputValue: [],
            expected: ''
        },
        {
            inputValue: undefined,
            expected: undefined
        }
    ], function (value) {
        it('should unformat value on DOM to string for the model', function() {
            var actual = field.unformat(value.inputValue);
            expect(actual).toEqual(value.expected);
        });
    });

    using('variations of repeat type and repeat day of week values',[
        {
            expectation: 'should error when day of week has no value and repeat_type is Weekly',
            repeatType: 'Weekly',
            repeatDow: '',
            isErrorExpected: true
        },
        {
            expectation: 'should not error when day of week has a value and repeat_type is Weekly',
            repeatType: 'Weekly',
            repeatDow: '3',
            isErrorExpected: false
        },
        {
            expectation: 'should not error when day of week has no value and repeat_type is not Weekly',
            repeatType: 'Daily',
            repeatDow: '',
            isErrorExpected: false
        }
    ], function (value) {
        it(value.expectation, function() {
            var errors = {};
            field.model.set('repeat_type', value.repeatType, {silent: true});
            field.model.set(field.name, value.repeatDow, {silent: true});
            field._doValidateRepeatDow(null, errors, $.noop);
            expect(!_.isEmpty(errors)).toBe(value.isErrorExpected);
        });
    });
});
