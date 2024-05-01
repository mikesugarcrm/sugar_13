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
describe('DashletSearchControls plugin', function() {
    var app;
    var context;
    var layout;
    var view;
    var dashboard;
    var moduleName = 'Accounts';
    var layoutName = 'dashlet';
    var viewName = 'purchase-history';

    beforeEach(function() {
        app = SugarTest.app;
        app.sideDrawer = {
            isOpen: () => false
        };
        // Load necessary components for the test
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'layout', 'dashlet');
        SugarTest.loadComponent('base', 'layout', 'dashboard');
        SugarTest.loadComponent('base', 'view', 'active-subscriptions');

        // Initialize test view metadata
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

        // Load the necessary plugins
        SugarTest.loadPlugin('Dashlet');
        SugarTest.loadPlugin('DashletSearchControls');

        // Create the test view object
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
        SugarTest.testMetadata.dispose();
        app.sideDrawer = null;
        app.data.reset();
        app.view.reset();
        view.dispose();
        layout.dispose();
        dashboard.dispose();
        app.cache.cutAll();
        view = null;
        layout = null;
        dashboard = null;
    });

    describe('onAttach', function() {
        it('should set up the controls if the required fields are present', function() {
            view.sortItems = [{
                id: 'key',
                text: 'value'
            }];
            view.searchFieldPlaceholder = 'my search placeholder';

            app.plugins.attach(view, 'view');
            var initDashletControlsStub = sinon.stub(view, '_initDashletControls');

            view.trigger('init');

            expect(initDashletControlsStub).toHaveBeenCalled();
        });

        it('should not set up the controls if the required fields are missing', function() {
            app.plugins.attach(view, 'view');
            var initDashletControlsStub = sinon.stub(view, '_initDashletControls');

            view.trigger('init');

            expect(initDashletControlsStub).not.toHaveBeenCalled();
        });
    });

    describe('_initDashletControls', function() {
        it('should create dashlet controls if the dashlet is not in config mode', function() {
            var createViewStub = sinon.stub(app.view, 'createView');
            var addComponentStub = sinon.stub(view.layout, 'addComponent');

            view._initDashletControls();

            expect(createViewStub).toHaveBeenCalled();
            expect(addComponentStub).toHaveBeenCalled();
        });

        it('should not create dashlet controls if the dashlet is in config mode', function() {
            view._mode = 'config';

            var createViewStub = sinon.stub(app.view, 'createView');
            var addComponentStub = sinon.stub(view.layout, 'addComponent');

            view._initDashletControls();

            expect(createViewStub).not.toHaveBeenCalled();
            expect(addComponentStub).not.toHaveBeenCalled();
        });
    });

    describe('_listenForEvents', function() {
        it('should not set up events if the required functions are not defined', function() {
            view.applySort = false;
            view.applySearch = false;

            var listenStub = sinon.stub(view, 'listenTo');

            view._listenForEvents();

            expect(listenStub).not.toHaveBeenCalled();
        });

        it('should set up events if the required functions are defined', function() {
            view.applySort = function() {};
            view.applySearch = function() {};

            var listenStub = sinon.stub(view, 'listenTo');

            view._listenForEvents();

            expect(listenStub).toHaveBeenCalledWith(view.layout, 'dashlet:controls:sort', view.applySort);
            expect(listenStub).toHaveBeenCalledWith(view.layout, 'dashlet:controls:search', view.applySearch);
        });

        it('should not set up events if the dashlet is in config mode', function() {
            view._mode = 'config';

            var listenStub = sinon.stub(view, 'listenTo');

            view._listenForEvents();

            expect(listenStub).not.toHaveBeenCalled();
        });
    });
});
