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
describe('ForecastWorksheets.Base.Fields.Currency', function() {
    var app,
        model,
        moduleName,
        metadata;

    beforeEach(function() {
        SugarTest.loadPlugin('ClickToEdit');
        SugarTest.loadComponent('base', 'field', 'currency');
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        moduleName = 'ForecastWorksheets';
        metadata = {
            fields: {
                "amount": {
                    "name": "amount",
                    "vname": "LBL_AMOUNT",
                    "type": "currency",
                    "dbType": "currency",
                    "comment": "Unconverted amount of the opportunity",
                    "importable": "required",
                    "duplicate_merge": "1",
                    "required": true,
                    "options": "numeric_range_search_dom",
                    "enable_range_search": true,
                    "validation": {
                        "type": "range",
                        "min": 0
                    }
                },
                "currency_id": {
                    "name": "currency_id",
                    "type": "id",
                    "group": "currency_id",
                    "vname": "LBL_CURRENCY",
                    "function": {
                        "name": "getCurrencyDropDown",
                        "returns": "html"
                    },
                    "reportable": false,
                    "comment": "Currency used for display purposes"
                },
                "base_rate": {
                    "name": "base_rate",
                    "vname": "LBL_CURRENCY_RATE",
                    "type": "double",
                    "required": true
                }
            },
            views: [],
            layouts: [],
            _hash: "d7e699e7cf748d05ac311b0165e7591a"
        };

        app = SugarTest.app;

        app.data.declareModel(moduleName, metadata);
        app.user.setPreference('currency_id', '-99');

        model = app.data.createBean(moduleName, {
            amount: 123456789.12,
            currency_id: '-99',
            base_rate: 1
        });
        model.isCopy = function() {
            return (model.isCopied === true);
        };
    });

    afterEach(function() {
        sinon.restore();
        delete app.plugins.plugins['field']['ClickToEdit'];
        delete app.plugins.plugins['view']['ClickToEdit'];
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        moduleName = null;
        metadata = null;
        SugarTest.testMetadata.dispose();
    });

    describe('ClickToEdit Plugin', function() {
        var field;
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'amount',
                'currency',
                'detail',
                {
                    related_fields: ['currency_id', 'base_rate'],
                    currency_field: 'currency_id',
                    base_rate_field: 'base_rate'
                },
                moduleName,
                model,
                undefined,
                true
            );
            field.action = 'detail';
            sinon.stub(field, 'render').callsFake(function() {});
            sinon.stub(field, 'setCurrencyValue').callsFake(function() {});
        });

        afterEach(function() {
            field.render.restore();
            field.setCurrencyValue.restore();
            field = null;
        });


        it('should have ClickToEdit Plugin registered', function() {
            expect(field.plugins).toContain('ClickToEdit');
        });

        describe('ClickToEdit fieldValueChanged', function() {
            beforeEach(function() {
                field.value = '1.000000';
            });
            afterEach(function() {
                field.value = undefined;
            });

            it('should return true when adding 1', function() {
                sinon.stub(field.$el, 'find').callsFake(function() {
                    return {
                        val: function() {
                            return '+1.000000';
                        }
                    }
                });
                expect(field._fieldValueChanged(field)).toBeTruthy();
            });

            it('should return true when subtracting 1', function() {
                sinon.stub(field.$el, 'find').callsFake(function() {
                    return {
                        val: function() {
                            return '-1.000000';
                        }
                    }
                });
                expect(field._fieldValueChanged(field)).toBeTruthy();
            });

            it('should return true when adding a percent', function() {
                sinon.stub(field.$el, 'find').callsFake(function() {
                    return {
                        val: function() {
                            return '+1%';
                        }
                    }
                });
                expect(field._fieldValueChanged(field)).toBeTruthy();
            });

            it('should return true when subtracting a percent', function() {
                sinon.stub(field.$el, 'find').callsFake(function() {
                    return {
                        val: function() {
                            return '-1%';
                        }
                    }
                });
                expect(field._fieldValueChanged(field)).toBeTruthy();
            });

            it('should return false when values are the same', function() {
                sinon.stub(field.$el, 'find').callsFake(function() {
                    return {
                        val: function() {
                            return '1.000000';
                        }
                    }
                });
                expect(field._fieldValueChanged(field)).toBeFalsy();
            });
        });

        describe('_verifyCurrencyValue', function() {
            beforeEach(function() {
                field.value = '1.000000';
                app.user.setPreference('number_grouping_separator', 'A');
                app.user.setPreference('decimal_separator', 'z');
            });
            afterEach(function() {
                field.value = undefined;
                app.user.setPreference('number_grouping_separator', ',');
                app.user.setPreference('decimal_separator', '.');
            });

            describe('when using users preferences', function() {
                it('should return true when format matches users format', function() {
                    expect(field._fieldVerifyCurrencyValue('1A000z00')).toBeTruthy();
                });

                it('should return true when number starts with decimal separator', function() {
                    expect(field._fieldVerifyCurrencyValue('z05')).toBeTruthy();
                });

                it('should return false when format does not match users format', function() {
                    expect(field._fieldVerifyCurrencyValue('1,000x00')).toBeFalsy();
                });

                it('should return false when number starts with an invalid decimal separator', function() {
                    expect(field._fieldVerifyCurrencyValue('.05')).toBeFalsy();
                });
            });

            describe('should fall back to system defaults when users are undefined', function() {
                beforeEach(function() {
                    app.user.setPreference('number_grouping_separator', undefined);
                    app.user.setPreference('decimal_separator', undefined);
                    sinon.stub(app.metadata, 'getConfig').callsFake(function() {
                        return {
                            'defaultDecimalSeparator': 'd',
                            'defaultNumberGroupingSeparator': 'g'
                        }
                    });
                });

                afterEach(function() {
                    app.user.setPreference('number_grouping_separator', ',');
                    app.user.setPreference('decimal_separator', '.')
                });

                it('and return false when they do not match', function() {
                    expect(field._fieldVerifyCurrencyValue('1,0000x00')).toBeFalsy();
                });

                it('and return true when they do match the system defaults', function() {
                    expect(field._fieldVerifyCurrencyValue('d05')).toBeTruthy();
                    expect(field._fieldVerifyCurrencyValue('122g212d05')).toBeTruthy();
                });
            });

            describe('should fall back to hardcoded values when no user prefs and no system defaults', function() {
                beforeEach(function() {
                    app.user.setPreference('number_grouping_separator', undefined);
                    app.user.setPreference('decimal_separator', undefined);
                    sinon.stub(app.metadata, 'getConfig').callsFake(function() {
                        return {}
                    });
                });

                afterEach(function() {
                    app.user.setPreference('number_grouping_separator', ',');
                    app.user.setPreference('decimal_separator', '.')
                });

                it('and return false when they do not match', function() {
                    expect(field._fieldVerifyCurrencyValue('1,0000x00')).toBeFalsy();
                });

                it('and return true when they do match the system defaults', function() {
                    expect(field._fieldVerifyCurrencyValue('.05')).toBeTruthy();
                    expect(field._fieldVerifyCurrencyValue('122,212.05')).toBeTruthy();
                });
            });
        });

        describe('_parsePercentage', function() {
            beforeEach(function() {
                sinon.stub(field.model, 'get').callsFake(function() {
                    return '1.000000'
                });
                field.value = '1.000000';
            });
            afterEach(function() {
                field.value = undefined;
                app.user.setPreference('number_grouping_separator', ',');
                app.user.setPreference('decimal_separator', '.');
            });

            describe('should use users preferences', function() {
                beforeEach(function() {
                    app.user.setPreference('number_grouping_separator', 'A');
                    app.user.setPreference('decimal_separator', ',');
                });

                afterEach(function() {
                    app.user.setPreference('number_grouping_separator', ',');
                    app.user.setPreference('decimal_separator', '.');
                });

                it('should increase field value by +50.8%', function() {
                    expect(field._fieldParsePercentage('+50,8%')).toEqual('$1,51');
                });

                it('should increase field value by +50.8', function() {
                    expect(field._fieldParsePercentage('+50,8')).toEqual('$51,80');
                });

                it('should decrease field value by -50.8%', function() {
                    expect(field._fieldParsePercentage('-50,8%')).toEqual('$0,49');
                });

                it('should decrease field value by -50.8', function() {
                    expect(field._fieldParsePercentage('-50,8')).toEqual('$-49,80');
                });

                it('should increase field value by +.5', function() {
                    expect(field._fieldParsePercentage('+,5')).toEqual('$1,50');
                });

                it('should decrease field value by -.5', function() {
                    expect(field._fieldParsePercentage('-,5')).toEqual('$0,50');
                });

                it('should increase field value by +.5%', function() {
                    expect(field._fieldParsePercentage('+,5%')).toEqual('$1,01');
                });

                it('should decrease field value by -.5%', function() {
                    expect(field._fieldParsePercentage('-,5%')).toEqual('$1,00');
                });

                it('should increase field value by +1000', function() {
                    expect(field._fieldParsePercentage('+1000')).toEqual('$1A001,00');
                });

                it('should decrease field value by -1000', function() {
                    expect(field._fieldParsePercentage('-1000')).toEqual('$-999,00');
                });

                it('should increase field value by +1A000', function() {
                    expect(field._fieldParsePercentage('+1A000')).toEqual('$1A001,00');
                });

                it('should decrease field value by -1A000', function() {
                    expect(field._fieldParsePercentage('-1A000')).toEqual('$-999,00');
                });
            });

            describe('should fall back to system defaults when users are undefined', function() {
                beforeEach(function() {
                    app.user.setPreference('number_grouping_separator', undefined);
                    app.user.setPreference('decimal_separator', undefined);
                    sinon.stub(app.metadata, 'getConfig').callsFake(function() {
                        return {
                            'defaultDecimalSeparator': 'd',
                            'defaultNumberGroupingSeparator': 'g'
                        }
                    });
                    sinon.stub(field, 'format').callsFake(function(amount, currencyId) {
                        return app.currency.formatAmount(amount, currencyId, undefined, 'g', 'd');
                    });

                    sinon.stub(field, 'unformat').callsFake(function(amount) {
                        return app.utils.unformatNumberString(amount, 'g', 'd');
                    });
                });

                afterEach(function() {
                    app.user.setPreference('number_grouping_separator', ',');
                    app.user.setPreference('decimal_separator', '.')
                });

                it('should increase field value by +50.8%', function() {
                    expect(field._fieldParsePercentage('+50d8%')).toEqual('$1d51');
                });

                it('should increase field value by +50.8', function() {
                    expect(field._fieldParsePercentage('+50d8')).toEqual('$51d80');
                });

                it('should decrease field value by -50.8%', function() {
                    expect(field._fieldParsePercentage('-50d8%')).toEqual('$0d49');
                });

                it('should decrease field value by -50.8', function() {
                    expect(field._fieldParsePercentage('-50d8')).toEqual('$-49d80');
                });

                it('should increase field value by +.5', function() {
                    expect(field._fieldParsePercentage('+d5')).toEqual('$1d50');
                });

                it('should decrease field value by -.5', function() {
                    expect(field._fieldParsePercentage('-d5')).toEqual('$0d50');
                });

                it('should increase field value by +.5%', function() {
                    expect(field._fieldParsePercentage('+d5%')).toEqual('$1d01');
                });

                it('should decrease field value by -.5%', function() {
                    expect(field._fieldParsePercentage('-d5%')).toEqual('$1d00');
                });

                it('should increase field value by +1000', function() {
                    expect(field._fieldParsePercentage('+1000')).toEqual('$1g001d00');
                });

                it('should decrease field value by -1000', function() {
                    expect(field._fieldParsePercentage('-1000')).toEqual('$-999d00');
                });

                it('should increase field value by +1,000', function() {
                    expect(field._fieldParsePercentage('+1g000')).toEqual('$1g001d00');
                });

                it('should decrease field value by -1,000', function() {
                    expect(field._fieldParsePercentage('-1g000')).toEqual('$-999d00');
                });
            });

            describe('should fall back to hardcoded values when no user prefs and no system defaults', function() {
                beforeEach(function() {
                    app.user.setPreference('number_grouping_separator', undefined);
                    app.user.setPreference('decimal_separator', undefined);
                    sinon.stub(app.metadata, 'getConfig').callsFake(function() {
                        return {}
                    });
                });

                afterEach(function() {
                    app.user.setPreference('number_grouping_separator', ',');
                    app.user.setPreference('decimal_separator', '.')
                });

                it('should increase field value by +50.8%', function() {
                    expect(field._fieldParsePercentage('+50.8%')).toEqual('$1.51');
                });

                it('should decrease field value by -50.8%', function() {
                    expect(field._fieldParsePercentage('-50.8%')).toEqual('$0.49');
                });

                it('should increase field value by +.5%', function() {
                    expect(field._fieldParsePercentage('+.5%')).toEqual('$1.01');
                });

                it('should decrease field value by -.5%', function() {
                    expect(field._fieldParsePercentage('-.5%')).toEqual('$1.00');
                });

                it('should increase field value by +50.8', function() {
                    expect(field._fieldParsePercentage('+50.8')).toEqual('$51.80');
                });

                it('should decrease field value by -50.8', function() {
                    expect(field._fieldParsePercentage('-50.8')).toEqual('$-49.80');
                });

                it('should increase field value by +.5', function() {
                    expect(field._fieldParsePercentage('+.5')).toEqual('$1.50');
                });

                it('should decrease field value by -.5', function() {
                    expect(field._fieldParsePercentage('-.5')).toEqual('$0.50');
                });

                it('should increase field value by +1000', function() {
                    expect(field._fieldParsePercentage('+1000')).toEqual('$1,001.00');
                });

                it('should decrease field value by -1000', function() {
                    expect(field._fieldParsePercentage('-1000')).toEqual('$-999.00');
                });

                it('should increase field value by +1,000', function() {
                    expect(field._fieldParsePercentage('+1,000')).toEqual('$1,001.00');
                });

                it('should decrease field value by -1,000', function() {
                    expect(field._fieldParsePercentage('-1,000')).toEqual('$-999.00');
                });
            });
        });

        describe('validateField', function() {
            beforeEach(function() {
                sinon.stub(field.model, 'get').callsFake(function() {
                    return '1.000000'
                });
                field.value = '1.000000';
                app.user.setPreference('number_grouping_separator', undefined);
                app.user.setPreference('decimal_separator', undefined);
                sinon.stub(app.metadata, 'getConfig').callsFake(function() {
                    return {}
                });
            });

            afterEach(function() {
                app.user.setPreference('number_grouping_separator', ',');
                app.user.setPreference('decimal_separator', '.')
            });

            it('should return false for +0,5', function() {
                expect(field._fieldDoValidate(field, '+0,5')).toBeFalsy();
            });

            it('should return false for +,5', function() {
                expect(field._fieldDoValidate(field, '+,5')).toBeFalsy();
            });

            it('should return false for -0,5', function() {
                expect(field._fieldDoValidate(field, '-0,5')).toBeFalsy();
            });

            it('should return false for -,5', function() {
                expect(field._fieldDoValidate(field, '-,5')).toBeFalsy();
            });

            it('should return 1.50 for +0.5', function() {
                expect(field._fieldDoValidate(field, '+0.5')).toEqual("$1.50");
            });

            it('should return 0.50 for -0.5', function() {
                expect(field._fieldDoValidate(field, '-0.5')).toEqual("$0.50");
            });

            it('should return 1.50 for +.5', function() {
                expect(field._fieldDoValidate(field, '+.5')).toEqual("$1.50");
            });

            it('should return 0.50 for -.5', function() {
                expect(field._fieldDoValidate(field, '-.5')).toEqual("$0.50");
            });

            it('should return false for 1000.00+100', function() {
                expect(field._fieldDoValidate(field, '1000.00+100')).toBeFalsy();
            });

            it('should return false for 1000.00-100', function() {
                expect(field._fieldDoValidate(field, '1000.00-100')).toBeFalsy();
            });

            it('should return false for 1000.00+asdfasdfa', function() {
                expect(field._fieldDoValidate(field, '1000.00+asdfasdfa')).toBeFalsy();
            });

            it('should return false for 1000.00asdfasdfa', function() {
                expect(field._fieldDoValidate(field, '1000.00asdfasdfa')).toBeFalsy();
            });

            it('should increase field value by +1000', function() {
                expect(field._fieldDoValidate(field, '+1000')).toEqual('$1,001.00');
            });

            it('should return false for -1000 as negative numbers are not supported', function() {
                expect(field._fieldDoValidate(field, '-1000')).toBeFalsy();
            });

            it('should return false for +1,000,00.00', function() {
                expect(field._fieldDoValidate(field, '+1,000,00.00')).toBeFalsy();
            });
        });
    });
});
