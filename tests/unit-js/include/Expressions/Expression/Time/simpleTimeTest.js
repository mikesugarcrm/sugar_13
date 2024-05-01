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
describe('Simple Time Expression Functions', function() {
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

    describe('Define Time Expression Function', function() {
        it('should return equavilent of Date.getTime() for a given date', function() {
            var timeString = new SUGAR.expressions.StringLiteralExpression(['Jan 1, 1970 00:00:00']);
            var time = new SUGAR.expressions.DefineTimeExpression(timeString, getSLContext(model));
            var date = new Date('Jan 1, 1970 00:00:00');
            expect(time.evaluate()).toEqual(date.getTime());
        });
    });

    describe('Hour of Day Expression Function', function() {
        it('should return hour of day', function() {
            var timeString = new SUGAR.expressions.StringLiteralExpression(['Jul 19, 2018 3:45:56']);
            var time = new SUGAR.expressions.DefineTimeExpression(timeString, getSLContext(model));
            var hourOfDay = new SUGAR.expressions.HourOfDayExpression([time]);
            expect(parseFloat(hourOfDay.evaluate())).toBe(3);
        });
    });
});
