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
describe('View.Views.Base.DriCustomerJourneyMomentumDashletView', function() {
    let app;
    let view;
    let context;
    let layout;
    let initOptions;
    let moduleName = 'Accounts';
    let viewName = 'dri-customer-journey-momentum-dashlet';
    let layoutName = 'record';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadPlugin('Dashlet');
        SugarTest.loadPlugin('Chart');
        SugarTest.loadPlugin('CssLoader');
        SugarTest.testMetadata.addViewDefinition(
            viewName,
            {
                'panels': [
                    {
                        fields: []
                    }
                ]
            },
            moduleName
        );
        SugarTest.testMetadata.set();
        context = app.context.getContext();
        context.set({
            module: moduleName,
            layout: layoutName
        });
        context.prepare();
        layout = app.view.createLayout({
            name: layoutName,
            context: context
        });
        view = SugarTest.createView(
            'base',
            moduleName,
            'dri-customer-journey-momentum-dashlet',
            null,
            context,
            null,
            layout
        );
        initOptions = {
            context: context,
        };
        sinon.stub(view, '_super');
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        delete app.plugins.plugins.view.Dashlet;
        layout = null;
        view = null;
        data = null;
    });

    describe('initialize', function() {
        it('should call the initialize function and initialze some properties', function() {
            sinon.stub(view, 'listenTo');
            view.initialize(initOptions);
            expect(view.listenTo).toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('initialize');
        });
    });

    describe('setActiveCycle', function() {
        it('should call the setActiveCycle function to set incoming id after validations', function() {
            let id = '99';
            sinon.stub(view, 'loadData');
            view.setActiveCycle(id);
            expect(view.selected).toBe(id);
            expect(view.loadData).toHaveBeenCalled();
        });
    });

    describe('unbind', function() {
        it('should call the unbind function properly', function() {
            sinon.stub(view, 'stopListening');
            view.unbind();
            expect(view.stopListening).toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('unbind');
        });
    });

    describe('renderChart.', function() {
        it('shuld call the renderChart function to render chart with check for visibility and data', function() {
            sinon.stub(view, 'isChartReady').returns(true);
            sinon.stub(view, 'displayNoData');
            sinon.stub(_, 'isFunction').returns(true);
            view.renderChart();
            expect(view.chart_loaded).toBe(true);
            expect(view.isChartReady).toHaveBeenCalled();
            expect(view.displayNoData).toHaveBeenCalled();
        });
    });

    describe('loadData', function() {
        it('should initialize some propertise to call app.api.buuildurl to make app.api.call to loadData', function() {
            view.meta.config = false;
            view.loaded = true;
            sinon.stub(app.api, 'buildURL').returns('www.github.com');
            sinon.stub(app.api, 'call');
            view.loadData(initOptions);
            expect(view.loaded).toBe(false);
            expect(view.data).toBe(null);
            expect(app.api.buildURL).toHaveBeenCalled();
            expect(app.api.call).toHaveBeenCalled();
        });
    });

    describe('hasChartData', function() {
        it('should call the hasChartData to return chartData', function() {
            view.data = 'function';
            expect(view.hasChartData()).toBe(true);
        });
    });

    describe('loadCompleted', function() {
        it('Initializes some propertise and make sure data completely loaded', function() {
            data = {
                'won': {
                    'amount_usdollar': 10,
                    'count': 1
                }
            };
            view.loaded = false;
            view.error = 'missing ;';
            sinon.stub(app.template, 'get').returns('Accounts');
            sinon.stub(view, 'evaluateResult');
            sinon.stub(view, 'render');
            view.loadCompleted(data);
            expect(view.loaded).toBe(true);
            expect(view.template).toBe('Accounts');
            expect(view.error).toBe('');
            expect(app.template.get).toHaveBeenCalled();
            expect(view.evaluateResult).toHaveBeenCalled();
            expect(view.evaluateResult).toHaveBeenCalled();
            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('loadError', function() {
        it('Initializes some propertise and render data error if data not loaded', function() {
            view.tplErrorMap = {};
            let error = {};
            sinon.stub(app.template, 'get').returns('Template');
            sinon.stub(view, 'render');
            view.loadError(error);
            expect(view.template).toBe('Template');
            expect(app.template.get).toHaveBeenCalled();
            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('_render', function() {
        describe('_render', function() {
            it('check  wether the user has license or not if it have then allow to render', function() {
                sinon.stub(app.user, 'hasAutomateLicense').returns(true);
                sinon.stub(view, 'displayNoData');
                view.loaded = true;
                view._render();
                expect(view.displayNoData).toHaveBeenCalled();
                expect(view._super).toHaveBeenCalledWith('_render');
            });
            it('If user dont have license then simple return', function() {
                sinon.stub(app.user, 'hasAutomateLicense').returns(false);
                sinon.stub(view.$el, 'html');
                view._noAccessTemplate = sinon.stub();
                view._render();
                expect(view.$el.html).toHaveBeenCalled();
            });
        });
    });

    describe('evaluateResult', function() {
        it('should properly call evaluateResult function that processes the chart data', function() {
            data = {
                'ratio': '2/3',
                'id': 345,
                'name': 'Dashlet',
                'values': 567,
                'data': {
                    'length': 10,
                },
            };
            view.evaluateResult(data);
            expect(view.total).toEqual('2/3');
            expect(view.data).toEqual(data);
            expect(view.selected).toBe(345);
            expect(view.chartCollection.properties.title).toBe('Dashlet');
            expect(view.chartCollection.properties.values).toEqual(567);
            expect(view.chartCollection.properties.value).toEqual('2/3');
            expect(view.chartCollection.properties.colorLength).toEqual(10);
        });
    });
});
