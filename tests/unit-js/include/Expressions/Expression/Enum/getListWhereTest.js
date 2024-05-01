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
describe('Get List Where Expression Function', function() {
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

    describe('Get List Where Expression Function', function() {
        it('returns the matched array from lists of where param[0] exists', function() {
            var find = new SUGAR.expressions.StringLiteralExpression(['test_find']);
            var foo = new SUGAR.expressions.StringLiteralExpression(['foo']);
            var bar = new SUGAR.expressions.StringLiteralExpression(['bar']);
            var arrOne = new SUGAR.expressions.DefineEnumExpression([bar, foo]);
            var arrTwo = new SUGAR.expressions.DefineEnumExpression([foo, bar]);
            var arrThree = new SUGAR.expressions.DefineEnumExpression([find, arrTwo]);
            var lists = new SUGAR.expressions.DefineEnumExpression([arrOne, arrThree]);
            var res = new SUGAR.expressions.SugarListWhereExpression([find, lists]);
            expect(res.evaluate()).toEqual(arrTwo.evaluate());
        });
    });
});
