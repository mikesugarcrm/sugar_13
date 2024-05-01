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

describe('Dashboards.Base.Layout.Dashboard', function() {

    var app;
    var layout;
    var context;
    var sandbox = sinon.createSandbox();

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'layout', 'dashboard', 'Dashboards');

        app.routing.start();
    });

    afterEach(function() {
        sandbox.restore();
        sinon.restore();
        app.router.stop();

        app.cache.cutAll();
        Handlebars.templates = {};
        layout.dispose();
    });

    describe('Home Dashboard', function() {
        var apiStub;

        beforeEach(function() {
            apiStub = sandbox.stub(app.api, 'records').callsFake(
                function(method, module, data, params, callbacks, options) {
                    callbacks.success();
                    callbacks.complete();
                }
            );
            context = new app.Context({
                module: 'Home',
                layout: 'dashboard'
            });
            layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null, context, true);
            sandbox.stub(layout, '_renderEmptyTemplate');
        });

        it('should initialize dashboard model and collection', function() {
            var model = layout.context.get('model');
            expect(model.apiModule).toBe('Dashboards');
            layout.loadData();
            var expectedApiUrl = 'Dashboards';
            expect(apiStub).toHaveBeenCalledWith('read', expectedApiUrl);
            apiStub.resetHistory();

            model.set('foo', 'Blah');
            model.save();
            expect(apiStub).toHaveBeenCalledWith('create', expectedApiUrl,
                {view_name: '', foo: 'Blah', id: undefined});
            apiStub.resetHistory();

            model.set('id', 'fake-id-value');
            model.save();
            expect(apiStub).toHaveBeenCalledWith('update', expectedApiUrl);
        });
    });

    describe('Module Dashboard', function() {
        var apiStub;
        var parentLayout;
        var parentModule;

        beforeEach(function() {
            apiStub = sandbox.stub(app.api, 'records').callsFake(
                function(method, module, data, params, callbacks, options) {
                    callbacks.success();
                    callbacks.complete();
                }
            );

            parentModule = 'Accounts';
            context = new app.Context({
                module: parentModule,
                layout: 'records'
            });
            parentLayout = app.view.createLayout({
                name: 'records',
                type: 'records',
                module: parentModule,
                context: context
            });
            layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null,
                parentLayout.context.getChildContext({
                    module: 'Dashboards'
                }), true);
            layout.context.parent = context;
            sandbox.stub(layout, '_renderEmptyTemplate');
            parentLayout.addComponent(layout);
        });

        afterEach(function() {
            parentLayout.dispose();
            parentModule = null;
        });

        it('should initialize dashboard model and collection', function() {
            var model = layout.context.get('model');
            var expectedApiUrl = 'Dashboards';
            var collection = app.data.createBeanCollection('Dashboards', [model]);
            context.set('collection', collection);

            expect(model.apiModule).toBe('Dashboards');
            expect(model.dashboardModule).toBe(parentModule);

            sandbox.stub(context, 'isDataFetched').returns(false);
            sandbox.stub(collection, 'once').withArgs('sync').yieldsOn(layout);
            layout.loadData();

            expect(apiStub).toHaveBeenCalledWith('read', expectedApiUrl);
            apiStub.resetHistory();

            model.set('foo', 'Blah');
            model.save();
            expect(apiStub).toHaveBeenCalledWith('create', expectedApiUrl,
                {view_name: 'records', foo: 'Blah', id: undefined});
            apiStub.resetHistory();

            model.set('id', 'fake-id-value');
            model.save();
            expect(apiStub).toHaveBeenCalledWith('update', expectedApiUrl);
        });

        it('should navigate RHS panel without replacing document URL', function() {
            sandbox.stub(layout.context.parent, 'isDataFetched').returns(true);
            layout.navigateLayout('new-fake-id-value');

            expect(apiStub).toHaveBeenCalledWith('read', 'Dashboards', {
                view_name: 'records',
                id: 'new-fake-id-value'
            });
        });
    });

    describe('Multi-line Dashboard', function() {
        var apiStub;
        var parentLayout;
        var parentModule;

        beforeEach(function() {
            apiStub = sandbox.stub(app.api, 'records').callsFake(
                function(method, module, data, params, callbacks, options) {
                    callbacks.success();
                    callbacks.complete();
                }
            );

            parentModule = 'Cases';
            context = new app.Context({
                module: parentModule,
                layout: 'multi-line'
            });
            parentLayout = app.view.createLayout({
                name: 'records',
                type: 'records',
                module: parentModule,
                context: context
            });
            layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null,
                parentLayout.context.getChildContext({
                    module: 'Dashboards'
                }), true);
            layout.context.parent = context;
            sandbox.stub(layout, '_renderEmptyTemplate');
            parentLayout.addComponent(layout);
        });

        afterEach(function() {
            parentLayout.dispose();
            parentModule = null;
        });

        it('should set the header def for multi-line side drawer', function() {
            layout.layout.context.parent = new SUGAR.App.Bean({layout: 'multi-line'});
            var layoutSpy = sandbox.spy(layout.layout, 'initComponents');
            layout.navigateLayout('fake-id-value');
            var componentsDef = [{
                view: 'side-drawer-headerpane',
                loadModule: 'Dashboards'
            }, {
                layout: 'dashlet-main'
            }, {
                view: 'dashboard-fab',
                loadModule: 'Dashboards'
            }];
            expect(layoutSpy.args[0][0][0].layout.components).toEqual(componentsDef);
        });

        it('should fetch multi-line dashboard', function() {
            sandbox.stub(layout.context.parent, 'isDataFetched').returns(true);
            layout.navigateLayout('new-fake-id-value');

            expect(apiStub).toHaveBeenCalledWith('read', 'Dashboards', {
                view_name: 'multi-line',
                id: 'new-fake-id-value'
            });
        });
    });

    describe('Focus Dashboard', function() {
        var apiStub;
        var parentLayout;
        var parentModule;

        beforeEach(function() {
            apiStub = sandbox.stub(app.api, 'records').callsFake(
                function(method, module, data, params, callbacks, options) {
                    callbacks.success();
                    callbacks.complete();
                }
            );

            parentModule = 'Cases';
            context = new app.Context({
                module: parentModule,
                layout: 'focus'
            });
            parentLayout = app.view.createLayout({
                name: 'records',
                type: 'records',
                module: parentModule,
                context: context
            });
            layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null,
                parentLayout.context.getChildContext({
                    module: 'Dashboards'
                }), true);
            layout.context.parent = context;
            sandbox.stub(layout, '_renderEmptyTemplate');
            parentLayout.addComponent(layout);
        });

        afterEach(function() {
            parentLayout.dispose();
            parentModule = null;
        });

        it('should set the header def for focus drawer', function() {
            layout.layout.context.parent = new SUGAR.App.Bean({layout: 'focus'});
            var layoutSpy = sandbox.spy(layout.layout, 'initComponents');
            layout.navigateLayout('fake-id-value');
            var componentsDef = [{
                view: 'side-drawer-headerpane',
                loadModule: 'Dashboards'
            }, {
                layout: 'dashlet-main'
            }, {
                view: 'dashboard-fab',
                loadModule: 'Dashboards'
            }];
            expect(layoutSpy.args[0][0][0].layout.components).toEqual(componentsDef);
        });

        it('should fetch focus dashboard', function() {
            sandbox.stub(layout.context.parent, 'isDataFetched').returns(true);
            layout.navigateLayout('new-fake-id-value');

            expect(apiStub).toHaveBeenCalledWith('read', 'Dashboards', {
                view_name: 'focus',
                id: 'new-fake-id-value'
            });
        });
    });

    describe('initialize', function() {
        describe('Home dashboard', function() {
            var keyStub;
            var setKeyStub;

            beforeEach(function() {
                context = new app.Context({
                    module: 'Home',
                    layout: 'dashboard',
                    modelId: 'testId'
                });

                keyStub = sandbox.stub(app.user.lastState, 'key');
                setKeyStub = sandbox.stub(app.user.lastState, 'set');
            });

            it('should initialize an existing Home dashboard and save it as last visit', function() {
                keyStub.returns('Home.key');

                layout = app.view.createLayout({
                    type: 'dashboard',
                    name: 'dashboard',
                    context: context,
                    module: 'Dashboards',
                    loadModule: 'Dashboards'
                });

                expect(keyStub).toHaveBeenCalledWith('Home.', layout);
                expect(context.get('model').get('id')).toEqual('testId');
                expect(setKeyStub).toHaveBeenCalledWith('Home.key', 'testId');
            });
        });

        describe('RHS dashboard', function() {
            var parentLayout;
            var childContext;

            beforeEach(function() {
                context = new app.Context({
                    module: 'Accounts',
                    layout: 'records'
                });
                parentLayout = app.view.createLayout({
                    name: 'records',
                    type: 'records',
                    module: 'Accounts',
                    context: context
                });

                childContext = parentLayout.context.getChildContext({
                    module: 'Dashboards'
                });
            });

            afterEach(function() {
                parentLayout.dispose();
                childContext = null;
            });

            it('should initialize an existing RHS dashboard', function() {
                childContext.set('modelId', 'testId');
                layout = app.view.createLayout({
                    type: 'dashboard',
                    name: 'dashboard',
                    context: childContext,
                    module: 'Dashboards'
                });

                expect(childContext.get('model').get('id')).toEqual('testId');
            });

            it('should initialize a new default RHS dashboard', function() {
                var sidebarLayout = SugarTest.createLayout('base', 'Accounts', 'default', {name: 'sidebar'}, context);

                layout = app.view.createLayout({
                    type: 'dashboard',
                    name: 'dashboard',
                    context: childContext,
                    module: 'Dashboards'
                });
                sandbox.stub(layout, 'closestComponent').withArgs('sidebar').returns(sidebarLayout);

                layout.initialize({
                    context: childContext,
                    meta: {
                        method: 'record'
                    }
                });

                expect(layout.dashboardVisibleState).toEqual('open');
                sidebarLayout.trigger('sidebar:state:changed', 'close');
                expect(layout.dashboardVisibleState).toEqual('close');

                expect(childContext.isCreate()).toBeTruthy();
                sidebarLayout.dispose();
            });
        });

        describe('Search facet dashboard', function() {
            var keyStub;
            var setKeyStub;
            var facetDashboardModel;

            beforeEach(function() {
                context = new app.Context({
                    module: 'Home',
                    modelId: 'search'
                });
                context.parent = new app.Context({
                    layout: 'search',
                    search: true
                });

                facetDashboardModel = new app.data.createBean('Home', {
                    view_name: 'search',
                    module: 'Home'
                });
                var contextBro = new app.Context({
                    module: 'Home',
                    collection: app.data.createBeanCollection('Home', [facetDashboardModel])
                });
                sandbox.stub(context.parent, 'getChildContext')
                    .withArgs({module: 'Home'}).returns(contextBro);
                keyStub = sandbox.stub(app.user.lastState, 'key');
                setKeyStub = sandbox.stub(app.user.lastState, 'set');
            });

            it('should initialize an existing search facet dashboard', function() {
                facetDashboardModel.dashboardModule = 'Home';
                keyStub.returns('Home.search.key');

                layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null, context, true);

                expect(keyStub).toHaveBeenCalledWith('Home.search', layout);
                expect(context.get('model')).toEqual(facetDashboardModel);
                expect(context.get('skipFetch')).toBeTruthy();
                expect(setKeyStub).toHaveBeenCalledWith('Home.search.key', 'search');
            });
        });
    });

    describe('loadData', function() {
        it('should load data after its parent context is synced', function() {
            context = new app.Context({
                module: 'Accounts',
                layout: 'records'
            });
            var parentLayout = app.view.createLayout({
                name: 'records',
                type: 'records',
                module: 'Accounts',
                context: context
            });
            layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null,
                parentLayout.context.getChildContext({
                    module: 'Dashboards'
                }), true
            );
            var collection = app.data.createBeanCollection('Accounts',
                [app.data.createBean('Accounts')]
            );
            context.set('collection', collection);
            var superStub = sandbox.stub(layout, '_super');

            sandbox.stub(collection, 'once').withArgs('sync').yieldsOn(layout);

            layout.loadData({fetch: true});

            expect(superStub.lastCall.args[1][0]).toEqual({fetch: true});

            parentLayout.dispose();
        });

        it('should navigate to search facet dashboard', function() {
            context = new app.Context({
                module: 'Home',
                modelId: 'search'
            });
            context.parent = new app.Context({
                layout: 'search',
                search: true
            });
            layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null, context, true);
            var fakeMetadata = {
                components: [
                    {
                        row: [],
                        width: 12
                    }
                ]
            };
            sandbox.stub(app.metadata, 'getLayout').withArgs('Home', 'search-dashboard').returns({
                metadata: fakeMetadata
            });
            var navigateLayoutStub = sandbox.stub(layout, 'navigateLayout');

            layout.loadData({});

            expect(layout.context.get('skipFetch')).toBeTruthy();
            expect(layout.collection.models[0].get('metadata')).toEqual(fakeMetadata);
            expect(navigateLayoutStub).toHaveBeenCalledWith('search');
        });
    });

    describe('Custom Error Handlers', function() {
        var redirectStub;
        var fragmentStub;

        beforeEach(function() {
            context = new app.Context({
                module: 'Dashboards',
                layout: 'dashboard'
            });
            layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null, context, true);
            redirectStub = sandbox.stub(app.router, 'redirect');
            fragmentStub = sandbox.stub(Backbone.history, 'getFragment');
        });

        using('different routes', [
            {
                route: 'Home/test',
                redirectCalled: true
            },
            {
                route: 'test',
                redirectCalled: false
            }
        ], function(value) {
            it('should redirect depending on the route when handleNotFoundError is invoked', function() {
                fragmentStub.returns(value.route);
                layout.error.handleNotFoundError();
                expect(redirectStub.called).toBe(value.redirectCalled);
            });
        });

        it('should return false when handleValidationError is invoked', function() {
            expect(layout.error.handleValidationError()).toBe(false);
        });
    });

    describe('navigateLayout', function() {
        var parentLayout;
        var initComponentsStub;
        var removeComponentStub;
        var loadDataStub;

        beforeEach(function() {
            context = new app.Context({
                module: 'Accounts',
                layout: 'records'
            });
            parentLayout = app.view.createLayout({
                name: 'records',
                type: 'records',
                module: 'Accounts',
                context: context
            });
            layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null,
                parentLayout.context.getChildContext({
                    module: 'Dashboards'
                }, true)
            );
            parentLayout.addComponent(layout);

            sandbox.stub(layout, 'dispose');
            sandbox.stub(layout, 'getLastStateKey').returns('Last State Key');

            initComponentsStub = sandbox.stub(layout.layout, 'initComponents');
            removeComponentStub = sandbox.stub(layout.layout, 'removeComponent');
            loadDataStub = sandbox.stub(layout.layout, 'loadData');
            sandbox.stub(layout.layout, 'render');
        });

        afterEach(function() {
            parentLayout.dispose();
        });

        it('should load components for navigating to default dashboard layout', function() {
            var expectedComponent = {
                layout: {
                    type: 'dashboard',
                    components: [],
                    last_state: {
                        id: 'last-visit'
                    }
                },
                context: {
                    module: 'Home',
                    forceNew: true
                },
                loadModule: 'Dashboards'
            };
            layout.navigateLayout('list');
            expect(initComponentsStub.lastCall.args[0][0]).toEqual(expectedComponent);
            expect(removeComponentStub).toHaveBeenCalledWith(0);
            expect(loadDataStub.lastCall.args[0]).toEqual({});
        });

        it('should load components for a new dashboard layout', function() {
            var expectedComponent = {
                layout: {
                    type: 'dashboard',
                    components: [
                        {
                            view: 'dashboard-headerpane',
                            loadModule: 'Dashboards'
                        },
                        {
                            layout: 'dashlet-main'
                        },
                        {
                            view: 'dashboard-fab',
                            loadModule: 'Dashboards'
                        }
                    ],
                    last_state: {
                        id: 'last-visit'
                    }
                },
                context: {
                    module: 'Home',
                    forceNew: true,
                    create: true
                },
                loadModule: 'Dashboards'
            };
            layout.navigateLayout('create');
            expect(initComponentsStub.lastCall.args[0][0]).toEqual(expectedComponent);
        });

        it('should load components for an existing dashboard layout', function() {
            sandbox.stub(app.user.lastState, 'set');
            var expectedComponent = {
                layout: {
                    type: 'dashboard',
                    components: [
                        {
                            view: 'dashboard-headerpane',
                            loadModule: 'Dashboards'
                        },
                        {
                            layout: 'dashlet-main'
                        },
                        {
                            view: 'dashboard-fab',
                            loadModule: 'Dashboards'
                        }
                    ],
                    last_state: {
                        id: 'last-visit'
                    }
                },
                context: {
                    module: 'Home',
                    forceNew: true,
                    modelId: 'model_id'
                },
                loadModule: 'Dashboards'
            };
            layout.navigateLayout('model_id');
            expect(initComponentsStub.lastCall.args[0][0]).toEqual(expectedComponent);
            expect(app.user.lastState.set).toHaveBeenCalledWith('Last State Key', 'model_id');
        });
    });

    describe('initComponents', function() {
        beforeEach(function() {
            context = new app.Context({
                module: 'Dashboards',
                layout: 'dashboard'
            });
            layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null, context, true);
            sandbox.stub(layout.model, 'trigger');
            sandbox.stub(layout, 'isSearchContext').returns(true);
            sandbox.stub(layout, '_super');
        });

        it('should trigger "change:metadata" on the model if we are in the search results page', function() {
            layout.initComponents('hello-world');

            expect(layout.model.trigger).toHaveBeenCalledWith('change:metadata');
        });
    });

    describe('loadData in search results page', function() {
        beforeEach(function() {
            context = new app.Context({
                module: 'Dashboards',
                layout: 'dashboard'
            });
            layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null, context, true);
            sandbox.spy(layout.model, 'trigger');
            sandbox.stub(layout, 'isSearchContext').returns(true);
            sandbox.stub(layout, '_getInitialDashboardMetadata');
            sandbox.stub(layout, 'navigateLayout');
        });

        it('should return if the model already has the metadata property', function() {
            layout.model.set('metadata', '');
            layout.loadData();
            expect(layout.navigateLayout).not.toHaveBeenCalled();
        });

        it('should set "skipFetch" and "currentDashboardIndex" in the context, and call "navigateLayout"', function() {
            layout.loadData();
            expect(layout.navigateLayout).toHaveBeenCalled();
            expect(layout.context.get('skipFetch')).toBe(true);
        });
    });

    describe('setDefaultDashboard', function() {
        var parentModule;
        var parentLayout;
        var childContext;
        var getLastStateKeyStub;
        var getLastStateStub;
        var setLastStateStub;
        var navigateStub;

        beforeEach(function() {
            parentModule = 'Accounts';
            context = new app.Context({
                module: parentModule,
                layout: 'records'
            });
            parentLayout = app.view.createLayout({
                name: 'records',
                type: 'records',
                module: parentModule,
                context: context
            });
            childContext = parentLayout.context.getChildContext({
                module: 'Dashboards'
            });
            layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null, childContext, true);

            getLastStateKeyStub = sandbox.stub(layout, 'getLastStateKey');
            getLastStateStub = sandbox.stub(app.user.lastState, 'get');
            setLastStateStub = sandbox.stub(app.user.lastState, 'set');
            navigateStub = sandbox.stub(layout, 'navigateLayout');
        });

        afterEach(function() {
            parentLayout.dispose();
            childContext = null;
        });

        it('should select the last viewed dashboard', function() {
            var firstModel = app.data.createBean('Dashboards', {
                id: '1',
                name: 'first Dashboard',
                my_favorite: true
            });
            var secondModel = app.data.createBean('Dashboards', {
                id: '2',
                name: 'second Dashboard',
                my_favorite: true
            });
            sandbox.stub(layout, 'getComponent');

            layout.collection = app.data.createBeanCollection('Dashboards',
                [firstModel, secondModel]
            );
            getLastStateKeyStub.returns('1');
            getLastStateStub.withArgs('1').returns('1');

            layout.setDefaultDashboard();

            expect(setLastStateStub).toHaveBeenCalledWith('1', '');
            expect(navigateStub).toHaveBeenCalledWith(firstModel.id);
        });

        it('should select the default dashboard modified most recently', function() {
            var firstModel = {
                id: '1',
                name: 'first Dashboard',
                default_dashboard: true,
                my_favorite: true
            };
            var secondModel = {
                id: '2',
                name: 'second Dashboard',
                my_favorite: true
            };
            layout.collection = app.data.createBeanCollection('Dashboards',
                [firstModel, secondModel]
            );

            getLastStateKeyStub.returns('3');
            getLastStateStub.withArgs('3').returns('3'); // no dashboard should be found

            layout.setDefaultDashboard();

            expect(navigateStub).toHaveBeenCalledWith(firstModel.id);
        });

        it('should select the last modified favorite dashboard', function() {
            var firstModel = {
                id: '1',
                name: 'first Dashboard',
                my_favorite: true
            };
            var secondModel = {
                id: '2',
                name: 'second Dashboard',
                my_favorite: true
            };
            // none of the dashboards in collection are default dashboards
            layout.collection = app.data.createBeanCollection('Dashboards',
                [firstModel, secondModel]
            );

            getLastStateKeyStub.returns('3');
            getLastStateStub.withArgs('3').returns('3'); // no dashboard should be found

            layout.setDefaultDashboard();

            expect(navigateStub).toHaveBeenCalledWith(firstModel.id);
        });

        it('should render dashboard-empty template', function() {
            var renderEmptyTemplateStub = sandbox.stub(layout, '_renderEmptyTemplate');
            layout.$el.html = sandbox.stub();

            layout.setDefaultDashboard();

            expect(renderEmptyTemplateStub).toHaveBeenCalled()
        });
    });

    describe('handleSave', function() {
        var saveStub;

        beforeEach(function() {
            context = new app.Context({
                module: 'Dashboards',
                layout: 'dashboard'
            });
            layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null, context, true);
            saveStub = sandbox.stub(layout.model, 'save');
        });

        it('should navigate to the new Home Dashboard model on save success', function() {
            var navigateStub = sandbox.stub(app, 'navigate');
            context.set('create', true);
            saveStub.yieldsToOn('success', layout);

            layout.handleSave();

            expect(saveStub.lastCall.args[0].my_favorite).toBeTruthy();
            expect(navigateStub).toHaveBeenCalledWith(layout.context, layout.model);
        });

        it('should navigate to the new RHS Dashboard model on save success', function() {
            var parentContext = new app.Context({
                module: 'Accounts',
                layout: 'records'
            });
            var contextBro = parentContext.getChildContext({
                module: 'Home',
                layout: 'dashboard'
            });

            var navigateStub = sandbox.stub(layout, 'navigateLayout');
            contextBro.set('collection', app.data.createBeanCollection('Dashboards', []));
            context.set('create', true);
            layout.context.parent = parentContext;
            saveStub.yieldsToOn('success', layout);

            layout.handleSave();

            expect(contextBro.get('collection').models[0]).toEqual(layout.model);
            expect(navigateStub).toHaveBeenCalled();
        });

        it('should trigger an event for an existing dashboard being saved successfully', function() {
            var triggerStub = sandbox.stub(context, 'trigger');
            layout.model.set('id', 'model_id');
            saveStub.yieldsToOn('success', layout);

            layout.handleSave();

            expect(saveStub.lastCall.args[0].my_favorite).toBeUndefined();
            expect(triggerStub).toHaveBeenCalledWith('record:set:state', 'view');
        });

        it('should show an error alert when saving fails', function() {
            var alertStub = sandbox.stub(app.alert, 'show');

            saveStub.yieldsToOn('error', layout);

            layout.handleSave();

            expect(alertStub).toHaveBeenCalled();
        });
    });

    describe('FilterSharing', function() {
        var Bean = SUGAR.App.Bean;
        var mockMeta = {
            components: [{
                rows: [
                    [{
                        width: 12,
                        context: {
                            module: 'Accounts'
                        },
                        view: {
                            label: 'Filtered Accounts',
                            type: 'dashablelist',
                            module: 'Accounts',
                            last_state: {
                                id: 'dashable-list'
                            },
                            intelligent: 0,
                            limit: 5,
                            filter_id: 'ba87e3c0-6ff5-11e9-bce2-10ddb1df9244',
                            display_columns: ['name', 'phone_office', 'assigned_user_name', 'email']
                        }
                    }]
                ],
                width: 12
            }]
        };
        var dashboardTeams = [{
            name: 'Administrator',
            name_2: '',
            id: '4b0f0c96-6fe4-11e9-84d2-10ddb1df9244'
        }, {
            name: 'Jim',
            name_2: 'Brennan',
            id: '4dc2d31e-6fe4-11e9-9341-10ddb1df9244'
        }, {
            name: 'Will',
            name_2: 'Westin',
            id: '4e790e04-6fe4-11e9-93cc-10ddb1df9244'
        }];
        var filterData;
        var returnValue;

        beforeEach(function() {
            filterData = {
                name: 'testfilter',
                id: 'ba87e3c0-6ff5-11e9-bce2-10ddb1df9244',
                created_by: '1',
                created_by_name: 'Administrator',
                team_name: [{
                    id: '1',
                    name: 'Global',
                    name_2: ''
                }, {
                    name: 'Administrator',
                    name_2: '',
                    id: '4b0f0c96-6fe4-11e9-84d2-10ddb1df9244'
                }, {
                    name: 'Jim',
                    name_2: 'Brennan',
                    id: '4dc2d31e-6fe4-11e9-9341-10ddb1df9244'
                }],
                assigned_user_id: '',
                assigned_user_name: ''
            };
            context = new app.Context({
                module: 'Dashboards',
                layout: 'dashboard'
            });
            returnValue = {
                Contacts: {
                    filters: {
                        basic: {
                            meta: {
                                filters: [
                                    {id: 'all_records'},
                                    {id: 'assigned_to_me'},
                                    {id: 'favorites'}
                                ]
                            }
                        }
                    }
                }
            };
            layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null, context, true);
            SugarTest.loadPlugin('FilterSharing');
            SugarTest.app.plugins.attach(layout, 'layout');
            SugarTest.seedMetadata(true);
        });

        afterEach(function() {
            filterData = null;
            sinon.restore();
        });

        it('should identify the layout as a dashboard', function() {
            expect(layout.isDashboard()).toEqual(true);
        });

        it('should be able to verify if a filter is custom or not', function() {
            sinon.stub(app.metadata, 'getModules').returns(returnValue);
            expect(layout.isCustomFilter('assigned_to_me')).toEqual(false);
            expect(layout.isCustomFilter('ba87e3c0-6ff5-11e9-bce2-10ddb1df9244')).toEqual(true);
        });

        it('should be able to get metadata filter ids from meta', function() {
            sinon.stub(app.metadata, 'getModules').returns(returnValue);
            expect(layout.getMetadataFilterIds()).toEqual(['all_records','assigned_to_me','favorites']);
        });

        it('should be able to get metadata filter ids from model', function() {
            returnValue = {Contacts: {}, Accounts: {}};
            sinon.stub(app.metadata, 'getModules').returns(returnValue);
            expect(layout.getMetadataFilterIds()).toEqual([]);
        });

        it('should be able to verify if a dashlet is a list view dashlet', function() {
            var dashlet = mockMeta.components[0].rows[0][0];
            var filterid = dashlet.view.filter_id;

            dashlet.view.type = 'chart';
            expect(layout.isFilteredListViewDashlet(dashlet)).toBeFalsy();

            delete dashlet.view.filter_id;
            dashlet.view.type = 'dashablelist';
            expect(layout.isFilteredListViewDashlet(dashlet)).toBeFalsy();

            dashlet.view.filter_id = filterid;
            expect(layout.isFilteredListViewDashlet(dashlet)).toEqual(true);
        });

        it('should find the custom filer id placed on a list view dashlet', function() {
            layout.model.set({
                metadata: mockMeta,
                team_name: dashboardTeams,
                assigned_user_id: '1'
            });
            expect(layout.getListViewFilterIds()).toEqual(['ba87e3c0-6ff5-11e9-bce2-10ddb1df9244']);
        });

        it('should remove the global team from a shared filter', function() {
            var filterModel = layout.getPrivateFilter(filterData);
            var teams = filterModel.get('team_name');
            var globalTeam = _.findWhere(teams, {id: '1'});
            expect(globalTeam).toBeFalsy();
        });

        it('should update the list view dashlet filter with the teams the dashboard is shared with', function() {
            var filterModel = layout.getPrivateFilter(filterData);
            var hasBeenModified = layout.updateFilterTeams(filterModel, dashboardTeams);

            expect(hasBeenModified).toBeTruthy();
            expect(filterModel.get('team_name')).toEqual(dashboardTeams);

            hasBeenModified = layout.updateFilterTeams(filterModel, dashboardTeams[0]);
            expect(hasBeenModified).toBeTruthy();
            expect(filterModel.get('team_name')).toEqual(dashboardTeams[0]);
        });

        it('should not update the list view dashlet filter with when there are no changes', function() {
            filterData.team_name = dashboardTeams;
            var filterModel = layout.getPrivateFilter(filterData);
            var hasBeenModified = layout.updateFilterTeams(filterModel, dashboardTeams);

            expect(hasBeenModified).toBeFalsy();
            expect(filterModel.get('team_name')).toEqual(dashboardTeams);
        });

        it('should save a filter that has been updated with the teams shared with', function() {
            var filterModel = new Bean(filterData);
            sinon.stub(layout, 'getPrivateFilter').callsFake(function() {
                return filterModel;
            });
            var saveStub = sinon.stub(filterModel, 'save');
            layout.updateListViewFilters({
                dashboardTeams: dashboardTeams,
                assignedUserId: '1'
            }, filterData);
            expect(saveStub).toHaveBeenCalled();
        });
    });

    describe('restoreConsoleDashlets', function() {
        var getStub;
        beforeEach(function() {
            context = new app.Context({
                module: 'Home',
                layout: 'dashboard'
            });
            layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null, context, true);
            sinon.stub(app.api, 'buildURL').returns('testURL');
            layout.layout = {
                getComponent: sinon.stub().returns({
                    getComponent: sinon.stub().returns({
                        model: {
                            set: sinon.stub(),
                            setSyncedAttributes: sinon.stub(),
                            off: $.noop
                        }
                    })
                }),
                closestComponent: sinon.stub(),
                off: $.noop
            };
            sinon.stub(layout.model, 'get')
                .withArgs('id').returns('123');

            sinon.stub(app.api, 'call');

            layout.metaFileNames = {'123': 'test'};
        });

        it('should call buildUrl function', function() {
            layout.restoreConsoleDashlets(0);

            expect(app.api.buildURL).toHaveBeenCalledWith(
                'Dashboards',
                'restore-tab-metadata',
                {id: '123'},
                {dashboard: 'test', tab_index: 0, dashboard_module: 'Home'});
        });

        it('should call api', function() {
            layout.restoreConsoleDashlets(0);

            expect(app.api.call).toHaveBeenCalledWith('update', 'testURL');
        });
    });
});
