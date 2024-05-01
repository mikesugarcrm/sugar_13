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
describe('Base.View.CommentlogDashlet', function() {
    var app;
    var context;
    var dashboard;
    var layout;
    var layoutName = 'dashlet';
    var moduleName = 'Cases';
    var dashletMeta = {
        fields: [
            {
                name: 'commentlog',
                type: 'commentlog',
                dashlet: true,
            }
        ]
    };
    var view;
    var viewName = 'commentlog-dashlet';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();

        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadHandlebarsTemplate('commentlog', 'field', 'base', 'dashlet');
        SugarTest.loadComponent('base', 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'layout', 'dashlet');
        SugarTest.loadComponent('base', 'layout', 'dashboard');

        SugarTest.testMetadata.addViewDefinition(
            viewName,
            dashletMeta
        );
        SugarTest.testMetadata.set();
        app.data.declareModels();
        SugarTest.loadPlugin('Dashlet');

        context = app.context.getContext();
        context.set({
            module: moduleName,
            layout: layoutName,
            rowModel: app.data.createBean(moduleName, {_module: moduleName}),
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
        Handlebars.templates = {};
        view = null;
        layout = null;
        dashboard = null;
    });

    it('should contain the Dashlet and Editable plugins', function() {
        expect(view.plugins).toContain('Dashlet');
        expect(view.plugins).toContain('Editable');
    });

    describe('extendedOptions', function() {
        it('should set correct limits for fetch options', function() {
            var extendedOptions = view.getExtendedOptions();
            expect(extendedOptions.limit).toEqual(3);
            extendedOptions = view.getExtendedOptions({loadAll: true});
            expect(extendedOptions.limit).toEqual(-1);
        });

        it('should call fetch', function() {
            var fetchCollectionStub = sinon.stub(view.collection, 'fetch');
            view.loadData();
            expect(fetchCollectionStub).toHaveBeenCalled();
        });
    });

    describe('hasUnsavedChanges', function() {
        using(
            'different comment texts',
            [
                {func: 'not a function', result: false},
                {func: function() { return ''; }, result: false},
                {func: function() { return 'Partial comment'; }, result: true},
            ],
            function(data) {
                it('should check for unsaved comments', function() {
                    view.render();
                    var field = view.getField('commentlog');
                    field.getCurrentCommentText = data.func;
                    expect(view.hasUnsavedChanges()).toBe(data.result);
                });
            }
        );
    });
});
