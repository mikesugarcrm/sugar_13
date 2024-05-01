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

describe('ProrateValueExpression function', function() {
    var app;
    var oldApp;
    var dm;
    var sinonSandbox;
    var meta;
    var model;

    var getSLContext = function(modelOrCollection, context) {
        var isCollection = (modelOrCollection instanceof dm.beanCollection);
        var model = isCollection ? new modelOrCollection.model() : modelOrCollection;
        context = context || new app.Context({
            url: 'someurl',
            module: model.module,
            model: model
        });
        var view = SugarTest.createComponent('View', {
            context: context,
            type: 'edit',
            module: model.module
        });
        return new SUGAR.expressions.SidecarExpressionContext(view, model, isCollection ? modelOrCollection : false);
    };

    beforeEach(function() {
        oldApp = App;
        App = App || SUGAR.App;
        sinonSandbox = sinon.createSandbox();
        SugarTest.seedMetadata();
        app = SugarTest.app;
        meta = SugarTest.loadFixture('revenue-line-item-metadata');
        app.metadata.set(meta);
        dm = app.data;
        dm.reset();
        dm.declareModels();
        model = dm.createBean('RevenueLineItems', SugarTest.loadFixture('rli'));
    });

    afterEach(function() {
        App = oldApp;
        sinonSandbox.restore();
    });

    describe('evaluate', function() {
        var baseValue;
        var numeratorValue;
        var numeratorUnit;
        var denominatorValue;
        var denominatorUnit;

        beforeEach(function() {
            baseValue = new SUGAR.expressions.ConstantExpression([100]);
            numeratorValue = new SUGAR.expressions.ConstantExpression([12]);
            denominatorValue = new SUGAR.expressions.ConstantExpression([7]);
        });

        it('should prorate correctly between same units', function() {
            numeratorUnit = new SUGAR.expressions.StringLiteralExpression(['day']);
            denominatorUnit = new SUGAR.expressions.StringLiteralExpression(['day']);
            var res = new SUGAR.expressions.ProrateValueExpression(
                [baseValue, numeratorValue, numeratorUnit, denominatorValue, denominatorUnit],
                getSLContext(model)
            );
            expect(parseFloat(res.evaluate())).toBe(171.428571);
        });

        it('should prorate correctly from years to months', function() {
            numeratorUnit = new SUGAR.expressions.StringLiteralExpression(['year']);
            denominatorUnit = new SUGAR.expressions.StringLiteralExpression(['month']);
            var res = new SUGAR.expressions.ProrateValueExpression(
                [baseValue, numeratorValue, numeratorUnit, denominatorValue, denominatorUnit],
                getSLContext(model)
            );
            expect(parseFloat(res.evaluate())).toBe(2057.142857);
        });

        it('should prorate correctly from months to years', function() {
            numeratorUnit = new SUGAR.expressions.StringLiteralExpression(['month']);
            denominatorUnit = new SUGAR.expressions.StringLiteralExpression(['year']);
            var res = new SUGAR.expressions.ProrateValueExpression(
                [baseValue, numeratorValue, numeratorUnit, denominatorValue, denominatorUnit],
                getSLContext(model)
            );
            expect(parseFloat(res.evaluate())).toBe(14.285714);
        });

        it('should prorate correctly from years to days', function() {
            numeratorUnit = new SUGAR.expressions.StringLiteralExpression(['year']);
            denominatorUnit = new SUGAR.expressions.StringLiteralExpression(['day']);
            var res = new SUGAR.expressions.ProrateValueExpression(
                [baseValue, numeratorValue, numeratorUnit, denominatorValue, denominatorUnit],
                getSLContext(model)
            );
            expect(parseFloat(res.evaluate())).toBe(62571.428571);
        });

        it('should prorate correctly from days to years', function() {
            numeratorUnit = new SUGAR.expressions.StringLiteralExpression(['day']);
            denominatorUnit = new SUGAR.expressions.StringLiteralExpression(['year']);
            var res = new SUGAR.expressions.ProrateValueExpression(
                [baseValue, numeratorValue, numeratorUnit, denominatorValue, denominatorUnit],
                getSLContext(model)
            );
            expect(parseFloat(res.evaluate())).toBe(0.469667);
        });

        it('should prorate correctly from months to days', function() {
            numeratorUnit = new SUGAR.expressions.StringLiteralExpression(['month']);
            denominatorUnit = new SUGAR.expressions.StringLiteralExpression(['day']);
            var res = new SUGAR.expressions.ProrateValueExpression(
                [baseValue, numeratorValue, numeratorUnit, denominatorValue, denominatorUnit],
                getSLContext(model)
            );
            expect(parseFloat(res.evaluate())).toBe(5214.285714);
        });

        it('should prorate correctly from days to months', function() {
            numeratorUnit = new SUGAR.expressions.StringLiteralExpression(['day']);
            denominatorUnit = new SUGAR.expressions.StringLiteralExpression(['month']);
            var res = new SUGAR.expressions.ProrateValueExpression(
                [baseValue, numeratorValue, numeratorUnit, denominatorValue, denominatorUnit],
                getSLContext(model)
            );
            expect(parseFloat(res.evaluate())).toBe(5.636008);
        });
    });
});
