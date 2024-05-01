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
describe('Reports.Base.Views.ReportChart', function() {
    var app;
    var view;
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('fields', []);
        context.set('model', new Backbone.Model());
        context.prepare();

        sinon.stub(app.api, 'call').callsFake(function() {});

        view = SugarTest.createView('base', 'Reports', 'report-chart', {}, context, true);
    });
    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        app = null;
        view.context = null;
        view.model = null;
        view = null;
    });

    describe('_beforeInit', function() {
        it('should set properties appropriately', function() {
            view._beforeInit();

            expect(view._chartDef).toEqual({
                type: 'chart',
                customLegend: true,
            });
        });
    });

    describe('_getXaxisLabel', function() {
        it('should properly return xAxisLabel', function() {
            const xLabel = view._getXaxisLabel(
                [{label: 'Test'}],
                {
                    seriesName: 'TestSerios',
                    groupName: 'TestGroup',
                },
                'line chart'
            );

            expect(xLabel).toEqual('TestSerios');
        });
    });

    describe('_getYaxisLabel', function() {
        it('should properly return YaxisLabel', function() {
            const yLabel = view._getYaxisLabel({
                summary_columns: [{
                    group_function: 'TEST',
                    label: 'TESTLabel',
                }],
                numericalChartColumn: 'test_key:test_field:TEST',
            });

            expect(yLabel).toEqual('TESTLabel');
        });
    });

    describe('_getChartConfig', function() {
        it('should properly return the chart config by given type', function() {
            const chartConfig = view._getChartConfig('stacked group by chart');

            expect(chartConfig).toEqual({
                orientation: 'vertical',
                barType: 'stacked',
                chartType: 'group by chart',
            });
        });
    });

    describe('_getChartDefaultSettings', function() {
        it('should properly return chart default settings', function() {
            const chartDefaultSettings = view._getChartDefaultSettings(false);

            expect(chartDefaultSettings).toEqual({
                direction: app.lang.direction,
                colorData: 'class',
                allowScroll: true,
                config: true,
                hideEmptyGroups: true,
                reduceXTicks: true,
                rotateTicks: true,
                show_controls: false,
                show_title: false,
                show_x_label: true,
                show_y_label: true,
                staggerTicks: false,
                wrapTicks: false,
                showValues: 'middle',
                auto_refresh: 0,
            });
        });
    });

    describe('dispose', function() {
        it('should properly dispose elements', function() {
            view.dispose(false);

            expect(view._chartField).toEqual(null);
        });
    });
});
