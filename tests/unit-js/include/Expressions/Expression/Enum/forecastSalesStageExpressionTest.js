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
describe('ForecastSalesStageExpression Function', function() {
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

    var dict = {
        'Closed Lost': 'Closed Lost',
        'Prospecting': 'Prospecting',
        'Qualification': 'Qualification',
        'Need Analysis': 'Need Analysis',
        'Value Proposition': 'Value Proposition',
        'Id. Decision Makers': 'Id. Decision Makers',
        'Perception Analysis': 'Perception Analysis',
        'Proposal': 'Proposal',
        'Negotiation/Review': 'Negotiation/Review',
        'Closed Won': 'Closed Won',
    };

    var trueExpr = new SUGAR.expressions.TrueExpression([]);
    var falseExpr = new SUGAR.expressions.FalseExpression([]);

    describe('returns all the valid sales stages for the Forecast module from the sales_stage_dom, depending on ' +
        'boolean params', function() {
        it('should check both false case', function() {
            var res = new SUGAR.expressions.ForecastSalesStageExpression([falseExpr, falseExpr], getSLContext(model));

            let mockObjArray = sinonSandbox.mock(App.lang);
            mockObjArray.expects('getAppListStrings').once().withArgs('sales_stage_dom').returns(dict);
            var mockObjConfig = sinonSandbox.mock(App.metadata);
            mockObjConfig.expects('getModule').once().withArgs('Forecasts', 'config').returns({'sales_stage_won':
                    ['Closed Won'], 'sales_stage_lost': ['Closed Lost']});
            expect(res.evaluate()).toEqual(['Prospecting',
                'Qualification',
                'Need Analysis',
                'Value Proposition',
                'Id. Decision Makers',
                'Perception Analysis',
                'Proposal',
                'Negotiation/Review'
            ]);
            mockObjConfig.verify();
            mockObjArray.verify();
        });

        it('should check both true case', function() {
            var res = new SUGAR.expressions.ForecastSalesStageExpression([trueExpr, trueExpr], getSLContext(model));

            let mockObjArray = sinonSandbox.mock(App.lang);
            mockObjArray.expects('getAppListStrings').once().withArgs('sales_stage_dom').returns(dict);
            var mockObjConfig = sinonSandbox.mock(App.metadata);
            mockObjConfig.expects('getModule').once().withArgs('Forecasts', 'config').returns({'sales_stage_won':
                    ['Closed Won'], 'sales_stage_lost': ['Closed Lost']});
            expect(res.evaluate()).toEqual(Object.keys(dict));
            mockObjConfig.verify();
            mockObjArray.verify();
        });

        it('should check App = undefined case', function() {
            var temp = App;
            App = undefined;
            var falseExpr = new SUGAR.expressions.FalseExpression([]);
            var res = new SUGAR.expressions.ForecastSalesStageExpression([falseExpr, falseExpr], getSLContext(model));
            SUGAR.language = {get: function() {}};
            var mockObj = sinonSandbox.mock(SUGAR.language);
            mockObj.expects('get').twice().withArgs('app_list_strings', 'sales_stage_dom').returns('value');
            res.evaluate();
            expect(res.evaluate()).toBe('value');
            mockObj.verify();
            App = temp;
        });
    });
});
