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
describe('View.Views.Base.Calendar.AddFilterView', function() {
    var app;
    var view;
    var model;
    var module = 'Calendar';
    var context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('add-filter', 'view', 'base', 'add-filter', module);
        SugarTest.loadComponent('base', 'view', 'add-filter', module);
        SugarTest.declareData('base', module, true, true);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        model = app.data.createBean(module);

        context = app.context.getContext();

        context.set({
            module: module,
            model: model,
        });

        view = SugarTest.createView(
            'base',
            module,
            'add-filter',
            null,
            context,
            true,
            model,
            true
        );
    });

    afterEach(function() {
        view.dispose();
        model = null;
        view = null;
    });

    describe('initialize', function() {
        it('should set initial parameters', function() {
            expect(view._allModulesId).toBe('All');
        });
    });

    describe('render', function() {
        it('should build and render the filter component', function() {
            var buildModuleFilterListStub = sinon.stub(view, 'buildModuleFilterList');
            var buildFilterStub = sinon.stub(view, 'buildFilter');

            view.render();

            expect(buildModuleFilterListStub).toHaveBeenCalledOnce();
            expect(buildFilterStub).toHaveBeenCalledOnce();

            buildModuleFilterListStub.restore();
            buildFilterStub.restore();
        });

        it('should render the filter component', function() {
            view.collection = app.data.createBeanCollection('Filters');
            view.collection.allowed_modules = ['Users', 'Teams'];
            view.render();
            expect(view.$('.select2.search-filter').length).toEqual(2);
            expect(view.$('span.choice-filter-label').html()).toEqual('LBL_MODULE_ALL');
            expect(view.$('input.search-name').length).toEqual(1);
        });
    });

    describe('applyFilter', function() {
        it('should apply the filter and trigger an event on context', function() {
            view.collection = app.data.createBeanCollection('Filters');

            var contextTriggerStub = sinon.stub(view.context, 'trigger');

            view._selectedModule = 'Users';
            view._currentSearch = {};

            view.applyFilter();

            expect(contextTriggerStub.getCall(0).args[0]).toEqual('calendar:add:search');
            expect(contextTriggerStub.getCall(0).args[1]).toEqual(['Users']);
            expect(contextTriggerStub.getCall(0).args[2]).toEqual({});

            contextTriggerStub.restore();
        });
    });
});
