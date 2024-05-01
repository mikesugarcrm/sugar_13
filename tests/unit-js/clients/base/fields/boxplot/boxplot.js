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

describe('Base.Fields.BoxplotField', function() {
    var app;
    var context;
    var model;
    var moduleName;
    var field;
    var fieldDef;
    var options;
    var collection;
    var modelForNegativeAmount;
    var modelForMixedAmount;
    beforeEach(function() {
        app = SugarTest.app;
        moduleName = 'Opportunities';
        model = app.data.createBean(moduleName, {
            worst_case: '1000',
            best_case: '3000',
            amount: '2000',
        });

        modelForNegativeAmount = app.data.createBean(moduleName, {
            worst_case: '-3000',
            best_case: '-1000',
            amount: '-2000',
        });

        modelForMixedAmount = app.data.createBean(moduleName, {
            worst_case: '-3000',
            best_case: '0',
            amount: '-2000',
        });

        context = new app.Context();
        context.set({model: model});

        collection = new Backbone.Collection({models: model});
        fieldDef = {
            'name': 'boxplot',
            'type': 'boxplot',
        };

        options = {
            context: context,
            model: model,
            moduleName: moduleName,
        };

        field = SugarTest.createField('base', 'boxplot', 'boxplot');
        app.user.setPreference('decimal_precision', 2);
        app.user.setPreference('currency_symbol', '$');
        sinon.stub(field, '_super').callsFake(function() {
        });
    });

    afterEach(function() {
        sinon.restore();
        app = null;
        context = null;
        model = null;
        field = null;
        fieldDef = null;
        moduleName = null;
    });

    describe('initialize()', function() {
        beforeEach(function() {
            field.initialize(options);
            sinon.stub(field, '_caseComparator');
        });

        it('should call the _super()', function() {
            field.initialize(options);
            expect(field._super).toHaveBeenCalledWith('initialize', [options]);
        });

        it('should call the _caseComparator()', function() {
            field._caseComparator();
            expect(field._caseComparator).toHaveBeenCalled();
        });
    });

    describe('_render()', function() {
        beforeEach(function() {
            sinon.stub(field, '_render');
            sinon.stub(field, '_boxPlotCalculator');
        });

        it('should call the _render()', function() {
            field._render();
            expect(field._render).toHaveBeenCalled();
        });

        it('should call the _boxPlotCalculatorForPositiveAmounts()', function() {
            field.collection.overallBestCase = 3000;
            field.collection.overallWorstCase = 1000;
            field._render();
            field._boxPlotCalculator(field.collection.overallBestCase,
                field.collection.overallWorstCase);
            expect(field._boxPlotCalculator).toHaveBeenCalled();
        });

        it('should call the _boxPlotCalculator', function() {
            field.collection.overallBestCase = -1000;
            field.collection.overallWorstCase = -3000;
            field._render();
            field._boxPlotCalculator(field.collection.overallBestCase,
                field.collection.overallWorstCase);
            expect(field._boxPlotCalculator).toHaveBeenCalled();
        });

        it('should return the _super("render")', function() {
            field._render();
            expect(field._render()).toBe(field._super('render'));
        });
    });

    describe('checking parameters for positive values in _boxPlotCalculator()', function() {
        beforeEach(function() {
            sinon.stub(field, '_render');
            field.model = model;
        });

        it('calls _boxPlotCalculator()', function() {
            field.collection.overallBestCase = 3000;
            field.collection.overallWorstCase = 1000;
            field.likelyRound = '';
            field.overallCaseDifference = 0;
            field.worstCase = '';
            field.likely = '';
            field.bestCase = '';
            field.likelyMarginFromWorstCase = '';
            field.likelyPercent = '';
            field.caretPos = '';
            field.amountPos = '';
            field.boxStart = '';
            field.boxEnd = '';
            field.boxWidth = '';
            field._boxPlotCalculator(field.collection.overallBestCase,
                field.collection.overallWorstCase);
            expect(field.overallCaseDifference).toEqual(2000.00);
            expect(field.worstCase).toEqual('$1,000.00');
            expect(field.bestCase).toEqual('$3,000.00');
            expect(field.likelyRound).toEqual('$2,000');
            expect(field.likelyMarginFromWorstCase).toEqual(1000);
            expect(field.likelyPercent).toEqual(50);
            expect(field.caretPos).toEqual(47.3);
            expect(field.amountPos).toEqual(41);
            expect(field.boxStart).toEqual(0);
            expect(field.boxEnd).toEqual(100);
            expect(field.boxWidth).toEqual(99);
        });
    });

    describe('check parameters for negative values in _boxPlotCalculator', function() {
        beforeEach(function() {
            sinon.stub(field, '_render');
            field.model = modelForNegativeAmount;
        });

        it('calls _boxPlotCalculator()', function() {
            field.collection.overallBestCase = 1000;
            field.collection.overallWorstCase = -3000;
            field.likelyRound = '';
            field.overallCaseDifference = 0;
            field.worstCase = '';
            field.likely = '';
            field.bestCase = '';
            field.likelyMarginFromWorstCase = '';
            field.likelyPercent = '';
            field.caretPos = '';
            field.amountPos = '';
            field.boxStart = '';
            field.boxEnd = '';
            field.boxWidth = '';
            field._boxPlotCalculator(field.collection.overallBestCase,
                field.collection.overallWorstCase);
            expect(field.overallCaseDifference).toEqual(4000.00);
            expect(field.worstCase).toEqual('$-3,000.00');
            expect(field.bestCase).toEqual('$-1,000.00');
            expect(field.likelyRound).toEqual('$-2,000');
            expect(field.likelyMarginFromWorstCase).toEqual(1000);
            expect(field.likelyPercent).toEqual(25);
            expect(field.caretPos).toEqual(22.3);
            expect(field.amountPos).toEqual(15);
            expect(field.boxStart).toEqual(0);
            expect(field.boxEnd).toEqual(50);
            expect(field.boxWidth).toEqual(50);
        });
    });

    describe('check parameters for mixed values in _boxPlotCalculator', function() {
        beforeEach(function() {
            sinon.stub(field, '_render');
            field.model = modelForMixedAmount;
        });

        it('calls _boxPlotCalculator()', function() {
            field.collection.overallBestCase = 2000;
            field.collection.overallWorstCase = -3000;
            field.likelyRound = '';
            field.overallCaseDifference = 0;
            field.worstCase = '';
            field.likely = '';
            field.bestCase = '';
            field.likelyMarginFromWorstCase = '';
            field.likelyPercent = '';
            field.caretPos = '';
            field.amountPos = '';
            field.boxStart = '';
            field.boxEnd = '';
            field.boxWidth = '';
            field._boxPlotCalculator(field.collection.overallBestCase,
                field.collection.overallWorstCase);
            expect(field.overallCaseDifference).toEqual(5000.00);
            expect(field.worstCase).toEqual('$-3,000.00');
            expect(field.bestCase).toEqual('$0.00');
            expect(field.likelyRound).toEqual('$-2,000');
            expect(field.likelyMarginFromWorstCase).toEqual(1000);
            expect(field.likelyPercent).toEqual(20);
            expect(field.caretPos).toEqual(17.3);
            expect(field.amountPos).toEqual(10);
            expect(field.boxStart).toEqual(0);
            expect(field.boxEnd).toEqual(60);
            expect(field.boxWidth).toEqual(60);
        });
    });
});
