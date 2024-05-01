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
describe('Base.View.Saved-Reports-Chart', function() {
    var app;
    var context;
    var layout;
    var meta;
    var view;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadPlugin('Dashlet');

        context = new app.Context();
        context.set('model', new Backbone.Model());
        meta = {
            config: false
        };
        context.parent = new app.Context({module: 'Home'});

        layout = SugarTest.createLayout(
            'base',
            'Home',
            'list',
            null,
            context.parent
        );

        view = SugarTest.createView(
            'base',
            'Reports',
            'saved-reports-chart',
            meta,
            context,
            false,
            layout,
            true
        );
        view.settings = new Backbone.Model({
            saved_report_id: 'a'
        })
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        layout.dispose();
        app = null;
        view = null;
        layout = null;
    });

    describe('parseAllSavedReports()', function() {
        var opts;

        beforeEach(function() {
            opts = {
                records: [
                    {id: 'a', name: 'A'},
                    {id: 'b', name: 'B'},
                    {id: 'c', name: 'C'}
                ]
            };
            sinon.stub(SugarTest.app.acl, 'hasAccess').returns(true);
        });

        afterEach(function() {
            opts = null;
        });

        it('should build reportOptions correctly', function() {
            view.parseAllSavedReports(opts);
            expect(view.reportOptions.a).toEqual('A');
        });
    });

    describe('setChartParams()', function() {
        var field;

        beforeEach(function() {
            SugarTest.loadComponent('base', 'field', 'chart');
            field = SugarTest.createField({
                name: 'chart_field',
                type: 'chart',
                viewName: 'detail',
                fieldDef: {
                    'name': 'chart',
                    'label': 'Chart',
                    'type': 'chart',
                    'view': 'detail'
                }
            });

            sinon.spy(field, 'displayNoData');

            view.chartField = field;
        });

        afterEach(function() {
            field.dispose();
        });

        using(
            'whether chart was updated',
            [
                {updated: false, label: 'Translated Label', x_axis_label: ''},
                {updated: true, label: 'reportDataLabel', x_axis_label: 'My Group'}
            ],
            function(jasmineParams) {
                it('should set the params', function() {
                    var oldDirection = app.lang.direction;
                    app.lang.direction = 'ltr';
                    var label = 'LBL_MY_TEST_REPORT';
                    var translation = 'Translated Label';
                    sinon.stub(app.lang, 'get').withArgs(label, view.module).returns(translation);
                    view.settings.set('label', label, {silent: true});
                    var data = {
                        reportData: {
                            group_defs: [{name: 'account_type'}, {name: 'industry'}],
                            label: 'reportDataLabel'
                        },
                        chartData: {
                            label: ['', 'Education'],
                            values: [{label: 'Customer'}, {label: 'Analyst'}],
                            properties: [{
                                groupName: 'My Group',
                                base_module: 'Accounts',
                                allow_drillthru: true,
                                title: 'Title'
                            }],
                            vertical: true,
                            direction: 'ltr'
                        }
                    };

                    view.setChartParams(data, jasmineParams.updated);

                    var expected = {
                        saved_report_id: 'a',
                        label: jasmineParams.label,
                        allowScroll: true,
                        auto_refresh: 0,
                        colorData: 'class',
                        config: true,
                        hideEmptyGroups: true,
                        reduceXTicks: true,
                        rotateTicks: true,
                        show_controls: false,
                        show_title: false,
                        show_x_label: false,
                        show_y_label: false,
                        showValues: 0,
                        staggerTicks: true,
                        wrapTicks: true,
                        x_axis_label: jasmineParams.x_axis_label,
                        y_axis_label: '',
                        chart_type: 'bar chart',
                        report_title: 'Title',
                        show_legend: false,
                        stacked: true,
                        module: 'Accounts',
                        allow_drillthru: true,
                        vertical: true,
                        direction: 'ltr'
                    };
                    expect(view.settings.attributes).toEqual(expected);
                    app.lang.direction = oldDirection;
                });
            }
        );
    });

    describe('getChartState()', function() {
        var chartLabels;
        var dashConfig;
        var reportData;

        beforeEach(function() {
            chartLabels = context.get('chartLabels');
            dashConfig = context.get('dashConfig');
            reportData = context.get('reportData');
        });

        afterEach(function() {
            if (!_.isUndefined(chartLabels)) {
                context.set('chartLabels', chartLabels);
            } else if (!_.isUndefined(context.get('chartLabels'))) {
                context.unset('chartLabels');
            }
            if (!_.isUndefined(dashConfig)) {
                context.set('dashConfig', dashConfig);
            } else if (!_.isUndefined(context.get('dashConfig'))) {
                context.unset('dashConfig');
            }
            if (!_.isUndefined(reportData)) {
                context.set('reportData', reportData);
            } else if (!_.isUndefined(context.get('reportData'))) {
                context.unset('reportData');
            }
        });

        using('various chart types', [
            // funnel chart, 2 groupbys
            {
                testCase: 'funnel chart, 2 groupbys',
                chartState: {seriesIndex: 1},
                chartLabels: {seriesLabel: 'Customer'},
                dashConfig: {chart_type: 'funnel chart'},
                reportData: {group_defs: [{name: 'account_type'}, {name: 'industry'}]},
                chartData: {
                    label: ['', 'Education'],
                    values: [{label: 'Customer'}, {label: 'Analyst'}]
                }
            },
            // funnel chart, 1 groupby
            {
                testCase: 'funnel chart, 1 groupby',
                chartState: {seriesIndex: 0},
                chartLabels: {seriesLabel: 'Education'},
                dashConfig: {chart_type: 'funnel chart'},
                reportData: {group_defs: [{name: 'industry'}]},
                chartData: {
                    label: ['', 'Education'],
                }
            },
            // bar chart, 2 groupbys
            {
                testCase: 'bar chart, 2 groupbys',
                chartState: {groupIndex: 0, pointIndex: 0, seriesIndex: 0},
                chartLabels: {seriesLabel: 'Customer'},
                dashConfig: {chart_type: 'bar chart'},
                reportData: {group_defs: [{name: 'account_type'}, {name: 'industry'}]},
                chartData: {
                    label: ['', 'Education'],
                    values: [{label: 'Customer'}, {label: 'Analyst'}]
                }
            },
            // bar chart, 1 groupby
            {
                testCase: 'bar chart, 1 groupby',
                chartState: {groupIndex: 1, pointIndex: 1, seriesIndex: 0},
                chartLabels: {groupLabel: 'Education'},
                dashConfig: {chart_type: 'bar chart'},
                reportData: {group_defs: [{name: 'industry'}]},
                chartData: {
                    label: ['', 'Education'],
                }
            },
            // group by chart, 2 groupbys
            {
                testCase: 'group by chart, 2 groupbys',
                chartState: {groupIndex: 0, pointIndex: 0, seriesIndex: 1},
                chartLabels: {groupLabel: 'Customer', seriesLabel: 'Education'},
                dashConfig: {chart_type: 'group by chart'},
                reportData: {group_defs: [{name: 'account_type'}, {name: 'industry'}]},
                chartData: {
                    label: ['', 'Education'],
                    values: [{label: 'Customer'}, {label: 'Analyst'}]
                }
            },
            // group by chart, 1 groupby
            {
                testCase: 'group by chart, 1 groupby',
                chartState: {groupIndex: 1, pointIndex: 1, seriesIndex: 1},
                chartLabels: {groupLabel: 'Education'},
                dashConfig: {chart_type: 'group by chart'},
                reportData: {group_defs: [{name: 'industry'}]},
                chartData: {
                    label: ['', 'Education'],
                }
            },
            // horizontal grouped bar chart, 2 groupbys
            {
                testCase: 'horizontal grouped bar chart, 2 groupbys',
                chartState: {groupIndex: 0, pointIndex: 0, seriesIndex: 1},
                chartLabels: {groupLabel: 'Customer', seriesLabel: 'Education'},
                dashConfig: {chart_type: 'horizontal grouped bar chart'},
                reportData: {group_defs: [{name: 'account_type'}, {name: 'industry'}]},
                chartData: {
                    label: ['', 'Education'],
                    values: [{label: 'Customer'}, {label: 'Analyst'}]
                }
            },
            // horizontal grouped bar chart, 1 groupby
            {
                testCase: 'horizontal grouped bar chart, 1 groupby',
                chartState: {groupIndex: 1, pointIndex: 1, seriesIndex: 1},
                chartLabels: {groupLabel: 'Education'},
                dashConfig: {chart_type: 'horizontal grouped bar chart'},
                reportData: {group_defs: [{name: 'industry'}]},
                chartData: {
                    label: ['', 'Education'],
                }
            },
            // vertical grouped bar chart, 2 groupbys
            {
                testCase: 'vertical grouped bar chart, 2 groupbys',
                chartState: {groupIndex: 0, pointIndex: 0, seriesIndex: 1},
                chartLabels: {groupLabel: 'Customer', seriesLabel: 'Education'},
                dashConfig: {chart_type: 'vertical grouped bar chart'},
                reportData: {group_defs: [{name: 'account_type'}, {name: 'industry'}]},
                chartData: {
                    label: ['', 'Education'],
                    values: [{label: 'Customer'}, {label: 'Analyst'}]
                }
            },
            // vertical grouped bar chart, 1 groupby
            {
                testCase: 'vertical grouped bar chart, 1 groupby',
                chartState: {groupIndex: 1, pointIndex: 1, seriesIndex: 1},
                chartLabels: {groupLabel: 'Education'},
                dashConfig: {chart_type: 'vertical grouped bar chart'},
                reportData: {group_defs: [{name: 'industry'}]},
                chartData: {
                    label: ['', 'Education'],
                }
            },
            // line chart, 2 groupbys
            {
                testCase: 'line chart, 2 groupbys',
                chartState: {groupIndex: 1, pointIndex: 1, seriesIndex: 0},
                chartLabels: {groupLabel: 'Customer', seriesLabel: 'Education'},
                dashConfig: {chart_type: 'line chart'},
                reportData: {group_defs: [{name: 'account_type'}, {name: 'industry'}]},
                chartData: {
                    label: ['', 'Education'],
                    values: [{label: 'Customer'}, {label: 'Analyst'}]
                }
            },
            // line chart, 1 groupby
            {
                testCase: 'line chart, 1 groupby',
                chartState: {groupIndex: 1, pointIndex: 0, seriesIndex: 1},
                chartLabels: {groupLabel: 'Education'},
                dashConfig: {chart_type: 'line chart'},
                reportData: {group_defs: [{name: 'industry'}]},
                chartData: {
                    label: ['', 'Education'],
                }
            },
            // pie chart, 2 groupbys
            {
                testCase: 'pie chart, 2 groupbys',
                chartState: {seriesIndex: 1},
                chartLabels: {seriesLabel: 'Customer'},
                dashConfig: {chart_type: 'pie chart'},
                reportData: {group_defs: [{name: 'account_type'}, {name: 'industry'}]},
                chartData: {
                    label: ['', 'Education'],
                    values: [{label: 'Customer'}, {label: 'Analyst'}]
                }
            },
            // pie chart, 1 groupby
            {
                testCase: 'pie chart, 1 groupby',
                chartState: {seriesIndex: 0},
                chartLabels: {seriesLabel: 'Education'},
                dashConfig: {chart_type: 'pie chart'},
                reportData: {group_defs: [{name: 'industry'}]},
                chartData: {
                    label: ['', 'Education'],
                }
            }
        ], function(value) {
            it('get chart state from labels for ' + value.testCase, function() {
                context.set('chartLabels', value.chartLabels);
                context.set('dashConfig', value.dashConfig);
                context.set('reportData', value.reportData);
                var chartState = view.getChartState(value.chartData);
                expect(chartState).toEqual(value.chartState);
            });
        });
    });
});
