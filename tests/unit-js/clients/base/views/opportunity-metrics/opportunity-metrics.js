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
describe('Opportunity Metrics Dashlet', function() {
    var sinonSandbox;
    var app;
    var layout;
    var view;
    var data;
    var moduleName = 'Accounts';
    var viewName = 'opportunity-metrics';
    var layoutName = 'record';

    beforeEach(function() {
        app = SugarTest.app;

        app.user.setPreference('currency_id', '-99');
        app.user.setPreference('decimal_precision', 2);
        app.user.setPreference('decimal_separator', '.');
        app.user.setPreference('number_grouping_separator', ',');

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

        data = {
            'won': {
                'amount_usdollar': 10,
                'count': 1
            },
            'lost': {
                'amount_usdollar': 20,
                'count': 2
            },
            'active': {
                'amount_usdollar': 30,
                'count': 3
            }
        };
    });

    afterEach(function() {
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

    describe('Test chart is initialized properly.', function() {
        it('The view chart should be initialized as a function.', function() {
            var chartExists = _.isFunction(view.chart);

            expect(chartExists).toBe(true);
        });
    });

    describe('Test chart is rendered properly.', function() {
        it('The view chart_loaded variable should be set to false if the chart does not render properly or has not yet been rendered.', function() {
            expect(view.chart_loaded).toBe(false);
        });
        it('The view chart_loaded variable should be set to true if the chart renders properly.', function() {
            view.evaluateResult(data);
            view.render();

            expect(view.chart_loaded).toBe(true);
        });
    });

    describe('Test chart data should be evaluted properly.', function() {
        it('Has chart data been processed.', function() {
            var stubListStrings = sinon.stub(app.lang, 'getAppListStrings').callsFake(function() {
                return {'won': 'Won', 'lost': 'Lost', 'active': 'Active'};
            });

            view.evaluateResult(data);

            var expectMetrics = {
                'won': {
                    'amount_usdollar': 10,
                    'count': 1,
                    'formattedAmount': '$10',
                    'icon': 'caret-up',
                    'cssClass': 'won',
                    'dealLabel': 'won',
                    'stageLabel': 'Won'
                },
                'lost': {
                    'amount_usdollar': 20,
                    'count': 2,
                    'formattedAmount': '$20',
                    'icon': 'caret-down',
                    'cssClass': 'lost',
                    'dealLabel': 'lost',
                    'stageLabel': 'Lost'
                },
                'active': {
                    'amount_usdollar': 30,
                    'count': 3,
                    'formattedAmount': '$30',
                    'icon': 'minus',
                    'cssClass': 'active',
                    'dealLabel': 'active',
                    'stageLabel': 'Active'
                }
            };

            var expectChart = {
                'data': [
                    {
                        'value': 1,
                        'key': 'Won',
                        'classes': 'won'
                    },
                    {
                        'value': 2,
                        'key': 'Lost',
                        'classes': 'lost'
                    },
                    {
                        'value': 3,
                        'key': 'Active',
                        'classes': 'active'
                    }
                ],
                'properties': {
                    'title': 'LBL_DASHLET_OPPORTUNITY_NAME',
                    'value': 3,
                    'label': 6
                }
            };

            expect(view.total).toBe(6);

            expect(view.metricsCollection.won.amount_usdollar).toEqual(expectMetrics.won.amount_usdollar);
            expect(view.metricsCollection.won.count).toEqual(expectMetrics.won.count);
            expect(view.metricsCollection.won.formattedAmount).toEqual(expectMetrics.won.formattedAmount);
            expect(view.metricsCollection.won.icon).toEqual(expectMetrics.won.icon);
            expect(view.metricsCollection.won.cssClass).toEqual(expectMetrics.won.cssClass);
            expect(view.metricsCollection.won.dealLabel).toEqual(expectMetrics.won.dealLabel);
            expect(view.metricsCollection.won.stageLabel).toEqual(expectMetrics.won.stageLabel);

            expect(view.chartCollection.data[0].value).toEqual(expectChart.data[0].value);
            expect(view.chartCollection.data[0].key).toEqual(expectChart.data[0].key);
            expect(view.chartCollection.data[0].classes).toEqual(expectChart.data[0].classes);

            stubListStrings.restore();
        });

        it('User seperator preferences respected.', function() {
            app.user.setPreference('decimal_separator', ',');
            app.user.setPreference('number_grouping_separator', '.');

            data = {
                'won': {
                    'amount_usdollar': 1000.00,
                },
                'lost': {
                    'amount_usdollar': 20.00,
                },
                'active': {
                    'amount_usdollar': 30,
                }
            };

            view.evaluateResult(data);

            var expectMetrics = {
                'won': {
                    'formattedAmount': '$1.000',
                },
                'lost': {
                    'formattedAmount': '$20',
                },
                'active': {
                    'formattedAmount': '$30',
                }
            };

            expect(view.metricsCollection.won.formattedAmount).toEqual(expectMetrics.won.formattedAmount);
            expect(view.metricsCollection.lost.formattedAmount).toEqual(expectMetrics.lost.formattedAmount);
            expect(view.metricsCollection.active.formattedAmount).toEqual(expectMetrics.active.formattedAmount);

            app.user.setPreference('decimal_separator', '.');
            app.user.setPreference('number_grouping_separator', ',');
        });
    });

});
