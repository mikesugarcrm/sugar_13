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
describe('View.Fields.Base.RepeatCountField', function() {
    var app, field, createFieldProperties, module, sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'int');
        SugarTest.testMetadata.set();
        module = 'Meetings';
        createFieldProperties = {
            client: 'base',
            name: 'repeat_count',
            type: 'repeat-count',
            viewName: 'edit',
            module: module
        };
        app.config.calendar = {
            maxRepeatCount: 1000
        };
        field = SugarTest.createField(createFieldProperties);

        sandbox = sinon.createSandbox();
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

    describe('formatting the value for the DOM', function() {
        using('non-empty values', [4, '4'], function(value) {
            it('should return a string', function() {
                expect(field.format(value)).toEqual(value.toString());
            });
        });

        using('empty values', [0, '0', '', null, undefined], function(value) {
            it('should return an empty string', function() {
                expect(field.format(value)).toEqual('');
            });
        });
    });

    describe('unformatting the value for storage', function() {
        // `unformat` should only accept strings
        using('values', ['0', '4', '4.2', '111.9', '11.99'], function(value) {
            it('should return an integer', function() {
                expect(field.unformat(value)).toBe(parseFloat(value));
            });
        });

        it('should convert an empty string to 0', function() {
            expect(field.unformat('')).toBe(0);
        });

        it('should convert a formatted number string to an unformatted integer', function() {
            expect(field.unformat('5,001')).toBe(5001);
        });

        using('values', ['foo', null, undefined], function(value) {
            it('should return the original value if it cannot be coerced to an integer', function() {
                expect(field.unformat(value)).toEqual(value);
            });
        });
    });

    using('repeat count values',[
        {
            expectation: 'should error when repeat_count is greater than the max config value',
            repeatCount: 1001,
            isErrorExpected: true
        },
        {
            expectation: 'should not error when repeat_count is equal to the max config value',
            repeatCount: 1000,
            isErrorExpected: false
        },
        {
            expectation: 'should not error when repeat_count is less than the max config value',
            repeatCount: 999,
            isErrorExpected: false
        }
    ], function (value) {
        it(value.expectation, function() {
            var errors = {};
            field.model.set(field.name, value.repeatCount, {silent: true});
            field._doValidateRepeatCountMax(null, errors, $.noop);
            expect(!_.isEmpty(errors)).toBe(value.isErrorExpected);
        });
    });
});
