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
describe('Base.Layout.Preview', function() {
    var app;
    var drawer;
    var testLayout;
    var module = 'Contacts';
    var parentLayout;
    var testMeta = {
        lazy_loaded: true,
        components: [
            {
                'view': {'type': 'preview-header'},
            },
            {
                'view': {'type': 'preview'},
            },
            {
                'layout': {'type': 'preview-activitystream'},
                'context': {
                    'module': 'Activities',
                    'forceNew': true,
                }
            },
        ]
    };

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
        drawer = app.drawer;
        app.drawer = {
            isActive: function() {
                return true;
            }
        };

        parentLayout = SugarTest.createLayout('base', null, 'default', app.controller.context);
        // We need to pass a copy of 'testMeta' since initializing the layout will empty the components array.
        var testMetaCopy = app.utils.deepCopy(testMeta);

        testLayout = SugarTest.createLayout(
            'base',
            module,
            'preview',
            testMetaCopy,
            app.controller.context,
            false,
            {
                layout: parentLayout
            }
        );
    });

    afterEach(function() {
        app.drawer = drawer;
        sinon.restore();
        SugarTest.testMetadata.dispose();
        testLayout.dispose();
        testLayout = null;
        parentLayout.dispose();
        parentLayout = null;
    });

    it('should have its own context', function() {
        expect(testLayout.context.parent).toEqual(app.controller.context);
        expect(testLayout.context).not.toEqual(app.controller.context);
    });
    describe('_toggle', function() {
        using('different `_hidden` states and preview context configurations', [
            {
                // Panel hidden, with the same model.
                hidden: true,
                sameModel: true,
                expect: {
                    init: false,
                    show: true,
                    hide: false
                }
            },
            {
                // Panel shown, with the same model.
                hidden: false,
                sameModel: true,
                expect: {
                    init: false,
                    show: false,
                    hide: true
                }
            },
            {
                // Panel hidden, with a different model.
                hidden: true,
                sameModel: false,
                expect: {
                    init: true,
                    show: false,
                    hide: false
                }
            },
            {
                // Panel shown, with a different model.
                hidden: false,
                sameModel: false,
                expect: {
                    init: true,
                    show: false,
                    hide: false
                }
            },
        ], function(provider) {
            it('should decide whether to initialize the preview panel, show it, or hide it', function() {
                testLayout._hidden = provider.hidden;

                var model = app.data.createBean();
                var collection = app.data.createBeanCollection();
                if (provider.sameModel) {
                    testLayout.context.set('model', model);
                }

                var initPreviewStub = sinon.stub(testLayout, '_initPreviewPanel');
                var showStub = sinon.stub(testLayout, 'showPreviewPanel');
                var hideStub = sinon.stub(testLayout, 'hidePreviewPanel');
                var decorateStub = sinon.stub(app.events, 'trigger').withArgs('list:preview:decorate');

                testLayout._toggle(model, collection);

                expect(initPreviewStub.calledWith(model, collection)).toBe(provider.expect.init);
                expect(showStub.called).toBe(provider.expect.show);
                expect(decorateStub.called).toBe(provider.expect.show);
                expect(hideStub.called).toBe(provider.expect.hide);

                testLayout.dispose();
            });
        });
    });
    describe('_initPreviewPanel', function() {
        var componentTypes = _.pluck(_.map(testMeta.components, function(value) {
            return value.view ? value.view : value.layout;
        }), 'type');

        using('different models', [
            {
                modelModule1: 'Contacts',
                modelModule2: 'Cases',
            }
        ], function(provider) {
            it('should reload context data when components already exist', function() {
                var collection = app.data.createBeanCollection(provider.modelModule1);
                var model = app.data.createBean(provider.modelModule1);
                model.set('id', 'test');

                var collection2 = app.data.createBeanCollection(provider.modelModule2);
                var model2 = app.data.createBean(provider.modelModule2);
                model2.set('id', 'test2');

                testLayout.context.set('module', provider.modelModule1);

                var initComponentsSpy = sinon.spy(testLayout, 'initComponents');
                var reloadDataStub = sinon.stub(testLayout.context, 'reloadData');
                var loadDataStub = sinon.stub(testLayout.context, 'loadData');

                // doesn't have components
                testLayout._initPreviewPanel(model, collection);
                expect(initComponentsSpy).toHaveBeenCalled();
                expect(loadDataStub).toHaveBeenCalled();

                // reset since it was called earlier
                initComponentsSpy.resetHistory();
                reloadDataStub.resetHistory();

                // has components now, but the model changed
                testLayout._initPreviewPanel(model2, collection);
                expect(reloadDataStub).toHaveBeenCalled();
            });
        });

        using('different types of modules', [
            {
                // Has not initialized its components yet.
                compInitialized: false,
                modelModule: module,
                contextModule: 'Accounts',
                expect: {
                    components: componentTypes,
                    init: true
                }
            },
            {
                // Has already initialized its components, the given model and
                // the context have a different module.
                compInitialized: true,
                modelModule: module,
                contextModule: 'Accounts',
                expect: {
                    components: componentTypes,
                    init: true
                }
            },
            {
                // Has already initialized its components, the given model has
                // the same module as the current context.
                compInitialized: true,
                modelModule: module,
                contextModule: module,
                expect: {
                    components: componentTypes,
                    init: false
                }
            },
        ], function(provider) {
            it('should decide whether to initialize and render its child components', function() {
                var collection = app.data.createBeanCollection(provider.modelModule);
                var model = app.data.createBean(provider.modelModule);
                model.set('id', 'test');

                testLayout.context.set('module', provider.contextModule);

                if (provider.compInitialized) {
                    testLayout.initComponents(testLayout._componentsMeta, testLayout.context, model.module);
                }

                var initComponentsSpy = sinon.spy(testLayout, 'initComponents');
                var renderSpy = sinon.spy(testLayout, 'render');
                var disposeSpy = sinon.spy(testLayout, '_disposeComponents');
                var showStub = sinon.stub(testLayout, 'showPreviewPanel');
                var decorateStub = sinon.stub(app.events, 'trigger').withArgs('list:preview:decorate');
                var loadDataStub = sinon.stub(testLayout.context, 'loadData');

                testLayout._initPreviewPanel(model, collection);

                expect(initComponentsSpy.calledWith(testLayout._componentsMeta, testLayout.context, model.module))
                    .toBe(provider.expect.init);
                expect(_.pluck(testLayout._components, 'type')).toEqual(provider.expect.components);
                expect(testLayout.context.get('model')).toBe(model);
                expect(renderSpy.called).toBe(provider.expect.init);
                expect(disposeSpy.called).toBe(provider.expect.init);
                expect(showStub).toHaveBeenCalled();
                expect(loadDataStub).toHaveBeenCalled();
                expect(decorateStub).toHaveBeenCalled();
            });
        });

        using('different app.drawer configurations', [
            {
                drawer: undefined,
                expectInit: true
            },
            {
                drawer: null,
                expectInit: true
            },
            {
                drawer: {
                    isActive: function() {
                        return false;
                    }
                },
                expectInit: false
            },
        ], function(provider) {
            it('should not initialize and render the child components if the layout is not in the foreground',
                function() {
                    let model = app.data.createBean(module);
                    let initPreviewStub = sinon.stub(testLayout, '_initPreviewPanel');

                    app.drawer = provider.drawer;
                    testLayout._toggle(model);

                    expect(initPreviewStub.called).toBe(provider.expectInit);
                }
            );
        });
    });
});
