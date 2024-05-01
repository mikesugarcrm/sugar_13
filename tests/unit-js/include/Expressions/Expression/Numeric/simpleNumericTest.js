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

describe('Simple Numeric Functions Test', function() {
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

    describe('Average Function', function() {
        var a = new SUGAR.expressions.ConstantExpression([1]);
        var b = new SUGAR.expressions.ConstantExpression([2]);
        var c = new SUGAR.expressions.ConstantExpression([3]);
        var d = new SUGAR.expressions.ConstantExpression([4]);
        var e = new SUGAR.expressions.ConstantExpression([5]);
        var f = new SUGAR.expressions.ConstantExpression([6]);
        var g = new SUGAR.expressions.ConstantExpression([7]);
        var h = new SUGAR.expressions.ConstantExpression([8]);
        var i = new SUGAR.expressions.ConstantExpression([9]);

        it('should return average of the sum of 1 to 9', function() {
            var res =  new SUGAR.expressions.AverageExpression([a, b, c, d, e, f, g, h, i], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(5);
        });
    });

    describe('Absolute Value Function Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([-1]);
        var b = new SUGAR.expressions.ConstantExpression([1]);
        var c = new SUGAR.expressions.ConstantExpression([-10]);
        var d = new SUGAR.expressions.ConstantExpression([10]);

        it('should return absolute value of a number', function() {
            var res =  new SUGAR.expressions.AbsoluteValueExpression([a], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(1);
            res =  new SUGAR.expressions.AbsoluteValueExpression([b], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(1);
            res =  new SUGAR.expressions.AbsoluteValueExpression([c], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(10);
            res =  new SUGAR.expressions.AbsoluteValueExpression([d], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(10);
        });
    });

    describe('Add Function Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([-1]);
        var b = new SUGAR.expressions.ConstantExpression([1]);
        var c = new SUGAR.expressions.ConstantExpression([10]);
        var d = new SUGAR.expressions.ConstantExpression([10]);

        it('should return sum of a set of numbers', function() {
            var res =  new SUGAR.expressions.AddExpression([a, b], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(0);
            res =  new SUGAR.expressions.AddExpression([c, d], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(20);
            res =  new SUGAR.expressions.AddExpression([a, b, c, d], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(20);
        });
    });

    describe('Subtract Function Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([-1]);
        var b = new SUGAR.expressions.ConstantExpression([1]);
        var c = new SUGAR.expressions.ConstantExpression([10]);
        var d = new SUGAR.expressions.ConstantExpression([10]);

        it('should return difference of a set of numbers', function() {
            var res =  new SUGAR.expressions.SubtractExpression([a, b], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(-2);
            res =  new SUGAR.expressions.SubtractExpression([c, d], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(0);
            res =  new SUGAR.expressions.SubtractExpression([a, b, c, d], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(-22);
        });
    });

    describe('Ceiling Function Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([-1.010101]);
        var b = new SUGAR.expressions.ConstantExpression([1.010101]);
        var c = new SUGAR.expressions.ConstantExpression([10.9]);
        var d = new SUGAR.expressions.ConstantExpression([11.2]);

        it('should round up to next whole number', function() {
            var res =  new SUGAR.expressions.CeilingExpression([a], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(-1);
            res =  new SUGAR.expressions.CeilingExpression([b], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(2);
            res =  new SUGAR.expressions.CeilingExpression([c], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(11);
            res =  new SUGAR.expressions.CeilingExpression([d], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(12);
        });
    });

    describe('Floor Function Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([-1.010101]);
        var b = new SUGAR.expressions.ConstantExpression([1.010101]);
        var c = new SUGAR.expressions.ConstantExpression([10.9]);
        var d = new SUGAR.expressions.ConstantExpression([11.2]);

        it('should round down to next whole number', function() {
            var res =  new SUGAR.expressions.FloorExpression([a], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(-2);
            res =  new SUGAR.expressions.FloorExpression([b], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(1);
            res =  new SUGAR.expressions.FloorExpression([c], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(10);
            res =  new SUGAR.expressions.FloorExpression([d], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(11);
        });
    });

    describe('Constant Expression Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([-1]);
        var b = new SUGAR.expressions.ConstantExpression([1]);
        var c = new SUGAR.expressions.ConstantExpression([-10.1231]);
        var d = new SUGAR.expressions.ConstantExpression([10.1231]);

        it('should return constant number', function() {
            expect(parseFloat(a.evaluate())).toBe(-1);
            expect(parseFloat(b.evaluate())).toBe(1);
            expect(parseFloat(c.evaluate())).toBe(-10.1231);
            expect(parseFloat(d.evaluate())).toBe(10.1231);
        });
    });

    describe('Divide Expression Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([-1]);
        var b = new SUGAR.expressions.ConstantExpression([1]);
        var c = new SUGAR.expressions.ConstantExpression([10.1231]);
        var d = new SUGAR.expressions.ConstantExpression([10.1231]);
        var e = new SUGAR.expressions.ConstantExpression([1.5]);
        var f = new SUGAR.expressions.ConstantExpression([3]);

        it('should return quotient of any two numbers', function() {
            var res =  new SUGAR.expressions.DivideExpression([a, b], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(-1);
            res =  new SUGAR.expressions.DivideExpression([c, d], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(1);
            res = new SUGAR.expressions.DivideExpression([e, f], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(0.5);
        });
    });

    describe('Multiply Expression Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([-1]);
        var b = new SUGAR.expressions.ConstantExpression([1]);
        var c = new SUGAR.expressions.ConstantExpression([10]);
        var d = new SUGAR.expressions.ConstantExpression([10]);
        var e = new SUGAR.expressions.ConstantExpression([1.5]);
        var f = new SUGAR.expressions.ConstantExpression([3]);

        it('should return product of any two numbers', function() {
            var res =  new SUGAR.expressions.MultiplyExpression([a, b], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(-1);
            res =  new SUGAR.expressions.MultiplyExpression([c, d], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(100);
            res = new SUGAR.expressions.MultiplyExpression([e, f], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(4.5);
        });
    });

    describe('Maximum Expression Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([-3]);
        var b = new SUGAR.expressions.ConstantExpression([-2]);
        var c = new SUGAR.expressions.ConstantExpression([-1]);
        var d = new SUGAR.expressions.ConstantExpression([0]);
        var e = new SUGAR.expressions.ConstantExpression([1]);
        var f = new SUGAR.expressions.ConstantExpression([2]);
        var g = new SUGAR.expressions.ConstantExpression([3]);

        it('should return maximum value of a set of numbers', function() {
            var res =  new SUGAR.expressions.MaximumExpression([a, b, c, d, e, f, g], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(3);
        });
    });

    describe('Minimum Expression Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([-3]);
        var b = new SUGAR.expressions.ConstantExpression([-2]);
        var c = new SUGAR.expressions.ConstantExpression([-1]);
        var d = new SUGAR.expressions.ConstantExpression([0]);
        var e = new SUGAR.expressions.ConstantExpression([1]);
        var f = new SUGAR.expressions.ConstantExpression([2]);
        var g = new SUGAR.expressions.ConstantExpression([3]);

        it('should return minimum value of a set of numbers', function() {
            var res =  new SUGAR.expressions.MinimumExpression([a, b, c, d, e, f, g], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(-3);
        });
    });

    describe('Median Expression Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([-3]);
        var b = new SUGAR.expressions.ConstantExpression([-2]);
        var c = new SUGAR.expressions.ConstantExpression([-1]);
        var d = new SUGAR.expressions.ConstantExpression([0]);
        var e = new SUGAR.expressions.ConstantExpression([1]);
        var f = new SUGAR.expressions.ConstantExpression([2]);
        var g = new SUGAR.expressions.ConstantExpression([3]);

        it('should return median of a set of numbers (odd number of numbers in set)', function() {
            var res =  new SUGAR.expressions.MedianExpression([a, b, c, d, e, f, g], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(0);
        });

        it('should return average of middle two numbers when set is of even length', function() {
            var res =  new SUGAR.expressions.MedianExpression([a, b, c, e, f, g], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(0);
        });
    });

    describe('Logarithmic Expression Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([10]);
        var b = new SUGAR.expressions.ConstantExpression([100]);
        var c = new SUGAR.expressions.ConstantExpression([0.1]);
        var d = new SUGAR.expressions.ConstantExpression([0.01]);

        it('should return log of a number', function() {
            var res =  new SUGAR.expressions.LogExpression([a, a], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(1);
            res =  new SUGAR.expressions.LogExpression([b, a], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(2);
            res =  new SUGAR.expressions.LogExpression([c, a], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(-1);
            res =  new SUGAR.expressions.LogExpression([d, a], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(-2);
        });
    });

    describe('Power Expression Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([2]);
        var b = new SUGAR.expressions.ConstantExpression([3]);
        var c = new SUGAR.expressions.ConstantExpression([4]);
        var d = new SUGAR.expressions.ConstantExpression([-1]);

        it('should return a to the power of b (a^b) given PowerExpression[a,b]', function() {
            var res =  new SUGAR.expressions.PowerExpression([a,b], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(8);
            res =  new SUGAR.expressions.PowerExpression([b,a], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(9);
            res =  new SUGAR.expressions.PowerExpression([c,d], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(0.25);
            res =  new SUGAR.expressions.PowerExpression([d,a], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(1);
        });
    });

    describe('Negate Expression Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([2]);
        var b = new SUGAR.expressions.ConstantExpression([-3]);
        var c = new SUGAR.expressions.ConstantExpression([4]);
        var d = new SUGAR.expressions.ConstantExpression([-1]);

        it('should return -a given NegateExpression[a]', function() {
            var res =  new SUGAR.expressions.NegateExpression([a], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(-2);
            res =  new SUGAR.expressions.NegateExpression([b], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(3);
            res =  new SUGAR.expressions.NegateExpression([c], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(-4);
            res =  new SUGAR.expressions.NegateExpression([d], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(1);
        });
    });

    describe('Round Expression Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([3.66667]);
        var b = new SUGAR.expressions.ConstantExpression([3.45]);
        var c = new SUGAR.expressions.ConstantExpression([100.234567]);
        var round1 = new SUGAR.expressions.ConstantExpression([2]);
        var round2 = new SUGAR.expressions.ConstantExpression([0]);
        var round3 = new SUGAR.expressions.ConstantExpression([3]);

        it('should return rounded number to b precision given RoundExpression[a,b]', function() {
            var res =  new SUGAR.expressions.RoundExpression([a, round1], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(3.67);
            res =  new SUGAR.expressions.RoundExpression([b, round2], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(3);
            res =  new SUGAR.expressions.RoundExpression([c, round3], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(100.235);
        });
    });

    describe('Natural Logarithmic Expression Test', function() {
        var a = new SUGAR.expressions.ConstantExpression([1]);
        var b = new SUGAR.expressions.ConstantExpression([Math.E]);
        var c = new SUGAR.expressions.ConstantExpression([Math.pow(Math.E,3)]);

        it('should return natural log of a number', function() {
            var res =  new SUGAR.expressions.NaturalLogExpression([a], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(0);
            res =  new SUGAR.expressions.NaturalLogExpression([b], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(1);
            res =  new SUGAR.expressions.NaturalLogExpression([c], getSLContext(model));
            expect(parseFloat(res.evaluate())).toBe(3);
        });
    });

    describe('String Length Expression Function', function() {

        it('should return length of a string', function() {
            var testString = 'This is a test string';
            var testStrExpression = new SUGAR.expressions.StringLiteralExpression([testString]);
            var strlenExpr = new SUGAR.expressions.StringLengthExpression([testStrExpression], getSLContext(model));
            expect(parseFloat(strlenExpr.evaluate())).toBe(testString.length);
        });

        it('should return length of a multibyte string', function() {
            var multiByteTestString = '🌴🌴🌴';
            var testStrExpression = new SUGAR.expressions.StringLiteralExpression([multiByteTestString]);
            var strlenExpr = new SUGAR.expressions.StringLengthExpression([testStrExpression], getSLContext(model));
            expect(parseFloat(strlenExpr.evaluate())).toBe(multiByteTestString.length);
        });
    });

    describe('Standard Deviation Expression Function', function() {

        it('should return standard deviation of a set of numbers', function() {
            var a = new SUGAR.expressions.ConstantExpression([4]);
            var b = new SUGAR.expressions.ConstantExpression([5]);
            var c = new SUGAR.expressions.ConstantExpression([6]);
            var d = new SUGAR.expressions.ConstantExpression([7]);
            var e = new SUGAR.expressions.ConstantExpression([10]);

            var stddev = new SUGAR.expressions.StandardDeviationExpression([a, b, c, d, e], getSLContext(model));
            var num = stddev.evaluate();
            expect(num).toBeCloseTo(2.06);
        });
    });

    describe('Index Of Expression Function', function() {
        var a = new SUGAR.expressions.ConstantExpression([4]);
        var b = new SUGAR.expressions.ConstantExpression([5]);
        var c = new SUGAR.expressions.ConstantExpression([6]);
        var d = new SUGAR.expressions.ConstantExpression([7]);
        var e = new SUGAR.expressions.ConstantExpression([10]);
        var notInList = new SUGAR.expressions.ConstantExpression([20]);
        var test = new SUGAR.expressions.DefineEnumExpression([a, b, c, d, e]);
        it('should return index of a certain value in a provided list', function() {
            var res = new SUGAR.expressions.IndexOfExpression([c, test], getSLContext(model));
            expect(res.evaluate()).toBe(2);
        });

        it('should return -1 for a value not in the list', function() {
            var res = new SUGAR.expressions.IndexOfExpression([notInList, test], getSLContext(model));
            expect(res.evaluate()).toBe(-1);
        });
    });
});
