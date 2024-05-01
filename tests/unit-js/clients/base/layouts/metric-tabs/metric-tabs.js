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
describe('Base.Layout.MetricTabs', function() {
    let app;
    let context;
    let parentContext;
    let layout;
    let parentLayout;
    let options;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        parentContext = app.context.getContext();
        parentContext.set('on', sinon.stub);
        parentContext.prepare();
        context.parent = parentContext;
        context.set('model', new Backbone.Model());
        context.set('metric_context', 'service_console');
        context.set('metric_module', 'Cases');
        context.prepare();

        parentLayout = {
            layout: {
                meta: {
                    metric_context: 'service_console',
                    metric_module: 'Cases'
                }
            },
            off: $.noop
        };

        layout = SugarTest.createLayout('base', '', 'metric-tabs', null,
            context, null, {layout: parentLayout});
        sinon.stub(layout, '_super');
    });

    afterEach(function() {
        sinon.restore();
        context = null;
        layout = null;
        app = null;
    });

    describe('initialize', function() {
        let options;
        beforeEach(function() {
            options = {test: 'test'};
            sinon.stub(layout, 'getHiddenMetrics');
            sinon.stub(layout, 'setActiveMetricKey');
            sinon.stub(layout, 'setLoaderCount');
        });
        afterEach(function() {
            options = null;
        });

        it('should call all the required methods', function() {
            layout.initialize(options);

            expect(layout._super).toHaveBeenCalledWith('initialize', [options]);
            expect(layout.getHiddenMetrics).toHaveBeenCalled();
            expect(layout.setActiveMetricKey).toHaveBeenCalled();
            expect(layout.setLoaderCount).toHaveBeenCalled();
            expect(layout.showLoader).toBeTruthy();
            expect(layout.hasVisibleMetrics).toBeTruthy();
            expect(layout.hasHiddenMetrics).toBeFalsy();
        });
    });

    describe('setActiveMetric', function() {
        let metric;
        beforeEach(function() {
            layout.context.metrics = [
                {id: 'test1'},
                {id: 'test2'}
            ];
            metric = {id: 'test1'};
            sinon.stub(layout.model, 'set');
            sinon.stub(layout, 'getActiveMetricKey');
            sinon.stub(app.user.lastState, 'set');
        });

        it('should set the user last state', function() {
            layout.setActiveMetric(metric);

            expect(layout.getActiveMetricKey).toHaveBeenCalled();
            expect(app.user.lastState.set).toHaveBeenCalled();
            expect(layout.model.set).toHaveBeenCalledWith('active', 'test1');
        });
    });

    describe('getNextActiveMetric', function() {
        let model;
        let res;
        it('should return empty array if metric length is 1', function() {
            model = {id: 'test'};
            layout.context.metrics = [{id: 'test'}];

            res = layout.getNextActiveMetric(model);
            expect(res).toEqual([]);
        });

        it('should return second last model when last model is removed', function() {
            model = {id: 'test3'};
            layout.context.metrics = [
                {id: 'test1'},
                {id: 'test2'},
                {id: 'test3'}
            ];

            res = layout.getNextActiveMetric(model);
            expect(res).toEqual({id: 'test2'});
        });

        it('should return next last model when a model is removed', function() {
            model = {id: 'test2'};
            layout.context.metrics = [
                {id: 'test1'},
                {id: 'test2'},
                {id: 'test3'}
            ];

            res = layout.getNextActiveMetric(model);
            expect(res).toEqual({id: 'test3'});
        });

        it('should return same index model using last state index when model is undefined ', function() {
            sinon.stub(app.user.lastState, 'get').returns({index: 1});
            layout.context.metrics = [
                {id: 'test1'},
                {id: 'test2'},
                {id: 'test3'}
            ];

            res = layout.getNextActiveMetric();
            expect(res).toEqual({id: 'test2'});
        });

        it('should return last model when index is out of bounds and model is undefined ', function() {
            sinon.stub(app.user.lastState, 'get').returns({index: 4});
            layout.context.metrics = [
                {id: 'test1'},
                {id: 'test2'},
                {id: 'test3'}
            ];

            res = layout.getNextActiveMetric();
            expect(res).toEqual({id: 'test3'});
        });
    });

    describe('_render', function() {
        beforeEach(function() {
            layout.loaderCount = 5;
            layout.showLoader = false;
            layout.context.metrics = [];
            sinon.stub(layout, 'setLoaderCount');
            sinon.stub(app.template, 'getLayout').returns(sinon.stub());
            sinon.stub(layout.$el, 'html');
            sinon.stub(layout, 'setActiveMetric');
            sinon.stub(layout, 'getNextActiveMetric');
            sinon.stub(layout, '_resetRibbon');
            sinon.stub(layout, 'getActiveMetricId');
        });

        it('should set loader count', function() {
            layout._render();

            expect(layout.setLoaderCount).toHaveBeenCalled();
            expect(layout._super).toHaveBeenCalledWith('_render');
            expect(app.template.getLayout).not.toHaveBeenCalled();
        });

        it('should use the loader template if showLoader is true', function() {
            layout.showLoader = true;
            layout._render();

            expect(app.template.getLayout).toHaveBeenCalledWith('metric-tabs.metric-loader');
            expect(layout.$el.html).toHaveBeenCalled();
        });

        it('should reset the metric ribbon when context metrics are defined', function() {
            layout.context.metrics = [
                {id: 'test1'},
                {id: 'test2'}
            ];
            layout._render();

            expect(layout.setActiveMetric).toHaveBeenCalled();
            expect(layout.getNextActiveMetric).toHaveBeenCalled();
            expect(layout.getActiveMetricId).toHaveBeenCalled();
            expect(layout._resetRibbon).toHaveBeenCalled();
        });
    });

    describe('getHiddenMetrics', function() {
        it('should get hidden metrics', function() {
            sinon.stub(app.api, 'buildURL').returns('testUrl');
            sinon.stub(app.api, 'call');
            layout.getHiddenMetrics();

            expect(app.api.buildURL).toHaveBeenCalledWith('Metrics', 'hidden', null, {
                metric_context: 'service_console',
                metric_module: 'Cases'
            });
            expect(app.api.call).toHaveBeenCalled();
        });
    });

    describe('handleMetricDelete', function() {
        let model;
        let deletedModel;
        let nextActiveMetric;
        beforeEach(function() {
            model = {
                get: sinon.stub().returns('test')
            };
            sinon.stub(layout, 'getHiddenMetrics');
            sinon.stub(layout, 'setActiveMetric');
        });
        afterEach(function() {
            model = null;
            nextActiveMetric = null;
            deletedModel = null;
        });

        it('should get next active metrics if deleted model is active', function() {
            deletedModel = {
                active: true
            };
            nextActiveMetric = {
                test: 'test1'
            };
            layout.layout.loadData = sinon.stub();
            sinon.stub(layout, 'getNextActiveMetric').returns(nextActiveMetric);
            sinon.stub(layout, 'getComponent').returns(deletedModel);

            layout.handleMetricDelete(model);

            expect(layout.getComponent).toHaveBeenCalledWith('test');
            expect(layout.getNextActiveMetric).toHaveBeenCalledWith(model);
            expect(layout.getHiddenMetrics).toHaveBeenCalled();
            expect(layout.layout.loadData).toHaveBeenCalled();
            expect(layout.setActiveMetric).toHaveBeenCalled();
        });

        it('should not get next active metric if deleted model is not active', function() {
            deletedModel = {
                active: false
            };

            layout.layout.loadData = sinon.stub();
            sinon.stub(layout, 'getNextActiveMetric');
            sinon.stub(layout, 'getComponent').returns(deletedModel);

            layout.handleMetricDelete(model);

            expect(layout, 'getNextActiveMetric').not.toHaveBeenCalledWith(model);
            expect(layout, 'setActiveMetric').not.toHaveBeenCalled();
        });
    });

    describe('handleMetricHide', function() {
        let metricToHide;
        let nextActiveMetric;
        beforeEach(function() {
            layout.context.metrics = [
                {
                    id: 'test'
                },
                {
                    id: 'test1'
                }
            ];

            layout.context.hiddenMetrics = [];

            sinon.stub(layout, 'setActiveMetric');
            sinon.stub(layout, '_saveConfig');
        });
        afterEach(function() {
            metricToHide = null;
            nextActiveMetric = null;
        });

        it('should get next active metrics if hidden model is active', function() {
            metricToHide = {
                meta: {
                    id: 'test'
                },
                active: true,
                $el: {
                    remove: sinon.stub()
                }
            };
            nextActiveMetric = {
                id: 'test1'
            };
            layout.layout.loadData = sinon.stub();
            sinon.stub(layout, 'getNextActiveMetric').returns(nextActiveMetric);

            layout.handleMetricHide(metricToHide);

            expect(layout._saveConfig).toHaveBeenCalledWith({
                visible_list: ['test1'],
                hidden_list: ['test'],
                metric_module: 'Cases',
                metric_context: 'service_console'
            });
            expect(layout.getNextActiveMetric).toHaveBeenCalledWith({id: 'test'});
            expect(layout.setActiveMetric).toHaveBeenCalled();
        });

        it('should not get next active metric if deleted model is not active', function() {
            metricToHide = {
                meta: {
                    id: 'test'
                },
                active: false,
                $el: {
                    remove: sinon.stub()
                }
            };

            layout.layout.loadData = sinon.stub();
            sinon.stub(layout, 'getNextActiveMetric');

            layout.handleMetricHide(metricToHide);

            expect(layout._saveConfig).toHaveBeenCalledWith({
                visible_list: ['test1'],
                hidden_list: ['test'],
                metric_module: 'Cases',
                metric_context: 'service_console'
            });
            expect(layout.getNextActiveMetric).not.toHaveBeenCalledWith({id: 'test'});
            expect(layout.setActiveMetric).not.toHaveBeenCalled();
        });
    });
});
