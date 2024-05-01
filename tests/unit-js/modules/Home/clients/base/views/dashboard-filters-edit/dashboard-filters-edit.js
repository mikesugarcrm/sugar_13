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
describe('HomeDashboardFiltersEdit', function() {
    let view;
    let app;

    beforeEach(function() {
        app = SugarTest.app;
        var context = app.context.getContext();

        view = SugarTest.createView('base', 'Home', 'dashboard-filters-edit', null, context, true);
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        it('should initialize _activeFilterGroupId to false', function() {
            expect(view._activeFilterGroupId).toBeFalsy();
        });

        it('should initialize _filterGroups to an empty object', function() {
            expect(view._filterGroups).toEqual({});
        });

        it('should initialize _filterGroupsView to an empty object', function() {
            expect(view._filterGroupsView).toEqual({});
        });

        it('should call _initProperties', function() {
            spyOn(view, '_initProperties');
            view.initialize({});
            expect(view._initProperties).toHaveBeenCalled();
        });

        it('should call _registerEvents', function() {
            spyOn(view, '_registerEvents');
            view.initialize({});
            expect(view._registerEvents).toHaveBeenCalled();
        });
    });

    describe('_initProperties', function() {
        it('should initialize _activeFilterGroupId to false', function() {
            view._activeFilterGroupId = 'foo';
            view._initProperties();
            expect(view._activeFilterGroupId).toBe(false);
        });

        it('should initialize _filterGroups to an empty object', function() {
            view._filterGroups = {foo: {}};
            view._initProperties();
            expect(view._filterGroups).toEqual({});
        });

        it('should initialize _filterGroupsView to an empty object', function() {
            view._filterGroupsView = {foo: {}};
            view._initProperties();
            expect(view._filterGroupsView).toEqual({});
        });
    });

    describe('_registerEvents', function() {
        it('should listen to the "dashboard-filter-widget-clicked" event', function() {
            spyOn(view, 'manageFieldInFilterGroup');
            let triggerOnModelStub = sinon.stub(view.context, 'trigger')
                .withArgs('dashboard-filter-widget-clicked').returns(view.manageFieldInFilterGroup());
            view.context.trigger('dashboard-filter-widget-clicked', {});
            expect(view.manageFieldInFilterGroup).toHaveBeenCalled();
        });

        it('should listen to the "dashboard-filter-group-selected" event', function() {
            spyOn(view, 'filtersGroupSelected');
            let triggerOnModelStub = sinon.stub(view.context, 'trigger')
                .withArgs('dashboard-filter-group-selected').returns(view.filtersGroupSelected());
            view.context.trigger('dashboard-filter-group-selected', 'foo');
            expect(view.filtersGroupSelected).toHaveBeenCalled();
        });

        it('should listen to the "dashboard-filter-group-name-changed" event', function() {
            spyOn(view, 'filterGroupNameChanged');
            let triggerOnModelStub = sinon.stub(view.context, 'trigger')
                .withArgs('dashboard-filter-group-name-changed').returns(view.filterGroupNameChanged());
            view.context.trigger('dashboard-filter-group-name-changed', 'New Label', 'foo');
            expect(view.filterGroupNameChanged).toHaveBeenCalled();
        });

        it('should listen to the "dashboard-filter-group-removed" event', function() {
            spyOn(view, 'removeFilterGroup');
            let triggerOnModelStub = sinon.stub(view.context, 'trigger')
                .withArgs('dashboard-filter-group-removed').returns(view.removeFilterGroup());
            view.context.trigger('dashboard-filter-group-removed', 'foo');
            expect(view.removeFilterGroup).toHaveBeenCalled();
        });

        it('should listen to the "dashboard-filter-group-invalid-save" event', function() {
            spyOn(view, 'invalidGroupSave');
            let triggerOnModelStub = sinon.stub(view.context, 'trigger')
                .withArgs('dashboard-filter-group-invalid-save').returns(view.invalidGroupSave());
            view.context.trigger('dashboard-filter-group-invalid-save', []);
            expect(view.invalidGroupSave).toHaveBeenCalled();
        });
    });
});
