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
describe('Quotes.Base.Fields.CurrencyTypeDropdown', function() {
    var app;
    var moduleName = 'Quotes';
    var metadata;
    var field;
    var model;
    var defaultCurrenciesObj;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        metadata = {
            fields: {
                'amount': {
                    'name': 'amount',
                    'vname': 'LBL_AMOUNT',
                    'type': 'currency',
                    'dbType': 'currency',
                    'comment': 'Unconverted amount of the opportunity',
                    'importable': 'required',
                    'duplicate_merge': '1',
                    'required': true,
                    'options': 'numeric_range_search_dom',
                    'enable_range_search': true,
                    'validation': {
                        'type': 'range',
                        'min': 0
                    }
                },
                'currency_id': {
                    'name': 'currency_id',
                    'type': 'id',
                    'group': 'currency_id',
                    'vname': 'LBL_CURRENCY',
                    'function': 'getCurrencies',
                    'function_bean': 'Currencies',
                    'reportable': false,
                    'comment': 'Currency used for display purposes'
                },
                'base_rate': {
                    'name': 'base_rate',
                    'vname': 'LBL_CURRENCY_RATE',
                    'type': 'double',
                    'required': true
                }
            },
            views: [],
            layouts: [],
            _hash: 'd7e699e7cf748d05ac311b0165e7591a'
        };

        app.data.declareModel(moduleName, metadata);

        model = app.data.createBean(moduleName, {
            amount: 123456789.12,
            currency_id: '-99',
            base_rate: 1
        });

        var fieldDef = {
            name: 'currency_id',
            type: 'currency-type-dropdown',
            label: 'Currency ID',
            currency_field: 'currency_id',
            base_rate_field: 'base_rate'
        };

        defaultCurrenciesObj = {
            '-99': 'USD',
            '-98': 'EUR'
        };

        sinon.stub(Handlebars, 'compile').callsFake(function() {
            return defaultCurrenciesObj;
        });

        sinon.stub(app.currency, 'getCurrenciesSelector').callsFake(function(data) {
            return data;
        });

        app.user.setPreference('currency_id', '-99');
        app.user.setPreference('decimal_separator', '.');
        app.user.setPreference('number_grouping_separator', ',');

        field = SugarTest.createField('base', 'currency_id', 'currency-type-dropdown', 'edit',
            fieldDef, moduleName, model, undefined, true);
    });

    afterEach(function() {
        sinon.restore();
        metadata = null;
        field.dispose();
        field = null;
        SugarTest.testMetadata.dispose();
    });

    describe('initialize()', function() {
        var options;
        beforeEach(function() {
            options = {
                def: {},
                model: model
            };
        });

        afterEach(function() {
            options = null;
        });

        it('should set currenciesTpls based on currency.getCurrenciesSelector', function() {
            field.initialize(options);
            expect(field.currenciesTpls).toBe(defaultCurrenciesObj);
        });

        it('should set enum options based on currency.getCurrenciesSelector', function() {
            field.initialize(options);
            expect(field.def.options).toBe(defaultCurrenciesObj);
        });

        it('should set enum options based on passed in options', function() {
            options.def.options = {
                '1': 'ABC',
                '2': 'DEF'
            };
            field.initialize(options);
            expect(field.def.options).toBe(options.def.options);
        });

        it('should set enum_width based on passed in options', function() {
            options.def.enum_width = '50%';
            field.initialize(options);
            expect(field.def.enum_width).toBe(options.def.enum_width);
        });

        it('should set searchBarThreshold based on passed in options', function() {
            options.def.searchBarThreshold = 3;
            field.initialize(options);
            expect(field.def.searchBarThreshold).toBe(options.def.searchBarThreshold);
        });

        it('should set currencyIdFieldName default to currency_id', function() {
            field.initialize(options);
            expect(field.currencyIdFieldName).toBe('currency_id');
        });

        it('should set currencyIdFieldName based on passed in options', function() {
            options.def.currency_field = 'custom_currency_id';
            field.initialize(options);
            expect(field.currencyIdFieldName).toBe(options.def.currency_field);
        });

        it('should set baseRateFieldName default to base_rate', function() {
            field.initialize(options);
            expect(field.baseRateFieldName).toBe('base_rate');
        });

        it('should set baseRateFieldName based on passed in options', function() {
            options.def.base_rate_field = 'custom_base_rate';
            field.initialize(options);
            expect(field.baseRateFieldName).toBe(options.def.base_rate_field);
        });

        it('should not override existing model values if model: not new, not copy', function() {
            sinon.stub(field.model, 'isNew').callsFake(function() {
                return false;
            });
            sinon.stub(field.model, 'isCopy').callsFake(function() {
                return false;
            });
            field.model.set({currency_id: 'TEST1'});

            field.initialize(options);
            expect(field.model.get('currency_id')).toBe('TEST1');
        });

        it('should not override existing model values if model: not new, IS copy', function() {
            sinon.stub(field.model, 'isNew').callsFake(function() {
                return false;
            });
            sinon.stub(field.model, 'isCopy').callsFake(function() {
                return true;
            });
            field.model.set({currency_id: 'TEST1'});

            field.initialize(options);
            expect(field.model.get('currency_id')).toBe('TEST1');
        });

        it('should not override existing model values if model: not new, not copy, HAS existing data', function() {
            sinon.stub(field.model, 'isNew').callsFake(function() {
                return false;
            });
            sinon.stub(field.model, 'isCopy').callsFake(function() {
                return false;
            });
            field.model.set({currency_id: 'TEST1'});

            field.initialize(options);
            expect(field.model.get('currency_id')).toBe('TEST1');
        });

        it('should set currency_id model value if model: not new, not copy, no existing data', function() {
            sinon.stub(field.model, 'isNew').callsFake(function() {
                return false;
            });
            sinon.stub(field.model, 'isCopy').callsFake(function() {
                return false;
            });

            field.initialize(options);
            expect(field.model.get('currency_id')).toBe(app.user.getPreference('currency_id'));
        });

        it('should set base_rate model value if model: not new, not copy, no existing data', function() {
            sinon.stub(field.model, 'isNew').callsFake(function() {
                return false;
            });
            sinon.stub(field.model, 'isCopy').callsFake(function() {
                return false;
            });
            field.model.set({currency_id: undefined});

            var currencyID = app.user.getPreference('currency_id');
            var conversionRate = app.metadata.getCurrency(currencyID).conversion_rate;
            field.initialize(options);
            expect(field.model.get('base_rate')).toBe(conversionRate);
        });
    });
});
