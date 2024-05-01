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
describe('KBContents.Base.Views.KBSDashletMostUseful', function() {

    var app, view, sandbox, context, meta, moduleName = 'KBContents';

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

        context.prepare();
        meta = {
            config: false
        };

        sandbox.stub(context.parent, 'get').callsFake(function() {
            return new Backbone.Collection();
        });
        layout = SugarTest.createLayout('base', moduleName, 'list', null, context.parent);
        SugarTest.loadPlugin('Dashlet');
        SugarTest.loadComponent(
            'base',
            'view',
            'kbs-dashlet-most-useful',
            moduleName
        );
        SugarTest.loadHandlebarsTemplate(
            'kbs-dashlet-most-useful',
            'view',
            'base',
            null,
            moduleName
        );
        view = SugarTest.createView(
            'base',
            moduleName,
            'kbs-dashlet-most-useful',
            meta,
            context,
            moduleName,
            layout
        );
        sandbox.stub(view.collection, 'sync');
    });

    afterEach(function() {
        sandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        Handlebars.templates = {};
        delete app.plugins.plugins['view']['Dashlet'];
        view = null;
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

        it('should fetch data on load', function() {
            fetchStub = sandbox.stub(view.collection, 'fetch');
            resetPaginationStub = sandbox.stub(view.collection, 'resetPagination');
            view.loadData();

            expect(resetPaginationStub).toHaveBeenCalled();
            expect(fetchStub).toHaveBeenCalled();
            expect(view.collection.getOption('params').mostUseful).toBeTruthy();
            expect(view.collection.getOption('params').next_offset).not.toBeDefined();
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
