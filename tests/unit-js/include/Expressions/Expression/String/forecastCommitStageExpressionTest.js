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
describe('Forecast Commit Stage Expression Function', function() {
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

    describe('Forecast Commit Stage Expression Function', function() {
        it('return the correct commit_stage for the number passed in based on forecast_setup', function() {
            var constNum = new SUGAR.expressions.ConstantExpression([30]);
            var res = new SUGAR.expressions.ForecastCommitStageExpression([constNum], getSLContext(model));
            // var mockConfig = {forecast_setup: 0};
            var mockObj = sinonSandbox.mock(App.metadata);
            mockObj.expects('getModule').once().withArgs('Forecasts', 'config').returns({'forecast_setup': 0});
            expect(res.evaluate()).toBe('');
            mockObj.verify();
        });

        it('return the correct commit_stage for the number passed (general use case)', function() {
            var constNum = new SUGAR.expressions.ConstantExpression([30]);
            var res = new SUGAR.expressions.ForecastCommitStageExpression([constNum], getSLContext(model));
            var mockConfig = {'forecast_setup': 1, 'forecast_ranges': 'show_binary', 'show_binary_ranges': {
                exclude:  {
                    max: 69,
                    min: 0
                },
                include: {
                    max: 100,
                    min: 70
                }
            }};
            var mockObj = sinonSandbox.mock(App.metadata);
            mockObj.expects('getModule').once().withArgs('Forecasts', 'config').returns(mockConfig);
            expect(res.evaluate()).toBe('exclude');
            mockObj.verify();
            constNum = new SUGAR.expressions.ConstantExpression([80]);
            res = new SUGAR.expressions.ForecastCommitStageExpression([constNum], getSLContext(model));
            mockObj = sinonSandbox.mock(App.metadata);
            mockObj.expects('getModule').once().withArgs('Forecasts', 'config').returns(mockConfig);
            expect(res.evaluate()).toBe('include');
            mockObj.verify();
        });
    });
});
