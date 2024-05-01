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
describe('Base.View.FilterFilterDropdown', function() {
    var view, layout, app, sinonSandbox;
    var parentLayout;
    var filterpanel;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'filter-filter-dropdown');
        SugarTest.testMetadata.set();

        parentLayout = app.view.createLayout({type: 'base'});
        filterpanel = SugarTest.createLayout(
            'base',
            'Cases',
            'filterpanel',
            {},
            null,
            null,
            {layout: parentLayout}
        );
        SugarTest.declareData('base', 'Filters');
        layout = SugarTest.createLayout('base', 'Cases', 'filter', {}, null, null, {layout: filterpanel});
        sinon.stub(app.BeanCollection.prototype, 'fetch').callsFake(function(options) {
            options.success();
        });
        layout.filters = app.data.createBeanCollection('Filters');
        layout.filters.setModuleName('Cases');
        layout.filters.load();
        view = SugarTest.createView("base", "Cases", "filter-filter-dropdown", null, null, null, layout);
        view.layout = layout;
        sinonSandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sinon.restore();
        sinonSandbox.restore();
        view.dispose();
        layout.dispose();
        parentLayout.dispose();
        filterpanel.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
    });

    describe('handleSelect callback of filter:select:module', function() {

        it('should select the dropdown value', function() {
            view.filterNode = {};
            view.filterNode.select2 = sinon.stub();

            view.handleSelect('my_awesome_filter');

            expect(view.filterNode.select2).toHaveBeenCalled();
            expect(view.filterNode.select2).toHaveBeenCalledWith('val', 'my_awesome_filter', true);
        });

    });

    describe('isFilterEditable', function() {

        beforeEach(function() {
            view.layout.filters.collection.add({id: 'test_id', name: 'TEST', created_by: 'test'});
            view.layout.filters.collection.add({id: 'test_id_2', name: 'TEST_2', created_by: 'test_2'});
            view.layout.filters.collection.add({
                id: 'test_not_editable',
                name: 'TEST_3', created_by: 'test_3',
                editable: false
            });
            app.user.set('id', 'test_2');
        });

        it('should not be able to create filter nor to have access to activities should return false', function() {
            sinonSandbox.stub(view.layout, 'canCreateFilter').returns(false);
            view.layout.showingActivities = false;
            view.filterDropdownEnabled = true;

            expect(view.isFilterEditable('all_records')).toBeFalsy();
        });

        it('should return true all_records and create', function() {
            expect(view.isFilterEditable('create')).toBeTruthy();
            expect(view.isFilterEditable('create')).not.toBeFalsy();
            expect(view.isFilterEditable('all_records')).toBeTruthy();
            expect(view.isFilterEditable('all_records')).not.toBeFalsy();
        });

        it('should not have access to model and return true in order to create', function() {
            view.layout.filters.collection.remove({id: 'test_id', name: 'TEST', created_by: 'test'});

            expect(view.isFilterEditable('test_id')).toBeTruthy();
        });

        it('should not be editable and return false', function() {
            expect(view.isFilterEditable('test_not_editable')).toBeFalsy();
        });

        it('should be editable if created by current user', function() {
            expect(view.isFilterEditable('test_id_2')).toBeTruthy();
            expect(view.isFilterEditable('test_id')).toBeFalsy();
        });
    });

    describe('handleModuleChange', function() {

        it('should disable filter dropdown when All Records is selected', function() {
            sinonSandbox.stub(app.metadata, 'getModule').callsFake(function() {return {isBwcEnabled: false};});
            expect(view.filterDropdownEnabled).toBeTruthy();
            view.layout.trigger("filter:change:module", "ALL_RECORDS", "all_modules");
            expect(view.filterDropdownEnabled).toBeFalsy();
        });

    });

    describe('filterList', function() {

        var expected, filterList;

        beforeEach(function() {
            view.layout.filters.collection.add({id: 'all_records', name: 'ALL_RECORDS', editable: false});
            view.layout.filters.collection.add({id: 'test_id', name: 'TEST'});
            view.layout.filters.collection.add({id: 'test_id_2', name: 'TEST_2'});
        });

        it('should return filter list with translated labels', function() {
            sinonSandbox.stub(view.layout, 'canCreateFilter').callsFake(function() {return false;});
            expected = [
                {id: 'test_id', text: app.lang.get('TEST')},
                {id: 'test_id_2', text: app.lang.get('TEST_2')},
                {id: 'all_records', text: app.lang.get('ALL_RECORDS'), firstNonUserFilter: true}
            ];
            filterList = view.getFilterList();
            expect(filterList).toEqual(expected);
        });

        it('should allow to override "all_records" filter', function() {
            view.labelAllRecordsFormatted = 'LBL_FILTER_ALL_DUPLICATES';
            sinonSandbox.stub(view.layout, 'canCreateFilter').callsFake(function() {return false;});
            sinonSandbox.stub(view.layout.filters, 'sort');
            expected = [
                {id: 'test_id', text: app.lang.get('TEST')},
                {id: 'test_id_2', text: app.lang.get('TEST_2')},
                {id: 'all_records', text: app.lang.get('LBL_FILTER_ALL_DUPLICATES'), firstNonUserFilter: true}
            ];
            filterList = view.getFilterList();
            expect(filterList).toEqual(expected);
        });

        it('should return filter list (including create) with translated labels', function() {
            sinonSandbox.stub(view.layout, 'canCreateFilter').callsFake(function() {return true;});
            expected = [
                {id: 'create', text: app.lang.get('LBL_FILTER_CREATE_NEW')},
                {id: 'test_id', text: app.lang.get('TEST')},
                {id: 'test_id_2', text: app.lang.get('TEST_2')},
                {id: 'all_records', text: app.lang.get('ALL_RECORDS'), firstNonUserFilter: true}
            ];
            filterList = view.getFilterList();
            expect(filterList).toEqual(expected);
        });

        it('should allow the use of user created filters', function() {
            app.user.set('id', 'seed_max');
            view.layout.filters.collection.add({
                id: 'max_filter',
                name: 'Filter created by Max',
                created_by: 'seed_max'
            });
            sinonSandbox.stub(view.layout, 'canCreateFilter').callsFake(function() {return true;});
            expected = [
                {id: 'create', text: app.lang.get('LBL_FILTER_CREATE_NEW')},
                {id: 'max_filter', text: app.lang.get('Filter created by Max')},
                {id: 'test_id', text: app.lang.get('TEST')},
                {id: 'test_id_2', text: app.lang.get('TEST_2')},
                {id: 'all_records', text: app.lang.get('ALL_RECORDS'), firstNonUserFilter: true}
            ];
            filterList = view.getFilterList();
            expect(filterList).toEqual(expected);
        });

        it('should not allow the use of user created filters', function() {
            app.user.set('id', 'seed_jim');
            view.layout.filters.collection.add({
                id: 'max_filter',
                name: 'Filter created by Max',
                created_by: 'seed_max'
            });
            sinonSandbox.stub(view.layout, 'canCreateFilter').callsFake(function() {return true;});
            expected = [
                {id: 'create', text: app.lang.get('LBL_FILTER_CREATE_NEW')},
                {id: 'test_id', text: app.lang.get('TEST')},
                {id: 'test_id_2', text: app.lang.get('TEST_2')},
                {id: 'all_records', text: app.lang.get('ALL_RECORDS'), firstNonUserFilter: true}
            ];
            filterList = view.getFilterList();
            expect(filterList).toEqual(expected);
        });
    });

    describe('select2 options', function() {

        describe('initSelection', function() {

            var $input, callback;

            beforeEach(function() {
                $input = $('<input type="text">');
                callback = sinon.stub();
                view.layout.filters.collection.add({id: 'test_id', name: 'TEST'});
            });


            it('should recognize when selected filter is create', function() {
                var $input = $('<input type="text">').val('create'),
                    callback = sinon.stub(),
                    expected = {id: "create", text: app.lang.get("LBL_FILTER_CREATE_NEW")};

                view.initSelection($input, callback);

                expect(callback).toHaveBeenCalled();
                expect(callback.lastCall.args[0]).toEqual(expected);
            });

            it('should get selected filter', function() {
                var $input = $('<input type="text">').val('test_id'),
                    callback = sinon.stub(),
                    expected = {id: 'test_id', text: app.lang.get('TEST')};

                view.initSelection($input, callback);

                expect(callback).toHaveBeenCalled();
                expect(callback.lastCall.args[0]).toEqual(expected);
            });

            it('should call "formatAllRecordsFilter" when selected filter is "all_records"', function() {
                view.layout.filters.collection.add({id: 'all_records', name: 'ALL_RECORDS'});
                var $input = $('<input type="text">').val('all_records'),
                    callback = sinon.stub(),
                    formatStub = sinonSandbox.spy(view, 'formatAllRecordsFilter');

                view.initSelection($input, callback);

                expect(callback).toHaveBeenCalled();
                expect(formatStub).toHaveBeenCalled();
            });

            it('should get all_records filter with basic label', function() {
                var $input = $('<input type="text">').val('all_records'),
                    callback = sinon.stub(),
                    expected = {id: 'all_records', text: app.lang.get('LBL_FILTER_ALL_RECORDS')};

                view.initSelection($input, callback);

                expect(callback).toHaveBeenCalled();
                expect(callback.lastCall.args[0]).toEqual(expected);
            });

        });

        describe('formatSelection', function() {
            var jQueryStubs, toggleFilterCursorStub;
            beforeEach(function() {
                jQueryStubs = {};
                jQueryStubs.attr = sinon.stub().returns(jQueryStubs);
                jQueryStubs.html = sinon.stub().returns(jQueryStubs);
                jQueryStubs.toggle = sinon.stub().returns(jQueryStubs);
                jQueryStubs.toggleClass = sinon.stub().returns(jQueryStubs);

                toggleFilterCursorStub = sinonSandbox.stub(view, 'toggleFilterCursor');
                sinonSandbox.stub(view, '$').callsFake(function() {
                    return jQueryStubs;
                });
                //Template replacement
                view._select2formatSelectionTemplate = function(val) {
                    return val;
                };
            });
            it('should format the filter dropdown on left', function() {
                var expected = {label: app.lang.get('LBL_FILTER'), enabled: view.filterDropdownEnabled};

                expect(view.formatSelection({id: 'test', text: 'TEST'})).toEqual(expected);
                expect(toggleFilterCursorStub).toHaveBeenCalled();
                expect(toggleFilterCursorStub).toHaveBeenCalledWith(true);
            });
            describe('formatting the selected filter (on right)', function() {
                it('should display the filter label because it is a custom filter', function() {
                    view.formatSelection({id: 'test', text: 'TEST'});
                    expect(jQueryStubs.html).toHaveBeenCalled();
                    expect(jQueryStubs.html).toHaveBeenCalledWith('TEST');
                });
                it('should call "formatAllRecordsFilter" to format label because filter is "all_records"', function() {
                    var formatStub = sinonSandbox.spy(view, 'formatAllRecordsFilter');
                    view.formatSelection({id: 'all_records', text: 'LBL_LISTVIEW_FILTER_ALL'});
                    expect(formatStub).toHaveBeenCalled();
                    expect(jQueryStubs.html).toHaveBeenCalled();
                });
                it('should hide the close button if the selected filter is "all_records"', function() {
                    view.formatSelection({id: 'all_records', text: 'TEST'});
                    expect(jQueryStubs.toggle).toHaveBeenCalledWith(false);
                });
                it('should show the close button otherwise', function() {
                    view.formatSelection({id: 'my_filter_id', text: 'TEST'});
                    expect(jQueryStubs.toggle).toHaveBeenCalledWith(true);
                });
            });
            describe('make the selected filter editable or not', function() {
                it('should be editable because it is a custom filter', function() {
                    view.layout.filters.collection.add({id: 'my_filter_id', editable: true});
                    view.formatSelection({id: 'my_filter_id', text: 'LBL_LISTVIEW_FILTER_ALL'});
                    expect(toggleFilterCursorStub).toHaveBeenCalled();
                    expect(toggleFilterCursorStub).toHaveBeenCalledWith(true);
                });
                it('should not be editable because it is a predefined filter', function() {
                    view.layout.filters.collection.add({id: 'favorites', editable: false});
                    view.formatSelection({id: 'favorites', text: 'LBL_LISTVIEW_FILTER_ALL'});
                    expect(toggleFilterCursorStub).toHaveBeenCalled();
                    expect(toggleFilterCursorStub).toHaveBeenCalledWith(false);
                });
                it('should not be editable because it is a bwc module', function() {
                    view.filterDropdownEnabled = false;
                    view.layout.showingActivities = false;
                    view.formatSelection({id: 'all_records', text: 'TEST'});
                    expect(toggleFilterCursorStub).toHaveBeenCalled();
                    expect(toggleFilterCursorStub).toHaveBeenCalledWith(false);
                });
                it('should not be editable because it is on Activity Stream', function() {
                    view.filterDropdownEnabled = true;
                    view.layout.showingActivities = true;
                    view.formatSelection({id: 'all_records', text: 'LBL_LISTVIEW_FILTER_ALL'});
                    expect(toggleFilterCursorStub).toHaveBeenCalled();
                    expect(toggleFilterCursorStub).toHaveBeenCalledWith(false);
                });
                it('should be editable if not on Activity Stream and not bwc module', function() {
                    view.filterDropdownEnabled = true;
                    view.layout.showingActivities = false;
                    view.formatSelection({id: 'all_records', text: 'LBL_LISTVIEW_FILTER_ALL'});
                    expect(toggleFilterCursorStub).toHaveBeenCalled();
                    expect(toggleFilterCursorStub).toHaveBeenCalledWith(true);
                });
            });
        });

        describe('formatAllRecordsFilter', function() {
            var model;
            beforeEach(function() {
                model = app.data.createBean('Filters', {id: 'all_records'}, {moduleName: 'Cases'});
                view.layout.layoutType = 'record';
                view.layout.layout.currentModule = 'Cases';
            });
            it('should display "Create" for any sidecar module', function() {
                view.filterDropdownEnabled = true;
                view.layout.showingActivities = false;
                var item = view.formatAllRecordsFilter({id: 'all_records'});
                expect(item.text).toEqual(view.labelCreateNewFilter);
            });
            it('should display "All <Module>s" for any bwc module', function() {
                model.set('name', 'LBL_LISTVIEW_FILTER_ALL');
                view.filterDropdownEnabled = false;
                view.layout.showingActivities = false;
                var item = view.formatAllRecordsFilter({id: 'all_records'}, model);
                expect(item.text).toEqual(view.labelAllRecords);
            });
            it('should display "All records" because record layout and filtering all related modules', function() {
                model.set('name', 'LBL_LISTVIEW_FILTER_ALL');
                view.filterDropdownEnabled = false;
                view.layout.showingActivities = false;
                var item = view.formatAllRecordsFilter({id: 'all_records'}, model);
                expect(item.text).toEqual(view.labelAllRecords);
            });
            it('should display "All Activities" because Activity Stream', function() {
                model.set('name', 'LBL_LISTVIEW_FILTER_ALL');
                view.layout.layout.currentModule = 'Activities';
                view.filterDropdownEnabled = false;
                view.layout.showingActivities = false;
                var item = view.formatAllRecordsFilter({id: 'all_records'}, model);
                expect(item.text).toEqual('LBL_LISTVIEW_FILTER_ALL');
            });
        });

        it('should formatResult for selected filter', function() {
            sinonSandbox.stub(layout, 'getLastFilter').callsFake(function() {return 'last_filter';});
            //Template replacement
            view._select2formatResultTemplate = function(val) {return val;};

            expect(view.formatResult({id: 'test', text: 'TEST'}))
                .toEqual({id: 'test', text: 'TEST', icon: undefined});

            expect(view.formatResult({id: 'create', text: 'Create'}))
                .toEqual({id: 'create', text: 'Create', icon: 'sicon-plus'});

            expect(view.formatResult({id: 'last_filter', text: 'Last selected filter'}))
                .toEqual({id: 'last_filter', text: 'Last selected filter', icon: 'sicon-check'});
        });

        it('should formatResultCssClass (add css class to visually add borders and separate categories)', function() {
            sinonSandbox.stub(layout, 'getLastFilter').callsFake(function() {return 'last_filter';});
            //Template replacement
            view._select2formatResultTemplate = function(val) {return val;};

            expect(view.formatResultCssClass({id: 'test', text: 'TEST'}))
                .toBeUndefined();

            expect(view.formatResultCssClass({id: 'create', text: 'Create'}))
                .toEqual('select2-result-border-bottom');

            expect(view.formatResultCssClass({id: 'test', text: 'TEST', firstNonUserFilter: true}))
                .toEqual('select2-result-border-top');
        });
    });


    describe('handleEditFilter', function() {
        var filterId;
        var triggerStub;
        beforeEach(function() {
            view.filterNode = $('');
            sinonSandbox.stub(view.filterNode, 'val').callsFake(function() {return filterId;});
            view.layout.filters.collection.add({id: 'test_id'});
            triggerStub = sinonSandbox.stub(layout, 'trigger');
        });
        it('should trigger "filter:create:open" if action is edit filter', function() {
            filterId = 'test_id';
            view.handleEditFilter();
            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalledWith('filter:create:open');
        });
        it('should trigger "filter:change:filter" if action is create new filter', function() {
            filterId = 'all_records';
            view.handleEditFilter();
            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalledWith('filter:select:filter', 'create');
        });
        it('shouldn\'t do anything if any key other than space or enter is pressed', function() {
            var evt = {
                type: 'keydown',
                keyCode: $.ui.keyCode.TAB,
                which: $.ui.keyCode.TAB,
                stopPropagation: sinon.spy(),
                preventDefault: sinon.spy(),
            };
            view.handleEditFilter(evt);
            expect(evt.stopPropagation).not.toHaveBeenCalled();
            expect(evt.preventDefault).not.toHaveBeenCalled();
            expect(triggerStub).not.toHaveBeenCalled();
        });
        using('space or enter', [
            $.ui.keyCode.ENTER,
            $.ui.keyCode.SPACE
        ], function(key) {
            it('should prevent default behavior, stop propagation and trigger a change on key press', function() {
                var evt = {
                    type: 'keydown',
                    keyCode: key,
                    which: key,
                    stopPropagation: sinon.spy(),
                    preventDefault: sinon.spy(),
                };
                view.handleEditFilter(evt);
                expect(evt.stopPropagation).toHaveBeenCalled();
                expect(evt.preventDefault).toHaveBeenCalled();
                expect(triggerStub).toHaveBeenCalled();
            });
        });
    });

    describe('handleClearFilter', function() {
        var triggerStub;
        var clearLastFilterStub;
        beforeEach(function() {
            triggerStub = sinonSandbox.stub(layout, 'trigger');
            clearLastFilterStub = sinonSandbox.stub(layout, 'clearLastFilter');
        });
        it('should stop propagation, clear last filter and trigger "filter:reinitialize"', function() {
            view.layout.filters.collection.defaultFilterFromMeta = 'test_default_filter';
            view.filterNode = $('');
            var evt = {
                'stopPropagation': sinon.spy()
            };
            view.handleClearFilter(evt);
            expect(evt.stopPropagation).toHaveBeenCalled();
            expect(clearLastFilterStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalledWith('filter:select:filter', 'test_default_filter');
        });
        using('space or enter', [
            $.ui.keyCode.ENTER,
            $.ui.keyCode.SPACE
        ], function(key) {
            it('should stop propagation, clear last filter and trigger "filter:reinitialize"', function() {
                view.layout.filters.collection.defaultFilterFromMeta = 'test_default_filter';
                view.filterNode = $('');
                var evt = {
                    type: 'keydown',
                    keyCode: key,
                    which: key,
                    stopPropagation: sinon.spy()
                };
                view.handleClearFilter(evt);
                expect(evt.stopPropagation).toHaveBeenCalled();
                expect(clearLastFilterStub).toHaveBeenCalled();
                expect(triggerStub).toHaveBeenCalledWith('filter:select:filter', 'test_default_filter');
            });
        });
        it('shouldn\'t do anything if any key other than space or enter is pressed', function() {
            var evt = {
                type: 'keydown',
                keyCode: $.ui.keyCode.DOWN,
                which: $.ui.keyCode.DOWN,
                stopPropagation: sinon.spy()
            };
            view.handleClearFilter(evt);
            expect(evt.stopPropagation).not.toHaveBeenCalled();
            expect(clearLastFilterStub).not.toHaveBeenCalled();
            expect(triggerStub).not.toHaveBeenCalled();
        });
    });
});
