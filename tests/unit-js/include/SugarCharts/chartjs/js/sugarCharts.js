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
describe('Chart.js SugarCharts', () => {
    let app;
    let sandbox;

    beforeEach(() => {
        app = SugarTest.app;
        SugarTest.loadFile('../include/SugarCharts/chartjs/js', 'sugarCharts', 'js', data => eval(data));
        SugarTest.loadFile('../include/javascript/chartjs', 'chart.min', 'js', data => eval(data));
        sandbox = sinon.createSandbox();
    });

    afterEach(() => {
        app.cache.cutAll();
        sandbox.restore();
    });

    describe('BaseChart.shouldSaveChart', () => {
        using('different params', [
            [
                {
                    reportView: true,
                    imageExportType: 'png',
                },
                true,
            ],
            [
                {
                    reportView: true,
                    imageExportType: 'jpeg',
                },
                true,
            ],
            [
                {
                    reportView: true,
                    imageExportType: '',
                },
                false,
            ],
            [
                {
                    reportView: false,
                    imageExportType: 'png',
                },
                false,
            ],
        ], (params, expected) => {
            it('should determine if the chart should be saved', () => {
                params.chartType = 'pieChart';

                let chart = SUGAR.charts.getChartInstance({}, params);
                expect(chart.shouldSaveChart()).toBe(expected);
            });
        });
    });

    describe('BaseChart.hasValues', () => {
        using('different data sets', [
            [
                {
                    values: [
                        {
                            values: [1, 2],
                        },
                    ],
                },
                true,
            ],
            [
                {
                    values: [
                        {
                            values: [],
                        },
                    ],
                },
                false,
            ],
            [
                {
                    values: [
                        {
                            values: [0],
                        },
                    ],
                },
                true,
            ],
            [
                {
                    values: [],
                },
                false,
            ],
        ], (data, expected) => {
            it('should determine if the provided data contains values', () => {
                let chart = SUGAR.charts.getChartInstance(data, {chartType: 'donutChart'});
                expect(chart.hasValues()).toBe(expected);
            });
        });
    });

    describe('BaseChart.isDiscreteData', () => {
        using('different data sets', [
            [
                {
                    label: [],
                    values: [],
                },
                false,
            ],
            [
                {
                    label: ['label1', 'label2'],
                    values: [
                        {
                            values: [1, 2, 3],
                        },
                    ],
                },
                false,
            ],
            [
                {
                    label: ['label1', 'label2', 'label3'],
                    values: [
                        {
                            label: ['label1'],
                            values: [1],
                        },
                        {
                            label: ['label2'],
                            values: [2],
                        },
                        {
                            label: ['label3'],
                            values: [3],
                        },
                    ],
                },
                true,
            ],
        ], (data, expected) => {
            it('should determine if the provided data is discrete', () => {
                let chart = SUGAR.charts.getChartInstance(data, {chartType: 'donutChart'});
                expect(chart.isDiscreteData()).toBe(expected);
            });
        });
    });

    describe('BaseChart.pickLabel', () => {
        using('different values', [
            ['my label', 'my label'],
            [['my label'], 'my label'],
            ['', 'LBL_CHART_UNDEFINED'],
            [[''], 'LBL_CHART_UNDEFINED'],
        ], (label, expected) => {
            it('should pick the proper label', () => {
                let chart = SUGAR.charts.getChartInstance({}, {chartType: 'donutChart'});
                expect(chart.pickLabel(label)).toBe(expected);
            });
        });
    });

    describe('BaseChart.sumValues', () => {
        using('different values', [
            [[1, 2, 3], 6],
            [[1.11, 2.22, 3.33], 6.66],
            [[], 0],
        ], (values, expected) => {
            it('should sum the provided values', () => {
                let chart = SUGAR.charts.getChartInstance({}, {chartType: 'donutChart'});
                expect(chart.sumValues(values)).toBe(expected);
            });
        });
    });

    describe('BaseChart.getColors', () => {
        using('different chart types and data lengths', [
            [
                {
                    properties: {
                        type: 'pie chart',
                    },
                    values: ['red', 'green', 'blue'],
                },
                {
                    chartType: 'pieChart',
                },
                33, // Base list length is 33, so we should always be at the next multiple of that
            ],
            [
                {
                    properties: {
                        type: 'funnel chart',
                    },
                    values: ['red', 'green', 'blue'],
                },
                {
                    chartType: 'funnelChart',
                },
                33,
            ],
            [
                {
                    properties: {
                        type: 'bar chart',
                    },
                    values: {values: ['red', 'green', 'blue']},
                    label: ['red', 'green', 'blue'],
                },
                {
                    chartType: 'barChart',
                    barType: 'stacked',
                },
                33,
            ],
        ], (data, params, expectedLength) => {
            it('should return the correct color list', () => {
                let chart = SUGAR.charts.getChartInstance(data, params);

                let colorList = chart.getColors();
                expect(colorList.length).toBe(expectedLength);
            });
        });
    });

    describe('BarChart.getChartOptions', () => {
        it('should flip the scales if horizontal', () => {
            let data = {
                label: ['label1', 'label2', 'label3'],
                values: [
                    {
                        label: ['label1'],
                        values: [1],
                    },
                    {
                        label: ['label2'],
                        values: [2],
                    },
                    {
                        label: ['label3'],
                        values: [3],
                    },
                ],
                properties: [{
                    title: 'my chart title',
                }],
            };
            let params = {
                chartType: 'barChart',
                show_x_label: true,
                x_axis_label: 'my x axis',
                show_y_label: false,
                y_axis_label: 'my y axis',
            };

            let verticalChart = SUGAR.charts.getChartInstance(
                data,
                Object.assign({orientation: 'vertical'}, params)
            );
            let verticalChartOptions = verticalChart.getChartOptions();
            expect(verticalChartOptions.options.scales.x.title.display).toBe(true);
            expect(verticalChartOptions.options.scales.x.title.text).toBe('my x axis');

            let horizontalChart = SUGAR.charts.getChartInstance(
                data,
                Object.assign({orientation: 'horizontal'}, params)
            );
            let horizontalChartOptions = horizontalChart.getChartOptions();
            expect(horizontalChartOptions.options.scales.x.title.display).toBe(false);
            expect(horizontalChartOptions.options.scales.x.title.text).toBe('my y axis');
        });
    });

    describe('transformData for each chart type', () => {
        let data = {
            properties: [{
                title: 'my chart',
            }],
            label: ['chris', 'jim', 'max', 'sally', 'will'],
            values: [
                {
                    label: 'Q1 2021',
                    values: [0, 0, 0, 300, 0],
                },
                {
                    label: 'Q2 2021',
                    values: [100, 0, 0, 0, 0],
                },
                {
                    label: 'Q3 2021',
                    values: [0, 0, 700, 1000, 0],
                },
                {
                    label: 'Q4 2021',
                    values: [8000, 1300, 7000, 900, 2600],
                },
            ],
        };

        describe('BarChart.transformData', () => {
            it('should properly transform the data for basic bar charts', () => {
                let expectedData = {
                    labels: ['Q1 2021', 'Q2 2021', 'Q3 2021', 'Q4 2021'],
                    datasets: [{
                        label: 'LBL_CHART_UNDEFINED',
                        data: [300, 100, 1700, 19800],
                    }],
                };

                let chart = SUGAR.charts.getChartInstance(data, {chartType: 'barChart', barType: 'basic'});
                let transformedData = chart.transformData();

                expect(transformedData.labels).toEqual(expectedData.labels);
                expect(transformedData.datasets.length).toEqual(1);
                expect(transformedData.datasets[0].data).toEqual(expectedData.datasets[0].data);

                expect(_.isArray(transformedData.datasets[0].backgroundColor)).toBe(true);
            });

            it('should properly transform the data for stacked or grouped bar charts', () => {
                let expectedData = {
                    labels: ['Q1 2021', 'Q2 2021', 'Q3 2021', 'Q4 2021'],
                    datasets: [
                        {
                            label: 'chris',
                            data: [0, 100, 0, 8000],
                        },
                        {
                            label: 'jim',
                            data: [0, 0, 0, 1300],
                        },
                        {
                            label: 'max',
                            data: [0, 0, 700, 7000],
                        },
                        {
                            label: 'sally',
                            data: [300, 0, 1000, 900],
                        },
                        {
                            label: 'will',
                            data: [0, 0, 0, 2600],
                        },
                    ],
                };

                let chart = SUGAR.charts.getChartInstance(data, {chartType: 'barChart', barType: 'stacked'});
                let transformedData = chart.transformData();

                expect(transformedData.labels).toEqual(expectedData.labels);
                expect(transformedData.datasets.length).toEqual(5);
                transformedData.datasets.forEach((dataset, index) => {
                    expect(dataset.label).toEqual(expectedData.datasets[index].label);
                    expect(dataset.data).toEqual(expectedData.datasets[index].data);
                });
            });
        });

        describe('PieChart.transformData', () => {
            it('should properly transform data for pie charts', () => {
                let expectedData = {
                    labels: ['Q1 2021', 'Q2 2021', 'Q3 2021', 'Q4 2021'],
                    datasets: [{
                        data: [300, 100, 1700, 19800],
                    }],
                };

                let chart = SUGAR.charts.getChartInstance(data, {chartType: 'pieChart'});
                let transformedData = chart.transformData();

                expect(transformedData.labels).toEqual(expectedData.labels);
                expect(transformedData.datasets.length).toEqual(1);
                expect(transformedData.datasets[0].data).toEqual(expectedData.datasets[0].data);
            });
        });

        describe('LineChart.transformData', () => {
            it('should properly transform data for line charts', () => {
                let expectedData = {
                    labels: ['chris', 'jim', 'max', 'sally', 'will'],
                    datasets: [
                        {
                            label: 'Q1 2021',
                            data: [0, 0, 0, 300, 0],
                        },
                        {
                            label: 'Q2 2021',
                            data: [100, 0, 0, 0, 0],
                        },
                        {
                            label: 'Q3 2021',
                            data: [0, 0, 700, 1000, 0],
                        },
                        {
                            label: 'Q4 2021',
                            data: [8000, 1300, 7000, 900, 2600],
                        },
                    ],
                };

                let chart = SUGAR.charts.getChartInstance(data, {chartType: 'lineChart'});
                let transformedData = chart.transformData();

                expect(transformedData.labels).toEqual(expectedData.labels);
                expect(transformedData.datasets.length).toEqual(4);
                transformedData.datasets.forEach((dataset, index) => {
                    expect(dataset.label).toEqual(expectedData.datasets[index].label);
                    expect(dataset.data).toEqual(expectedData.datasets[index].data);
                });
            });
        });

        describe('FunnelChart.transformData', () => {
            it('should properly transform data for funnel charts', () => {
                let expectedData = {
                    labels: ['Q4 2021', 'Q3 2021', 'Q2 2021', 'Q1 2021'],
                    datasets: [{
                        data: [19800, 1700, 100, 300],
                    }],
                };

                let chart = SUGAR.charts.getChartInstance(data, {chartType: 'funnelChart'});
                let transformedData = chart.transformData();

                expect(transformedData.labels).toEqual(expectedData.labels);
                expect(transformedData.datasets.length).toEqual(1);
                expect(transformedData.datasets[0].data).toEqual(expectedData.datasets[0].data);
            });
        });

        describe('TreemapChart.transformData', () => {
            it('should properly transform data for treemap charts', () => {
                let expectedData = {
                    labels: ['Q4 2021', 'Q3 2021', 'Q1 2021', 'Q2 2021'],
                    datasets: [{
                        tree: [
                            {
                                label: 'Q4 2021',
                                value: 19800,
                            },
                            {
                                label: 'Q3 2021',
                                value: 1700,
                            },
                            {
                                label: 'Q1 2021',
                                value: 300,
                            },
                            {
                                label: 'Q2 2021',
                                value: 100,
                            }
                        ],
                        key: 'value',
                        groups: ['label'],
                    }],
                };

                let chart = SUGAR.charts.getChartInstance(data, {chartType: 'treemapChart'});
                let transformedData = chart.transformData();

                expect(transformedData.labels).toEqual(expectedData.labels);
                expect(transformedData.datasets.length).toEqual(1);
                expect(transformedData.datasets[0].tree).toEqual(expectedData.datasets[0].tree);

                expect(chart.sortedData).toEqual(expectedData.datasets[0].tree);
            });
        });
    });

    describe('translateString', () => {
        it('should return a translated string', () => {
            let string;
            let langGetStub = sandbox.stub(app.lang, 'get').callsFake((label, module) => module ? 'Foo' : 'Bar');
            string = SUGAR.charts.translateString('LBL_CHART_UNDEFINED');
            expect(string).toBe('Bar');
            string = SUGAR.charts.translateString('LBL_CHART_NO_DRILLTHRU', 'Mock');
            expect(string).toBe('Foo');
            langGetStub.restore();
        });
    });

    describe('dataIsEmpty', () => {
        it('should return whether or not the data is empty', () => {
            expect(SUGAR.charts.dataIsEmpty()).toBe(true);
            expect(SUGAR.charts.dataIsEmpty('No Data')).toBe(true);
            expect(SUGAR.charts.dataIsEmpty('')).toBe(true);
            expect(SUGAR.charts.dataIsEmpty(['Foo'])).toBe(true);
            expect(SUGAR.charts.dataIsEmpty({values: 'Foo'})).toBe(true);
            expect(SUGAR.charts.dataIsEmpty({values: []})).toBe(true);
            expect(SUGAR.charts.dataIsEmpty({values: [{x: 1, y: 7}]})).toBe(false);
        });
    });

    describe('getChartStrings', () => {
        it('should return an object with translated chart strings', () => {
            let strings;
            let expected;
            sandbox.stub(
                SUGAR.charts,
                'translateString').callsFake((appString, module)  => appString + (module || '')
            );

            // test default user prefs just like getLocale
            strings = SUGAR.charts.getChartStrings();
            expected = {
                legend: {
                    close: 'LBL_CHART_LEGEND_CLOSE',
                    open: 'LBL_CHART_LEGEND_OPEN',
                    noLabel: 'LBL_CHART_UNDEFINED'
                },
                tooltip: {
                    amount: 'LBL_CHART_AMOUNT',
                    count: 'LBL_CHART_COUNT',
                    date: 'LBL_CHART_DATE',
                    group: 'LBL_CHART_GROUP',
                    key: 'LBL_CHART_KEY',
                    percent: 'LBL_CHART_PERCENT'
                },
                noData: 'LBL_CHART_NO_DATA',
                noLabel: 'LBL_CHART_UNDEFINED',
                noDrillthru: 'LBL_CHART_NO_DRILLTHRUReports'
            };
            expect(strings).toEqual(expected);
        });
    });

    describe('getLocale', () => {
        it('should return an object with locale format preference', () => {
            let expected;
            sandbox.stub(SUGAR.charts, 'getUserPreferences').callsFake(() => ({
                'decimal_separator': '.',
                'number_grouping_separator': ',',
                'currency_symbol': '$',
                'currency_id': -99,
                'datepref': 'm/d/Y',
                'timepref': 'h:ia',
                'decimal_precision': 2
            }));
            sandbox.stub(SUGAR.charts, '_dateStringArray').callsFake(listLabel => [listLabel]);
            let userMock = {
                'decimal_separator': '*',
                'number_grouping_separator': '^',
                'currency_symbol': '#',
                'currency_id': 123,
                'datepref': 'Y.M.D',
                'timepref': 'H:m A',
                'decimal_precision': 3
            };

            // first test default user prefs
            let prefs = SUGAR.charts.getLocale();
            expected = {
                'decimal': '.',
                'thousands': ',',
                'grouping': [3],
                'currency': ['$', ''],
                'currency_id': -99,
                'dateTime': '%a %b %e %X %Y',
                'date': '%m/%d/%Y',
                'time': '%I:%M',
                'periods': ['am', 'pm'],
                'days': ['dom_cal_day_long'],
                'shortDays': ['dom_cal_day_short'],
                'months': ['dom_cal_month_long'],
                'shortMonths': ['dom_cal_month_short'],
                'precision': 2
            };
            expect(prefs).toEqual(expected);

            // now test passing user overrides
            prefs = SUGAR.charts.getLocale(userMock);
            expected = {
                'decimal': '*',
                'thousands': '^',
                'grouping': [3],
                'currency': ['#', ''],
                'currency_id': 123,
                'dateTime': '%a %b %e %X %Y',
                'date': '%Y.%M.%D',
                'time': '%H:%m',
                'periods': [' AM', ' PM'],
                'days': ['dom_cal_day_long'],
                'shortDays': ['dom_cal_day_short'],
                'months': ['dom_cal_month_long'],
                'shortMonths': ['dom_cal_month_short'],
                'precision': 3
            };
            expect(prefs).toEqual(expected);
        });
    });

    describe('getUserLocale', () => {
        it('should return an object with locale format preference', () => {
            sandbox.stub(SUGAR.charts, 'getUserPreferences').callsFake(()  => ({
                'decimal_separator': '*',
                'number_grouping_separator': '^',
                'currency_symbol': '#',
                'currency_id': 123,
                'datepref': 'Y.M.D',
                'timepref': 'H:m A',
                'decimal_precision': 3
            }));
            sandbox.stub(SUGAR.charts, '_dateStringArray').callsFake(listLabel => [listLabel]);

            // test default user prefs just like getLocale
            let prefs = SUGAR.charts.getUserLocale();
            let expected = {
                'decimal': '*',
                'thousands': '^',
                'grouping': [3],
                'currency': ['#', ''],
                'currency_id': 123,
                'dateTime': '%a %b %e %X %Y',
                'date': '%Y.%M.%D',
                'time': '%H:%m',
                'periods': [' AM', ' PM'],
                'days': ['dom_cal_day_long'],
                'shortDays': ['dom_cal_day_short'],
                'months': ['dom_cal_month_long'],
                'shortMonths': ['dom_cal_month_short'],
                'precision': 3
            };
            expect(prefs).toEqual(expected);
        });
    });

    describe('userPreference', () => {
        it('should return a user preference', () => {
            sandbox.stub(app.user, 'getPreference').callsFake(pref => pref === 'Foo' ? 'Bar' : 'Baz');
            expect(SUGAR.charts.userPreference('Foo')).toBe('Bar');
            expect(SUGAR.charts.userPreference('Foop')).toBe('Baz');
        });
    });

    describe('getSystemLocale', () => {
        it('should return an object with locale format preference', () => {
            sandbox.stub(SUGAR.charts, '_getSystemPreferences').callsFake(() => ({
                'decimal_separator': '*',
                'number_grouping_separator': '^',
                'currency_symbol': '#',
                'currency_id': 123,
                'datepref': 'Y.M.D',
                'timepref': 'H:m A',
                'decimal_precision': 3
            }));
            sandbox.stub(SUGAR.charts, '_dateStringArray').callsFake(listLabel => [listLabel]);

            // test default user prefs just like getLocale
            let prefs = SUGAR.charts.getSystemLocale();
            let expected = {
                'decimal': '*',
                'thousands': '^',
                'grouping': [3],
                'currency': ['#', ''],
                'currency_id': 123,
                'dateTime': '%a %b %e %X %Y',
                'date': '%Y.%M.%D',
                'time': '%H:%m',
                'periods': [' AM', ' PM'],
                'days': ['dom_cal_day_long'],
                'shortDays': ['dom_cal_day_short'],
                'months': ['dom_cal_month_long'],
                'shortMonths': ['dom_cal_month_short'],
                'precision': 3
            };
            expect(prefs).toEqual(expected);
        });
    });

    describe('_dateFormat', () => {
        it('should return a date format string', () => {
            expect(SUGAR.charts._dateFormat()).toBe('%b %-d, %Y');
            expect(SUGAR.charts._dateFormat('M/D/Y')).toBe('%M/%D/%Y');
            expect(SUGAR.charts._dateFormat('y.m.d')).toBe('%y.%m.%d');
        });
    });

    describe('_timeFormat', () => {
        it('should return a time format string', () => {
            expect(SUGAR.charts._timeFormat()).toBe('%-I:%M:%S');
            expect(SUGAR.charts._timeFormat('H:m A')).toBe('%H:%m');
            expect(SUGAR.charts._timeFormat('h:i')).toBe('%I:%M');
        });
    });

    describe('_timePeriods', () => {
        it('should return a time format string', () => {
            expect(SUGAR.charts._timePeriods()).toEqual(['AM', 'PM']);
            expect(SUGAR.charts._timePeriods('H:m A')).toEqual([' AM', ' PM']);
            expect(SUGAR.charts._timePeriods('H:mA')).toEqual(['AM', 'PM']);
            expect(SUGAR.charts._timePeriods('H:m a')).toEqual([' am', ' pm']);
            expect(SUGAR.charts._timePeriods('H:ma')).toEqual(['am', 'pm']);
            expect(SUGAR.charts._timePeriods('H:m')).toEqual(['', '']);
        });
    });

    describe('_dateStringArray', () => {
        it('should return an array of date strings', () => {
            sandbox.stub(SUGAR.charts, 'translateListStrings').callsFake(
                listLabel => listLabel === 'noempty' ? {foo: 'Foo', bar: 'Bar'} : {baz: '', foo: 'Foo', bar: 'Bar'}
            );
            expect(SUGAR.charts._dateStringArray('noempty')).toEqual(['Foo', 'Bar']);
            expect(SUGAR.charts._dateStringArray('withempty')).toEqual(['Foo', 'Bar']);
        });
    });

    describe('getGrouping', () => {
        it('should return a grouping definition', () => {
            let reportDef = {group_defs: [{key0: 'Foo'}, {key1: 'Bar'}]};
            let grouping;

            grouping = SUGAR.charts.getGrouping(reportDef, 0);
            expect(grouping.key0).toBe('Foo');

            grouping = SUGAR.charts.getGrouping(reportDef, 1);
            expect(grouping.key1).toBe('Bar');

            reportDef.group_defs = [{key0: 'Baz'}];
            grouping = SUGAR.charts.getGrouping(reportDef);
            expect(grouping).toBe(reportDef.group_defs);
        });
    });

    describe('defineFiscalYearStart', () => {
        it('should define a cache fiscaltimeperiods property', () => {
            sinon.stub(app.api, 'call').callsFake(() => {
                app.cache.set('fiscaltimeperiods', {'annualDate': '2017-07-01'});
            });

            SUGAR.charts.defineFiscalYearStart();

            let timeperiods = app.cache.get('fiscaltimeperiods');
            expect(timeperiods).toEqual({annualDate: '2017-07-01'});
        });
    });

    describe('setFiscalStartDate', () => {
        it('should define annualDate in cache', () => {
            let firstQuarter = {
                'name': '2017 Q1',
                'start_date': '2017-01-01',
                'end_date': '2017-03-31',
                'type': 'Quarter'
            };
            let date;
            SUGAR.charts.setFiscalStartDate(firstQuarter);
            date = app.cache.get('fiscaltimeperiods');
            expect(date.annualDate).toBe('Sun, 01 Jan 2017 00:00:00 GMT');

            firstQuarter = {
                'name': '2017 Q1',
                'start_date': '2017-07-01',
                'end_date': '2017-09-30',
                'type': 'Quarter'
            };
            SUGAR.charts.setFiscalStartDate(firstQuarter);
            date = app.cache.get('fiscaltimeperiods');
            expect(date.annualDate).toBe('Sat, 01 Jul 2017 00:00:00 GMT');

            firstQuarter = {
                'name': '2018 Q1',
                'start_date': '2017-10-01',
                'end_date': '2017-12-31',
                'type': 'Quarter'
            };
            SUGAR.charts.setFiscalStartDate(firstQuarter);
            date = app.cache.get('fiscaltimeperiods');
            expect(date.annualDate).toBe('Sun, 01 Oct 2017 00:00:00 GMT');
        });

        it('should not set cache if no timeperiods are available', () => {
            let firstQuarter = false;
            SUGAR.charts.setFiscalStartDate(firstQuarter);
            let cache = app.cache.get('fiscaltimeperiods');
            expect(cache).toBe(undefined);
        });
    });

    describe('getFiscalStartDate', () => {
        it('should return a date string', () => {
            let date = SUGAR.charts.getFiscalStartDate();
            expect(date).toBeNull();
            app.cache.set('fiscaltimeperiods', {annualDate: '2017-04-01'});
            date = SUGAR.charts.getFiscalStartDate();
            expect(date).toBe('2017-04-01');
        });
    });

    describe('getDateValues', () => {
        it('should return a date value array', () => {
            let values = SUGAR.charts.getDateValues('March 2017', 'month');
            expect(values[0]).toBe('2017-03-01');
            expect(values[1]).toBe('2017-03-31');
            expect(values[2]).toBe('month');

            values = SUGAR.charts.getDateValues('2017', 'fiscalYear');
            expect(values[0]).toBe('2017-1-1');
            expect(values[1]).toBe('2017-12-31');
            expect(values[2]).toBe('fiscalYear');

            values = SUGAR.charts.getDateValues('Q1 2017', 'fiscalQuarter');
            expect(values[0]).toBe('2017-1-1');
            expect(values[1]).toBe('2017-3-31');
            expect(values[2]).toBe('fiscalQuarter');

            values = SUGAR.charts.getDateValues('2017-12-31', 'day');
            expect(values[0]).toBe('2017-12-31');

            values = SUGAR.charts.getDateValues('', 'month');
            expect(values[0]).toBe('');
            expect(values[1]).toBe('');
            expect(values[2]).toBe('month');
        });
    });

    describe('getValues', () => {
        it('should return a values array when getValues is called',() => {
            let def = {table_key: 'self', name: 'Bar', column_function: ''};
            let enums = {'self:Bar': {Foo: 'Baz'}};
            let type;
            let label;
            let values;

            sandbox.stub(app.lang, 'get').callsFake(() => 'Undefined');
            sandbox.stub(app.lang, 'getAppListStrings').callsFake(() => ({on: 'Foo', off: 'Bar'}));
            sinon.stub(app.date, 'getUserDateFormat').callsFake(() => 'MM/DD/YYYY');

            type = 'bool';
            label = 'Foo';
            values = SUGAR.charts.getValues(label, def, type, enums);
            expect(values).toEqual(['1']);
            label = 'Bar';
            values = SUGAR.charts.getValues(label, def, type, enums);
            expect(values).toEqual(['0']);

            type = 'enum';
            label = 'Foo';
            values = SUGAR.charts.getValues(label, def, type, enums);
            expect(values).toEqual(['Baz']);
            type = 'radioenum';
            values = SUGAR.charts.getValues(label, def, type, enums);
            expect(values).toEqual(['Baz']);

            type = 'anythingelse';
            label = 'Foo';
            values = SUGAR.charts.getValues(label, def, type, enums);
            expect(values).toEqual(['Foo']);
            label = 'Undefined';
            values = SUGAR.charts.getValues(label, def, type, enums);
            expect(values).toEqual(['']);

            type = 'date';
            label = '09/20/2017';
            values = SUGAR.charts.getValues(label, def, type, enums);
            expect(values).toEqual(['2017-09-20']);

            sinon.spy(SUGAR.charts, 'getDateValues');

            type = 'date';
            def.column_function = 'year';
            label = '2017-09-09';
            values = SUGAR.charts.getValues(label, def, type, enums);
            expect(values).toEqual(['2017-01-01', '2017-12-31', 'year']);
            expect(SUGAR.charts.getDateValues).toHaveBeenCalledWith(label, def.column_function);
        });
    });

    describe('buildFilter', () => {
        it('should return a filter array', () => {
            let reportDef = {
                'module': 'Mock',
                'group_defs': [
                    {
                        'name': 'group_mock',
                        label: 'Foo',
                        'table_key': 'self'
                    },
                    {
                        'name': 'series_mock',
                        label: 'Bar',
                        'table_key': 'self'
                    }
                ]
            };
            let params = {
                dataType: 'grouped',
                groupLabel: 'Foo',
                seriesLabel: 'Bar'
            };
            let enums = {'self:group_mock': {Foo: 'Baz'}, 'self:series_mock': {Bar: 'Fiz'}};

            sandbox.stub(SUGAR.charts, 'getFieldDef').callsFake(() => ({type: 'enum'}));

            let filter = SUGAR.charts.buildFilter(reportDef, params, enums);
            let expected = [
                {'self:group_mock': ['Baz']},
                {'self:series_mock': ['Fiz']}
            ];

            expect(filter).toEqual(expected);
        });
    });

    describe('getEnums', () => {
        it('should return an array of enum groupings',() => {
            let groupings = [{name: 'mock'}];
            let enumType;

            sandbox.stub(SUGAR.charts, 'getGrouping').callsFake(() => groupings);
            sandbox.stub(SUGAR.charts, 'getFieldDef').callsFake(() => enumType);

            let enums;

            enumType = {type: 'enum'};
            enums = SUGAR.charts.getEnums({});
            expect(enums).toEqual(groupings);

            enumType = {type: 'radioenum'};
            enums = SUGAR.charts.getEnums({});
            expect(enums).toEqual(groupings);

            enumType = {type: 'anythingelse'};
            enums = SUGAR.charts.getEnums({});
            expect(enums).toEqual([]);
        });
    });

    describe('getFieldDef', () => {
        it('should return a field metadata definition', () => {
            let fieldDefMock = {type: 'Foo'};
            sandbox.stub(app.metadata, 'getField').callsFake(() => fieldDefMock);

            let groupDef = {
                table_key: 'self',
                name: 'mock'
            };
            let reportDef = {
                'module': 'Mock',
                'group_defs': [
                    {
                        'name': 'mock',
                        'table_key': 'self',
                    }
                ]
            };
            let fieldDef = SUGAR.charts.getFieldDef(groupDef, reportDef);
            expect(fieldDefMock).toEqual(fieldDef);
            //TODO: test table_key split
        });
    });
});
