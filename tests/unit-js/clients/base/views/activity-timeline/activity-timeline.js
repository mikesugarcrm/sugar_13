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
describe('View.Views.Base.ActivityTimelineView', function() {
    var app;
    var context;
    var layout;
    var layoutName = 'dashlet';
    var moduleName = 'Cases';
    var view;
    var viewName = 'activity-timeline';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'preview');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'layout', 'dashlet');
        SugarTest.loadComponent('base', 'layout', 'dashboard');

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition(
            viewName,
            {
                activity_modules: [
                    {
                        module: 'Calls',
                        fields: [
                            'name',
                            'status',
                        ],
                    },
                    {
                        module: 'Emails',
                        record_date: 'date_sent',
                        fields: [
                            'name',
                            'date_sent',
                        ],
                    },
                ],
            },
            moduleName
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
        sinon.stub(view, '_super');
    });

    afterEach(function() {
        delete app.plugins.plugins['view']['Dashlet'];
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

    describe('initialize', function() {
        beforeEach(function() {
            sinon.stub(app.template, 'getView').returns('<div>test</div>');
            view.meta.pseudo = true;
            view.initialize();
        });

        it('should call view._super with initialize', function() {
            expect(view._super).toHaveBeenCalledWith('initialize');
        });

        it('should get template from activity-timeline view', function() {
            expect(app.template.getView).toHaveBeenCalledWith('activity-timeline-base');
            expect(view.template).toEqual('<div>test</div>');
        });

        it('should add required classes to the element', function() {
            expect(view.$el.hasClass('dashlet-unordered-list')).toBeTruthy();
        });
    });

    describe('_render', function() {
        it('should inject singular module name to title', function() {
            var singular = 'Singular';
            var finalTitle = singular + ' Interactions';
            var langGetStub = sinon.stub(app.lang, 'get').returns(finalTitle);
            var langGetModuleNameStub = sinon.stub(app.lang, 'getModuleName').returns(singular);
            var layoutStub = sinon.stub(layout, 'setTitle');
            view.meta.label = 'LBL_TEST_LABEL';

            sinon.stub(view, 'initializeFilter');
            view._render();

            expect(view._super).toHaveBeenCalledWith('_render');
            // ensure we use getModuleName to get singular module name and setTitle to update dashlet title
            expect(app.lang.get.lastCall.args).toEqual([view.meta.label, view.module, {moduleSingular: singular}]);
            expect(app.lang.getModuleName.lastCall.args[0]).toEqual(view.module);
            expect(layout.setTitle.lastCall.args[0]).toEqual(finalTitle);
        });
    });
});
