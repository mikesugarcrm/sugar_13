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
describe('Chart Plugin', function() {
    var sinonSandbox, app, layout, view, data;
    var moduleName = 'Accounts',
        viewName = 'opportunity-metrics',
        layoutName = 'record';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);
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

        var context = app.context.getContext();
        context.set({
            module: moduleName,
            layout: layoutName
        });
        context.prepare();

        layout = app.view.createLayout({
            name: layoutName,
            context: context
        });

        view = SugarTest.createView('base', moduleName, viewName, null, context, null, layout);
    });

    afterEach(function() {
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        delete app.plugins.plugins['view']['Dashlet'];
        layout = null;
        view = null;
        data = null;
    });

    describe('Test chart data available.', function() {
        it('_hasChartData should return false when the total data count is 0.', function() {
            view.total = 0;
            expect(view.hasChartData()).toBe(false);
        });
        it('_hasChartData should return true when the total data count > 0.', function() {
            view.total = 1;
            expect(view.hasChartData()).toBe(true);
        });
    });

    describe('Test chart is ready for rendering.', function() {
        beforeEach(function() {
            view.total = 1;
            view.meta.config = false;
            view.disposed = false;
        });

        it('The isChartReady method should return false if chart data has not been set.', function() {
            view.total = 0;
            expect(view.isChartReady()).toBe(false);
        });
        it('The isChartReady method should return true if chart data has been set.', function() {
            view.total = 1;
            expect(view.isChartReady()).toBe(true);
        });
        it('The isChartReady method should return false if chart meta config is true.', function() {
            view.meta.config = true;
            expect(view.isChartReady()).toBe(false);
        });
        it('The isChartReady method should return false if chart dispose is true.', function() {
            view.disposed = true;
            expect(view.isChartReady()).toBe(false);
        });
        it('The isChartReady method should return false if chart data total is zero.', function() {
            view.total = 0;
            expect(view.isChartReady()).toBe(false);
        });
    });

    describe('Test display no data footer rendering.', function() {
        it('The no data footer should be displayed and chart container hidden when displayNoData method called with true.', function() {
            view.render();
            view.displayNoData(true);
            expect(view.$el.find('[data-content="chart"]').hasClass('hide')).toBe(true);
            expect(view.$el.find('[data-content="nodata"]').hasClass('hide')).toBe(false);
        });
        it('The no data footer should be hidden and chart container displayed when displayNoData method called with false.', function() {
            view.render();
            view.displayNoData(false);
            expect(view.$el.find('[data-content="chart"]').hasClass('hide')).toBe(false);
            expect(view.$el.find('[data-content="nodata"]').hasClass('hide')).toBe(true);
        });
    });

    describe('Test chart is resized.', function() {
        var currentTransform = '';

        beforeEach(function() {
            view.total = 3;
            view.chartCollection = {
              "data": [
                {
                  "key": "Won",
                  "value": 0,
                  "classes": "won"
                },
                {
                  "key": "Lost",
                  "value": 0,
                  "classes": "lost"
                },
                {
                  "key": "Active",
                  "value": 3,
                  "classes": "active"
                }
              ],
              "properties": {
                "title": "Opportunity Metrics",
                "value": 3,
                "label": 3
              }
            };
            view.metricsCollection = {
              "won": {
                "amount_usdollar": 0,
                "count": 0,
                "formattedAmount": "$0",
                "icon": "caret-up",
                "cssClass": "won",
                "dealLabel": "won",
                "stageLabel": "Won"
              },
              "lost": {
                "amount_usdollar": 0,
                "count": 0,
                "formattedAmount": "$0",
                "icon": "caret-down",
                "cssClass": "lost",
                "dealLabel": "lost",
                "stageLabel": "Lost"
              },
              "active": {
                "amount_usdollar": 22107.888888,
                "count": 3,
                "formattedAmount": "$22,107",
                "icon": "minus",
                "cssClass": "active",
                "dealLabel": "active",
                "stageLabel": "Active"
              }
            };

            view.chart
                .width(640).height(320)
                .hole(view.total);

            view.render();
            view.chartResize();

            currentTransform = view.$el.find('.sc-chart-pie .sc-pie .sc-group').attr('transform');
        });

        afterEach(function() {
            view.total = 0;
            view.chartCollection = null;
            view.metricsCollection = null;
        });

        it('Chart should be resized to defined size.', function() {
            view.chart.width(1000);
            view.chartResize();
            var resizedTransform = view.$el.find('.sc-chart-pie .sc-pie .sc-group').attr('transform');

            expect(currentTransform).not.toBe(resizedTransform);
            expect(resizedTransform).toMatch(/^translate\(500[, ]+152.5\)/);
        });

        it('Chart should be resized when dashlet expand event fires on layout.', function() {
            view.chart.width(1000);
            view.layout.trigger('dashlet:collapse', false);
            var resizedTransform = view.$el.find('.sc-chart-pie .sc-pie .sc-group').attr('transform');

            expect(currentTransform).not.toBe(resizedTransform);
        });

        it('Chart should be resized when dashlet draggable stop event fires on layout context.', function() {
            view.chart.width(1000);
            view.layout.context.trigger('dashlet:draggable:stop');
            var resizedTransform = view.$el.find('.sc-chart-pie .sc-pie .sc-group').attr('transform');

            expect(currentTransform).not.toBe(resizedTransform);
        });
    });
});
