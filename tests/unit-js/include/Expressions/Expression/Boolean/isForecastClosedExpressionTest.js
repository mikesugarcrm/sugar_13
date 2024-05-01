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
describe('Is Forecast Closed Expression Function', function() {
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

    describe('forecast with different configurations', function() {
        it('should return true with default configuration', function() {
            var closedWon = new SUGAR.expressions.StringLiteralExpression(['Closed Won']);
            var closedLost = new SUGAR.expressions.StringLiteralExpression(['Closed Lost']);
            var res = new SUGAR.expressions.IsForecastClosedExpression([closedWon], getSLContext(model));
            expect(res.evaluate()).toBe('true');
            res = new SUGAR.expressions.IsForecastClosedExpression([closedLost], getSLContext(model));
            expect(res.evaluate()).toBe('true');
        });

        it('should return false with default configuration and status is not default closed won or lost', function() {
            var closedWon = new SUGAR.expressions.StringLiteralExpression(['random_closed']);
            var closedLost = new SUGAR.expressions.StringLiteralExpression(['random_closed_2']);
            var res = new SUGAR.expressions.IsForecastClosedExpression([closedWon], getSLContext(model));
            expect(res.evaluate()).toBe('false');
            res = new SUGAR.expressions.IsForecastClosedExpression([closedLost], getSLContext(model));
            expect(res.evaluate()).toBe('false');
        });

        it('should return true with custom configuration and status is matches closed won or lost', function() {
            var closedWon = new SUGAR.expressions.StringLiteralExpression(['random_closedWon']);
            var closedLost = new SUGAR.expressions.StringLiteralExpression(['random_closedLost']);
            var res = new SUGAR.expressions.IsForecastClosedExpression([closedWon], getSLContext(model));
            var mockConfig = {'sales_stage_won': ['random_closedWon'], 'sales_stage_lost': ['random_closedLost']};
            var mockObj = sinonSandbox.mock(App.metadata);
            mockObj.expects('getModule').twice().withArgs('Forecasts', 'config').returns(mockConfig);
            res.evaluate();
            expect(res.evaluate()).toBe('true');
            mockObj.verify();
            res = new SUGAR.expressions.IsForecastClosedExpression([closedLost], getSLContext(model));
            mockObj = sinonSandbox.mock(App.metadata);
            mockObj.expects('getModule').twice().withArgs('Forecasts', 'config').returns(mockConfig);
            res.evaluate();
            expect(res.evaluate()).toBe('true');
            mockObj.verify();
        });

        it('should return false with custom configuration and status does not match closed won or lost', function() {
            var closedWon = new SUGAR.expressions.StringLiteralExpression(['random_closedWon_fake']);
            var closedLost = new SUGAR.expressions.StringLiteralExpression(['random_closedLost_fake']);
            var res = new SUGAR.expressions.IsForecastClosedExpression([closedWon], getSLContext(model));
            var mockConfig = {'sales_stage_won': ['random_closedWon'], 'sales_stage_lost': ['random_closedLost']};
            var mockObj = sinonSandbox.mock(App.metadata);
            mockObj.expects('getModule').twice().withArgs('Forecasts', 'config').returns(mockConfig);
            res.evaluate();
            expect(res.evaluate()).toBe('false');
            mockObj.verify();
            res = new SUGAR.expressions.IsForecastClosedExpression([closedLost], getSLContext(model));
            mockObj = sinonSandbox.mock(App.metadata);
            mockObj.expects('getModule').twice().withArgs('Forecasts', 'config').returns(mockConfig);
            res.evaluate();
            expect(res.evaluate()).toBe('false');
            mockObj.verify();
        });
    });
});
