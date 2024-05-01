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
describe('Base.Field.DiscountSelect', function() {
    var app;
    var field;
    var fieldDef;
    var fieldModel;
    var moduleName = 'RevenueLineItems';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadComponent('base', 'field', 'discount-select');
        SugarTest.testMetadata.set();

        fieldModel = app.data.createBean(moduleName, {
            currency_id: '-99'
        });

        fieldDef = {
            name: 'discount-select',
            type: 'discount-select',
            options: []
        };

        sinon.stub(app.metadata, 'getCurrency').callsFake(function() {
            return {
                id: '-99',
                name: 'US Dollar',
                symbol: '$'
            };
        });

        sinon.stub(app.lang, 'get').callsFake(function() {
            return '% Percent';
        });

        field = SugarTest.createField(
            'base',
            'discount-select',
            'discount-select',
            'detail',
            fieldDef,
            'base',
            fieldModel
        );
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();

        field = null;
        fieldDef = null;
        app = null;
    });

    describe('initialize()', function() {
        beforeEach(function() {
            sinon.stub(field, 'fetchCurrency').callsFake(function() {});
            sinon.stub(field, 'updateDropdownSymbol').callsFake(function() {});
            sinon.stub(field, 'loadEnumOptions').callsFake(function() {});
        });

        it('should call fetchCurrency and set currentCurrency', function() {
            expect(field.currentCurrency.name).toBe('US Dollar');
            expect(field.currentCurrency.symbol).toBe('$');
        });

        it('should call updateDropdownSymbol and set currentDropdownSymbol', function() {
            expect(field.currentDropdownSymbol).toBe('$');
        });

        it('should call loadEnumOptions and set items', function() {
            expect(field.items).not.toBeEmpty();
        });
    });

    describe('getSelect2Options()', function() {
        it('should override some of the properties from the super method', function() {
            var select2Options = field.getSelect2Options();
            expect(select2Options).toEqual(jasmine.objectContaining({
                placeholder: field.currentDropdownSymbol,
                containerCssClass: field.containerCssClass,
                dropdownCss: {width: 'auto'},
                width: '28px',
            }));
        });
    });

    describe('_render()', function() {
        var onStub;
        var findStub;

        beforeEach(function() {
            field.model.set('currency_id', '-99', {silent: true});

            onStub = sinon.stub();

            sinon.stub(field, '_super').callsFake(function() {});
            sinon.stub(field, '$').callsFake(function() {
                return {
                    on: onStub,
                };
            });
            sinon.stub(field, 'fetchCurrency').callsFake(function() {});
            sinon.stub(field, 'loadEnumOptions').callsFake(function() {});
            sinon.stub(field, 'updateDropdownSymbol').callsFake(function() {});
            sinon.stub(field, 'updateDropdownText').callsFake(function() {});

            field.currentDropdownSymbol = '$';

            field._render();
        });

        afterEach(function() {
            onStub = null;
        });

        it('should call on', function() {
            expect(onStub).toHaveBeenCalled('select2-close');
        });

        it('should call updateDropDownText and update the dropdown symbol', function() {
            expect(field.updateDropdownText).toHaveBeenCalledWith('$');
        });

        it('should call fetchCurrency and set currentCurrency', function() {
            expect(field.currentCurrency.name).toBe('US Dollar');
            expect(field.currentCurrency.symbol).toBe('$');
        });

        it('should call updateDropdownSymbol and set currentDropdownSymbol', function() {
            expect(field.currentDropdownSymbol).toBe('$');
        });

        it('should call loadEnumOptions and set items', function() {
            expect(field.items).not.toBeEmpty();
        });
    });

    describe('handleDiscountSelectFieldChange()', function() {
        var modelParam;
        var fieldParam;
        var options;

        beforeEach(function() {
            modelParam = {};
            fieldParam = false;
            options = {};
            field.name = 'discount_select';

            sinon.stub(field, 'updateDropdownSymbol').callsFake(function() {});
            sinon.stub(field, 'updateDropdownText').callsFake(function() {});
        });

        afterEach(function() {
            modelParam = null;
            fieldParam = null;
            options = null;
        });

        it('should do nothing if the model does not exist', function() {
            field.handleDiscountSelectFieldChange(modelParam, fieldParam, options);

            expect(field.updateDropdownSymbol).not.toHaveBeenCalled();
            expect(field.updateDropdownText).not.toHaveBeenCalled();
        });

        it('it should have discount_select false as a default', function() {
            modelParam = field.model;
            field.handleDiscountSelectFieldChange(modelParam, fieldParam, options);

            expect(field.model.get('discount_select')).toBeFalsy();
        });

        it('should set discount_select true if the user clicked on the percent button', function() {
            modelParam = field.model;
            fieldParam = true;
            field.handleDiscountSelectFieldChange(modelParam, fieldParam, options);

            expect(field.model.get('discount_select')).toBeTruthy();
        });

        it('should revert any changes if cancel is clicked', function() {
            modelParam = field.model;
            fieldParam = true;
            options = {revert: true};
            field.handleDiscountSelectFieldChange(modelParam, fieldParam, options);

            expect(field.model.get('discount_select')).toBeFalsy();
        });
    });

    describe('handleCurrencyFieldChange()', function() {
        var modelParam;
        var fieldParam;
        var options;
        var textStub;

        beforeEach(function() {
            modelParam = {};
            fieldParam = '100';
            options = {};
            textStub = sinon.stub();

            sinon.stub(field, '$').callsFake(function() {
                return {
                    text: textStub
                };
            });

            sinon.stub(field, 'fetchCurrency').callsFake(function() {});
            sinon.stub(field, 'loadEnumOptions').callsFake(function() {});
            sinon.stub(field, 'updateDropdownSymbol').callsFake(function() {});
            sinon.stub(field, 'updateDropdownText').callsFake(function() {});
        });

        afterEach(function() {
            modelParam = null;
            fieldParam = null;
            options = null;
            textStub = null;
        });

        it('should not do anything if the currency stays the same', function() {
            modelParam = field.model;
            fieldParam = '-99';
            field.handleCurrencyFieldChange(modelParam, fieldParam, options);

            expect(field.model.get('currency_id')).toEqual('-99');
        });

        it('should call fetchCurrency and set details for the new currency', function() {
            modelParam = field.model;
            field.handleCurrencyFieldChange(modelParam, fieldParam, options);

            expect(field.currentCurrency.name).toBe('US Dollar');
            expect(field.currentCurrency.symbol).toBe('$');
        });

        it('should call loadEnumOptions and set new dropdown options', function() {
            modelParam = field.model;
            field.handleCurrencyFieldChange(modelParam, fieldParam, options);

            expect(field.items).not.toBeEmpty();
        });

        it('should call updateDropdownSymbol and set a new dropdown symbol', function() {
            modelParam = field.model;
            field.handleCurrencyFieldChange(modelParam, fieldParam, options);

            expect(field.currentDropdownSymbol).toBe('$');
        });

        it('should call updateDropdownText and set the dropdown symbol on the dropdown element', function() {
            modelParam = field.model;
            field.handleCurrencyFieldChange(modelParam, fieldParam, options);

            expect(field.updateDropdownText).toHaveBeenCalledWith('$');
        });

        it('should revert all changes if cancel is clicked', function() {
            modelParam = field.model;
            options = {revert: true};
            field.handleCurrencyFieldChange(modelParam, fieldParam, options);

            expect(field.currentDropdownSymbol).toBe('$');
        });
    });

    describe('fetchCurrency()', function() {
        beforeEach(function() {
            field.currentCurrency = null;
        });

        it('should fetch the currency and store it on the class', function() {
            field.fetchCurrency();

            expect(field.currentCurrency.id).toBe('-99');
            expect(field.currentCurrency.name).toBe('US Dollar');
        });
    });

    describe('bindDataChange()', function() {
        beforeEach(function() {
            sinon.stub(field.model, 'on').callsFake(function() {});

            field.bindDataChange();
        });

        it('should call field.model on change:discount_select', function() {
            expect(field.model.on).toHaveBeenCalledWith('change:' + field.name);
        });

        it('should call field.model on change:currency_id', function() {
            expect(field.model.on).toHaveBeenCalledWith('change:currency_id');
        });
    });
});
