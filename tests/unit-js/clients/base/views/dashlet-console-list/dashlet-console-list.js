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
describe('Base.View.DashletConsoleList', function() {
    var app;
    var view;
    var layout;
    var sampleFieldMetadata = [{name: 'foo'}, {name: 'bar'}];
    var sampleColumns = {foo: 'foo', bar: 'bar'};
    var moduleName = 'Cases';
    var viewName = 'dashlet-console-list';
    var layoutName = 'record';
    var options;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.testMetadata.addViewDefinition(
            viewName,
            {
                'panels': [
                    {
                        fields: []
                    }
                ]
            },
            moduleName
        );
        SugarTest.testMetadata.set();
        app.data.declareModels();
        SugarTest.loadPlugin('Dashlet');
        app.user.set('module_list', [moduleName]);

        var context = app.context.getContext();
        context.set({
            module: moduleName,
            layout: layoutName
        });
        context.parent = new Backbone.Model();
        context.parent.set('module', moduleName);
        context.prepare();

        layout = app.view.createLayout({
            name: layoutName,
            context: context
        });

        sinon.stub(app.data, 'createBeanCollection')
            .withArgs('Filters').returns({
            setModuleName: sinon.stub(),
            load: sinon.stub()
        });

        view = SugarTest.createView('base', moduleName, viewName, null, context, null, layout);
        options = view.options;
        sinon.stub(view, '_super').callsFake(function() {});
        view._availableModules = {Cases: 'Cases', Contacts: 'Contacts'};
        view.moduleIsAvailable = true;
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        delete app.plugins.plugins.view.Dashlet;
    });

    describe('initialize', function() {
        beforeEach(function() {
            sinon.stub(app.template, 'get').callsFake(function() {
                return 'testTemplate';
            });
            sinon.stub(view.collection, 'on').callsFake(function() {});
            view.initialize(options);
        });

        it('should initialize properties for the dashlet', function() {
            expect(view.currentFilterId).toEqual({});
            expect(view._super).toHaveBeenCalledWith('initialize', [options]);
            expect(view._select2formatSelectionTemplate).toEqual('testTemplate');
            expect(view._select2formatResultTemplate).toEqual('testTemplate');
        });

        it('should attach data:sync:complete listener on collection', function() {
            expect(view.collection.on).toHaveBeenCalledWith('data:sync:complete');
        });
    });

    describe('updatePlaceholder', function() {
        beforeEach(function() {
            SugarTest.declareData('base', 'Filters');
            filtersBeanPrototype = app.data.getBeanClass('Filters').prototype;
        });
        it('should update placeholder with field labels on filter', function() {
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    fields: {
                        first_name: {
                            vname: 'lbl_first_name'
                        },
                        last_name: {
                            vname: 'lbl_last_name'
                        }
                    }
                };
            });
            sinon.stub(filtersBeanPrototype, 'getModuleQuickSearchMeta').callsFake(function() {
                return {
                    fieldNames: ['first_name', 'last_name']
                };
            });
            view.$el.html('<input type="text" class="search-name">');
            view.updatePlaceholder(view.$el);
            expect(view.$el.attr('placeholder')).toEqual('LBL_SEARCH_BY lbl_first_name, lbl_last_name...');
        });
    });

    describe('handleSelect', function() {
        var evt;
        beforeEach(function() {
            sinon.stub(view, 'setSelection');
        });
        afterEach(function() {
            evt = null;
        });

        it('should return if the evt is keydown and the key is not enter or space', function() {
            evt = {
                preventDefault: $.noop,
                currentTarget: 'button[name=testBtn]',
                type: 'keydown',
                keyCode: 'test',
                stopPropagation: sinon.stub()
            };

            view.handleSelect(evt);
            expect(view.setSelection).not.toHaveBeenCalled();
            expect(evt.stopPropagation).not.toHaveBeenCalled();
        });

        using('various events', [{
            preventDefault: $.noop,
            currentTarget: 'button[name=testBtn]',
            type: 'keyup',
            keyCode: $.ui.keyCode.ENTER,
            stopPropagation: sinon.stub(),
            val: 'test'
        },{
            preventDefault: $.noop,
            currentTarget: 'button[name=testBtn]',
            type: 'click',
            stopPropagation: sinon.stub(),
            val: 'test'
        }], function(event) {
            it('should call stopPropogation and setSelection methods on keyup and click events', function() {
                view.handleSelect(event);
                expect(view.setSelection).toHaveBeenCalledWith('test');
                expect(event.stopPropagation).toHaveBeenCalled();
            });
        });
    });

    describe('setSelection', function() {
        var id;
        beforeEach(function() {
            id = 'testId';
            sinon.stub(view, 'buildFilterDefinition')
                .withArgs([], 'testSearch').returns([])
                .withArgs([{test: 'testDef'}], 'testSearch')
                .returns([{test: 'test'}]);
            sinon.stub(view, '_displayDashlet');

            view.currentSearch = 'testSearch';
        });
        afterEach(function() {
            id = null;
        });

        it('should set filterDef to empty if id is not found', function() {
            view.layout.filters = {
                collection: {
                    get: function() {
                        return undefined;
                    }
                }
            };
            view.setSelection(id);

            expect(view.buildFilterDefinition).toHaveBeenCalledWith([], 'testSearch');
            expect(view.currentFilterDef).toEqual([]);
            expect(view._displayDashlet).toHaveBeenCalledWith([]);
        });

        it('should set filterDef correctly if id is found', function() {
            view.layout.filters = {
                collection: {
                    get: function() {
                        return {
                            get: function() {
                                return [{test: 'testDef'}];
                            }
                        };
                    }
                }
            };
            view.setSelection(id);

            expect(view.buildFilterDefinition).toHaveBeenCalledWith([{test: 'testDef'}], 'testSearch');
            expect(view.currentFilterDef).toEqual([{test: 'test'}]);
            expect(view._displayDashlet).toHaveBeenCalledWith([{test: 'test'}]);
        });
    });

    describe('handleClearFilter', function() {
        var evt;
        beforeEach(function() {
            sinon.stub(view, 'setSelection');
            view.currentFilterId = {
                'Cases': 'test'
            };
        });
        afterEach(function() {
            evt = null;
        });
        it('should return if the evt is keydown and the key is not enter or space', function() {
            evt = {
                preventDefault: $.noop,
                currentTarget: 'button[name=testBtn]',
                type: 'keydown',
                keyCode: 'test',
                stopPropagation: sinon.stub()
            };

            view.handleClearFilter(evt);
            expect(view.setSelection).not.toHaveBeenCalled();
            expect(evt.stopPropagation).not.toHaveBeenCalled();
        });

        using('various events and filters', [{
            evt: {
                preventDefault: $.noop,
                currentTarget: 'button[name=testBtn]',
                type: 'click',
                stopPropagation: sinon.stub(),
                val: 'test'
            },
            filters: {
                collection: {
                    defaultFilterFromMeta: 'test'
                }
            },
            expected: 'all_records'
        },{
            evt: {
                preventDefault: $.noop,
                currentTarget: 'button[name=testBtn]',
                type: 'click',
                stopPropagation: sinon.stub(),
                val: 'test'
            },
            filters: {
                collection: {
                    defaultFilterFromMeta: 'new_default'
                }
            },
            expected: 'new_default'
        }], function(value) {
            it('should call stopPropogation and setSelection with correct filters on click', function() {
                view.layout.filters = value.filters;
                view.handleClearFilter(value.evt);

                expect(view.setSelection).toHaveBeenCalledWith(value.expected);
                expect(value.evt.stopPropagation).toHaveBeenCalled();
            });
        });
    });

    describe('refreshClicked', function() {
        it('should call _displayDashlet with currentFilterDef', function() {
            view.currentFilterDef = [{'case_number': {'$starts': 'test'}}];
            sinon.stub(view, '_displayDashlet');
            view.refreshClicked();

            expect(view._displayDashlet).toHaveBeenCalledWith([{'case_number': {'$starts': 'test'}}]);
        });
    });

    describe('_renderDropdown', function() {
        var data;
        var stub;
        beforeEach(function() {
            data = [{data: 'myData'}];
            stub = sinon.stub();
            view.$el.html('<input type="hidden" class="select2 search-filter console-list-dropdown"">');
            view.filterNode = sinon.stub(view, '$').callsFake(function() {
                return {
                    select2: function() {
                        return {
                            off: function() {
                                return {
                                    on: stub
                                };
                            }
                        };
                    },
                };
            });

            sinon.stub(view, 'fixChoiceFilter');
            sinon.stub(view, 'fixDropdownCss');

            view._renderDropdown(data);
        });

        afterEach(function() {
            data = null;
            stub = null;
        });

        it('should call this.$ with \'input.console-list-dropdown\'', function() {
            expect(view.$).toHaveBeenCalledWith('input.console-list-dropdown');
        });

        it('should call select2 method', function() {
            expect(view.$('input.console-list-dropdown').select2().off().on).toHaveBeenCalledWith('change');
        });

        it('should call fixChoiceFilter and fixDropdownCss methods', function() {
            expect(view.fixChoiceFilter).toHaveBeenCalledWith([{data: 'myData'}]);
            expect(view.fixDropdownCss).toHaveBeenCalled();
        });
    });

    describe('fixChoiceFilter', function() {
        var data;
        beforeEach(function() {
            view.currentFilterId = {
                'Cases': 'testId'
            };
            sinon.spy(view, '$');
        });
        afterEach(function() {
            data = null;
        });

        it('should call this.$ with \'.choice-filter-label\'', function() {
            data = {};
            view.$el.html('<span class="test-class" tabindex="0"></span>');
            view.fixChoiceFilter(data);

            expect(view.$).toHaveBeenCalledWith('.choice-filter-label');
        });

        it('should set choice-label if ids match', function() {
            data = [
                {
                    id: 'testId',
                    text: 'testLabel'
                }
            ];
            view.$el.html('<span class="choice-filter-label" tabindex="0"></span>');
            view.fixChoiceFilter(data);

            expect(view.$('.choice-filter-label')[0].innerHTML).toEqual('testLabel');
        });
    });

    describe('fixDropdownCss', function() {
        beforeEach(function() {
            sinon.spy(view, '$');
        });

        it('should remove arrowElem if present', function() {
            view.$el.html(
                '<span class="select2-chosen" id="select2-chosen-10">Filter' +
                    '<i class="sicon sicon-chevron-down"></i>' +
                '</span>' +
                '<i class=".select2-arrow"></i>');
            view.fixDropdownCss();

            expect(view.$('.select2-arrow')[0]).not.toBeDefined();
        });

        using('various htmls', [
            '<span class="select2-chosen" id="select2-chosen-10">Filter</span>',
            '<span class="select2-chosen" id="select2-chosen-10">Filter<i class="sicon-chevron-down"></i></span>'
        ], function(html) {
            it('should call append caret if not already present', function() {
                view.$el.html(html);
                view.fixDropdownCss();

                expect(view.$('.sicon.sicon-chevron-down')[0]).toBeDefined();
            });
        });

        it('should not append caret element if filter is missing', function() {
            view.$el.html(
                '<span class="select2-test" id="select2-chosen-10">Filter' +
                '</span>');
            view.fixDropdownCss();

            expect(view.$('.sicon.sicon-chevron-down')[0]).not.toBeDefined();
        });
    });

    describe('formatSelection', function() {
        var jQueryStubs;
        var item;
        beforeEach(function() {
            jQueryStubs = {};
            jQueryStubs.attr = sinon.stub().returns(jQueryStubs);
            jQueryStubs.html = sinon.stub().returns(jQueryStubs);
            jQueryStubs.toggle = sinon.stub().returns(jQueryStubs);
            jQueryStubs.toggleClass = sinon.stub().returns(jQueryStubs);

            sinon.stub(view, '$').callsFake(function() {
                return jQueryStubs;
            });

            //Template replacement
            view._select2formatSelectionTemplate = function(val) {
                return val;
            };
        });
        it('should format the filter dropdown', function() {
            item = {id: 'test', text: 'TEST'};
            var expected = {label: app.lang.get('LBL_FILTER'), enabled: view.filterDropdownEnabled};

            expect(view.formatSelection(item)).toEqual(expected);
            expect(view.$('.choice-filter-close').toggle).toHaveBeenCalledWith(true);
            expect(view.$('.choice-filter-close').toggleClass).toHaveBeenCalledWith('with-close', true);
        });
    });

    describe('formatResult', function() {
        it('should formatResult for selected filter', function() {
            view.currentFilterId = {
                'Cases': 'currentFilterId'
            };
            //Template replacement
            view._select2formatResultTemplate = function(val) {return val;};

            expect(view.formatResult({id: 'test', text: 'TEST'}))
                .toEqual({id: 'test', text: 'TEST', icon: undefined});

            expect(view.formatResult({id: 'currentFilterId', text: 'Last selected filter'}))
                .toEqual({id: 'currentFilterId', text: 'Last selected filter', icon: 'sicon-check'});
        });
    });

    describe('formatResultCssClass', function() {
        it('should return \'select2-result-border-top\' only for firstNonUserFilter', function() {
            expect(view.formatResultCssClass({id: 'test', text: 'TEST'}))
                .toBeUndefined();

            expect(view.formatResultCssClass({id: 'test', text: 'TEST', firstNonUserFilter: false}))
                .toBeUndefined();

            expect(view.formatResultCssClass({id: 'test', text: 'TEST', firstNonUserFilter: true}))
                .toEqual('select2-result-border-top');
        });
    });

    describe('throttledSearch', function() {
        it('should call applyQuickSearch method', function() {
            var event = {
                type: 'click'
            };
            sinon.stub(view, 'applyQuickSearch');
            view.throttledSearch(event);

            expect(view.applyQuickSearch).toHaveBeenCalledWith(true, 'click');
        });
    });

    describe('clearQuickSearch', function() {
        it('should call applyQuickSearch method', function() {
            var event = {
                type: 'click'
            };
            sinon.spy(view, '$');
            view.$el.html('<input type="text" class="search-name" value="test">');
            sinon.stub(view, 'applyQuickSearch');
            view.clearQuickSearch(event);

            expect(view.applyQuickSearch).toHaveBeenCalledWith(true, 'click');
            expect(view.$('input.search-name').val()).toEqual('');
        });
    });

    describe('applyQuickSearch', function() {
        var force;
        beforeEach(function() {
            sinon.stub(view, 'buildFilterDefinition').callsFake(function() {
                return [{
                    test: 'newDef'
                }];
            });
            sinon.stub(view, '_displayDashlet');

            view.collection = {
                once: sinon.stub(),
                off: sinon.stub()
            };

            view.currentFilterDef = [{
                test: 'testDef'
            }];
            sinon.stub(jQuery.fn, 'val').callsFake(function() {return 'newSearch';});
        });

        it('should do nothing if newSearch is same as view.currentSearch and force is undefined', function() {
            force = undefined;
            view.currentSearch = 'newSearch';
            view.applyQuickSearch(force, 'click');

            expect(view.buildFilterDefinition).not.toHaveBeenCalled();
            expect(view._displayDashlet).not.toHaveBeenCalled();
            expect(view.checkFooterVisibility).not.toHaveBeenCalled();
        });

        it('should build filter def and call _displayDashlet if force is true', function() {
            force = true;
            view.currentSearch = 'newSearch';
            view.applyQuickSearch(force, 'click');

            expect(view.buildFilterDefinition).toHaveBeenCalledWith([{test: 'testDef'}], 'newSearch');
            expect(view._displayDashlet).toHaveBeenCalled([{test: 'newDef'}]);
            expect(view.collection.once).toHaveBeenCalledWith('data:sync:complete');
        });

        it('should build filter def and call _displayDashlet if searches are differnt', function() {
            force = false;
            view.currentSearch = '';
            view.applyQuickSearch(force, 'click');

            expect(view.buildFilterDefinition).toHaveBeenCalledWith([{test: 'testDef'}], 'newSearch');
            expect(view._displayDashlet).toHaveBeenCalled([{test: 'newDef'}]);
            expect(view.collection.once).toHaveBeenCalledWith('data:sync:complete');
        });
    });

    describe('toggleClearQuickSearchIcon', function() {
        beforeEach(function() {
            sinon.spy(view, '$');
        });

        it('should remove .sicon-close.add-on if addIt is false', function() {
            view.$el.html('<div class="filter-view search">' +
                    '<i class="sicon sicon-remove add-on"></i>' +
                '</div>');

            view.toggleClearQuickSearchIcon(false);

            expect(view.$('.sicon-remove.add-on')[0]).not.toBeDefined();
        });

        it('should add .sicon-close.add-on if addIt is true and element is not present', function() {
            view.$el.html('<div class="filter-view search"></div>');
            view.toggleClearQuickSearchIcon(true);

            expect(view.$('.sicon-close.add-on')[0]).toBeDefined();
        });
    });

    describe('buildFilterDefinition', function() {
        var getModuleStub;
        var filtersBeanPrototype;
        var filter1;
        var filter2;
        var filter3;
        var fakeModuleMeta;

        beforeEach(function() {
            filter1 = {'name': {'$starts': 'A'}};
            filter2 = {'name_c': {'$starts': 'B'}};
            filter3 = {'$favorite': ''};
            fakeModuleMeta = {
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
            getModuleStub = sinon.stub(app.metadata, 'getModule').returns(fakeModuleMeta);
            filtersBeanPrototype = app.data.getBeanClass('Filters').prototype;
        });

        afterEach(function() {
            filtersBeanPrototype = null;
            filter1 = null;
            filter2 = null;
            filter3 = null;
            fakeModuleMeta = null;
        });

        it('should return empty string if view.layout.filters is not defined', function() {
            view.layout.filters = undefined;

            expect(view.buildFilterDefinition([filter1], '')).toEqual([]);
        });

        using('various filterDefs and search terms', [{
            viewFilter: [filter1],
            searchTerm: 'abc',
            testFilterDef: [filter1, filter2, filter3],
            searchTermFilter: [{'name': {'$starts': 'abc'}}],
            filteredFilter: [filter1, filter3],
            expected: [{'$and': [filter1, filter3, {'name': {'$starts': 'abc'}}]}]
        },{
            viewFilter: [filter1],
            searchTerm: 'test',
            testFilterDef: [],
            searchTermFilter: [{'name': {'$starts': 'test'}}],
            filteredFilter: [],
            expected: [{'name': {'$starts': 'test'}}]
        },{
            viewFilter: [filter1],
            searchTerm: 'test',
            testFilterDef: {'test': {'$test': 'test'}},
            searchTermFilter: [{'name': {'$starts': 'test'}}],
            filteredFilter: [{'test': {'$test': 'test'}}],
            expected: [{
                '$and': [
                    {'test': {'$test': 'test'}},
                    {'name': {'$starts': 'test'}}
                ]
            }]
        },{
            viewFilter: [],
            searchTerm: 'test',
            testFilterDef: [],
            searchTermFilter: [{
                '$or': [
                    {'name': {'$starts': 'test'}},
                    {'last_name': {'$starts': 'test'}}
                ]
            }],
            filteredFilter: [],
            expected: [{
                '$or': [
                    {'name': {'$starts': 'test'}},
                    {'last_name': {'$starts': 'test'}}
                ]
            }]
        }], function(value) {
            it('should build a filterDef correctly depending on a search term', function() {
                view.layout.filters = [value.viewFilter];
                filtersBeanPrototype.buildSearchTermFilter = function() {return value.searchTermFilter;};
                sinon.stub(view, 'filterSelectedFilter').callsFake(function() {
                    return value.filteredFilter;
                });
                var builtDef = view.buildFilterDefinition(value.testFilterDef, value.searchTerm);

                expect(builtDef).toEqual(value.expected);
            });
        });
    });

    describe('filterSelectedFilter', function() {
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
        });

        it('should return the filtered filters', function() {
            expect(view.filterSelectedFilter([filter1])).toEqual([filter1]);
            expect(view.filterSelectedFilter([filter2])).toEqual([]);
            expect(view.filterSelectedFilter([filter3])).toEqual([filter3]);
        });
    });

    describe('rowClicked', function() {
        var evt;
        var model;
        var stub;
        beforeEach(function() {
            model = new Backbone.Model('Cases');
            model.id = 'testId';
            view.collection = {
                models: [model],
                off: function() {}
            };
            view.tabId = {'Cases': 2};
            stub = sinon.stub();

            sinon.stub(view, 'closestComponent').callsFake(function() {
                return {
                    setModel: stub,
                    switchTab: stub,
                    moduleTabIndex: {
                        Contacts: 1,
                        Cases: 2,
                    }
                };
            });
        });

        afterEach(function() {
            evt = null;
            model = null;
        });

        it('should not switch tab when rowId is not found', function() {
            evt = {
                preventDefault: $.noop,
                currentTarget: {
                    dataset: {
                        id: 'testRowId'
                    }
                }
            };
            view.rowClicked(evt);

            expect(view.closestComponent('omnichannel-dashboard').switchTab).not.toHaveBeenCalled();
        });

        it('should call setModel and switchTab when rowId is found', function() {
            evt = {
                preventDefault: $.noop,
                currentTarget: {
                    dataset: {
                        id: 'testId'
                    }
                }
            };
            view.rowClicked(evt);

            expect(view.closestComponent('omnichannel-dashboard').setModel).toHaveBeenCalledWith(2, model);
            expect(view.closestComponent('omnichannel-dashboard').switchTab).toHaveBeenCalledWith(2);
        });
    });

    describe('triggerDashletSetup', function() {
        var data;
        it('should call _displayDashlet method with correct filterDef', function() {
            data = {
                filter_definition: 'testDef'
            };
            sinon.stub(view, '_displayDashlet');

            view.triggerDashletSetup(data);

            expect(view.filterIsAccessible).toBeTruthy();
            expect(view.currentFilterDef).toEqual('testDef');
            expect(view._displayDashlet).toHaveBeenCalledWith('testDef');
        });
    });

    describe('initDashlet', function() {
        var stubInitializeSettings;
        var stubConfigureDashlet;
        var stubDisplayDashlet;

        beforeEach(function() {
            view.meta = {};
            view.settings._events = {};
            view._events = {};
            view.layout.context._events = {};
            view.layout._before = {};
            stubInitializeSettings = sinon.stub(view, '_initializeSettings');
            stubConfigureDashlet = sinon.stub(view, '_configureDashlet');
            stubDisplayDashlet = sinon.stub(view, '_displayDashlet');
        });

        it('should call _configureDashlet when in config mode', function() {
            view.meta.config = true;
            view.initDashlet('config');

            expect(stubInitializeSettings).toHaveBeenCalledOnce();
            expect(stubConfigureDashlet).toHaveBeenCalledOnce();
            expect(stubDisplayDashlet).not.toHaveBeenCalled();
            expect(view.settings._events['change:module']).toBeDefined();
            expect(view.layout.context._events['filter:add']).toBeDefined();
            expect(view.layout._before['dashletconfig:save']).toBeDefined();
        });

        it('should call _displayDashlet when in preview mode', function() {
            view.meta.preview = true;
            view.initDashlet('preview');

            expect(stubInitializeSettings).toHaveBeenCalledOnce();
            expect(stubConfigureDashlet).not.toHaveBeenCalled();
            expect(stubDisplayDashlet).toHaveBeenCalledOnce();
            expect(view.settings._events['change:module']).not.toBeDefined();
            expect(view.layout.context._events['filter:add']).not.toBeDefined();
            expect(view.layout._before['dashletconfig:save']).not.toBeDefined();
        });

        it('should call _displayDashlet when in view mode with no filter_id', function() {
            var getStub = sinon.stub(view.settings, 'get').callsFake(function(param) {
                if (param === 'filter_id') {
                    return null;
                } else {
                    return this.attributes[param];
                }
            });

            view.initDashlet('view');

            expect(stubInitializeSettings).toHaveBeenCalledOnce();
            expect(stubConfigureDashlet).not.toHaveBeenCalled();
            expect(stubDisplayDashlet).toHaveBeenCalledOnce();
            expect(view.settings._events['change:module']).not.toBeDefined();
            expect(view.layout.context._events['filter:add']).not.toBeDefined();
            expect(view.layout._before['dashletconfig:save']).not.toBeDefined();
        });

        it('should call _displayDashlet when in view mode with a filter_id found', function() {
            SugarTest.declareData('base', 'Filters');
            sinon.stub(app.BeanCollection.prototype, 'fetch').callsFake(function(options) {
                options.success();
            });

            var filterTest1 = {
                id: 'testFilterID',
                filter_definition: [
                    {name: 'test1'}
                ]
            };

            view.settings.set('module', moduleName);
            view.settings.set('filter_id', filterTest1.id);

            // Prepare a collection with this filter.
            var filters = app.data.createBeanCollection('Filters');
            filters.setModuleName = sinon.stub();
            filters.load = sinon.stub();
            filters.collection = {
                models: [filterTest1]
            };
            view.initDashlet('view');

            expect(filters.setModuleName).toHaveBeenCalledWith(moduleName);
            expect(filters.load).toHaveBeenCalled();
        });

    });

    describe('updateFilterForModule', function() {
        using('various settings for custom filter', [{
            activeTab: 2,
            module: 'Cases',
            input: [{'name': {'$starts': 'A'}}, {'$favorite': ''}],
            expected: [{'name': {'$starts': 'A'}}, {'$favorite': ''}]
        },{
            activeTab: 1,
            module: 'Test',
            input: [],
            expected: []
        },{
            activeTab: 1,
            module: 'Cases',
            input: [{'name': {'$starts': 'A'}}, {'$favorite': ''}],
            expected: [{'name': {'$starts': 'A'}}, {'$favorite': ''}, {primary_contact_id: {$equals: 'testId'}}]
        },{
            activeTab: 1,
            module: 'Cases',
            input: [],
            expected: [{primary_contact_id: {$equals: 'testId'}}]
        }], function(value) {
            it('should append custom filter only if Contacts is the active tab', function() {
                view.moduleFilterField = {
                    Contacts: 'primary_contact_id'
                };
                view.customFilterModuleList = ['Cases'];
                view.module = value.module;
                stub: sinon.stub(view, 'closestComponent').callsFake(function() {
                    return {
                        getComponent: function() {
                            return {
                                getComponent: function() {
                                    return {
                                        activeTab: value.activeTab
                                    };
                                }
                            };
                        },
                        context: {
                            get: function() {
                                return {
                                    id: 'testId'
                                };
                            }
                        },
                        moduleTabIndex: {
                            Contacts: 1,
                            Cases: 2,
                        }
                    };
                });

                expect(view.updateFilterForModule(value.input)).toEqual(value.expected);
            });
        });
    });

    describe('loadData', function() {
        it('should not call _super loadData if filter is inaccessible', function() {
            view.filterIsAccessible = false;
            view.loadData();

            expect(view._super).not.toHaveBeenCalled();
        });

        it('should call _super loadData if filter is accessible', function() {
            view.filterIsAccessible = true;
            view.loadData();

            expect(view._super).toHaveBeenCalledWith('loadData');
        });
    });

    describe('showMoreRecords', function() {
        it('should call getNextPagination', function() {
            sinon.stub(view, 'getNextPagination');
            view.showMoreRecords();

            expect(view.getNextPagination).toHaveBeenCalled();
        });
    });

    describe('saveDashletFilter', function() {
        var triggerStub;
        var updateDashletStub;

        beforeEach(function() {
            triggerStub = sinon.stub(view.layout.context, 'trigger');
            updateDashletStub = sinon.stub(view, 'updateDashletFilterAndSave');
        });

        it('should trigger a filter:create:save if editing/creating a filter', function() {
            view.layout.context.editingFilter = new Backbone.Model({name: 'test'});
            view.saveDashletFilter();
            expect(triggerStub).toHaveBeenCalledWith('filter:create:save');
        });

        it('should call updateDashletFilterAndSave if saving a predefined filter', function() {
            view.layout.context.set('currentFilterId', 'testID');
            view.saveDashletFilter();
            expect(updateDashletStub).toHaveBeenCalledWith({id: 'testID'});
        });
    });

    describe('updateDashletFilterAndSave', function() {
        it('should be invoked by the filter:add event', function() {
            var updateDashletStub = sinon.stub(view, 'updateDashletFilterAndSave');
            var filterModel = new Backbone.Model();

            view.meta.config = true;
            view.initDashlet('config');
            view.layout.context.trigger('filter:add', filterModel);
            expect(updateDashletStub).toHaveBeenCalledWith(filterModel);
        });

        it('should call app.drawer.close and save the new dashlet model', function() {
            if (!app.drawer) {
                app.drawer = {
                    open: function() {},
                    close: function() {}
                };
            } else if (!app.drawer.close) {
                app.drawer.close = function() {};
            }

            var appEventsStub = sinon.stub(app.events, 'trigger');
            var drawerCloseStub = sinon.stub(app.drawer, 'close');
            var filterModel = new Backbone.Model({id: 'test'});

            view.updateDashletFilterAndSave(filterModel);
            expect(view.settings.get('filter_id')).toEqual(filterModel.get('id'));
            expect(view.dashModel.get('filter_id')).toEqual(filterModel.get('id'));
            expect(drawerCloseStub).toHaveBeenCalled();
            expect(appEventsStub).toHaveBeenCalledWith('dashlet:filter:save');
        });
    });

    describe('_initializeSettings', function() {
        it('should initialize dashlet settings', function() {
            view.settings = {
                get: sinon.stub(),
                set: sinon.stub(),
                off: sinon.stub()
            };
            view._defaultSettings = {
                limit: 5,
                filter_id: 'all_records',
            };
            sinon.stub(view, '_setDefaultModule');
            view._initializeSettings();

            expect(view._setDefaultModule).toHaveBeenCalled();
            expect(view.settings.get).toHaveBeenCalledWith('limit');
            expect(view.settings.get).toHaveBeenCalledWith('filter_id');
            expect(view.settings.get).toHaveBeenCalledWith('label');
            expect(view.settings.set).toHaveBeenCalledWith('limit', 5);
            expect(view.settings.set).toHaveBeenCalledWith('filter_id', 'all_records');
            expect(view.settings.set).toHaveBeenCalledWith('label', 'LBL_MODULE_NAME');
        });
    });

    describe('_setDefaultModule', function() {
        it('should set module in settings if it is present in _availableModules', function() {
            sinon.stub(view, '_getAvailableModules').callsFake(function() {
                return {
                    'Cases': 'Cases',
                    'Contacts': 'Contacts'
                };
            });
            view.settings = {
                get: sinon.stub(),
                set: sinon.stub(),
                off: sinon.stub()
            };
            view._setDefaultModule();

            expect(view.settings.set).toHaveBeenCalledWith('module', 'Cases');
        });

        it('should set first module from _availableModules in settings if in config view', function() {
            view.meta.config = true;
            sinon.stub(view, '_getAvailableModules').callsFake(function() {
                return {
                    'Contacts': 'Contacts'
                };
            });
            sinon.stub(view.context, 'get');
            view.settings = {
                get: sinon.stub(),
                set: sinon.stub(),
                off: sinon.stub()
            };
            view._setDefaultModule();

            expect(view.settings.set).toHaveBeenCalledWith('module', 'Contacts');
        });

        it('should set available module to false if not a config and not present in available', function() {
            view.meta.config = false;
            sinon.stub(view, '_getAvailableModules').callsFake(function() {
                return {
                    'Tasks': 'Tasks'
                };
            });
            view.settings = {
                get: sinon.stub(),
                set: sinon.stub(),
                off: sinon.stub()
            };
            view._setDefaultModule();

            expect(view.moduleIsAvailable).toBeFalsy();
        });
    });

    describe('_updateDisplayColumns', function() {
        it('should set display columns', function() {
            sinon.stub(view, '_getAvailableColumns').callsFake(function() {
                return {
                    'col1': 'col1',
                    'col2': 'col2'
                };
            });
            view.settings = {
                set: sinon.stub(),
                off: sinon.stub()
            };
            view._updateDisplayColumns();

            expect(view.settings.set).toHaveBeenCalledWith('display_columns', ['col1', 'col2']);
        });
    });

    describe('_addFilterComponent', function() {
        it('should be invoked by layout init', function() {
            var _addFilterComponentStub = sinon.stub(view, '_addFilterComponent');
            var initializeSettingsStub = sinon.stub(view, '_initializeSettings');
            var configureDashletStub = sinon.stub(view, '_configureDashlet');
            var displayDashletStub = sinon.stub(view, '_displayDashlet');

            view.meta.config = true;
            view.initDashlet('config');
            view.layout.trigger('init');

            expect(_addFilterComponentStub).toHaveBeenCalled();
        });

        it('should add the dashablelist-filter component', function() {
            var _addComponentsFromDefStub = sinon.stub(view.layout, '_addComponentsFromDef');
            var getComponentStub = sinon.stub(view.layout, 'getComponent');
            var _componentArray = [{
                    layout: 'dashablelist-filter'
                }];

            view._addFilterComponent();

            expect(getComponentStub).toHaveBeenCalledWith('dashablelist-filter');
            expect(_addComponentsFromDefStub).toHaveBeenCalledWith(_componentArray);
        });
    });

    describe('_getListMeta', function() {
        it('should return list view meta data for give module', function() {
            sinon.stub(app.metadata, 'getView');
            view._getListMeta(moduleName);

            expect(app.metadata.getView).toHaveBeenCalledWith(moduleName, 'list');
        });
    });

    describe('_getAvailableColumns', function() {
        it('should return empty object if module is not found in settings', function() {
            var stub = sinon.stub;
            view.settings.get = function() {
                return null;
            };

            expect(view._getAvailableColumns()).toEqual({});
        });

        it('should return all the columns if module is set', function() {
            sinon.stub(view, '_getListMeta');
            sinon.stub(view, 'getFieldMetaForView').callsFake(function() {
                return [
                    {
                        label: 'testLabel1',
                        name: 'test1'
                    },
                    {
                        label: 'testLabel2',
                        name: 'test2'
                    }
                ];
            });

            expect(view._getAvailableColumns()).toEqual({
                'test1': 'testLabel1',
                'test2': 'testLabel2'
            });
        });
    });

    describe('_displayDashlet', function() {
        var stubStartAutoRefresh;
        var stubGetColumns;
        var stubGetFields;
        var stubApplyFilterDef;
        var stubContextReload;

        beforeEach(function() {
            stubStartAutoRefresh = sinon.stub(view, '_startAutoRefresh');
            stubGetColumns = sinon.stub(view, '_getColumnsForDisplay');
            stubGetFields = sinon.stub(view, 'getFieldNames');
            stubApplyFilterDef = sinon.stub(view, '_applyFilterDef');
            stubContextReload = sinon.stub(view.context, 'reloadData');
        });

        it('should run through all of the logic necessary to render the dashlet', function() {
            var columns = _.map(sampleFieldMetadata, function(column) {
                    return _.extend(column, {sortable: true});
                });
            var fields = _.pluck(columns, 'name');

            stubGetColumns.returns(columns);
            stubGetFields.returns(fields);
            view.settings.set('limit', 5);
            view.meta = {panels: []};
            view._displayDashlet();

            expect(view.context.get('skipFetch')).toBeFalsy();
            expect(view.context.get('limit')).toBe(5);
            expect(view.context.get('fields')).toEqual(fields);
            expect(view.meta.panels).toEqual([{fields: columns}]);
            expect(stubStartAutoRefresh).toHaveBeenCalledOnce();
        });

        it('should apply the filter def and reload context if filterDef is supplied', function() {
            view._displayDashlet('testFilterDef');

            expect(view.context.get('skipFetch')).toBeFalsy();
            expect(stubApplyFilterDef).toHaveBeenCalledWith('testFilterDef');
            expect(stubContextReload).toHaveBeenCalledWith({recursive: false});
            expect(stubStartAutoRefresh).toHaveBeenCalledOnce();
            expect(stubGetFields).toHaveBeenCalledOnce();
            expect(stubGetColumns).toHaveBeenCalledOnce();
        });

        it('should not apply the filter def and reload context if no filterDef is supplied', function() {
            sinon.stub(view.layout, 'getComponent').callsFake(function() {
                return {
                    test: 'testComponent',
                    render: sinon.stub()
                };
            });

            view._displayDashlet();

            expect(stubApplyFilterDef).not.toHaveBeenCalled();
            expect(stubContextReload).not.toHaveBeenCalled();
            expect(view.layout.getComponent).toHaveBeenCalledWith('list-bottom');
        });
    });

    describe('checkFooterVisibility', function() {
        it('should do nothing if footer is not found', function() {
            sinon.stub(view, '$').callsFake(function() {
                return {
                    hide: sinon.stub()
                };
            });
            view.checkFooterVisibility();

            expect(view.$('.block-footer').hide).not.toHaveBeenCalled();
        });

        it('should do nothing if footer is found but no rows', function() {
            view.$el.html('<div class="block-footer"></div>');
            sinon.spy(view, '$');
            sinon.stub(jQuery.fn, 'hide');
            view.checkFooterVisibility();

            expect(jQuery.fn.hide).not.toHaveBeenCalled();
        });

        it('should hide if footer is found along with row', function() {
            view.$el.html('<div class="dashlet-console-list-row"></div><div class="block-footer"></div>');
            sinon.spy(view, '$');
            sinon.stub(jQuery.fn, 'hide');
            view.checkFooterVisibility();

            expect(jQuery.fn.hide).toHaveBeenCalled();
        });
    });

    describe('_applyFilterDef', function() {
        var getModuleStub;
        var filter1 = {'name': {'$starts': 'A'}};
        var filter2 = {'name_c': {'$starts': 'B'}};
        var filter3 = {'$favorite': ''};
        var filter4 = {'$custom': 'testId'};
        var fakeModuleMeta =
            {
                'fields': {'name': {}},
                'filters': {
                    'default': {
                        'meta': {
                            'filters': [
                                {'filter_definition': filter1,'id': 'test1'},
                                {'filter_definition': filter2,'id': 'test2'},
                                {'filter_definition': filter3,'id': 'test3'}
                            ]
                        }
                    }
                }
            };

        beforeEach(function() {
            getModuleStub = sinon.stub(app.metadata, 'getModule').returns(fakeModuleMeta);
        });

        it('should apply the field-filtered filterDef on the context collection', function() {
            var testFilterDef = [filter1, filter2, filter3];
            sinon.stub(view, 'updateFilterForModule').callsFake(function() {
                return [filter1, filter2, filter4];
            });
            view._applyFilterDef(testFilterDef);

            expect(view.updateFilterForModule).toHaveBeenCalledWith([filter1, filter3]);
            expect(view.context.get('collection').filterDef).toEqual([filter1, filter2, filter4]);
        });

        it('should not apply the filterDef on the context collection if not supplied', function() {
            view._applyFilterDef();
            expect(_.isEmpty(view.context.get('collection').filterDef)).toBeTruthy();
        });
    });

    describe('_getColumnsForDisplay', function() {
        var displayColumns = _.union(_.pluck(sampleFieldMetadata, 'name'), 'qux');
        var fieldMeta = [{name: 'foo', sortable: false}, {name: 'baz'}];

        beforeEach(function() {
            sinon.stub(view, 'getFieldMetaForView').returns(fieldMeta);
            sinon.stub(view, '_getListMeta').returns(fieldMeta);
        });

        it('should merge the field metadata onto the display_columns and return those fields', function() {
            view.settings.set('display_columns', displayColumns);
            var columns = view._getColumnsForDisplay();
            // "baz" should not have been added because only fields from display_columns should be used
            expect(columns.length).toBe(displayColumns.length);
            // "foo" should not be sortable
            var first = columns.shift();
            expect(first.sortable).toBeFalsy();
            // all other columns should be sortable
            var rest = _.every(columns, function(column) {
                return true === column.sortable;
            });
            expect(rest).toBeTruthy();
        });

        it('should return an empty array when display_columns is set but has no fields', function() {
            view.settings.set('display_columns', []);
            var columns = view._getColumnsForDisplay();
            expect(columns.length).toBe(0);
        });

        it('should call BaseDashablelistView#_updateDisplayColumns when display_columns is undefined', function() {
            view.settings.set('display_columns', null);
            var stubUpdateDisplayColumns = sinon.stub(view, '_updateDisplayColumns').callsFake(function() {
                view.settings.set('display_columns', displayColumns);
            });
            var columns = view._getColumnsForDisplay();
            expect(stubUpdateDisplayColumns).toHaveBeenCalledOnce();
            expect(columns.length).toBe(displayColumns.length);
        });
    });

    describe('_startAutoRefresh', function() {
        var refreshStub;
        beforeEach(function() {
            refreshStub = sinon.stub(view.settings, 'get');
            sinon.stub(view, '_stopAutoRefresh');
        });

        it('should not set timerId if refreshRate is 0', function() {
            refreshStub.returns(0);
            view._startAutoRefresh();

            expect(view._stopAutoRefresh).not.toHaveBeenCalled();
            expect(view._timerId).not.toBeDefined();
        });

        it('should not set timerId if refreshRate is not 0', function() {
            refreshStub.returns(10);
            view._startAutoRefresh();

            expect(view._stopAutoRefresh).toHaveBeenCalled();
            expect(view._timerId).toBeDefined();
        });
    });

    describe('_render', function() {
        it('should call _super with _render if not in config view', function() {
            view.meta.config = false;
            view._render();

            expect(view.action).not.toEqual('list');
            expect(view._super).toHaveBeenCalledWith('_render');
        });

        it('should set action to list if in config view', function() {
            view.meta.config = true;
            view._render();

            expect(view.action).toEqual('list');
            expect(view._super).toHaveBeenCalledWith('_render');
        });
    });

    describe('getFieldMetaForView', function() {
        it('should return empty array if meta is not an object', function() {

            expect(view.getFieldMetaForView(['test'])).toEqual([]);
        });

        it('should return empty array if meta is not an object', function() {
            var meta = {
                panels: [
                    {
                        fields: [
                            {
                                name: 'test'
                            }
                        ]
                    }
                ]
            };

            expect(view.getFieldMetaForView(meta)).toEqual([
                {
                    name: 'test'
                }
            ]);
        });
    });

    describe('_dispose', function() {
        it('should call stopAutoRefresh and _super', function() {
            sinon.stub(view, '_stopAutoRefresh');
            view._dispose();

            expect(view._stopAutoRefresh).toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
