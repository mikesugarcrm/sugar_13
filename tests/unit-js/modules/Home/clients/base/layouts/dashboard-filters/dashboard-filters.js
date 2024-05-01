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
describe('HomeDashboardFilterLayout', function() {
    let layout;
    let app;
    const moduleName = 'Home';
    const layoutName = 'dashboard-filters';

    beforeEach(function() {
        app = SugarTest.app;
        let context = new app.Context();
        let model = new Backbone.Model({
            id: 1,
            metadata: {
                filters: {}
            }
        });
        context.set('model', model);
        context.prepare();
        layout =  SugarTest.createLayout('base', moduleName, layoutName, null, context, true);
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        layout.dispose();
        layout = null;
    });

    describe('initialize()', function() {
        it('should initialize the layout with default properties', function() {
            expect(layout._activeFilterGroupId).toBeFalsy();
            expect(layout.dashboardId).toEqual(1);
        });
    });

    describe('_initProperties()', function() {
        it('should initialize the active filter group ID to false', function() {
            layout._initProperties();
            expect(layout._activeFilterGroupId).toBeFalsy();
        });

        it('should set the dashboard ID to the model ID', function() {
            layout._initProperties();
            expect(layout.dashboardId).toEqual(1);
        });
    });

    describe('_registerEvents()', function() {
        it('should register event listeners', function() {
            spyOn(layout, 'listenTo');
            layout._registerEvents();
            expect(layout.listenTo).toHaveBeenCalled();
        });
    });

    describe('modelSync()', function() {
        it('should call _syncGroups()', function() {
            spyOn(layout, '_syncGroups');
            layout.modelSync();
            expect(layout._syncGroups).toHaveBeenCalled();
        });
    });

    describe('_syncGroups()', function() {
        it('should get the filter groups from metadata', function() {
            layout.model.set('metadata', {filters: {group1: {fields: []}}});

            sinon.stub(layout, 'manageFilterState')
            .returns({});

            layout._syncGroups();
            expect(layout._filterGroups).toEqual({group1: {fields: []}});
        });

        it('should set the filter groups to an empty object if metadata does not have filters', function() {
            layout.model.set('metadata', {});

            sinon.stub(layout, 'manageFilterState')
            .returns({});

            layout._syncGroups();
            expect(layout._filterGroups).toEqual({});
        });
    });

    describe('manageFilterState()', function() {
        let editView;
        let detailView;
        beforeEach(function() {
            editView = SugarTest.createView('base', 'Home', 'dashboard-filters-edit', null, null, true, layout);
            detailView = SugarTest.createView('base', 'Home', 'dashboard-filters-detail', null, null, true, layout);
            getComponentStub = sinon.stub(layout, 'getComponent');
            getComponentStub.withArgs('dashboard-filters-edit').returns(editView);
            getComponentStub.withArgs('dashboard-filters-detail').returns(detailView);

            getComponentStub = sinon.stub(editView, 'manageDashboardFilters');
            getComponentStub.returns({});

            getComponentStub = sinon.stub(detailView, 'manageDashboardFilters');
            getComponentStub.returns({});
        });

        it('should toggle the edit and detail views based on the state', function() {
            let editView = layout.getComponent('dashboard-filters-edit');
            let detailsView = layout.getComponent('dashboard-filters-detail');

            spyOn(editView, 'manageDashboardFilters');
            spyOn(detailsView, 'manageDashboardFilters');

            layout._dashboardFilterView = editView;
            layout.manageFilterState('edit');
            expect(editView.manageDashboardFilters).toHaveBeenCalled();

            layout._dashboardFilterView = detailsView;
            layout.manageFilterState('detail');
            expect(detailsView.manageDashboardFilters).toHaveBeenCalled();
        });
    });

    describe('cancelDashboardFilters()', function() {
        it('should call _syncGroups()', function() {
            spyOn(layout, '_syncGroups');
            layout.cancelDashboardFilters();
            expect(layout._syncGroups).toHaveBeenCalled();
        });
    });

    describe('_invalidGroupsForSave()', function() {
        it('should return an array of invalid filter group IDs', function() {
            layout._filterGroups = {group1: {fields: []}, group2: {fields: ['field1']}};
            let invalidGroups = layout._invalidGroupsForSave();
            expect(invalidGroups).toContain('group1');
            expect(invalidGroups).not.toContain('group2');
        });
    });

    describe('_isValidSave()', function() {
        it('should return true if there are no filter groups', function() {
            layout._filterGroups = null;
            expect(layout._isValidSave()).toBeTruthy();
        });
    });
})
