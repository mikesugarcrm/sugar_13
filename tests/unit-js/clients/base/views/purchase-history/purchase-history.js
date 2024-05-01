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
describe('Base.View.PurchaseHistory', function() {
    var app;
    var context;
    var currentModule = '';
    var layout;
    var view;
    var dashboard;
    var layoutName = 'dashlet';
    var module = 'Purchases';
    var moduleName = 'Accounts';
    var viewName = 'purchase-history';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'preview');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'layout', 'dashlet');
        SugarTest.loadComponent('base', 'layout', 'dashboard');
        SugarTest.loadComponent('base', 'view', 'active-subscriptions');

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition(
            viewName,
            {
                panels: [
                    {
                        fields: [
                            {
                                name: 'linked_account_field',
                                type: 'enum',
                                options: ''
                            },
                            {
                                name: 'limit',
                                type: 'enum',
                                options: [5, 10, 15, 20]
                            }
                        ],
                    }

                ],
                fields: [
                    'name',
                    'start_date',
                    'end_date',
                    'total_quantity',
                    'total_revenue',
                    'pli_count',
                ],
            }
        );
        SugarTest.testMetadata.set();
        app.data.declareModels();
        SugarTest.loadPlugin('Dashlet');

        context = app.context.getContext();
        context.set({
            module: moduleName,
            layout: layoutName
        });
        context.parent = app.data.createBean('Home');
        context.parent.set('dashboard_module', moduleName);
        context.prepare();

        dashboard = app.view.createLayout({
            name: 'dashboard',
            type: 'dashboard',
            context: context.parent,
        });

        layout = app.view.createLayout({
            name: layoutName,
            context: context,
            meta: {index: 0},
            layout: dashboard,
        });

        view = SugarTest.createView(
            'base',
            moduleName,
            viewName,
            {module: moduleName},
            context,
            false,
            layout
        );
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        dashboard.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        view = null;
        layout = null;
        dashboard = null;
    });

    describe('_buildFieldsList', function() {
        describe('when we are on an Accounts module', function() {
            it('should set the configPanelOptions', function() {
                var configPanel = _.first(view.dashletConfig.panels);
                var options = _.first(configPanel.fields).options;

                expect(Object.keys(options).length).toBe(1);
                expect(options.id).toBe('ID');
            });
        });
    });

    describe('_initCollection', function() {
        it('should not take action when base module or model does not exist', function() {
            var beanCollectionStub = sinon.stub(app.data, 'createBeanCollection').returns({
                'off': $.noop
            });
            view.baseModule = 'Accounts';

            view._initCollection();
            expect(beanCollectionStub).not.toHaveBeenCalled();
        });

        describe('when the dashlet is on the Accounts module', function() {
            it('should create new bean collection', function() {
                var beanCollectionStub = sinon.stub(app.data, 'createBeanCollection').returns({
                    'off': $.noop
                });
                view.baseModule = 'Accounts';
                view.currentModule = 'Accounts';
                view._getAccountId = function() {
                    return 'my_id';
                };

                var accountModel = app.data.createBean('Accounts', {'id': 'my_id'});
                var parentContext = app.context.getContext();
                parentContext.set({
                    module: 'Accounts',
                    model: accountModel,
                });
                view.context = parentContext;
                view.settings.set({
                    linked_account_field: 'id',
                    limit: 10
                });

                view.meta = {
                    'fields': ['name']
                };
                view.purchasesModule = true;
                app.config.maxRecordFetchSize = 1000;
                view._initCollection();
                expect(beanCollectionStub.lastCall.args[0]).toEqual('Purchases');
                expect(beanCollectionStub.lastCall.args[2].fields)
                    .toEqual(['name', 'start_date', 'end_date', 'total_quantity', 'total_revenue', 'pli_count']);
                expect(beanCollectionStub.lastCall.args[2].filter[0].account_id.$equals).toEqual('my_id');
                expect(beanCollectionStub.lastCall.args[2].limit).toEqual(10);
            });
        });

        describe('when the dashlet is being added to a different module', function() {
            it('should create new bean collection', function() {
                var beanCollectionStub = sinon.stub(app.data, 'createBeanCollection').returns({
                    'off': $.noop
                });
                view.baseModule = 'Accounts';
                view.currentModule = 'Contacts';
                view._getAccountId = function() {
                    return 'my_id';
                };

                var accountModel = app.data.createBean('Accounts', {'id': 'my_id'});
                var contactModel = app.data.createBean('Contacts', {'account_id': 'my_id'});
                var parentContext = app.context.getContext();
                parentContext.set({
                    module: 'Accounts',
                    model: contactModel,
                });
                view.context = parentContext;
                view.settings.set({linked_account_field: 'account_id'});

                view.meta = {
                    'fields': ['name']
                };
                view.purchasesModule = true;
                app.config.maxRecordFetchSize = 1000;
                view._initCollection();
                expect(beanCollectionStub.lastCall.args[0]).toEqual('Purchases');
                expect(beanCollectionStub.lastCall.args[2].fields)
                    .toEqual(['name', 'start_date', 'end_date', 'total_quantity', 'total_revenue', 'pli_count']);
                expect(beanCollectionStub.lastCall.args[2].filter[0].account_id.$equals).toEqual('my_id');
                expect(beanCollectionStub.lastCall.args[2].limit).toEqual(10);
            });
        });
    });

    describe('_initPliCollection', function() {
        it('should create a new bean collection', function() {
            var beanCollectionStub = sinon.stub(app.data, 'createBeanCollection').returns({
                'off': $.noop
            });
            view.baseModule = 'Accounts';
            view.currentModule = 'Accounts';

            var purchaseModel = app.data.createBean('Purchases', {'id': 'purchase_id'});
            var accountModel = app.data.createBean('Accounts', {'id': 'my_id'});
            var parentContext = app.context.getContext();
            parentContext.set({
                module: 'Accounts',
                model: accountModel,
            });
            view.context = parentContext;
            view.settings.set({
                linked_account_field: 'id',
                limit: 10
            });

            view.meta = {
                'fields': ['name']
            };
            view.purchasesModule = true;
            app.config.maxRecordFetchSize = 1000;

            view._initPliCollection(purchaseModel);
            expect(beanCollectionStub.lastCall.args[0]).toEqual('PurchasedLineItems');
            expect(beanCollectionStub.lastCall.args[2].fields).toEqual([
                'id', 'name', 'service_start_date', 'service_end_date',
                'quantity', 'total_amount', 'currency_id', 'base_rate',
            ]);
            expect(beanCollectionStub.lastCall.args[2].filter[0].purchase_id.$equals).toEqual('purchase_id');
            expect(beanCollectionStub.lastCall.args[2].limit).toEqual(10);
        });
    });

    describe('applySort', function() {
        it('should update the collection options', function() {
            view.collectionOptions = {
                'fields': ['id', 'name'],
                'filter': [],
                'limit': 10,
                'params': {
                    'order_by': 'end_date:desc'
                }
            };
            view.collection = new Backbone.Collection();

            var fetchStub = sinon.stub(view.collection, 'fetch');

            view.applySort('total_revenue:asc');

            expect(view.collectionOptions.params.order_by).toEqual('total_revenue:asc');
            expect(view.collection._persistentOptions.params.order_by).toEqual('total_revenue:asc');

            expect(fetchStub).toHaveBeenCalled();
            expect(fetchStub.lastCall.args[0].limit).toEqual(10);
            expect(fetchStub.lastCall.args[0].fields).toEqual(['id', 'name']);
            expect(fetchStub.lastCall.args[0].params.order_by).toEqual('total_revenue:asc');
        });
    });

    describe('applySearch', function() {
        beforeEach(function() {
            sinon.stub(view, 'buildFilterDefinition').callsFake(function() {
                return [{
                    test: 'newDef'
                }];
            });
            sinon.stub(view, 'render');

            view.collection = {
                once: sinon.stub(),
                off: sinon.stub()
            };

            view.currentFilterDef = [{
                test: 'testDef'
            }];
            sinon.stub(jQuery.fn, 'val').callsFake(function() {return 'newSearch';});

            view.collectionOptions = {
                'fields': ['id', 'name'],
                'filter': [],
                'limit': 10,
                'params': {
                    'order_by': 'end_date:desc'
                }
            };
            view.collection = new Backbone.Collection();
            sinon.stub(view.collection, 'fetch');

        });

        it('should build filter def and call render', function() {
            view.baseFilter = [{
                test: 'testDef'
            }];
            view.applySearch('newSearch');

            expect(view.buildFilterDefinition).toHaveBeenCalledWith(view.baseFilter, 'newSearch');
            expect(view.render).toHaveBeenCalled();
            expect(view.collection.fetch).toHaveBeenCalledWith(view.collectionOptions);
        });
    });

    describe('buildFilterDefinition', function() {
        var getModuleStub;
        var filterBeanClass;
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
        });

        afterEach(function() {
            filterBeanClass = null;
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
            searchTermFilter: [{'name': {'$contains': 'abc'}}],
            filteredFilter: [filter1, filter3],
            expected: [{'$and': [filter1, filter3, {'name': {'$contains': 'abc'}}]}]
        },{
            viewFilter: [filter1],
            searchTerm: 'test',
            testFilterDef: [],
            searchTermFilter: [{'name': {'$contains': 'test'}}],
            filteredFilter: [],
            expected: [{'name': {'$contains': 'test'}}]
        },{
            viewFilter: [filter1],
            searchTerm: 'test',
            testFilterDef: {'test': {'$test': 'test'}},
            searchTermFilter: [{'name': {'$contains': 'test'}}],
            filteredFilter: [{'test': {'$test': 'test'}}],
            expected: [{
                '$and': [
                    {'test': {'$test': 'test'}},
                    {'name': {'$contains': 'test'}}
                ]
            }]
        },{
            viewFilter: [],
            searchTerm: 'test',
            testFilterDef: [],
            searchTermFilter: [{
                '$or': [
                    {'name': {'$contains': 'test'}},
                    {'last_name': {'$contains': 'test'}}
                ]
            }],
            filteredFilter: [],
            expected: [{
                '$or': [
                    {'name': {'$contains': 'test'}},
                    {'last_name': {'$contains': 'test'}}
                ]
            }]
        }], function(value) {
            it('should build a filterDef correctly depending on a search term', function() {
                getModuleStub = sinon.stub(app.metadata, 'getModule').returns(fakeModuleMeta);
                filterBeanClass = app.data.getBeanClass('Filters').prototype;

                view.layout.filters = [value.viewFilter];
                filterBeanClass.buildSearchTermFilter = function() {return value.searchTermFilter;};
                sinon.stub(view, 'filterSelectedFilter').callsFake(function() {
                    return value.filteredFilter;
                });
                var builtDef = view.buildFilterDefinition(value.testFilterDef, value.searchTerm);

                expect(builtDef).toEqual(value.expected);
            });
        });
    });

    describe('filterSelectedFilter', function() {
        var getModuleStub;
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
});
