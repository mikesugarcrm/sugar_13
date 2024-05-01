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
describe('modules.KBContents.clients.base.view.DashletNestesetList', function() {
    var moduleName = 'KBContents',
        app, view, sandbox, context, layout, treeData;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        context = app.context.getContext({
            module: moduleName
        });

        SugarTest.testMetadata.init();
        context.set('model', app.data.createBean(moduleName));
        context.set('collection', app.data.createBeanCollection(moduleName));
        context.set('module', moduleName);
        context.set('action', 'detail');
        SugarTest.loadPlugin('Dashlet');
        SugarTest.loadFile(
            '../modules/Categories/clients/base/plugins',
            'JSTree',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });
        SugarTest.loadFile(
            '../modules/Categories/clients/base/plugins',
            'NestedSetCollection',
            'js',
            function(d) {
                app.events.off('app:init');
                eval(d);
                app.events.trigger('app:init');
            });

        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'dashlet-nestedset-list', moduleName);
        SugarTest.testMetadata.set();

        sandbox.stub(app.metadata, 'getModule').callsFake(function(module, type) {
            if (type == 'config') {
                return {
                    category_root: null
                };
            }
            return {};
        });
        treeData = SugarTest.loadFixture('tree', '../tests/modules/Categories/fixtures');
        var viewMeta = {extra_provider: {module: moduleName, field: 'id'}};

        layout = SugarTest.createLayout('base', moduleName, 'dashboard');
        view = SugarTest.createView(
            'base', moduleName, 'dashlet-nestedset-list', viewMeta, context, true, layout
        );
        app.routing.start();
    });

    afterEach(function() {
        sandbox.restore();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        delete app.plugins.plugins['field']['Dashlet'];
        delete app.plugins.plugins['field']['NestedSetCollection'];
        delete app.plugins.plugins['field']['JSTree'];
        view = null;
        layout = null;
    });

    it('Get storage should return passed value.', function() {
        var expectedValue = 'fakeValue';
        view.categoryRoot = 'fakeRoot';
        view.moduleRoot = moduleName;

        expect(view._getStorage()('fakeKey', expectedValue)).toEqual(expectedValue);
    });

    it('Open record should navigate to the extra module.', function() {
        var navigateStub = sandbox.stub(app.router, 'navigate');

        sandbox.stub(view, 'openNode');
        sandbox.stub(view, 'closeNode');

        view.openRecord({type: 'folder'});
        expect(navigateStub).not.toHaveBeenCalled();

        view.extraModule.module = null;
        view.openRecord({type: 'document'});
        expect(navigateStub).not.toHaveBeenCalled();

        view.extraModule.module = moduleName;
        view.openRecord({type: 'document'});
        expect(navigateStub).toHaveBeenCalled();
    });

    it('Tree should load addition leafs for each collection model.', function() {
        var fakeCollection = new Backbone.Collection();
        var fetchStub = sandbox.stub(fakeCollection, 'fetch');
        view.loadedLeafs = {};
        view.extraModule.field = 'fakeField';
        view.collection = new app.NestedSetCollection(treeData);
        sandbox.stub(app.data, 'createBeanCollection').callsFake(function() {
            return fakeCollection;
        });

        view.treeLoaded();
        expect(fetchStub).toHaveBeenCalled();
    });

    it('Toggled folder should load children leafs.', function() {
        var loadLeafStub = sandbox.stub(view, 'loadAdditionalLeafs');
        view.collection = new app.NestedSetCollection(treeData);

        view.folderToggled({open: {}, id: '1'}, sandbox.stub());
        // The demo model with ID 1 has two children.
        // But we expect all children data to be loaded in single request.
        expect(loadLeafStub).toHaveBeenCalledOnce();
    });

    it('Click on folder should call leaf loading.', function() {
        view.render();
        var loadLeafStub = sandbox.stub(view, 'loadAdditionalLeafs');
        view.leafClicked({type: 'document'});
        expect(loadLeafStub).not.toHaveBeenCalled();

        view.leafClicked({type: 'folder'});
        expect(loadLeafStub).toHaveBeenCalled();
    });

    it('Open current parent should load a leaf.', function() {
        var loadLeafStub = sandbox.stub(view, 'loadAdditionalLeafs');
        view.extraModule.field = 'id';

        view.openCurrentParent();
        expect(loadLeafStub).toHaveBeenCalled();
    });

    it('The modelFieldChanged should change current field value.', function() {
        var actualVal = 'actualValue',
            expectedVal = 'expectedValue';
        view.loadedLeafs = {};
        view.loadedLeafs[actualVal] = {};
        view.currentFieldValue = actualVal;

        view.context.get('model').trigger('change:' + view.extraModule.field, {}, expectedVal);
        expect(view.currentFieldValue).toEqual(expectedVal);
    });

    it('Add leaf should save models in the loadedLeafs property.', function() {
        view.collection = new app.NestedSetCollection(treeData);
        var id = 'fakeId',
            models = view.collection.models;
        view.loadedLeafs = {};

        sandbox.stub(view, 'removeChildrens');
        sandbox.stub(view, 'hideChildNodes');
        sandbox.stub(view, 'insertNode');
        sandbox.stub(view, 'showChildNodes');

        view.addLeafs(models, id);
        expect(view.loadedLeafs[id].models).toEqual(models);
    });

    it('After load leafs callback must be called even if empty array of IDs passed to bulkLoadLeafs', function() {
        var afterLoadCallback = sinon.spy();
        var ids = [];
        view.bulkLoadLeafs(ids, afterLoadCallback);
        expect(afterLoadCallback).toHaveBeenCalled();
    });
});
