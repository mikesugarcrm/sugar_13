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
describe('KBContents.Base.Views.RelatedDocuments', function() {

    var app, view, sandbox, context, meta, layout, moduleName = 'KBContents';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        context = app.context.getContext({
            module: moduleName
        });
        context.set('model', new Backbone.Model());
        context.parent = app.context.getContext({
            module: moduleName
        });
        meta = {
            config: false
        };
        layout = SugarTest.createLayout('base', moduleName, 'list', null, context.parent);
        SugarTest.loadPlugin('Dashlet');
        SugarTest.loadComponent(
            'base',
            'view',
            'related-documents',
            moduleName
        );
        SugarTest.loadHandlebarsTemplate(
            'related-documents',
            'view',
            'base',
            null,
            moduleName
        );
        view = SugarTest.createView(
            'base',
            moduleName,
            'related-documents',
            meta,
            context,
            moduleName,
            layout
        );
    });

    afterEach(function() {
        sandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        layout.dispose();
        Handlebars.templates = {};
        delete app.plugins.plugins['view']['Dashlet'];
        view = null;
        layout = null;
    });

    describe('initDashlet()', function() {
        var initSettingsStub, initCollectionStub;

        beforeEach(function() {
            initSettingsStub = sandbox.stub(view, '_initSettings');
            initCollectionStub = sandbox.stub(view, '_initCollection');
        });

        it('should call _initSettings() and _initCollection() when initDashlet', function() {
            view.meta.config = true;
            view.dashletConfig = {};
            view.dashletConfig.dashlet_config_panels = {};
            view.initDashlet({});
            expect(initSettingsStub).toHaveBeenCalled();
            expect(initCollectionStub).toHaveBeenCalled();
        });
    });

    describe('_initSettings()', function() {
        var defaultSettings, attributes, settingsSetStub;

        beforeEach(function() {
            defaultSettings = view._defaultSettings;
            attributes = view.settings.attributes;
            settingsSetStub = sandbox.stub(view.settings, 'set');
        });

        afterEach(function() {
            view._defaultSettings = defaultSettings;
            view.settings.attributes = attributes;
        });

        it('should use default settings when init settings', function() {
            view._defaultSettings = {
                name: 'Test1',
                id: 200,
                r2: 'red'
            };
            view.settings.attributes = {
                id: 34
            };
            view._initSettings();

            expect(settingsSetStub).toHaveBeenCalledWith({
                name: 'Test1',
                id: 34,
                r2: 'red'
            });
        });
    });

    describe('_initCollection()', function() {
        var collection, contextSetStub;

        beforeEach(function() {
            collection = view.collection;
            contextSetStub = sandbox.stub(view.context, 'set');
        });

        afterEach(function() {
            view.collection = collection;
        });

        it('should set collection to context when init collection', function() {
            view._initCollection();
            expect(contextSetStub).toHaveBeenCalled();
        });
    });

    describe('bindDataChange()', function() {
        var collectionOnStub, collection;

        beforeEach(function() {
            collectionOnStub = sinon.stub(view.collection, 'on').callsFake(function() {});
            collection = view.collection;
        });

        afterEach(function() {
            view.collection = collection;
        });

        it('should add add/remove/reset event listener on collection only when collection defined', function() {
            view.meta.config = true;
            view.bindDataChange();
            expect(collectionOnStub).toHaveBeenCalled();
        });

        it('should not add add/remove/reset event listener on collection when collection not defined', function() {
            view.meta.config = true;
            view.collection = undefined;
            view.bindDataChange();
            expect(collectionOnStub).not.toHaveBeenCalled();
        });
    });

    describe('loadMoreData()', function() {
        var paginateStub, next_offset;

        beforeEach(function() {
            paginateStub = sandbox.stub(view.collection, 'paginate');
            next_offset = view.collection.next_offset;
        });

        afterEach(function() {
            view.collection.next_offset = next_offset;
        });

        it('should paginate when next_offset is great then zero', function() {
            view.collection.next_offset = 1;
            view.loadMoreData();
            expect(paginateStub).toHaveBeenCalled();
        });

        it('should not paginate when next_offset is zero or less then zero', function() {
            view.collection.next_offset = 0;
            view.loadMoreData();
            expect(paginateStub).not.toHaveBeenCalled();
        });
    });

    describe('loadData()', function() {
        var fetchStub, dataFetched;

        beforeEach(function() {
            fetchStub = sandbox.stub(view.collection, 'fetch');
            dataFetched = view.collection.dataFetched;
        });

        afterEach(function() {
            view.collection.dataFetched = dataFetched;
        });

        it('should fetch data when data not fetched yet', function() {
            view.collection.dataFetched = false;
            view.loadData();
            expect(fetchStub).toHaveBeenCalled();
        });

        it('should not fetch data when data already fetched', function() {
            view.collection.dataFetched = true;
            view.loadData();
            expect(fetchStub).not.toHaveBeenCalled();
        });
    });

    describe('Render when collection changed', function() {
        var renderStub;
        beforeEach(function() {
            renderStub = sandbox.stub(view, 'render');
            view.bindDataChange();
        });

        it('should render when added item to collection', function() {
            view.collection.trigger('add');
            expect(renderStub).toHaveBeenCalled();
        });

        it('should render when reset collection', function() {
            view.collection.trigger('reset');
            expect(renderStub).toHaveBeenCalled();
        });

        it('should render when removed item from collection', function() {
            view.collection.trigger('remove');
            expect(renderStub).toHaveBeenCalled();
        });
    });
});
