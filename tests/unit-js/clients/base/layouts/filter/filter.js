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
describe('Base.Layout.Filter', function() {

    var app, layout, moduleName = 'Accounts';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.declareData('base', 'Filters');
        sinon.stub(app.BeanCollection.prototype, 'fetch').callsFake(function(options) {
            options.success();
        });
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        layout.dispose();
        layout.context = null;
        layout = null;
    });

    describe('filter layout', function() {
        var parentLayout;
        beforeEach(function() {
            parentLayout = app.view.createLayout({
                module: moduleName
            });
            layout = SugarTest.createLayout(
                'base',
                moduleName,
                'filter',
                {last_state: {id: 'filter'}},
                false,
                false,
                {layout: parentLayout}
            );
        });

        afterEach(function() {
            parentLayout.dispose();
        });

        describe('events', function() {
            it('should call apply on filter:apply', function() {
                var stub = sinon.stub(layout, 'applyFilter');
                // clear previous events
                layout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                layout.trigger('filter:apply');
                expect(stub).toHaveBeenCalled();
            });

            it('should null the context editing filter and trigger parent layout filter:create:close', function() {
                var spy = sinon.spy();
                parentLayout.on('filter:create:close', spy);

                layout.trigger('filter:create:close');
                expect(layout.context.editingFilter).toEqual(null);
                expect(spy).toHaveBeenCalled();
            });
            it('should set the layout context editing filter and trigger parent layout filter:create:open', function() {
                var spy = sinon.spy();
                parentLayout.on('filter:create:open', spy);
                var filtermodule = 'test';
                layout.listViewModule = 'testModule';
                parentLayout.context.editingFilter = '';

                layout.trigger('filter:create:open', filtermodule);
                expect(parentLayout.context.editingFilter).toEqual(filtermodule);
                expect(spy).toHaveBeenCalled();
            });
            it('should set the context editing filter and trigger parent layout filter:create:open', function() {
                var spy = sinon.spy();
                parentLayout.on('filter:create:open', spy);
                var filtermodule = 'test';
                layout.trigger('filter:create:open', filtermodule);
                expect(layout.context.editingFilter).toEqual(filtermodule);
                expect(spy).toHaveBeenCalled();
            });
            it('should trigger parent layout subpanel change on subpanel:change', function() {
                var spy = sinon.spy();
                parentLayout.on('subpanel:change', spy);
                layout.trigger('subpanel:change');
                expect(spy).toHaveBeenCalled();
            });
            it('should call initialize filter state on filter:get', function() {
                var stub = sinon.stub(layout, 'initializeFilterState');
                // clear previous events
                layout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                layout.trigger('filter:get');
                expect(stub).toHaveBeenCalled();
            });
            it('should trigger layout filter apply on parent layout filter:apply', function() {
                var stub = sinon.stub(layout, 'initializeFilterState');
                // clear previous events
                layout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                layout.trigger('filter:get');
                expect(stub).toHaveBeenCalled();
            });
            it('should call handleFilterPanelChange on parent layout filterpanel:change', function() {
                var stub = sinon.stub(layout, 'handleFilterPanelChange');
                // clear previous events
                layout.off();
                parentLayout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                parentLayout.trigger('filterpanel:change');
                expect(stub).toHaveBeenCalled();
            });

            describe('addFilter', function() {

                beforeEach(function() {
                    layout.filters = app.data.createBeanCollection('Filters');
                    layout.filters.setModuleName(moduleName);
                    layout.filters.load();
                });

                var setLastFilterStub, clearFilterEditStateStub, layoutTriggerStub;
                beforeEach(function() {

                    setLastFilterStub = sinon.stub(layout, 'setLastFilter');
                    clearFilterEditStateStub = sinon.stub(layout, 'clearFilterEditState');
                });
                it('should be called by layout context', function() {
                    var addFilterStub = sinon.stub(layout, 'addFilter');

                    // clear previous events
                    layout.off();
                    layout.context.off();
                    // replace the original fn with the spy
                    layout.initialize(layout.options);

                    layout.context.trigger('filter:add');
                    expect(addFilterStub).toHaveBeenCalled();
                });
                it('should add the filter, update saved filters, set last state, clear edit state and reinitialize"',
                    function() {
                        layoutTriggerStub = sinon.stub(layout.layout, 'trigger');
                        layout.addFilter(new Backbone.Model({id: 'new_filter'}));
                        expect(layout.filters.collection.get('new_filter')).toBeDefined();
                        expect(clearFilterEditStateStub).toHaveBeenCalled();
                        expect(layoutTriggerStub).toHaveBeenCalled();
                        expect(layoutTriggerStub).toHaveBeenCalledWith('filter:reinitialize');
                        expect(layout.context.get('currentFilterId')).toEqual('new_filter');
                    }
                );

                describe('bulk API', function() {
                    var getRelevantCtxListStub, loadOptions, ctxCollection, fakeContext, hasAtLeastStub, triggerBulkStub;

                    beforeEach(function() {
                        loadOptions = false;
                        ctxCollection = function() {
                            var collection = app.data.createBeanCollection(moduleName);
                            collection.resetPagination = function() {};
                            hasAtLeastStub = sinon.stub(collection, 'hasAtLeast');
                            return collection;
                        };
                        fakeContext = function(attrs) {
                            attrs = _.extend({collection: ctxCollection()}, attrs);
                            var ctx = app.context.getContext(attrs);
                            ctx.resetPagination = function() {};
                            sinon.stub(ctx, 'loadData').callsFake(function(opts) {
                                loadOptions = opts;
                            });
                            return ctx;
                        };
                        sinon.stub(app.metadata, 'getModule').returns({});
                        getRelevantCtxListStub = sinon.stub(layout, 'getRelevantContextList');
                        triggerBulkStub = sinon.stub(app.api, 'triggerBulkCall');
                    });

                    using('different context lists', [
                        {
                            ctxList: [
                                {collapsed: false}
                            ],
                            fetchBulkCalled: false,
                            countBulkCalled: false,
                            expectCount: false
                        },
                        {
                            ctxList: [
                                {collapsed: false},
                                {collapsed: false}
                            ],
                            fetchBulkCalled: true,
                            countBulkCalled: false,
                            expectCount: false
                        },
                        {
                            ctxList: [
                                {collapsed: false},
                                {collapsed: true}
                            ],
                            fetchBulkCalled: true,
                            countBulkCalled: false,
                            expectCount: true
                        },
                        {
                            ctxList: [
                                {collapsed: false},
                                {collapsed: true},
                                {collapsed: true}
                            ],
                            fetchBulkCalled: true,
                            countBulkCalled: true,
                            expectCount: true
                        },
                        {
                            ctxList: [
                                {collapsed: true}
                            ],
                            fetchBulkCalled: false,
                            countBulkCalled: false,
                            expectCount: true
                        },
                        {
                            ctxList: [
                                {collapsed: true},
                                {collapsed: true}
                            ],
                            fetchBulkCalled: true,
                            countBulkCalled: true,
                            expectCount: true
                        }
                    ], function(options) {
                        it('should use the bulk API to fetch the count of collapsed subpanels', function() {
                            options.ctxList = _.map(options.ctxList, function(attrs) {
                                return fakeContext(attrs);
                            });
                            getRelevantCtxListStub.returns(options.ctxList);

                            layout.applyFilter();

                            expect(hasAtLeastStub.called).toEqual(options.expectCount);
                        });

                        it('should use the bulk API when more than one context is loaded', function() {
                            options.ctxList = _.map(options.ctxList, function(attrs) {
                                return fakeContext(attrs);
                            });
                            getRelevantCtxListStub.returns(options.ctxList);

                            layout.applyFilter();

                            var hasBulkId = loadOptions.apiOptions.bulk !== false;

                            expect(getRelevantCtxListStub).toHaveBeenCalled();
                            expect(hasBulkId).toEqual(options.fetchBulkCalled);
                        });
                    });
                });
            });
            describe('refreshDropdown', function() {
                beforeEach(function() {
                    layout.layout.currentModule = 'TestModule';
                });
                it('should be called by app events', function() {
                    var refreshStub = sinon.stub(layout, 'refreshDropdown');

                    // clear previous events
                    app.events.off('dashlet:filter:save');
                    layout.stopListening(app.events);
                    // replace the original fn with the spy
                    layout.initialize(layout.options);

                    app.events.trigger('dashlet:filter:save');
                    expect(refreshStub).toHaveBeenCalled();
                });
                it('should not trigger a filter:reinitialize if the module does not match this.layout.currentModule',
                    function() {
                        let triggerStub = sinon.stub(layout.layout, 'trigger');
                        let testModule = 'Contacts';

                        layout.refreshDropdown(testModule);
                        expect(triggerStub).not.toHaveBeenCalledWith('filter:reinitialize');
                    }
                );
                it('should trigger a filter:reinitialize if the module matches this.layout.currentModule',
                    function() {
                        let triggerStub = sinon.stub(layout.layout, 'trigger');
                        let testModule = 'TestModule';
                        let baseController = app.view._getController({
                            type: 'layout',
                            name: 'filter'
                        });
                        baseController.loadedModules[testModule] = true;

                        layout.refreshDropdown(testModule);

                        expect(baseController.loadedModules[testModule]).toBeFalsy();
                        expect(triggerStub).toHaveBeenCalledWith('filter:reinitialize');
                    }
                );
            });
            it('should call removeFilter  on parent layout filter:remove', function() {
                var stub = sinon.stub(layout, 'removeFilter');
                // clear previous events
                layout.off();
                parentLayout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                parentLayout.trigger('filter:remove');
                expect(stub).toHaveBeenCalled();
            });
            it('should call initializeFilterState  on parent layout filter:reinitialize', function() {
                var stub = sinon.stub(layout, 'initializeFilterState');
                // clear previous events
                layout.off();
                parentLayout.off();
                // replace the original fn with the spy
                layout.initialize(layout.options);

                parentLayout.trigger('filter:reinitialize');
                expect(stub).toHaveBeenCalled();
            });
        });

        it('should remove filters', function() {
            var model = new Backbone.Model({id: '123'});
            var clearLastFilterStub = sinon.stub(layout, 'clearLastFilter');
            layout.filters = app.data.createBeanCollection('Filters');
            layout.filters.setModuleName(moduleName);
            layout.filters.load();
            layout.filters.collection.add(model);
            parentLayout.off();
            var spy = sinon.spy();
            parentLayout.on('filter:reinitialize', spy);
            layout.removeFilter(model);
            // removed the model
            expect(_.contains(layout.filters.collection.models, model)).toBeFalsy();
            // triggered filter reinit
            expect(spy).toHaveBeenCalled();
            expect(clearLastFilterStub).toHaveBeenCalled();
            expect(clearLastFilterStub).toHaveBeenCalledWith(layout.currentModule, layout.layoutType);
            expect(layout.context.get('currentFilterId')).toEqual(null);
        });

        it('should add filters', function() {
            var model = new Backbone.Model({id: '123'});
            layout.filters = app.data.createBeanCollection('Filters');
            layout.filters.setModuleName(moduleName);
            layout.filters.load();
            layout.filters.collection.add(model);
            parentLayout.off();
            var spy = sinon.spy();
            parentLayout.on('filter:reinitialize', spy);
            layout.addFilter(model);
            // added the model
            expect(_.contains(layout.filters.collection.models, model)).toBeTruthy();
            // triggered filter reinit
            expect(spy).toHaveBeenCalled();
        });

        it('should handle filter panel change for regular modules', function() {
            var spy1 = sinon.spy();
            var spy2 = sinon.spy();
            layout.on('filter:render:module', spy1);
            layout.on('filter:change:module', spy2);
            sinon.stub(app.data, 'getRelatedModule');

            layout.handleFilterPanelChange(moduleName, true);

            sinon.assert.notCalled(spy1);
            sinon.assert.notCalled(spy2);
        });
        it('should handle filter panel change for activity stream', function() {
            var spy1 = sinon.spy();
            var spy2 = sinon.spy();
            layout.on('filter:change:module', spy2);
            layout.on('filter:render:module', spy1);
            var stubCache = sinon.stub(app.user.lastState, 'get');
            var stubData = sinon.stub(app.data, 'getRelatedModule');

            layout.handleFilterPanelChange('activitystream', false);


            sinon.assert.notCalled(stubCache);
            sinon.assert.notCalled(stubData);
            expect(spy1).toHaveBeenCalled();
            expect(spy2.getCall(0).args[0]).toEqual('Activities');
        });
        it('should handle filter panel change for related records', function() {
            var spy1 = sinon.spy();
            var spy2 = sinon.spy();
            layout.layoutType = 'record';
            layout.on('filter:render:module', spy1);
            layout.on('filter:change:module', spy2);
            var stubCache = sinon.stub(app.user.lastState, 'get').callsFake(function() {
                return 'Bugs';
            });
            var stubData = sinon.stub(app.data, 'getRelatedModule').callsFake(function() {
                return 'Test';
            });

            layout.handleFilterPanelChange(moduleName, false);

            expect(spy1).toHaveBeenCalled();
            expect(spy2.getCall(0).args[1]).toEqual('Bugs');
            expect(spy2.getCall(0).args[0]).toEqual('Test');
        });
        describe('handleFilterChange', function() {
            var ctxt, lastEditState, model;
            var stubCache, triggerStub, layoutTriggerStub, retrieveFilterEditStateStub;
            beforeEach(function() {
                SugarTest.testMetadata.init();
                SugarTest.testMetadata.set();

                ctxt = new Backbone.Model({collection: {
                    resetPagination: function() {},
                    reset: function() {}
                }});

                stubCache = sinon.stub(app.user.lastState, 'set');
                sinon.stub(layout, 'getRelevantContextList').callsFake(function() {
                    return [ctxt];
                });
                triggerStub = sinon.stub(layout, 'trigger');
                layoutTriggerStub = sinon.stub(layout.layout, 'trigger');

                lastEditState = undefined;
                retrieveFilterEditStateStub = sinon.stub(layout, 'retrieveFilterEditState').callsFake(function() {
                    return lastEditState;
                });

                model = new Backbone.Model({id: '123', filter_definition: 'test'});
                model.getSynced = $.noop;
                layout.filters = app.data.createBeanCollection('Filters');
                layout.filters.setModuleName(moduleName);
                layout.filters.load();
                layout.filters.collection.add(model);
            });
            afterEach(function() {
                SugarTest.testMetadata.dispose();
            });

            it('should save last filter into cache', function() {
                layout.handleFilterChange(model.get('id'));
                expect(stubCache).toHaveBeenCalled();
            });

            describe('preserving last search', function() {
                it('should open the filter form if the edit state is found for this filter', function() {
                    lastEditState = {
                        id: model.get('id'),
                        name: 'test',
                        filter_definition: [{'$favorite': ''}]
                    };
                    expect(model.toJSON()).not.toEqual(lastEditState);
                    layout.handleFilterChange(model.get('id'));
                    expect(triggerStub).toHaveBeenCalled();
                    expect(triggerStub).toHaveBeenCalledWith('filter:create:open');
                    expect(layoutTriggerStub).toHaveBeenCalled();
                    expect(layoutTriggerStub).toHaveBeenCalledWith('filter:set:name');
                    expect(layoutTriggerStub).toHaveBeenCalledWith('filter:toggle:savestate');
                });
                xit('should not validate if the filter definition has not changed', function() {
                    model.set({
                        name: 'test',
                        filter_definition: [{'$favorite': ''}]
                    });
                    lastEditState = {
                        id: '123',
                        name: 'test',
                        filter_definition: [{'$favorite': ''}]
                    };
                    expect(model.toJSON()).toEqual(lastEditState);
                    layout.handleFilterChange(model.get('id'), false);
                    expect(triggerStub).not.toHaveBeenCalled();
                    expect(layoutTriggerStub).toHaveBeenCalled();
                    expect(layoutTriggerStub).toHaveBeenCalledWith('filter:create:close');
                });
                it('should close the filter form if no edit state is found for this filter', function() {
                    layout.handleFilterChange(model.get('id'));
                    expect(triggerStub).not.toHaveBeenCalledWith('filter:create:open');
                    expect(layoutTriggerStub).toHaveBeenCalled();
                    expect(layoutTriggerStub).toHaveBeenCalledWith('filter:create:close');
                });
            });
            it('shoud determine if we need to clear the collection(s) and trigger quicksearch if yes', function() {
                layout.handleFilterChange(model.get('id'), false);
                expect(ctxt.get('collection').origFilterDef).toEqual(model.get('filter_definition'));
                expect(triggerStub).toHaveBeenCalled();
                expect(triggerStub).toHaveBeenCalledWith('filter:apply');
            });
            it('shoud determine if we need to clear the collection(s) and do nothing if no', function() {
                ctxt.get('collection').origFilterDef = model.get('filter_definition');
                layout.handleFilterChange(model.get('id'), false);
                expect(triggerStub).not.toHaveBeenCalled();
            });
        });

        describe('applyFilter', function() {
            using(
                'different filter def inputs',
                [
                    {
                        input: {'$name': 'test'},
                        defaultFilterDef: undefined,
                        result: [{'$name': 'test'}]
                    },
                    {
                        input: [{'$name': 'test'}],
                        defaultFilterDef: [{'$owner': ''}],
                        result: [{'$name': 'test'}, {'$owner': ''}]
                    }
                ],
                function(data) {
                    it('should be able to apply a filter', function() {
                        var ctxt = app.context.getContext();
                        ctxt.set({
                            module: moduleName,
                            layout: 'filter'
                        });
                        ctxt.prepare();;
                        ctxt.get('collection').defaultFilterDef = data.defaultFilterDef;
                        var _oResetPagination = ctxt.get('collection').resetPagination;
                        ctxt.get('collection').resetPagination = function() {};
                        var stub = sinon.stub(ctxt, 'loadData').callsFake(function(options) {
                            options.success();
                        });
                        var resetLoadFlagSpy = sinon.spy(ctxt, 'resetLoadFlag');
                        var query = 'test query';

                        var spy = sinon.spy();

                        sinon.stub(layout, 'getRelevantContextList').callsFake(function() {
                            return [ctxt];
                        });
                        sinon.spy(layout, 'buildFilterDef');

                        app.events.on('preview:close', spy);

                        layout.applyFilter(query, data.input);

                        expect(ctxt.get('collection').filterDef).toEqual(data.result);
                        expect(ctxt.get('collection').origFilterDef).toEqual([{'$name': 'test'}]);
                        expect(ctxt.get('skipFetch')).toBeFalsy();
                        expect(resetLoadFlagSpy).toHaveBeenCalled();
                        expect(stub).toHaveBeenCalled();
                        expect(spy).toHaveBeenCalled();
                        ctxt.get('collection').resetPagination = _oResetPagination;
                    });
                }
            );
            it('should not apply a filter if auto_apply is false', function() {
                var getRelevantContextListStub = sinon.stub(layout, 'getRelevantContextList');
                var query = 'test query';
                var testFilterDef = {'$name': 'test'};

                layout.context.set('filterOptions', {auto_apply: false});
                layout.applyFilter(query, testFilterDef);
                expect(getRelevantContextListStub).not.toHaveBeenCalled();
            });
            it('should be able to add or remove a clear icon depending on the quicksearch field', function() {
                sinon.stub(layout, 'getRelevantContextList').callsFake(function() { return []; });
                layout.$el = $('<div></div>');
                layout.applyFilter('not empty');
                expect(layout.$('.add-on.sicon-close')[0]).not.toBeUndefined();
                layout.applyFilter('');
                expect(layout.$('.add-on.sicon-close')[0]).toBeUndefined();
            });
        });

        it('should get relevant context lists for activities', function() {
            layout.showingActivities = true;
            var ctxt = app.context.getContext({
                collection: app.data.createBeanCollection(),
                module: moduleName,
                layout: 'filter'
            });
            ctxt.prepare();
            var activityView = app.view.createView({
                name: 'activitystream',
                context: ctxt,
            });
            layout.layout._components = [activityView];
            var expectedList = [ctxt];
            sinon.mock(parentLayout, 'getActivityContext', function() {
                return ctxt;
            });
            var resultList = layout.getRelevantContextList();
            _.each(expectedList, function(ctx) {
                expect(_.contains(resultList, ctx)).toBeTruthy();
            });
        });
        it('should get relevant context lists for records layouts', function() {
            layout.showingActivities = false;
            layout.layoutType = 'records';
            var ctxt = app.context.getContext();
            ctxt.set({
                collection: new Backbone.Collection(),
                module: moduleName,
                layout: 'filter'
            });
            ctxt.prepare();
            var expectedList = [layout.context];
            var resultList = layout.getRelevantContextList();
            _.each(expectedList, function(ctx) {
                expect(_.contains(resultList, ctx)).toBeTruthy();
            });
        });
        it('should get relevant context lists for any other views', function() {
            layout.showingActivities = false;
            layout.layoutType = 'list';
            var ctxt = app.context.getContext();
            ctxt.set({
                collection: new Backbone.Collection(),
                module: moduleName,
                layout: 'filter',
                link: 'test1',
                isSubpanel: true,
                hidden: false
            });
            layout.context.children.push(ctxt);

            var ctxt1 = app.context.getContext();
            ctxt1.set({
                collection: new Backbone.Collection(),
                module: moduleName,
                layout: 'filter',
                link: 'test1',
                isSubpanel: true,
                hidden: false
            });
            layout.context.children.push(ctxt1);

            var ctxtWithoutCollection = app.context.getContext();
            ctxtWithoutCollection.set({
                module: moduleName,
                layout: 'filter',
                link: 'testNoCollection',
                isSubpanel: true,
                hidden: false
            });
            layout.context.children.push(ctxtWithoutCollection);

            var ctxtWithModelId = app.context.getContext();
            ctxtWithModelId.set({
                collection: new Backbone.Collection(),
                modelId: 'model_id',
                module: moduleName,
                layout: 'filter',
                link: 'testModelId',
                isSubpanel: true,
                hidden: false
            });
            layout.context.children.push(ctxtWithModelId);

            var expectedList = [ctxt, ctxt1];
            var resultList = layout.getRelevantContextList();
            _.each(expectedList, function(ctx) {
                expect(_.contains(resultList, ctx)).toBeTruthy();
            });
            expect(_.contains(resultList, ctxtWithoutCollection)).toBeFalsy();
        });

        describe('buildFilterDef', function() {
            var getModuleStub, filtersBeanPrototype;
            var filter1 = {'name': {'$starts': 'A'}};
            var filter2 = {'name_c': {'$starts': 'B'}};
            var filter3 = {'$favorite': ''};
            var fakeModuleMeta = {
                'fields': {'name': {}, 'test': {}},
                'filters': {
                    'default': {
                        'meta': {
                            'filters': [
                                {'filter_definition': filter1, 'id': 'test1'},
                                {'filter_definition': filter2, 'id': 'test2'},
                                {'filter_definition': filter3, 'id': 'test3'}
                            ]
                        }
                    }
                }
            };

            beforeEach(function() {
                getModuleStub = sinon.stub(app.metadata, 'getModule').returns(fakeModuleMeta);
                filtersBeanPrototype = app.data.getBeanClass('Filters').prototype;
            });

            it('should build a field-filtered filterDef', function() {
                var testFilterDef = [filter1, filter2, filter3];
                var result = [filter1, filter3];
                var ctxt = app.context.getContext();

                ctxt.set({
                    module: moduleName,
                    layout: 'filter'
                });
                ctxt.prepare();

                sinon.stub(filtersBeanPrototype, 'getModuleQuickSearchMeta')
                    .returns({fieldNames: ['name']});

                var builtDef = layout.buildFilterDef(testFilterDef, null, ctxt);
                expect(builtDef).toEqual(result);
            });

            it('should build a field-filtered filterDef with a search term', function() {
                var searchTerm = 'abc';
                var searchFilterDef = {'name': {'$starts': searchTerm}};
                var testFilterDef = [filter1, filter2, filter3];
                var result = [{'$and': [filter1, filter3, searchFilterDef]}];
                var ctxt = app.context.getContext();

                ctxt.set({
                    module: moduleName,
                    layout: 'filter'
                });
                ctxt.prepare();

                sinon.stub(filtersBeanPrototype, 'getModuleQuickSearchMeta')
                    .returns({fieldNames: ['name']});

                var builtDef = layout.buildFilterDef(testFilterDef, searchTerm, ctxt);
                expect(builtDef).toEqual(result);
            });

            it('should be able to build a filter def via a search term', function() {
                var searchTerm = 'test';
                var ctxt = app.context.getContext();
                var odef = {};
                var result = [{'name': {'$starts': 'test'}}];

                ctxt.set({
                    module: moduleName,
                    layout: 'filter'
                });
                ctxt.prepare();

                sinon.stub(filtersBeanPrototype, 'getModuleQuickSearchMeta')
                    .returns({fieldNames: ['name']});

                var builtDef = layout.buildFilterDef(odef, searchTerm, ctxt);
                expect(builtDef).toEqual(result);
            });

            it('should be able to build filter defs with multiple quick search fields', function() {
                var searchTerm = 'test';
                var ctxt = app.context.getContext();
                var odef = {'test': {'$test': 'test'}};
                var result = [{
                    '$and': [
                        {'test': {'$test': 'test'}},
                        {'name': {'$starts': 'test'}}
                    ]
                }];

                ctxt.set({
                    module: moduleName,
                    layout: 'filter'
                });
                ctxt.prepare();

                sinon.stub(filtersBeanPrototype, 'getModuleQuickSearchMeta')
                    .returns({fieldNames: ['name']});

                var builtDef = layout.buildFilterDef(odef, searchTerm, ctxt);
                expect(builtDef).toEqual(result);
            });

            it('should be able to append filter defs on filter build', function() {
                var searchTerm = 'test';
                var ctxt = app.context.getContext();
                var odef = {};
                var result = [{
                    '$or': [
                        {'name': {'$starts': 'test'}},
                        {'last_name': {'$starts': 'test'}}
                    ]
                }];

                ctxt.set({
                    module: moduleName,
                    layout: 'filter'
                });
                ctxt.prepare();

                sinon.stub(filtersBeanPrototype, 'getModuleQuickSearchMeta')
                    .returns({fieldNames: ['name', 'last_name']});

                var builtDef = layout.buildFilterDef(odef, searchTerm, ctxt);
                expect(builtDef).toEqual(result);
            });
        });

        describe('initializeFilterState', function() {
            var lastStateFilter, relatedModule;
            var stubCache, nextCallStub;

            beforeEach(function() {
                lastStateFilter = undefined;
                stubCache = sinon.stub(app.user.lastState, 'get').callsFake(function() {
                    return lastStateFilter;
                });
                nextCallStub = sinon.stub(layout, 'getFilters');

                layout.filters = app.data.createBeanCollection('Filters');
                layout.filters.setModuleName(moduleName);
                layout.filters.load();

                // Add the test filter to the filter collection
                layout.filters.collection.add(new Backbone.Model({'id': 'testFilter'}));

                sinon.stub(app.data, 'getRelatedModule').callsFake(function() {
                    return relatedModule;
                });
            });

            using('different params (layoutType, module, link, filter)', [
                {
                    params: [moduleName],
                    layoutType: 'record',
                    showingActivities: false,
                    expected: [moduleName, 'all_records']
                },
                {
                    params: [moduleName],
                    lastStateFilter: 'testFilter',
                    layoutType: 'record',
                    showingActivities: false,
                    expected: [moduleName, 'testFilter']
                },
                {
                    params: [moduleName, null, 'anotherFilter'],
                    lastStateFilter: 'testFilter',
                    layoutType: 'record',
                    showingActivities: false,
                    expected: [moduleName, 'anotherFilter']
                },
                {
                    params: [moduleName, 'contacts'],
                    lastStateFilter: 'testFilter',
                    relatedModule: 'Contacts',
                    layoutType: 'record',
                    showingActivities: false,
                    expected: ['Contacts', 'testFilter']
                },
                {
                    params: [moduleName],
                    lastStateFilter: 'testFilter',
                    layoutType: 'record',
                    showingActivities: true,
                    expected: ['Activities', 'testFilter']
                },
                {
                    params: [moduleName],
                    lastStateFilter: undefined,
                    layoutType: 'record',
                    showingActivities: true,
                    expected: ['Activities', undefined]
                }
            ], function(option) {
                it('should retrieve the module to filter and the filter to select', function() {
                    option.params = option.params || [];

                    layout.layoutType = option.layoutType;
                    layout.showingActivities = option.showingActivities;

                    lastStateFilter = option.lastStateFilter;
                    relatedModule = option.relatedModule;

                    layout.initializeFilterState(option.params[0], option.params[1], option.params[2]);
                    expect(nextCallStub.lastCall.args).toEqual(option.expected);
                });
            });
        });

        it('should get filters from the server', function() {
            var modName = 'TestModule', defaultName = 'testDefault';
            layout.filters = app.data.createBeanCollection('Filters');
            layout.filters.setModuleName(moduleName);
            layout.filters.load();
            sinon.stub(layout, 'selectFilter');
            layout.getFilters(modName, defaultName);
            expect(layout.selectFilter.getCall(0).args).toEqual([defaultName]);
        });

        describe('selectFilter', function() {
            var stubs;

            beforeEach(function() {
                layout.filters = app.data.createBeanCollection('Filters');
                layout.filters.setModuleName(moduleName);
                layout.filters.load();
                layout.filters.collection.add([
                    {
                        id: 'favorites',
                        filter_definition: [
                            {'$favorites': ''}
                        ]
                    },
                    {
                        id: 'owner',
                        filter_definition: [
                            {'$owner': ''}
                        ]
                    },
                    {
                        id: 'my_filter',
                        filter_definition: [
                            {'name': 'Test'}
                        ]
                    }
                ]);
                layout.filters.collection.defaultFilterFromMeta = 'owner';
                stubs = {
                    setLastFilter: sinon.stub(layout, 'setLastFilter'),
                    trigger: sinon.stub(layout, 'trigger')
                };
            });
            using('different filters', [
                {
                    asked: undefined,
                    selected: 'owner'
                },
                {
                    asked: 'not_exists',
                    selected: 'owner'
                },
                {
                    asked: 'my_filter',
                    selected: 'my_filter'
                }
            ], function(value) {
                it('should retrieve the filter or fallback to default on failure', function() {
                    layout.selectFilter(value.asked);
                    expect(stubs.trigger).toHaveBeenCalledWith('filter:render:filter');
                    expect(stubs.trigger).toHaveBeenCalledWith('filter:select:filter', value.selected);
                });
            });
        });
        describe('canCreateFilter', function() {
            var hasAccess, metadata;
            beforeEach(function() {
                var ctxt = new Backbone.Model({collection: {}, module: moduleName});
                sinon.stub(layout, 'getRelevantContextList').callsFake(function() {
                    return [ctxt];
                });
                sinon.stub(app.acl, 'hasAccess').callsFake(function() {
                    return hasAccess;
                });
                sinon.stub(app.metadata, 'getModule').callsFake(function() {
                    return metadata;
                });
            });
            it('should return true because user has access and it is enabled in the metadata', function() {
                hasAccess = true;
                metadata = {
                    'filters': {
                        'f1': {
                            'meta': {
                                'create': true
                            }
                        }
                    }
                };
                expect(layout.canCreateFilter()).toBeTruthy();
            });
            it('should return false because it is set to false in the metadata', function() {
                hasAccess = true;
                metadata = {
                    'filters': {
                        'f1': {
                            'meta': {
                                'create': false
                            }
                        }
                    }
                };
                expect(layout.canCreateFilter()).toBeFalsy();
            });
            it('should return false because user has no access', function() {
                hasAccess = false;
                metadata = {
                    'filters': {
                        'f1': {
                            'meta': {
                                'create': true
                            }
                        }
                    }
                };
                expect(layout.canCreateFilter()).toBeFalsy();
            });
        });

        //See SP-1820. We should initialize filter state if and only if all the filter components are already rendered.
        it('should not init filter state on render', function() {
            var initStub = sinon.stub(layout, 'initializeFilterState');
            layout._render();
            expect(initStub).not.toHaveBeenCalled();
        });

        it('should clear filters on unbind', function() {
            layout.filters = app.data.createBeanCollection('Filters');
            var offSpy = sinon.spy(layout.filters, 'dispose');

            layout.unbind();

            expect(layout.filters).toEqual(null);
            expect(offSpy).toHaveBeenCalled();
        });

        describe('last selected filter', function() {
            var expectedKey = 'Accounts:filter:last-TestModule-TestLayout',
                filterModule = 'TestModule',
                layoutName = 'TestLayout',
                stubCache;

            beforeEach(function() {
                layout.context.set('filterOptions', {});
            });

            it('should save filter id into cache', function() {
                var expectedValue = 'tvalue';
                stubCache = sinon.stub(app.user.lastState, 'set');
                layout.setLastFilter(filterModule, layoutName, 'tvalue');
                expect(stubCache).toHaveBeenCalled();
                expect(stubCache.getCall(0).args[0]).toEqual(expectedKey);
                expect(stubCache.getCall(0).args[1]).toEqual(expectedValue);
                expect(layout.context.get('currentFilterId')).toEqual('tvalue');
            });
            it('should not save filter id into cache if stickiness is false', function() {
                stubCache = sinon.stub(app.user.lastState, 'set');
                layout.context.get('filterOptions').stickiness = false;
                layout.setLastFilter(filterModule, layoutName, 'tvalue');
                expect(stubCache).not.toHaveBeenCalled();
                expect(layout.context.get('currentFilterId')).toEqual('tvalue');
            });
            it('should get filter id from cache', function() {
                stubCache = sinon.stub(app.user.lastState, 'get');
                var value = layout.getLastFilter(filterModule, layoutName);
                expect(stubCache).toHaveBeenCalled();
                expect(stubCache.getCall(0).args[0]).toEqual(expectedKey);
                expect(layout.context.get('currentFilterId')).toEqual(value);
            });
            it('should not get filter id from cache if stickiness is false', function() {
                stubCache = sinon.stub(app.user.lastState, 'get');
                layout.context.get('filterOptions').stickiness = false;
                layout.getLastFilter(filterModule, layoutName, 'tvalue');
                expect(stubCache).not.toHaveBeenCalled();
            });
            it('should clear filter id from cache', function() {
                stubCache = sinon.stub(app.user.lastState, 'remove');
                layout.clearLastFilter(filterModule, layoutName);
                expect(stubCache).toHaveBeenCalled();
                expect(stubCache.getCall(0).args[0]).toEqual(expectedKey);
            });
            it('should not clear filter id from cache if stickiness is false', function() {
                stubCache = sinon.stub(app.user.lastState, 'remove');
                layout.context.get('filterOptions').stickiness = false;
                layout.clearLastFilter(filterModule, layoutName);
                expect(stubCache).not.toHaveBeenCalled();
            });
            it('should create the correct last filter id', function() {
                layoutName = 'records';
                var key = layout.getLastFilterKey(filterModule, layoutName);
                expect(key).toEqual('Accounts:filter:last-TestModule-records');

                layoutName = 'pipeline-records';
                key = layout.getLastFilterKey(filterModule, layoutName);
                expect(key).toEqual('Accounts:filter:last-TestModule-records');

                layoutName = 'TestLayout';
                key = layout.getLastFilterKey(filterModule, layoutName);
                expect(key).toEqual('Accounts:filter:last-TestModule-TestLayout');
            });
        });

        describe('last edit state filter', function() {
            var expectedKey = 'Accounts:filter:edit-TestModule-TestLayout';
            var stubCache;

            beforeEach(function() {
                layout.layout.currentModule = 'TestModule';
                layout.layoutType = 'TestLayout';
                layout.context.set('filterOptions', {});
            });

            it('should save filter definition into cache', function() {
                var expectedValue = {
                    'filter_definition': [{'account_type': {'$in': ['Competitor']}}],
                    'name': 'Test Name'
                };
                stubCache = sinon.stub(app.user.lastState, 'set');
                var filter = {
                    'filter_definition': [{'account_type': {'$in': ['Competitor']}}],
                    'name': 'Test Name'
                };
                layout.saveFilterEditState(filter);
                expect(stubCache).toHaveBeenCalled();
                expect(stubCache.getCall(0).args[0]).toEqual(expectedKey);
                expect(stubCache.getCall(0).args[1]).toEqual(expectedValue);
            });
            it('should not save filter definition into cache if stickiness is false', function() {
                stubCache = sinon.stub(app.user.lastState, 'set');
                var filter = {
                    'filter_definition': [{'account_type': {'$in': ['Competitor']}}],
                    'name': 'Test Name'
                };
                layout.context.get('filterOptions').stickiness = false;
                layout.saveFilterEditState(filter);
                expect(stubCache).not.toHaveBeenCalled();
            });
            it('should get filter definition from cache', function() {
                stubCache = sinon.stub(app.user.lastState, 'get');
                layout.retrieveFilterEditState();
                expect(stubCache).toHaveBeenCalled();
                expect(stubCache.getCall(0).args[0]).toEqual(expectedKey);
            });
            it('should not get filter definition from cache if stickiness is false', function() {
                stubCache = sinon.stub(app.user.lastState, 'get');
                layout.context.get('filterOptions').stickiness = false;
                layout.retrieveFilterEditState();
                expect(stubCache).not.toHaveBeenCalled();
            });
            it('should clear filter definition from cache', function() {
                stubCache = sinon.stub(app.user.lastState, 'remove');
                layout.clearFilterEditState();
                expect(stubCache).toHaveBeenCalled();
                expect(stubCache.getCall(0).args[0]).toEqual(expectedKey);
            });
            it('should not clear filter definition from cache if stickiness is false', function() {
                stubCache = sinon.stub(app.user.lastState, 'remove');
                layout.context.get('filterOptions').stickiness = false;
                layout.clearFilterEditState();
                expect(stubCache).not.toHaveBeenCalled();
            });
        });

        describe('filter:create:close', function() {
            var clearFilterEditStateStub, parentLayoutTriggerStub, clearLastFilterStub;
            beforeEach(function() {
                clearFilterEditStateStub = sinon.stub(layout, 'clearFilterEditState');
                parentLayoutTriggerStub = sinon.stub(layout.layout, 'trigger');
                clearLastFilterStub = sinon.stub(layout, 'clearLastFilter');
            });
            it('should reset "editingFilter" on context', function() {
                layout.context.editingFilter = 'filter_id';
                layout.trigger('filter:create:close');
                expect(layout.context.editingFilter).toBeNull();
            });
            it('should trigger "filter:create:close" on parent layout', function() {
                layout.trigger('filter:create:close');
                expect(clearLastFilterStub).not.toHaveBeenCalled();
                expect(parentLayoutTriggerStub).toHaveBeenCalled();
                expect(parentLayoutTriggerStub).not.toHaveBeenCalledWith('filter:reinitialize');
                expect(parentLayoutTriggerStub).toHaveBeenCalledWith('filter:create:close');
            });
            it('should clear last filter and call "filter:reinitialize" when canceling filter creation', function() {
                sinon.stub(layout, 'getLastFilter').callsFake(function() { return 'create'; });
                layout.trigger('filter:create:close');
                expect(clearLastFilterStub).toHaveBeenCalled();
                expect(parentLayoutTriggerStub).toHaveBeenCalled();
                expect(parentLayoutTriggerStub).toHaveBeenCalledWith('filter:reinitialize');
                expect(parentLayoutTriggerStub).toHaveBeenCalledWith('filter:create:close');
            });
        });
    });
});
