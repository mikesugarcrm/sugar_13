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
describe('OpportunitySalesStage Expression Function', function() {
    var app;
    var oldApp;
    var dm;
    var meta;
    var model;
    var modelCollection;
    var rliStr;
    var salesStageStr;
    var rliFixtureData;
    var oppFixtureData;

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
        SugarTest.seedMetadata();
        app = SugarTest.app;
        meta = SugarTest.loadFixture('revenue-line-item-metadata');
        app.metadata.set(meta);
        dm = app.data;
        dm.reset();
        dm.declareModels();
        oppFixtureData = SugarTest.loadFixture('opp-sales-stage-expr-opp-data');
        rliFixtureData = SugarTest.loadFixture('opp-sales-stage-expr-rli-data');
        rliStr = new SUGAR.expressions.StringLiteralExpression(['revenuelineitems']);
        salesStageStr = new SUGAR.expressions.StringLiteralExpression(['sales_stage']);
        sinon.stub(app.lang, 'getAppListStrings').callsFake(function() {
            return {
                'Prospecting': 'Prospecting',
                'Qualification': 'Qualification',
                'Needs Analysis': 'Needs Analysis',
                'Value Proposition': 'Value Proposition',
                'Id. Decision Makers': 'Id. Decision Makers',
                'Perception Analysis': 'Perception Analysis',
                'Proposal/Price Quote': 'Proposal/Price Quote',
                'Negotiation/Review': 'Negotiation/Review',
                'Closed Won': 'Closed Won',
                'Closed Lost': 'Closed Lost'
            }
        });
    });

    afterEach(function() {
        sinon.restore();
        App = oldApp;
    });

    it('should return the latest stage with all open sales stages', function() {
        model = dm.createBean('Opportunities', oppFixtureData);
        modelCollection = dm.createBeanCollection('RevenueLineItems');
        _.each(rliFixtureData.records, function(rliData) {
            modelCollection.add(rliData);
        }, this);
        sinon.stub(App.data, 'createRelatedBean')
            .withArgs(model, null, 'revenuelineitems').returns({
            collection: modelCollection
        });

        var res = new SUGAR.expressions.OpportunitySalesStageExpression([rliStr, salesStageStr], getSLContext(model));
        sinon.stub(App.metadata, 'getModule')
            .withArgs('Forecasts', 'config').returns({
            is_setup: 1,
            sales_stage_won: ['Closed Won'],
            sales_stage_lost: ['Closed Lost']
        });

        expect(res.evaluate()).toBe('Negotiation/Review');
    });

    it('should return the stage of the latest open date with most sales stages closed', function() {
        model = dm.createBean('Opportunities', oppFixtureData);
        modelCollection = dm.createBeanCollection('RevenueLineItems');
        _.each(rliFixtureData.records, function(rliData, i) {
            if (i === 0) {
                rliData.sales_stage = 'Closed Won';
            } else if (i === 1) {
                rliData.sales_stage = 'Closed Lost';
            }

            modelCollection.add(rliData);
        }, this);
        sinon.stub(App.data, 'createRelatedBean')
            .withArgs(model, null, 'revenuelineitems').returns({
            collection: modelCollection
        });

        var res = new SUGAR.expressions.OpportunitySalesStageExpression([rliStr, salesStageStr], getSLContext(model));
        sinon.stub(App.metadata, 'getModule')
            .withArgs('Forecasts', 'config').returns({
            is_setup: 1,
            sales_stage_won: ['Closed Won'],
            sales_stage_lost: ['Closed Lost']
        });

        expect(res.evaluate()).toBe('Negotiation/Review');
    });

    it('should return closed lost if all sales stages are closed lost', function() {
        model = dm.createBean('Opportunities', oppFixtureData);
        modelCollection = dm.createBeanCollection('RevenueLineItems');
        _.each(rliFixtureData.records, function(rliData) {
            rliData.sales_stage = 'Closed Lost';

            modelCollection.add(rliData);
        }, this);
        sinon.stub(App.data, 'createRelatedBean')
            .withArgs(model, null, 'revenuelineitems').returns({
            collection: modelCollection
        });

        var res = new SUGAR.expressions.OpportunitySalesStageExpression([rliStr, salesStageStr], getSLContext(model));
        sinon.stub(App.metadata, 'getModule')
            .withArgs('Forecasts', 'config').returns({
            is_setup: 1,
            sales_stage_won: ['Closed Won'],
            sales_stage_lost: ['Closed Lost']
        });

        expect(res.evaluate()).toBe('Closed Lost');
    });

    it('should return prospecting if all sales stages are the first index', function() {
        model = dm.createBean('Opportunities', oppFixtureData);
        modelCollection = dm.createBeanCollection('RevenueLineItems');
        _.each(rliFixtureData.records, function(rliData) {
            rliData.sales_stage = 'Prospecting';

            modelCollection.add(rliData);
        }, this);
        sinon.stub(App.data, 'createRelatedBean')
            .withArgs(model, null, 'revenuelineitems').returns({
            collection: modelCollection
        });

        var res = new SUGAR.expressions.OpportunitySalesStageExpression([rliStr, salesStageStr], getSLContext(model));
        sinon.stub(App.metadata, 'getModule')
            .withArgs('Forecasts', 'config').returns({
            is_setup: 1,
            sales_stage_won: ['Closed Won'],
            sales_stage_lost: ['Closed Lost']
        });

        expect(res.evaluate()).toBe('Prospecting');
    });

    it('should return a custom closed lost status if all are closed lost and forecasts config changed', function() {
        model = dm.createBean('Opportunities', oppFixtureData);
        modelCollection = dm.createBeanCollection('RevenueLineItems');
        _.each(rliFixtureData.records, function(rliData) {
            rliData.sales_stage = 'Closed Lost';

            modelCollection.add(rliData);
        }, this);
        sinon.stub(App.data, 'createRelatedBean')
            .withArgs(model, null, 'revenuelineitems').returns({
            collection: modelCollection
        });

        var res = new SUGAR.expressions.OpportunitySalesStageExpression([rliStr, salesStageStr], getSLContext(model));
        sinon.stub(App.metadata, 'getModule')
            .withArgs('Forecasts', 'config').returns({
            is_setup: 1,
            sales_stage_won: ['Closed Won'],
            sales_stage_lost: ['CustomClosedLostStatus', 'Closed Lost']
        });

        expect(res.evaluate()).toBe('CustomClosedLostStatus');
    });

    it('should return Won if all sales stages are closed and there is at least one won', function() {
        model = dm.createBean('Opportunities', oppFixtureData);
        modelCollection = dm.createBeanCollection('RevenueLineItems');
        _.each(rliFixtureData.records, function(rliData, i) {
            rliData.sales_stage = i === 2 ? 'Closed Won' : 'Closed Lost';

            modelCollection.add(rliData);
        }, this);
        sinon.stub(App.data, 'createRelatedBean')
            .withArgs(model, null, 'revenuelineitems').returns({
            collection: modelCollection
        });

        var res = new SUGAR.expressions.OpportunitySalesStageExpression([rliStr, salesStageStr], getSLContext(model));
        sinon.stub(App.metadata, 'getModule')
            .withArgs('Forecasts', 'config').returns({
            is_setup: 1,
            sales_stage_won: ['Closed Won'],
            sales_stage_lost: ['Closed Lost']
        });

        expect(res.evaluate()).toBe('Closed Won');
    });

    it('should return a custom Won if all are closed, at least one Won when forecast config changed', function() {
        model = dm.createBean('Opportunities', oppFixtureData);
        modelCollection = dm.createBeanCollection('RevenueLineItems');
        _.each(rliFixtureData.records, function(rliData, i) {
            rliData.sales_stage = i === 2 ? 'Closed Won' : 'Closed Lost';

            modelCollection.add(rliData);
        }, this);
        sinon.stub(App.data, 'createRelatedBean')
            .withArgs(model, null, 'revenuelineitems').returns({
            collection: modelCollection
        });

        var res = new SUGAR.expressions.OpportunitySalesStageExpression([rliStr, salesStageStr], getSLContext(model));
        sinon.stub(App.metadata, 'getModule')
            .withArgs('Forecasts', 'config').returns({
            is_setup: 1,
            sales_stage_won: ['CustomClosedWonStatus', 'Closed Won'],
            sales_stage_lost: ['Closed Lost']
        });

        expect(res.evaluate()).toBe('CustomClosedWonStatus');
    });
});
