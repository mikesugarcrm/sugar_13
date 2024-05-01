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
describe('Base.View.Dashablelist', function() {
    var app,
        view,
        layout,
        sampleFieldMetadata = [{name: 'foo'}, {name: 'bar'}],
        sampleColumns = {foo: 'foo', bar: 'bar'},
        moduleName = 'Accounts',
        viewName = 'dashablelist',
        layoutName = 'record';

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

        view = SugarTest.createView('base', moduleName, viewName, null, context, null, layout);
        view._availableModules = {Accounts: 'Accounts', Contacts: 'Contacts'};
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
        delete app.plugins.plugins['view']['Dashlet'];
    });

    it('Get correct link fields between current and main modules', function() {
        var mainModule = 'Accounts',
            relateModule = 'Contacts',
            fieldDefs = [
                {
                    name: 'a',
                    type: 'link',
                    rel_mod: relateModule
                }, {
                    name: 'b',
                    type: 'bool',
                    rel_mod: relateModule
                }, {
                    name: 'c',
                    type: 'link',
                    rel_mod: mainModule
                }];
        SugarTest.testMetadata.updateModuleMetadata(mainModule, {fields: fieldDefs});

        sinon.stub(app.data, 'getRelatedModule').callsFake(function(module, field) {
            return _.findWhere(app.metadata.getModule(module).fields, {name: field}).rel_mod || '';
        });
        sinon.stub(app.lang, 'get').returnsArg(0);

        expect(view.getLinkedFields(relateModule)).toEqual({'a': 'a'});
    });

    it('should call _displayDashlet', function() {
        var stubDisplayDashlet = sinon.stub(view, '_displayDashlet');
        var data = {'filter_definition': {'name': {'$starts': 'A'}}};

        view.triggerDashletSetup(data);
        expect(stubDisplayDashlet).toHaveBeenCalledWith(data.filter_definition);
    });

    describe('initialize the dashlet', function() {
        describe('init dashlet workflow', function() {
            var stubInitializeSettings,
                stubConfigureDashlet,
                stubDisplayDashlet;

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

            it('should call BaseDashablelistView#_configureDashlet when in config mode', function() {
                view.meta.config = true;
                view.initDashlet('config');
                expect(stubInitializeSettings).toHaveBeenCalledOnce();
                expect(stubConfigureDashlet).toHaveBeenCalledOnce();
                expect(stubDisplayDashlet).not.toHaveBeenCalled();
                expect(view.settings._events['change:module']).toBeDefined();
                expect(view.layout.context._events['filter:add']).toBeDefined();
                expect(view.layout._before['dashletconfig:save']).toBeDefined();
            });

            it('should call BaseDashablelistView#_displayDashlet when in preview mode', function() {
                view.meta.preview = true;
                view.initDashlet('preview');
                expect(stubInitializeSettings).toHaveBeenCalledOnce();
                expect(stubConfigureDashlet).not.toHaveBeenCalled();
                expect(stubDisplayDashlet).toHaveBeenCalledOnce();
                expect(view.settings._events['change:module']).not.toBeDefined();
                expect(view.layout.context._events['filter:add']).not.toBeDefined();
                expect(view.layout._before['dashletconfig:save']).not.toBeDefined();
            });

            it('should call BaseDashablelistView#_displayDashlet when in view mode with no filter_id', function() {
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

                var filterTest = {
                    id: 'testFilterID',
                    filter_definition: [
                        {name: 'test'}
                    ]
                };

                view.settings.set('module', moduleName);
                view.settings.set('filter_id', filterTest.id);

                // Prepare a collection with this filter.
                var filters = app.data.createBeanCollection('Filters');
                filters.setModuleName(moduleName);
                filters.load();
                filters.collection.add(filterTest);

                view.initDashlet('view');

                expect(stubDisplayDashlet).toHaveBeenCalledWith(filterTest.filter_definition);
            });

            it('should not call _super.loadData() when no filter access', function() {
                var superStub = sinon.stub(view, '_super').callsFake(function() {});
                view.filterIsAccessible = false;
                view.loadData();
                expect(superStub).not.toHaveBeenCalled();
            });

            it('should not call _displayNoFilterAccess when filter limit exceeded and has access', function() {
                var stubDisplayNoFilterAccess = sinon.stub(view, '_displayNoFilterAccess');
                SugarTest.declareData('base', 'Filters');
                sinon.stub(app.BeanCollection.prototype, 'fetch').callsFake(function(options) {
                    options.success();
                });
                view.settings.set('module', moduleName);
                view.settings.set('filter_id', 'testFilterID');
                var filters = app.data.createBeanCollection('Filters');
                filters.setModuleName(moduleName);
                filters.load();
                view.initDashlet('view');
                expect(stubDisplayNoFilterAccess).not.toHaveBeenCalledOnce();
            });
        });

        describe('setting default options', function() {
            it('should default all undefined settings', function() {
                sinon.stub(app.lang, 'get').returnsArg(1);
                view._initializeSettings();
                expect(view.settings.get('module')).toBe(moduleName);
                expect(view.settings.get('label')).toBe('LBL_MODULE_NAME');
                expect(view.settings.get('limit')).toBe(5);
                expect(view.settings.get('intelligent')).toBe('0');
                expect(view.settings.get('filter_id')).toBe('assigned_to_me');
            });

            it('should not change the module setting when the module is approved', function() {
                var module = 'Contacts';
                view.settings.set('module', module);
                view._setDefaultModule();
                expect(view.settings.get('module')).toBe(module);
            });

            it('should set the moduleIsAvailable to false when the module is unapproved',
               function() {
                   view.settings.set('module', 'Leads');
                   view._setDefaultModule();
                   expect(view.moduleIsAvailable).toBeFalsy();
               }
            );

            it('should use the view\'s module when the module is unapproved in config mode',
               function() {
                   view.meta.config = true;
                   view.settings.set('module', 'Leads');
                   view._setDefaultModule();
                   expect(view.settings.get('module')).toEqual(moduleName);
               }
            );

            it('should use the first available module when view\'s module is unapproved and' +
                'a parent module is in a black list in config mode',
               function() {
                   view.meta.config = true;
                   view.settings.set('module', 'Leads');
                   view.context.parent.set('module', 'Home');
                   view.moduleBlacklist.push('Home');
                   view._setDefaultModule();
                   expect(view.settings.get('module')).toEqual(_.first(_.keys(view._getAvailableModules())));
               }
            );

            it('should use the module from the context when the module setting is undefined', function() {
                var module = 'Contacts';
                view.context.set('module', module);
                view.settings.unset('module', {silent: true});
                view._setDefaultModule();
                expect(view.settings.get('module')).toBe(module);
            });
        });

        describe('when intelligent', function() {
            var previousContext = {};

            beforeEach(function() {
                previousContext.module = app.controller.context.get('module');
                previousContext.layout = app.controller.context.get('layout');
                app.controller.context.set('module', moduleName);
                app.controller.context.set('layout', layoutName);
            });

            afterEach(function() {
                app.controller.context.set('module', previousContext.module);
                app.controller.context.set('layout', previousContext.layout);
            });

            it('should set the context\'s link and the collection\'s relate', function() {
                var testField = 'test_field';
                view.settings.set('intelligent', '1');
                view.settings.set('linked_fields', testField);
                view.intelligent = '1';
                view.moduleIsAvailable = false;
                view.initDashlet();
                expect(view.context.get('link')).toEqual(testField);
                expect(view.collection.getOption('relate')).toBeTruthy();
            });
        });
    });

    describe('configure the dashlet', function() {
        it('should update the label, columns, filter_id when the module is changed', function() {
            var oldModule = 'Accounts',
                newModule = 'Contacts',
                stubUpdateDisplayColumns = sinon.stub(view, '_updateDisplayColumns'),
                stubDashletFilterReinit = sinon.stub(view.layout, 'trigger');

            sinon.stub(view, '_initializeSettings');
            sinon.stub(view, '_configureDashlet');
            sinon.stub(app.lang, 'get').returnsArg(1);

            view.meta = {config: true};
            view.settings.set('module', oldModule);
            view.settings.set('label', 'Foo');
            view.initDashlet('config');

            expect(view.settings._events['change:module']).toBeDefined();

            view.settings.set('module', newModule);

            expect(view.dashModel.get('module')).toBe(newModule);
            expect(view.dashModel.get('filter_id')).toBe('assigned_to_me');
            expect(view.settings.get('label')).toBe(newModule);
            expect(stubDashletFilterReinit).toHaveBeenCalledWith('dashlet:filter:reinitialize');
            expect(stubUpdateDisplayColumns).toHaveBeenCalledOnce();
        });

        it('should run through all of the logic necessary to render the dashlet configuration view', function() {
            var module = 'Accounts';
            view.settings.set('module', module);
            _.extend(view._availableModules, {Leads: 'Leads'});
            view._availableColumns = {};
            view._availableColumns[module] = sampleColumns;
            view.meta = {
                panels: [
                    {
                        fields: [{name: 'module'}, {name: 'display_columns'}]
                    }
                ]
            };
            view._configureDashlet();
            var fieldMeta = view.getFieldMetaForView(view.meta),
                moduleField = _.findWhere(fieldMeta, {name: 'module'}),
                columnsField = _.findWhere(fieldMeta, {name: 'display_columns'});
            expect(moduleField.options).toEqual(view._availableModules);
        });

        describe('get the approved modules', function() {
            var stubAppUserGet,
                stubGetFieldMetaForView;

            beforeEach(function() {
                sinon.stub(app.lang, 'get').returnsArg(1);
                stubGetFieldMetaForView = sinon.stub(view, 'getFieldMetaForView');
                stubAppUserGet = sinon.stub(app.metadata, 'getModuleNames').callsFake(function() {
                    return ['Accounts', 'Contacts', 'Leads'];
                });
            });

            it('should return additional modules', function() {
                view._availableModules = {};
                stubGetFieldMetaForView.returns(sampleFieldMetadata);
                stubAppUserGet.restore();
                stubAppUserGet = sinon.stub(app.metadata, 'getModuleNames').callsFake(function() {
                    return ['Project'];
                });
                var modules = view._getAvailableModules();
                expect(modules).toEqual({Project: 'Project', ProjectTask: 'ProjectTask'});
            });

            it('should cache and return the approved modules', function() {
                view._availableModules = {};
                stubGetFieldMetaForView.returns(sampleFieldMetadata);
                var modules = view._getAvailableModules();
                expect(modules).toEqual({Accounts: 'Accounts', Contacts: 'Contacts', Leads: 'Leads'});
            });

            it('should not cache and return unapproved modules', function() {
                view._availableModules = {};
                view.moduleBlacklist.push('Accounts');
                stubGetFieldMetaForView.returns(sampleFieldMetadata);
                var modules = view._getAvailableModules();
                expect(modules).toEqual({Contacts: 'Contacts', Leads: 'Leads'});
            });

            it('should not cache and return modules without a list view', function() {
                view._availableModules = {};
                stubGetFieldMetaForView.returns([]);
                var modules = view._getAvailableModules();
                expect(modules).toEqual({});
            });

            it('should return the approved modules from cache', function() {
                var modules = view._getAvailableModules();
                expect(stubAppUserGet).not.toHaveBeenCalled();
                expect(modules).toEqual({Accounts: 'Accounts', Contacts: 'Contacts'});
            });
        });

        describe('get the available columns', function() {
            it('should return an empty set when the module is not set', function() {
                view.settings.set('module', null);
                var columns = view._getAvailableColumns();
                expect(columns).toEqual({});
            });
        });
    });

    describe('saveDashletFilter', function() {
        var triggerStub,
            updateDashletStub;

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
            let filterModel = new Backbone.Model();
            let updateDashletStub = sinon.stub(view, 'updateDashletFilterAndSave');
            sinon.stub(view, '_initializeSettings');
            sinon.stub(view, '_configureDashlet');
            sinon.stub(view, '_displayDashlet');

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
            }

            let appEventsStub = sinon.stub(app.events, 'trigger');
            let drawerCloseStub = sinon.stub(app.drawer, 'close');
            let filterModel = new Backbone.Model({id: 'test'});

            view.updateDashletFilterAndSave(filterModel);
            expect(view.settings.get('filter_id')).toEqual(filterModel.get('id'));
            expect(view.dashModel.get('filter_id')).toEqual(filterModel.get('id'));
            expect(drawerCloseStub).toHaveBeenCalled();
            expect(appEventsStub).toHaveBeenCalledWith('dashlet:filter:save');
        });
    });

    describe('_addFilterComponent', function() {
        it('should be invoked by layout init', function() {
            let _addFilterComponentStub = sinon.stub(view, '_addFilterComponent');
            sinon.stub(view, '_initializeSettings');
            sinon.stub(view, '_configureDashlet');
            sinon.stub(view, '_displayDashlet');

            view.meta.config = true;
            view.initDashlet('config');
            view.layout.trigger('init');

            expect(_addFilterComponentStub).toHaveBeenCalled();
        });

        it('should add the dashablelist-filter component', function() {
            let _addComponentsFromDefStub = sinon.stub(view.layout, '_addComponentsFromDef');
            let getComponentStub = sinon.stub(view.layout, 'getComponent');
            let _componentArray = [{
                layout: 'dashablelist-filter'
            }];

            view._addFilterComponent();

            expect(getComponentStub).toHaveBeenCalledWith('dashablelist-filter');
            expect(_addComponentsFromDefStub).toHaveBeenCalledWith(_componentArray);
        });
    });

    describe('_applyFilterDef', function() {
        var getModuleStub,
            filter1 = {'name': {'$starts': 'A'}},
            filter2 = {'name_c': {'$starts': 'B'}},
            filter3 = {'$favorite': ''},
            fakeModuleMeta = {
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

            view._applyFilterDef(testFilterDef);
            expect(view.context.get('collection').filterDef).toEqual([filter1, filter3]);
        });

        it('should not apply the filterDef on the context collection if not supplied', function() {
            view._applyFilterDef();
            expect(_.isEmpty(view.context.get('collection').filterDef)).toBeTruthy();
        });
    });

    describe('view the dashlet', function() {
        describe('_displayDashlet', function() {
            var stubStartAutoRefresh,
                stubGetColumns,
                stubGetFields,
                stubApplyFilterDef,
                stubContextReload;

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
                    }),
                    fields = _.pluck(columns, 'name');

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
                view._displayDashlet();

                expect(view.context.get('skipFetch')).toBeFalsy();
                expect(stubApplyFilterDef).not.toHaveBeenCalled();
                expect(stubContextReload).not.toHaveBeenCalled();
                expect(stubStartAutoRefresh).toHaveBeenCalledOnce();
                expect(stubGetFields).toHaveBeenCalledOnce();
                expect(stubGetColumns).toHaveBeenCalledOnce();
            });

            it('should not apply the filter def and reload context if no filterDef is supplied', function() {
                view._displayDashlet('testFilterDef');

                expect(view.context.get('skipFetch')).toBeFalsy();
                expect(stubApplyFilterDef).toHaveBeenCalledWith('testFilterDef');
                expect(stubContextReload).toHaveBeenCalled();
                expect(stubStartAutoRefresh).toHaveBeenCalledOnce();
                expect(stubGetFields).toHaveBeenCalledOnce();
                expect(stubGetColumns).toHaveBeenCalledOnce();
            });
        });

        describe('get the columns to include in the list', function() {
            var displayColumns = _.union(_.pluck(sampleFieldMetadata, 'name'), 'qux'),
                fieldMeta = [{
                    name: 'foo',
                    sortable: false
                }, {
                    name: 'baz',
                    selected: true
                }, {
                    name: 'alternate_phone',
                    selected: false
                }, {
                    name: 'bar',
                    selected: true,
                }];

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

            it('should not display columns that are not marked as selected by default', function() {
                var expectedColumns = ['foo', 'baz'];
                view.settings.set('display_columns', ['foo', 'baz', 'alternate_phone']);
                view._hideUnselectedColumns();
                var columns = view.settings.get('display_columns');
                expect(columns).toEqual(expectedColumns);
            });
        });

        describe('get correct list view metadata (_getListMeta)', function() {
            var sidecarStub, metadataStub;

            beforeEach(function() {
                sidecarStub = sinon.stub(app.metadata, 'getView');
                metadataStub = sinon.stub(app.metadata, 'getModule');
            });

            it('uses Sidecar metadata when a module is not in backwards compatibility mode', function() {
                metadataStub.returns({isBwcEnabled: false});
                view._getListMeta('foo');
                expect(sidecarStub).toHaveBeenCalledOnce();
            });
        });
    });
});
