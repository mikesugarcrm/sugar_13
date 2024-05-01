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
describe('SugarCharts', function() {
    var app;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile(
            '../include/SugarCharts/sucrose/js',
            'sugarCharts',
            'js',
            function(d) {
                eval(d);
            });
        SugarTest.loadFile(
            '../include/javascript/sucrose',
            'sucrose.min',
            'js',
            function(d) {
                eval(d);
            });
        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        app.cache.cutAll();
        sinon.restore();
    });

    it('should return a state object when buildChartState is called', function() {
        var eo;
        var labels;
        var state;
        eo = {
            series: {seriesIndex: 1}
        };
        labels = [];
        state = SUGAR.charts.buildChartState(eo, labels);
        expect(state.seriesIndex).toBe(1);
        expect(state.groupIndex).not.toBeDefined();
        expect(state.pointIndex).not.toBeDefined();

        eo = {seriesIndex: 2};
        state = SUGAR.charts.buildChartState(eo, labels);
        expect(state.seriesIndex).toBe(2);
        expect(state.groupIndex).not.toBeDefined();
        expect(state.pointIndex).not.toBeDefined();

        eo = {pointIndex: 3};
        state = SUGAR.charts.buildChartState(eo, labels);
        expect(state.seriesIndex).toBe(3);
        expect(state.groupIndex).not.toBeDefined();
        expect(state.pointIndex).not.toBeDefined();

        labels = ['Foo'];
        eo = {pointIndex: 4};
        state = SUGAR.charts.buildChartState(eo, labels);
        expect(state.seriesIndex).not.toBeDefined();
        expect(state.pointIndex).toBe(4);
        expect(state.groupIndex).not.toBeDefined();

        eo = {groupIndex: 5};
        state = SUGAR.charts.buildChartState(eo, labels);
        expect(state.seriesIndex).not.toBeDefined();
        expect(state.pointIndex).not.toBeDefined();
        expect(state.groupIndex).toBe(5);

        eo = {seriesIndex: 6, groupIndex: 6, pointIndex: 6};
        state = SUGAR.charts.buildChartState(eo, labels);
        expect(state.seriesIndex).toBe(6);
        expect(state.pointIndex).toBe(6);
        expect(state.groupIndex).toBe(6);
    });

    it('should return a series label when extractSeriesLabel is called', function() {
        var state = {seriesIndex: 1};
        var data = [{key: 'Foo'}, {key: 'Bar'}];
        var label = SUGAR.charts.extractSeriesLabel(state, data);
        expect(label).toBe('Bar');
    });

    it('should return a group label when extractGroupLabel is called', function() {
        var state = {groupIndex: 1};
        var labels = ['Foo', 'Bar'];
        var label = SUGAR.charts.extractGroupLabel(state, labels);
        expect(label).toBe('Bar');

        state = {groupIndex: 1, pointIndex: 0};
        label = SUGAR.charts.extractGroupLabel(state, labels);
        expect(label).toBe('Foo');
    });

    it('should return a grouping definition when getGrouping is called', function() {
        var reportDef = {group_defs: [{key0: 'Foo'}, {key1: 'Bar'}]};
        var i;
        var grouping;

        i = 0;
        grouping = SUGAR.charts.getGrouping(reportDef, i);
        expect(grouping.key0).toBe('Foo');

        i = 1;
        grouping = SUGAR.charts.getGrouping(reportDef, i);
        expect(grouping.key1).toBe('Bar');

        reportDef.group_defs = [{key0: 'Baz'}];
        grouping = SUGAR.charts.getGrouping(reportDef);
        expect(grouping).toBe(reportDef.group_defs);
    });

    it('should define a cache fiscaltimeperiods property when defineFiscalYearStart is called', function() {
        var apiStub = sinon.stub(app.api, 'call').callsFake(function() {
            app.cache.set('fiscaltimeperiods', {'annualDate': '2017-07-01'});
        });
        var timeperiods;

        SUGAR.charts.defineFiscalYearStart();

        timeperiods = app.cache.get('fiscaltimeperiods');
        expect(timeperiods).toEqual({annualDate: '2017-07-01'});

        apiStub.restore();
    });

    it('should define annualDate in cache when setFiscalStartDate is called', function() {
        var firstQuarter = {
            'name': '2017 Q1',
            'start_date': '2017-01-01',
            'end_date': '2017-03-31',
            'type': 'Quarter'
        };
        var date;
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

    it('should not set cache if no timeperiods are available', function() {
        var firstQuarter = false;
        SUGAR.charts.setFiscalStartDate(firstQuarter);
        var cache = app.cache.get('fiscaltimeperiods');
        expect(cache).toBe(undefined);
    });

    it('should return a date string when getFiscalStartDate is called', function() {
        var date = SUGAR.charts.getFiscalStartDate();
        expect(date).toBeNull();
        app.cache.set('fiscaltimeperiods', {annualDate: '2017-04-01'});
        date = SUGAR.charts.getFiscalStartDate();
        expect(date).toBe('2017-04-01');
    });

    it('should return a date value array when getDateValues is called', function() {
        var values = SUGAR.charts.getDateValues('March 2017', 'month');
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

    it('should return a values array when getValues is called', function() {
        var def = {table_key: 'self', name: 'Bar', column_function: ''};
        var enums = {'self:Bar': {Foo: 'Baz'}};
        var type;
        var label;

        var langGetStub = sandbox.stub(app.lang, 'get').callsFake(function() {
            return 'Undefined';
        });
        var getAppListStringsStub = sandbox.stub(app.lang, 'getAppListStrings').callsFake(function() {
            return {on: 'Foo', off: 'Bar'};
        });
        var userDateFormatStub = sinon.stub(app.date, 'getUserDateFormat').callsFake(function() {
            return 'MM/DD/YYYY';
        });

        var values;

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

        langGetStub.restore();
        getAppListStringsStub.restore();
    });

    it('should return a filter array when buildFilter is called', function() {
        var reportDef = {
            'module': 'Mock',
            'group_defs': [
                {
                    'name': 'group_mock',
                    'label': 'Foo',
                    'table_key': 'self'
                },
                {
                    'name': 'series_mock',
                    'label': 'Bar',
                    'table_key': 'self'
                }
            ]
        };
        var params = {
            dataType: 'grouped',
            groupLabel: 'Foo',
            seriesLabel: 'Bar'
        };
        var enums = {'self:group_mock': {Foo: 'Baz'}, 'self:series_mock': {Bar: 'Fiz'}};

        var getFieldDefStub = sandbox.stub(SUGAR.charts, 'getFieldDef').callsFake(function() {
                return {type: 'enum'};
            });

        var filter = SUGAR.charts.buildFilter(reportDef, params, enums);
        var expected = [
            {'self:group_mock': ['Baz']},
            {'self:series_mock': ['Fiz']}
        ];

        expect(filter).toEqual(expected);

        getFieldDefStub.restore();
    });

    it('should return and array of enum groupings when getEnums is called', function() {
        var groupings = [{name: 'mock'}];
        var enumType;
        var getGroupingStub = sandbox.stub(SUGAR.charts, 'getGrouping').callsFake(function() {
                return groupings;
            });
        var getFieldDefStub = sandbox.stub(SUGAR.charts, 'getFieldDef').callsFake(function() {
                return enumType;
            });
        var enums;

        enumType = {type: 'enum'};
        enums = SUGAR.charts.getEnums({});
        expect(enums).toEqual(groupings);

        enumType = {type: 'radioenum'};
        enums = SUGAR.charts.getEnums({});
        expect(enums).toEqual(groupings);

        enumType = {type: 'anythingelse'};
        enums = SUGAR.charts.getEnums({});
        expect(enums).toEqual([]);

        getGroupingStub.restore();
        getFieldDefStub.restore();
    });

    it('should return a field metadata definition when getFieldDef is called', function() {
        var fieldDefMock = {type: 'Foo'};
        var getFieldStub = sandbox.stub(app.metadata, 'getField').callsFake(function() {
                return fieldDefMock;
            });
        var groupDef = {
            table_key: 'self',
            name: 'mock'
        };
        var reportDef = {
            'module': 'Mock',
            'group_defs': [
                {
                    'name': 'mock',
                    'table_key': 'self',
                }
            ]
        };
        var fieldDef = SUGAR.charts.getFieldDef(groupDef, reportDef);
        expect(fieldDefMock).toEqual(fieldDef);
        //TODO: test table_key split
    });

    it('should return a translated string when translateString is called', function() {
        var string;
        var langGetStub = sandbox.stub(app.lang, 'get').callsFake(function(label, module) {
            return module ? 'Foo' : 'Bar';
        });
        string = SUGAR.charts.translateString('LBL_CHART_UNDEFINED');
        expect(string).toBe('Bar');
        string = SUGAR.charts.translateString('LBL_CHART_NO_DRILLTHRU', 'Mock');
        expect(string).toBe('Foo');
        langGetStub.restore();
    });

    it('should return an array of translated strings when translateListStrings is called', function() {
        var strings;
        var appListStub = sandbox.stub(app.lang, 'getAppListStrings').callsFake(function(appList) {
            return 'Bar';
        });
        strings = SUGAR.charts.translateListStrings('dom_switch_bool');
        expect(strings).toBe('Bar');
        appListStub.restore();
    });

    it('should return a chart data json object when transformDataToD3 is called', function() {
        var json = {
            'properties': [{
                'title': 'Total is 20'
            }],
            'label': [
                'Red',
                'Yellow',
                'Green'
            ],
            'values': [
                {
                    'label': 'Apples',
                    'values': [6, 4, 2],
                    'valuelabels': ['6', '4', '2']
                },
                {
                    'label': 'Oranges',
                    'values': [3, 5, 0],
                    'valuelabels': ['3', '5', '0']
                }
            ]
        };
        var params = {barType: 'grouped', module: 'Mock'};
        var config = {chartType: 'barChart', barType: 'stacked', ReportModule: true};

        var expected = {
            'properties': {
                'title': 'Total is 20',
                'xDataType': 'ordinal',
                'yDataType': 'numeric',
                'groups': [
                    {'group': 1, 'label': 'Apples'},
                    {'group': 2, 'label': 'Oranges'}
                ],
                'values': [
                    {'group': 1, 'total': 12},
                    {'group': 2, 'total': 8}
                ],
                'groupName': 'LBL_CHART_GROUP',
                'groupType': 'string',
                'seriesName': 'LBL_CHART_KEY',
                'seriesType': 'string'
            },
            'data': [
                {
                    'key': 'Red',
                    'type': 'bar',
                    'values': [
                        {'series': 0, 'label': '6', 'x': 1, 'y': 6},
                        {'series': 0, 'label': '3', 'x': 2, 'y': 3}
                    ]
                },
                {
                    'key': 'Yellow',
                    'type': 'bar',
                    'values': [
                        {'series': 1, 'label': '4', 'x': 1, 'y': 4},
                        {'series': 1, 'label': '5', 'x': 2, 'y': 5}
                    ]
                },
                {
                    'key': 'Green',
                    'type': 'bar',
                    'values': [
                        {'series': 2, 'label': '2', 'x': 1, 'y': 2},
                        {'series': 2, 'label': '0', 'x': 2, 'y': 0}
                    ]
                }
            ]
        };
        var chartData;

        //Deprecated: use transformDataToD3() instead
        chartData = SUGAR.charts.translateDataToD3(json, params, config);
        expect(chartData).toEqual(expected);

        chartData = SUGAR.charts.transformDataToD3(json, params, config);
        expect(chartData).toEqual(expected);
    });

    it('should return boolean when isDataEmpty is called', function() {
        var empty;
        //Deprecated: use dataIsEmpty() instead
        empty = SUGAR.charts.isDataEmpty();
        expect(empty).toBe(false);
        empty = SUGAR.charts.isDataEmpty('No Data');
        expect(empty).toBe(false);
        empty = SUGAR.charts.isDataEmpty('');
        expect(empty).toBe(false);
        empty = SUGAR.charts.isDataEmpty(['Foo']);
        expect(empty).toBe(true);
    });

    it('should return boolean when dataIsEmpty is called', function() {
        var empty;
        empty = SUGAR.charts.dataIsEmpty();
        expect(empty).toBe(true);
        empty = SUGAR.charts.dataIsEmpty('No Data');
        expect(empty).toBe(true);
        empty = SUGAR.charts.dataIsEmpty('');
        expect(empty).toBe(true);
        empty = SUGAR.charts.dataIsEmpty(['Foo']);
        expect(empty).toBe(true);
        empty = SUGAR.charts.dataIsEmpty({values: 'Foo'});
        expect(empty).toBe(true);
        empty = SUGAR.charts.dataIsEmpty({values: []});
        expect(empty).toBe(true);
        empty = SUGAR.charts.dataIsEmpty({values: [{x: 1, y: 7}]});
        expect(empty).toBe(false);
    });

    it('should return an object with translated chart strings when getChartStrings is called', function() {
        var strings;
        var expected;
        var translateStub = sandbox.stub(SUGAR.charts, 'translateString').callsFake(function(appString, module) {
            return appString + (module || '');
        });

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

        translateStub.restore();
    });

    it('should return an object with locale format preference when getLocale is called', function() {
        var strings;
        var expected;
        var userPrefStub = sandbox.stub(SUGAR.charts, 'getUserPreferences').callsFake(function() {
            return {
                'decimal_separator': '.',
                'number_grouping_separator': ',',
                'currency_symbol': '$',
                'currency_id': -99,
                'datepref': 'm/d/Y',
                'timepref': 'h:ia',
                'decimal_precision': 2
            };
        });
        var dateArrayStub = sandbox.stub(SUGAR.charts, '_dateStringArray').callsFake(function(listLabel) {
            return [listLabel];
        });
        var userMock = {
            'decimal_separator': '*',
            'number_grouping_separator': '^',
            'currency_symbol': '#',
            'currency_id': 123,
            'datepref': 'Y.M.D',
            'timepref': 'H:m A',
            'decimal_precision': 3
        };

        // first test default user prefs
        prefs = SUGAR.charts.getLocale();
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

        userPrefStub.restore();
        dateArrayStub.restore();
    });

    it('should return an object with locale format preference when getUserLocale is called', function() {
        var strings;
        var expected;
        var userPrefStub = sandbox.stub(SUGAR.charts, 'getUserPreferences').callsFake(function() {
            return {
                'decimal_separator': '*',
                'number_grouping_separator': '^',
                'currency_symbol': '#',
                'currency_id': 123,
                'datepref': 'Y.M.D',
                'timepref': 'H:m A',
                'decimal_precision': 3
            };
        });
        var dateArrayStub = sandbox.stub(SUGAR.charts, '_dateStringArray').callsFake(function(listLabel) {
            return [listLabel];
        });

        // test default user prefs just like getLocale
        prefs = SUGAR.charts.getUserLocale();
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

        userPrefStub.restore();
        dateArrayStub.restore();
    });

    it('should return a user preference when userPreference is called', function() {
        var strings;
        var expected;
        var appUserPref = sandbox.stub(app.user, 'getPreference').callsFake(function(pref) {
            return pref === 'Foo' ? 'Bar' : 'Baz';
        });
        pref = SUGAR.charts.userPreference('Foo');
        expect(pref).toBe('Bar');
        pref = SUGAR.charts.userPreference('Foop');
        expect(pref).toBe('Baz');
        appUserPref.restore();
    });

    it('should return an object with locale format preference when getSystemLocale is called', function() {
        var strings;
        var expected;
        var sysPrefStub = sandbox.stub(SUGAR.charts, '_getSystemPreferences').callsFake(function() {
            return {
                'decimal_separator': '*',
                'number_grouping_separator': '^',
                'currency_symbol': '#',
                'currency_id': 123,
                'datepref': 'Y.M.D',
                'timepref': 'H:m A',
                'decimal_precision': 3
            };
        });
        var dateArrayStub = sandbox.stub(SUGAR.charts, '_dateStringArray').callsFake(function(listLabel) {
            return [listLabel];
        });

        // test default user prefs just like getLocale
        prefs = SUGAR.charts.getSystemLocale();
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

        sysPrefStub.restore();
        dateArrayStub.restore();
    });

    // Pseudo private methods
    it('should return a date format string when _dateFormat is called', function() {
        var formatter;
        formatter = SUGAR.charts._dateFormat();
        expect(formatter).toBe('%b %-d, %Y');
        formatter = SUGAR.charts._dateFormat('M/D/Y');
        expect(formatter).toBe('%M/%D/%Y');
        formatter = SUGAR.charts._dateFormat('y.m.d');
        expect(formatter).toBe('%y.%m.%d');
    });

    it('should return a time format string when _timeFormat is called', function() {
        var formatter;
        formatter = SUGAR.charts._timeFormat();
        expect(formatter).toBe('%-I:%M:%S');
        formatter = SUGAR.charts._timeFormat('H:m A');
        expect(formatter).toBe('%H:%m');
        formatter = SUGAR.charts._timeFormat('h:i');
        expect(formatter).toBe('%I:%M');
    });

    it('should return a time format string when _timePeriods is called', function() {
        var periods;
        periods = SUGAR.charts._timePeriods();
        expect(periods).toEqual(['AM', 'PM']);
        periods = SUGAR.charts._timePeriods('H:m A');
        expect(periods).toEqual([' AM', ' PM']);
        periods = SUGAR.charts._timePeriods('H:mA');
        expect(periods).toEqual(['AM', 'PM']);
        periods = SUGAR.charts._timePeriods('H:m a');
        expect(periods).toEqual([' am', ' pm']);
        periods = SUGAR.charts._timePeriods('H:ma');
        expect(periods).toEqual(['am', 'pm']);
        periods = SUGAR.charts._timePeriods('H:m');
        expect(periods).toEqual(['', '']);
    });

    it('should return an array of date strings when _dateStringArray is called', function() {
        var dateStrings;
        var dateArrayStub = sandbox.stub(SUGAR.charts, 'translateListStrings').callsFake(function(listLabel) {
            return listLabel === 'noempty' ? {foo: 'Foo', bar: 'Bar'} : {baz: '', foo: 'Foo', bar: 'Bar'};
        });
        dateStrings = SUGAR.charts._dateStringArray('noempty');
        expect(dateStrings).toEqual(['Foo', 'Bar']);
        dateStrings = SUGAR.charts._dateStringArray('withempty');
        expect(dateStrings).toEqual(['Foo', 'Bar']);
        dateArrayStub.restore();
    });

    it('should return a tooltip template when _getTooltipTemplate is called', function() {
        var strings;
        var expected;
        var appTemplate = sandbox.stub(app.template, 'getField').callsFake(function(field, templateName, module) {
            return templateName === 'multipletooltiptemplate' ?
                'Foo' :
                'Bar';
        });
        template = SUGAR.charts._getTooltipTemplate('barChart');
        expect(template).toBe('Foo');
        template = SUGAR.charts._getTooltipTemplate('lineChart');
        expect(template).toBe('Foo');
        template = SUGAR.charts._getTooltipTemplate('pieChart');
        expect(template).toBe('Bar');
        appTemplate.restore();
    });

    describe('when button objects are use for the confirmation buttons', function() {
        var tooltipTemplate;
        var appCurrency;
        var numberFormat;
        var percentFormat;
        var isNumeric;
        var chart;

        beforeEach(function() {
            tooltipTemplate = sandbox.stub(SUGAR.charts, '_getTooltipTemplate').callsFake(function(point) {
                return JSON.stringify(point);
            });
            appCurrency = sandbox.stub(app.currency, 'formatAmountLocale').callsFake(function(value, currencyId) {
                return '#' + value;
            });
            numberFormat = sandbox.stub(sucrose.utility, 'numberFormat').callsFake(
                function(value, precision, currency, locale) {
                    return value;
                }
            );
            percentFormat = sandbox.stub(sucrose.utility, 'numberFormatPercent').callsFake(
                function(value, total, locale) {
                    return (value * 100 / total) + '%';
                }
            );
            isNumeric = sandbox.stub(sucrose.utility, 'isNumeric').callsFake(function(value) {
                var v = parseFloat(value);
                return !isNaN(v) && typeof v === 'number' && isFinite(v);
            });
            chart = {
                strings: function() {
                    return {
                        noDrillthru: 'Drill through not supported.',
                        tooltip: {
                            amount: 'Amount',
                            count: 'Count'
                        }
                    };
                },
                locality: function() {
                    return {
                        'decimal': '*',
                        'thousands': '^',
                        'grouping': [3],
                        'currency': ['#', ''],
                        'currency_id': 123,
                        'precision': 3
                    };
                },
                getKey: function() {
                    return function(d) { return d.key; };
                },
                getValue: function() {
                    return function(d) { return d.y; };
                },
                xValueFormat: function() {
                    return function(d, i, label, isDateTime, formatter) {
                        return label ? label : isDateTime ? '12/31/2017' : d;
                    };
                }
            };
        });

        afterEach(function() {
            tooltipTemplate.restore();
            appCurrency.restore();
            numberFormat.restore();
            percentFormat.restore();
            isNumeric.restore();
            chart = null;
            properties = null;
        });

        it('should render a tooltip with single data field template', function() {
            var tooltip;
            var expected;
            var properties = {
                yDataType: 'numeric',
                total: 100
            };
            var eo = {
                key: 'My Key',
                y: 50
            };
            var params = {
                allow_drillthru: true
            };

            // Test numeric tooltip
            tooltip = SUGAR.charts.formatTooltipSingle(eo, properties, chart, tooltipTemplate, params);
            expected = {
                key: 'My Key',
                label: 'Count',
                value: 50,
                percent: '50%'
            };
            expect(tooltip).toEqual(JSON.stringify(expected));

            // Test currency tooltip
            properties.yDataType = 'currency';
            tooltip = SUGAR.charts.formatTooltipSingle(eo, properties, chart, tooltipTemplate, params);
            expected = {
                key: 'My Key',
                label: 'Amount',
                value: '#50',
                percent: '50%'
            };
            expect(tooltip).toEqual(JSON.stringify(expected));

            // Verify percent formatting is called
            properties.total = 200;
            tooltip = SUGAR.charts.formatTooltipSingle(eo, properties, chart, tooltipTemplate, params);
            expected = {
                key: 'My Key',
                label: 'Amount',
                value: '#50',
                percent: '25%'
            };
            expect(tooltip).toEqual(JSON.stringify(expected));

            // Verify drill through message is displayed
            params.allow_drillthru = false;
            tooltip = SUGAR.charts.formatTooltipSingle(eo, properties, chart, tooltipTemplate, params);
            expected = {
                key: 'My Key',
                label: 'Amount',
                value: '#50',
                percent: '25%',
                msg: 'Drill through not supported.'
            };
            expect(tooltip).toEqual(JSON.stringify(expected));
        });

        it('should render a tooltip with multiple data fields template', function() {
            var tooltip;
            var expected;
            var properties = {
                yDataType: 'numeric',
                xDataType: 'ordinal',
                seriesType: 'string',
                seriesName: 'Series',
                groupName: 'Group'
            };
            var eo = {
                point: {
                    x: 1,
                    y: 50
                },
                pointIndex: 0,
                group: {
                    label: 'My group',
                    _height: 100
                },
                series: {
                    key: 'My series'
                }
            };
            var params = {
                allow_drillthru: true
            };

            // Test numeric tooltip
            tooltip = SUGAR.charts.formatTooltipMultiple(eo, properties, chart, tooltipTemplate, params);
            expected = {
                valueName: 'Count',
                valueLabel: 50,
                groupName: 'Group',
                groupLabel: 'My group',
                seriesName: 'Series',
                seriesLabel: 'My series',
                percent: '50%'
            };
            expect(tooltip).toEqual(JSON.stringify(expected));

            // Test currency tooltip
            properties.yDataType = 'currency';
            tooltip = SUGAR.charts.formatTooltipMultiple(eo, properties, chart, tooltipTemplate, params);
            expected = {
                valueName: 'Amount',
                valueLabel: '#50',
                groupName: 'Group',
                groupLabel: 'My group',
                seriesName: 'Series',
                seriesLabel: 'My series',
                percent: '50%'
            };
            expect(tooltip).toEqual(JSON.stringify(expected));

            // Verify percent formatting is called
            eo.group._height = 200;
            tooltip = SUGAR.charts.formatTooltipMultiple(eo, properties, chart, tooltipTemplate, params);
            expected = {
                valueName: 'Amount',
                valueLabel: '#50',
                groupName: 'Group',
                groupLabel: 'My group',
                seriesName: 'Series',
                seriesLabel: 'My series',
                percent: '25%'
            };
            expect(tooltip).toEqual(JSON.stringify(expected));

            // Verify drill through message is displayed
            params.allow_drillthru = false;
            tooltip = SUGAR.charts.formatTooltipMultiple(eo, properties, chart, tooltipTemplate, params);
            expected = {
                valueName: 'Amount',
                valueLabel: '#50',
                groupName: 'Group',
                groupLabel: 'My group',
                seriesName: 'Series',
                seriesLabel: 'My series',
                percent: '25%',
                msg: 'Drill through not supported.'
            };
            expect(tooltip).toEqual(JSON.stringify(expected));
            params.allow_drillthru = true;

            // Verify percent not displayed if 100%
            eo.group._height = 50;
            tooltip = SUGAR.charts.formatTooltipMultiple(eo, properties, chart, tooltipTemplate, params);
            expected = {
                valueName: 'Amount',
                valueLabel: '#50',
                groupName: 'Group',
                groupLabel: 'My group',
                seriesName: 'Series',
                seriesLabel: 'My series'
            };
            expect(tooltip).toEqual(JSON.stringify(expected));

            // Verify series label is displayed as numeric
            eo.series.key = 50;
            properties.seriesType = 'numeric';
            tooltip = SUGAR.charts.formatTooltipMultiple(eo, properties, chart, tooltipTemplate, params);
            expected = {
                valueName: 'Amount',
                valueLabel: '#50',
                groupName: 'Group',
                groupLabel: 'My group',
                seriesName: 'Series',
                seriesLabel: 50
            };
            expect(tooltip).toEqual(JSON.stringify(expected));

            // Verify series label is displayed as currency
            properties.seriesType = 'currency';
            tooltip = SUGAR.charts.formatTooltipMultiple(eo, properties, chart, tooltipTemplate, params);
            expected = {
                valueName: 'Amount',
                valueLabel: '#50',
                groupName: 'Group',
                groupLabel: 'My group',
                seriesName: 'Series',
                seriesLabel: '#50'
            };
            expect(tooltip).toEqual(JSON.stringify(expected));
        });
    });

    // Not suitable for unit tests. See integration tests.
    // callback
    // openDrawer
    // renderChart
    // renderError
    // get
    // trackWindowResize
    // saveImageFile
});
