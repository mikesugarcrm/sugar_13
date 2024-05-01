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
describe('Base.Fields.Int', function() {

    var app;
    var field;

    describe('default field definition', function() {
        beforeEach(function() {
            app = SugarTest.app;

            field = SugarTest.createField('base', 'int', 'int', 'detail');
        });

        afterEach(function() {
            sinon.restore();
            field.dispose();
            field = null;
        });

        it('should format/unformat the value based on user preferences', function() {

            var preferenceStub = sinon.stub(app.user, 'getPreference');
            var value = 123456.502;

            expect(field.format(999.9)).toEqual('999.9');
            expect(field.format(9999.99)).toEqual('9,999.99');

            // this is to make sure that test still fails if we are relying on a
            // /possible system default precision set to 0
            preferenceStub.withArgs('decimal_precision').returns(4);
            preferenceStub.withArgs('number_grouping_separator').returns('.');
            preferenceStub.withArgs('decimal_separator').returns(',');

            expect(field.format(999.9)).toEqual('999,9');

            expect(field.format(value)).toEqual('123.456,502');
            expect(field.unformat('123.457')).toEqual(123457);

            preferenceStub.withArgs('number_grouping_separator').returns(',');
            preferenceStub.withArgs('decimal_separator').returns('.');

            expect(field.format(value)).toEqual('123,456.502');
            expect(field.unformat('123,457')).toEqual(123457);


            //SS-3076 - This tests if a user's thousand's separator is empty
            preferenceStub.withArgs('number_grouping_separator').returns('');
            preferenceStub.withArgs('decimal_separator').returns('.');
            expect(field.format('50002')).toEqual('50002');
            expect(field.unformat('5.0')).toEqual(5);
            expect(field.unformat('50002.00')).toEqual(50002);
            expect(field.unformat('50,002.00')).toEqual(50002);
        });

        it('should format/unformat zero', function() {
            expect(field.format(0)).toEqual('0');
            expect(field.unformat('0')).toEqual(0);
        });

        it('should not format/unformat a non number string', function() {
            expect(field.format('Asdt')).toEqual('Asdt');
            expect(field.unformat('Asdt')).toEqual('Asdt');
        });

        it('should not format/unformat very large number', function() {
            expect(field.format('1234567890123456789012')).toEqual('1234567890123456789012');
            expect(field.unformat('1234567890123456789012')).toEqual('1234567890123456789012');
        });

        using('int values',[
            {
                expectation: 'should error when value is greater than the max config value',
                value: 2147483648,
                isErrorExpected: true
            },
            {
                expectation: 'should not error when value is equal to the max config value',
                value: 2147483647,
                isErrorExpected: false
            },
            {
                expectation: 'should not error when value is between the min and max config value',
                value: 1,
                isErrorExpected: false
            },
            {
                expectation: 'should not error when value is equal to the min config value',
                value: -2147483648,
                isErrorExpected: false
            },
            {
                expectation: 'should error when value is less than the min config value',
                value: -2147483649,
                isErrorExpected: true
            }
        ], function(value) {
            it(value.expectation, function() {
                var errors = {};
                app.config.sugarMinInt = -2147483648;
                app.config.sugarMaxInt = 2147483647;
                field.model.set(field.name, value.value, {silent: true});
                field._doValidateMinMaxInt(null, errors, $.noop);
                expect(!_.isEmpty(errors)).toBe(value.isErrorExpected);
            });
        });
    });

    describe('with disable format', function() {
        beforeEach(function() {
            app = SugarTest.app;

            field = SugarTest.createField('base', 'int', 'int', 'detail', {
                disable_num_format: true
            });
        });

        afterEach(function() {
            sinon.restore();
            field.dispose();
            field = null;
        });

        it('should format the value not based on user preferences', function() {

            var preferenceStub = sinon.stub(app.user, 'getPreference');
            var value = 123456.502;

            // this is to make sure that test still fails if we are relying on
            // a possible system default precision set to 0
            preferenceStub.withArgs('decimal_precision').returns(4);

            preferenceStub.withArgs('number_grouping_separator').returns('.');
            preferenceStub.withArgs('decimal_separator').returns(',');

            expect(field.format(value)).toEqual('123456502');
            // unformat should still be based on user preferences, since the
            // user might paste a number from other app
            expect(field.unformat('123.456,502')).toEqual(123456.502);
            expect(field.unformat('123456,502')).toEqual(123456.502);

            preferenceStub.withArgs('number_grouping_separator').returns(',');
            preferenceStub.withArgs('decimal_separator').returns('.');

            expect(field.format('123456.502')).toEqual('123456502');
            expect(field.unformat('123456.502')).toEqual(123456.502);
            expect(field.unformat('123,456.502')).toEqual(123456.502);

        });
    });
});
