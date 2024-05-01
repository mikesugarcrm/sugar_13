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

const Currency = require('../../../src/utils/currency');
const User = require('../../../src/core/user');

describe('Utils/Currency', function() {
    beforeEach(function() {
        this.app = SugarTest.app;
        SugarTest.seedMetadata(true);
    });

    describe('fetching', function() {
        it('should get the base currency id', function() {
            expect(Currency.getBaseCurrencyId()).toEqual('-99');
        });

        it('should get a currency symbol', function() {
            var currency_id = '-99';
            var result = Currency.getCurrencySymbol(currency_id);
            expect(result).toEqual('$');
        });

        it('should return empty string as symbol for unknown currency id', function() {
            var currency_id = 'abcd';
            var result = Currency.getCurrencySymbol(currency_id);
            expect(result).toEqual('');
        });

        it('should get a list of currencies formatted', function() {
            var getCurrencies = sinon.stub(this.app.metadata, 'getCurrencies').callsFake(function() {
                return {
                    '-99': {
                        'name': 'US Dollars',
                        'iso': 'USD',
                        'status': 'Active',
                        'symbol': '$',
                        'rate': 1,
                        'date_entered': null,
                        'date_modified': null,
                    },
                    '4cb9b080-b11a-2be4-f513-50a26d3366fd': {
                        'name': 'Euro',
                        'iso': 'EUR',
                        'status': 'Active',
                        'symbol': '€',
                        'rate': '0.9',
                        'date_entered': '2012-11-13 15:55:37',
                        'date_modified': '2012-11-13 15:55:37',
                    }
                }
            });

            var result = Currency.getCurrenciesSelector(Handlebars.compile('{{symbol}} ({{iso}})'));
            expect(result).toEqual({
                '-99': '$ (USD)',
                '4cb9b080-b11a-2be4-f513-50a26d3366fd': '€ (EUR)'
            });

            getCurrencies.restore();
        });
    });

    describe('formatting', function() {
        it('should not format a blank value', function() {
            var amount = '';
            var currency_id = '-99';
            var result = Currency.formatAmount(amount, currency_id);
            expect(result).toEqual('');
        });

        it('should not format a null value', function() {
            var amount = null,
                currency_id = '-99';
            var result = Currency.formatAmount(amount, currency_id);
            expect(result).toEqual(null);
        });

        it('should not format an undefined value', function() {
            var amount = undefined;
            var currency_id = '-99';
            var result = Currency.formatAmount(amount, currency_id);
            expect(result).toEqual(undefined);
        });

        it('should not format a non-numeric value', function() {
            var amount = 'abc';
            var currency_id = '-99';
            var result = Currency.formatAmount(amount, currency_id);
            expect(result).toEqual('abc');
        });

        it('should format a currency', function() {
            var amount  = 1000;
            var currency_id = '-99';
            var result = Currency.formatAmount(amount, currency_id);
            expect(result).toEqual('$1,000.00');
        });

        it('should format a currency depending on config', function() {
            var amount = 1000,
                currency_id = '-99',
                getConfig = sinon.stub(this.app.metadata, 'getConfig').callsFake(function () {
                    return {
                        defaultCurrencySignificantDigits: 3
                    };
                });

            var result = Currency.formatAmount(amount, currency_id);

            expect(result).toEqual('$1,000.000');
            getConfig.restore();
        });

        it('should format a currency when number_grouping_separator is empty', function() {
            var amount = 1000,
                currency_id = '-99',
                number_grouping_separator = '';
            var result = Currency.formatAmount(amount, currency_id, null, number_grouping_separator);
                expect(result).toEqual('$1000.00');
        });

        it('should format a currency to user locale', function() {
            User.setPreference('decimal_precision', 3);
            User.setPreference('decimal_separator', ',');
            User.setPreference('number_grouping_separator', '#');
            var amount  = 1000,
                currency_id = '-99';
            var result = Currency.formatAmountLocale(amount, currency_id);
            expect(result).toEqual('$1#000,000');
        });

        it('should unformat a currency from user locale', function() {
            User.setPreference('decimal_precision', 2);
            User.setPreference('decimal_separator', '.');
            User.setPreference('number_grouping_separator', ',');
            var amount  = '$1,000.00';
            var result = Currency.unformatAmountLocale(amount);
            expect(result).toEqual('1000.00');
        });

        it('should unformat starting with decimal ok', function() {
            User.setPreference('decimal_precision', 2);
            User.setPreference('decimal_separator', '.');
            User.setPreference('number_grouping_separator', ',');
            var amount  = '.5';
            var result = Currency.unformatAmountLocale(amount);
            expect(result).toEqual('.5');
        });

        it('should unformat a negative currency from user locale', function() {
            User.setPreference('decimal_precision', 2);
            User.setPreference('decimal_separator', '.');
            User.setPreference('number_grouping_separator', ',');
            var amount  = '$-1,000.00';
            var result = Currency.unformatAmountLocale(amount);
            expect(result).toEqual('-1000.00');
        });

        it('should unformat a negative number starting with decimal ok', function() {
            User.setPreference('decimal_precision', 2);
            User.setPreference('decimal_separator', '.');
            User.setPreference('number_grouping_separator', ',');
            var amount  = '-.5';
            var result = Currency.unformatAmountLocale(amount);
            expect(result).toEqual('-.5');
        });
    });

    describe('converting', function() {
        it('should convert a currency with given rate', function() {
            var amount = 1000;
            var rate = 0.5;
            var result = Currency.convertWithRate(amount, rate);
            expect(result).toEqual('2000.000000');
            amount = 1000;
            rate = 2.0;
            result = Currency.convertWithRate(amount, rate);
            expect(result).toEqual('500.000000');
        });

        it('should return us dollar amount converted to euros', function() {
            var amount = 1000;
            var currencyId = '-99';
            var euroCurrencyId = 'abc123';
            var result = Currency.convertAmount(amount, currencyId, euroCurrencyId);
            expect(result).toEqual('900.000000');
        });

        it('should return euros amount converted to us dollar', function() {
            var amount = 900;
            var currencyId = 'abc123';
            var euroCurrencyId = '-99';
            var result = Currency.convertAmount(amount, currencyId, euroCurrencyId);
            expect(result).toEqual('1000.000000');
        });

        it('should return same amount for same currency conversion', function() {
            var amount = 1000;
            var currencyId = '-99';
            var result = Currency.convertAmount(amount, currencyId, currencyId);
            expect(result).toEqual('1000.000000');
            amount = 1000;
            currencyId = 'abc123';
            result = Currency.convertAmount(amount, currencyId, currencyId);
            expect(result).toEqual('1000.000000');
        });

        it('should return euros amount converted to base', function() {
            var amount = 900;
            var currencyId = 'abc123';
            var result = Currency.convertToBase(amount, currencyId);
            expect(result).toEqual('1000.000000');
        });

        it('should return euros amount converted from base', function() {
            var amount = 1000;
            var currencyId = 'abc123';
            var result = Currency.convertFromBase(amount, currencyId);
            expect(result).toEqual('900.000000');
        });
    });

    describe('getBaseCurrency()', function() {
        it('should return base currency id', function() {
            expect(Currency.getBaseCurrency().id).toBe('-99');
        });

        it('should return base currency symbol', function() {
            expect(Currency.getBaseCurrency().symbol).toBe('$');
        });

        it('should return base currency conversion_rate', function() {
            expect(Currency.getBaseCurrency().conversion_rate).toBe('1.0');
        });

        it('should return base currency iso4217', function() {
            expect(Currency.getBaseCurrency().iso4217).toBe('USD');
        });

        it('should return base currency currency_id', function() {
            expect(Currency.getBaseCurrency().currency_id).toBe('-99');
        });
    });
});
