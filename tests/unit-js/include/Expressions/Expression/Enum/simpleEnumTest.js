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
describe('Sugar Simple Enum Expression Functions', function() {
    var app;
    var oldApp;
    var dm;
    var sinonSandbox;
    var meta;
    var model;

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

    describe('Create Enum Expression Function', function() {
        it('returns enum object ', function() {
            var a = new SUGAR.expressions.ConstantExpression([4]);
            var b = new SUGAR.expressions.ConstantExpression([5]);
            var c = new SUGAR.expressions.ConstantExpression([6]);
            var d = new SUGAR.expressions.ConstantExpression([7]);
            var e = new SUGAR.expressions.ConstantExpression([10]);

            var test = new SUGAR.expressions.DefineEnumExpression([a, b, c, d, e]);

            expect(test.evaluate()).toEqual([4, 5, 6, 7, 10]);
        });
    });
});
