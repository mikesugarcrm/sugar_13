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
describe('Opportunities.Base.View.MergeDuplicates', function() {
    var app,
        view,
        layout,
        options,
        module = 'Opportunities',
        modelPrimary,
        modelSecondary,
        collection;

    beforeEach(function () {
        app = SugarTest.app;

        modelPrimary = new Backbone.Model({
            commit_stage: 'exclude',
            sales_stage: 'testExcluded'
        });
        modelSecondary = new Backbone.Model({
            commit_stage: 'include',
            sales_stage: 'testIncluded'
        });

        collection = new Backbone.Collection([modelPrimary, modelSecondary]);
        view = SugarTest.createView('base', module, 'merge-duplicates', null, null, true);
        view.collection = collection;
        view.fields = {commit_stage: SugarTest.createField('base', 'commit_stage', 'enum', 'edit', {options: 'commit_stage_dom'}, null, null, view.context)};
    });

    afterEach(function() {
        sinon.restore();
        app = null;
        view = null;
        options = null;
    });

    describe('bindDataChange', function() {
        var testModel;
        beforeEach(function() {
            sinon.spy(view.collection, 'on');
        });

        it('should not set collection.on event listener if Forecasts is not setup', function(){
            sinon.stub(app.metadata, 'getModule').returns({
                is_setup: false,
                forecast_by: module,
                sales_stage_won: ['testIncluded'],
                commit_stages_included: ['include']
            });
            view.bindDataChange();
            view.collection.trigger('change:sales_stage', modelSecondary);
            expect(view.collection.on).not.toHaveBeenCalled();
        });

        it('should not set collection.on event listener if Forecasts is setup but in Opps+RLI mode', function(){
            sinon.stub(app.metadata, 'getModule').returns({
                is_setup: true,
                forecast_by: 'RevenueLineItems',
                sales_stage_won: ['testIncluded'],
                commit_stages_included: ['include']
            });
            view.bindDataChange();
            view.collection.trigger('change:sales_stage', modelSecondary);
            expect(view.collection.on).not.toHaveBeenCalled();
        });

        it('should set commit_stage to included', function(){
            sinon.stub(app.metadata, 'getModule').returns({
                is_setup: true,
                forecast_by: module,
                sales_stage_won: ['testIncluded'],
                commit_stages_included: ['include']
            });
            view.bindDataChange();
            testModel = new Backbone.Model({
                sales_stage: 'testIncluded'
            });
            view.collection.trigger('change:sales_stage', testModel);

            expect(testModel.get('commit_stage')).toBe('include');
            expect(view.fields.commit_stage.action).toBe('disabled');
        });

        it('should not touch commit_stage if not in closed won or closed lost', function(){
            sinon.stub(app.metadata, 'getModule').returns({
                is_setup: true,
                forecast_by: module,
                sales_stage_won: ['testIncluded'],
                commit_stages_included: ['include']
            });
            view.bindDataChange();
            testModel = new Backbone.Model({
                sales_stage: 'badSalesStage'
            });
            view.collection.trigger('change:sales_stage', testModel);
            expect(testModel.get('commit_stage')).toBeUndefined();
            expect(view.fields.commit_stage.action).toBeUndefined();
        });

        it('should set commit_stage to excluded', function(){
            sinon.stub(app.metadata, 'getModule').returns({
                is_setup: true,
                forecast_by: module,
                sales_stage_lost: ['testExcluded'],
                commit_stages_included: ['include']
            });
            view.bindDataChange();
            testModel = new Backbone.Model({
                sales_stage: 'testExcluded'
            });
            view.collection.trigger('change:sales_stage', testModel);

            expect(testModel.get('commit_stage')).toBe('exclude');
            expect(view.fields.commit_stage.action).toBe('disabled');
        });
    });
})
