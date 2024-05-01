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
describe('Base.Field.DiscountAmount', function() {
    var app;
    var field;
    var fieldDef;
    var moduleName = 'RevenueLineItems';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        app.user.setPreference('currency_id', '-99');
        app.user.setPreference('decimal_separator', '.');
        app.user.setPreference('number_grouping_separator', ',');

        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'field', 'currency');
        SugarTest.loadComponent('base', 'field', 'discount-amount');

        var testModel = app.data.createBean(moduleName, {
            jasmin_test: 123456789.12,
            currency_id: '-99',
            base_rate: 1,
            discount_select: false,
        });
        testModel.isCopy = function() {
            return (testModel.isCopied === true);
        };

        sinon.stub(app.currency, 'convertAmount').callsFake(function() {
            return 100.00;
        });

        var fieldComponent = {
            name: 'jasmin_test',
            type: 'discount-amount',
            viewName: 'detail',
            fieldDef: {
                name: 'jasmin_test',
                type: 'discount-amount',
            },
            model: testModel,
            context: null,
            loadFromModule: false
        };

        sandbox = sinon.createSandbox();

        field = SugarTest.createField(fieldComponent);
    });

    afterEach(function() {
        field.dispose();
        SugarTest.testMetadata.dispose();
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();

        field = null;
        fieldDef = null;
        app = null;
    });

    describe('handleCurrencyFieldChange()', function() {
        var modelParam;
        var fieldParam;
        var options;

        beforeEach(function() {
            modelParam = {};
            fieldParam = '-99';
            field.name = 'discount_amount';
            field.model.set('discount_amount', 123456789.12, {silent: true});
            options = {};
        });

        afterEach(function() {
            modelParam = null;
            fieldParam = null;
            options = null;
        });

        it('should not do anything if the currency stays the same', function() {
            modelParam = field.model;
            field.handleCurrencyFieldChange(modelParam, fieldParam, options);

            expect(field.model.get('currency_id')).toEqual('-99');
        });

        it('should convert discount_amount if the currency has changed', function() {
            modelParam = field.model;
            fieldParam = '100';
            field.handleCurrencyFieldChange(modelParam, fieldParam, options);

            expect(field.model.get('discount_amount')).toBe(100.00);
        });

        it('should revert any changes if cancel is clicked', function() {
            modelParam = field.model;
            options = {revert: true};
            field.handleCurrencyFieldChange(modelParam, fieldParam, options);

            expect(field.model.get('discount_amount')).toBe(123456789.12);
        });
    });

    describe('format()', function() {
        beforeEach(function() {
            field.discountFieldName = 'discount_select';

            sandbox.stub(field, '_super').callsFake(function() {
                return true;
            });

            sandbox.stub(app.utils, 'formatNumberLocale').callsFake(function() {
                return true;
            });
        });

        afterEach(function() {
            field.model.clear({silent: true});
            sandbox.restore();
        });

        it('should call _super when discount_select is default (false)', function() {
            field.format(10.10);

            expect(field._super).toHaveBeenCalled();
            expect(app.utils.formatNumberLocale).not.toHaveBeenCalled();
        });

        it('should call _super when discount_select is set to false', function() {
            field.model.set('discount_select', false, {silent: true});
            field.format(10.10);

            expect(field._super).toHaveBeenCalled();
            expect(app.utils.formatNumberLocale).not.toHaveBeenCalled();
        });

        it('should call _super when discount_select is "false"', function() {
            field.model.set('discount_select', 'false', {silent: true});
            field.format(10.10);

            expect(field._super).toHaveBeenCalled();
            expect(app.utils.formatNumberLocale).not.toHaveBeenCalled();
        });

        it('should call formatNumberLocale when discount_select is set to true', function() {
            field.model.set('discount_select', true, {silent: true});
            field.format(10.10);

            expect(field._super).not.toHaveBeenCalled();
            expect(app.utils.formatNumberLocale).toHaveBeenCalled();
        });

        it('should call formatNumberLocale when discount_select is set to "true"', function() {
            field.model.set('discount_select', 'true', {silent: true});
            field.format(10.10);

            expect(field._super).not.toHaveBeenCalled();
            expect(app.utils.formatNumberLocale).toHaveBeenCalled();
        });
    });

    describe('unformat()', function() {
        beforeEach(function() {
            field.discountFieldName = 'discount_select';

            sandbox.stub(field, '_super').callsFake(function() {
                return true;
            });

            sandbox.stub(app.utils, 'unformatNumberStringLocale').callsFake(function() {
                return true;
            });
        });

        afterEach(function() {
            field.model.clear({silent: true});
            sandbox.restore();
        });

        it('should call _super when discount_select is default (false)', function() {
            field.unformat(10.10);

            expect(field._super).toHaveBeenCalled();
            expect(app.utils.unformatNumberStringLocale).not.toHaveBeenCalled();
        });

        it('should call _super when discount_select is set to false', function() {
            field.model.set('discount_select', false, {silent: true});
            field.unformat(10.10);

            expect(field._super).toHaveBeenCalled();
            expect(app.utils.unformatNumberStringLocale).not.toHaveBeenCalled();
        });

        it('should call _super when discount_select is set to "false"', function() {
            field.model.set('discount_select', 'false', {silent: true});
            field.unformat(10.10);

            expect(field._super).toHaveBeenCalled();
            expect(app.utils.unformatNumberStringLocale).not.toHaveBeenCalled();
        });

        it('should call unformatNumberStringLocale when discount_select is set to true', function() {
            field.model.set('discount_select', true, {silent: true});
            field.unformat(10.10);

            expect(app.utils.unformatNumberStringLocale).toHaveBeenCalled();
            expect(field._super).not.toHaveBeenCalled();
        });

        it('should call unformatNumberStringLocale when discount_select is set to "true"', function() {
            field.model.set('discount_select', 'true', {silent: true});
            field.unformat(10.10);

            expect(app.utils.unformatNumberStringLocale).toHaveBeenCalled();
            expect(field._super).not.toHaveBeenCalled();
        });
    });

    describe('_loadTemplate()', function() {
        beforeEach(function() {
            field.discountFieldName = 'discount_select';

            sandbox.stub(field, '_super').callsFake(function() {
                return true;
            });

            sandbox.stub(app.template, 'getField').callsFake(function() {
                return true;
            });

            field.view.action = 'detail';
            field.action = 'detail';
        });

        afterEach(function() {
            field.model.clear({silent: true});
            sandbox.restore();
        });

        it('should call getField when discount_select is default (false)', function() {
            field._loadTemplate();

            expect(field._super).toHaveBeenCalled();
            expect(app.template.getField).toHaveBeenCalledWith('currency', 'detail');
        });

        it('should call getField when discount_select is set to false', function() {
            field.model.set('discount_select', false, {silent: true});
            field._loadTemplate();

            expect(field._super).toHaveBeenCalled();
            expect(app.template.getField).toHaveBeenCalledWith('currency', 'detail');
        });

        it('should call getField when discount_select is set to "false"', function() {
            field.model.set('discount_select', 'false', {silent: true});
            field._loadTemplate();

            expect(field._super).toHaveBeenCalled();
            expect(app.template.getField).toHaveBeenCalledWith('currency', 'detail');
        });

        it('should call _super when discount_select is set to true', function() {
            field.model.set('discount_select', true, {silent: true});
            field._loadTemplate();

            expect(field._super).toHaveBeenCalled();
            expect(app.template.getField).not.toHaveBeenCalled();
        });

        it('should call getField when discount_select is set to "true"', function() {
            field.model.set('discount_select', 'true', {silent: true});
            field._loadTemplate();

            expect(field._super).toHaveBeenCalled();
            expect(app.template.getField).not.toHaveBeenCalled();
        });
    });
});
