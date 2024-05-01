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
describe('KBContents.Base.Views.KBSDashletUsefulness', function() {

    var app, layout, view, sandbox, context, appControllerContextGetStub,
        model, moduleName = 'KBContents';

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
        model = new Backbone.Model();
        model.set('useful', 10);
        model.set('notuseful', 20);
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition(
            'kbs-dashlet-usefulness',
            {
                dashlets: [
                    {
                        config: {
                            module: moduleName
                        },
                        preview: {},
                        filter: {
                            module: [moduleName]
                        }
                    }
                ]
            },
            moduleName
        );
        SugarTest.testMetadata.set();
        app.data.declareModels();
        SugarTest.loadPlugin('Dashlet');
        SugarTest.loadHandlebarsTemplate(
            'kbs-dashlet-usefulness',
            'view',
            'base',
            null,
            moduleName
        );
        layout = SugarTest.createLayout(
            'base',
            moduleName,
            'list',
            null,
            context.parent
        );
        sandbox.stub(model, 'fetch').callsFake(function(options) {
            options.success(model);
            options.complete();
        });
        sandbox.stub(model, 'clone').callsFake(function() {
            return model;
        });
        appControllerContextGetStub = sandbox.stub(
            app.controller.context,
            'get').callsFake(function() {
                return model;
            }
        );
        view = SugarTest.createView(
            'base',
            moduleName,
            'kbs-dashlet-usefulness',
            null,
            context,
            moduleName,
            layout,
            true
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
        model = null;
        view = null;
        layout = null;
    });

    describe('initialize()', function() {
        var listenToStub;

        beforeEach(function() {
            listenToStub = sandbox.stub(view, 'listenTo');
        });

        it('should initialize charData when initialize', function() {
            view.initialize({});
            expect(view.chartData).toEqual(jasmine.any(Backbone.Model));
        });

        it('should initialize/bind refresh when initialize', function() {
            view.initialize({});
            expect(view.refresh).toBeDefined();
        });

        it('should attach event listeners to change events when initialize', function() {
            view.initialize({});
            expect(listenToStub).toHaveBeenCalled();
            expect(listenToStub.getCall(0).args[1]).toEqual('change:useful');
            expect(listenToStub.getCall(1).args[1]).toEqual('change:notuseful');
        });
    });

    describe('dispose()', function() {
        var superStub, stopListeningStub;

        beforeEach(function() {
            superStub = sandbox.stub(view, '_super');
            stopListeningStub = sandbox.stub(view, 'stopListening');
        });

        it('should stop listening events when dispose', function() {
            view.dispose();
            expect(stopListeningStub).toHaveBeenCalled();
            expect(stopListeningStub.getCall(0).args[1]).toEqual('change:useful');
            expect(stopListeningStub.getCall(1).args[1]).toEqual('change:notuseful');
        });
    });

    describe('loadData()', function() {

        it('should load data when called loadData()', function() {
            model.set('useful', 33);
            model.set('notuseful', 44);
            view.loadData({});
            var rawChartData = view.chartData.attributes.rawChartData;
            expect(rawChartData).toBeDefined();
            expect(rawChartData.properties).toBeDefined();
            expect(rawChartData.values).toBeDefined();
            expect(rawChartData.values[0].values).toBeDefined();
            expect(rawChartData.values[0].values[0]).toEqual(33);
            expect(rawChartData.values[1].values).toBeDefined();
            expect(rawChartData.values[1].values[0]).toEqual(44);
        });
        it('should load data when called loadData() #2', function() {
            model.set('useful', 0);
            model.set('notuseful', 0);
            view.loadData({});
            var rawChartData = view.chartData.attributes.rawChartData;
            expect(rawChartData).toBeDefined();
            expect(rawChartData.values).toBeDefined();
            expect(rawChartData.values).toEqual({});
        });
    });
});
