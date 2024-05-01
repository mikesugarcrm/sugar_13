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

describe('Base.Fields.ForecastParetoChart', function() {

    var app, field, context, parent, sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();

        app.user.setPreference('decimal_precision', 2);
        app.user.setPreference('decimal_separator', '.');
        app.user.setPreference('number_grouping_separator', ',');

        context = app.context.getContext();
        context.parent = new Backbone.Model();
        context.parent.set('selectedUser', {id: 'test_user', is_manager: false});
        context.parent.set('selectedTimePeriod', 'test_timeperiod');
        context.parent.set('module', 'Forecasts');

        var def = {
                'name': 'paretoChart',
                'label': 'Pareto Chart',
                'type': 'forecast-pareto-chart',
                'view': 'detail'
            },
            model = new Backbone.Model();

        field = SugarTest.createField('base', 'paretoChart', 'forecast-pareto-chart', 'def', 'Forecasts', model);

        field._serverData = {
            'title': 'Test',
            'labels': [],
            'data': [
                {
                    'id': 'test_row_1',
                    'sales_stage': 'test_1',
                    'likely': 50,
                    'best': 50,
                    'worst': 50,
                    'forecast': 'exclude',
                    'probability': 10
                }
            ]
        };
    });

    afterEach(function() {
        sandbox.restore();
    });

    describe('adjustProbabilityLabels', function() {
        beforeEach(function() {
            field._serverData = {
                'title': 'Test',
                'labels': [],
                'data': [
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'exclude',
                        'probability': 10
                    },
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'exclude',
                        'probability': 15
                    },
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'exclude',
                        'probability': 20
                    },
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'exclude',
                        'probability': 10
                    }
                ]
            };
        });

        afterEach(function() {
        });

        it('should set 3 probability labels in the correct order', function() {
            field.adjustProbabilityLabels();

            expect(_.keys(field._serverData.labels.probability).length).toEqual(3);
            expect(field._serverData.labels.probability).toEqual({ 10: 10, 15: 15, 20: 20 });
        });
    });

    describe('convertDataToChartData', function() {
        var s1, s2;
        beforeEach(function() {
            s1 = sandbox.stub(field, 'convertManagerDataToChartData').callsFake(function() {
            });
            s2 = sandbox.stub(field, 'convertRepDataToChartData').callsFake(function() {
            });
        });

        afterEach(function() {
            sandbox.restore();
            s1 = null;
            s2 = null;
            field.preview_open = false;
            field.state = 'open';
        });

        it('should call convertManagerDataToChartData when manager', function() {
            field.model.set('display_manager', true, {silent: true});
            field.convertDataToChartData();

            expect(s1).toHaveBeenCalled();
            expect(s2).not.toHaveBeenCalled();
        });
        it('should call convertManagerDataToChartData when manager', function() {
            field.model.set('display_manager', false, {silent: true});
            field.convertDataToChartData();

            expect(s1).not.toHaveBeenCalled();
            expect(s2).toHaveBeenCalled();
        });

        it('should return -1 since preview is open', function() {
            field.preview_open = true;

            expect(field.convertDataToChartData()).toEqual(-1)
        });

        it('should return -1 since state is closed', function() {
            field.state = 'closed';

            expect(field.convertDataToChartData()).toEqual(-1)
        });

    });

    describe('convertManagerDataToChartData', function() {
        beforeEach(function() {
            sandbox.stub(field, 'convertDataToChartData').callsFake(function() {
            });
            sandbox.stub(field, 'generateD3Chart').callsFake(function() {
            });

            field.model.set('dataset', 'likely', {silent: true});
            field._serverData = {
                'title': 'Test',
                'quota': 5,
                'labels': {
                    dataset: {
                        'likely': 'Likely',
                        'likely_adjusted': 'Likely (Adjusted)',
                        'best': 'Best',
                        'best_adjusted': 'Best (Adjusted)'
                    }
                },
                'data': [
                    {
                        'id': 'test_row_1',
                        'user_id': 'test_1',
                        'name': 'test 1',
                        'likely': 50,
                        'likely_adjusted': 55,
                        'best': 50,
                        'best_adjusted': 55,
                        'worst': 50,
                        'worst_adjusted': 55
                    },
                    {
                        'id': 'test_row_2',
                        'user_id': 'test_2',
                        'name': 'test 2',
                        'likely': 50,
                        'likely_adjusted': 55,
                        'best': 50,
                        'best_adjusted': 55,
                        'worst': 50,
                        'worst_adjusted': 55
                    }
                ]
            };
        });

        afterEach(function() {
        });

        it('should contain two bars and two lines', function() {
            field.convertManagerDataToChartData();
            expect(field.d3Data.data.length).toEqual(4);

            // the first two should be bars
            var b = field.d3Data.data.slice(0, 2);
            // she second two should be lines
            var l = field.d3Data.data.slice(2);

            expect(b[0].type).toEqual('bar');
            expect(b[1].type).toEqual('bar');

            expect(l[0].type).toEqual('line');
            expect(l[1].type).toEqual('line');
        });

        it('properties should contain two groupData items', function() {
            field.convertManagerDataToChartData();
            expect(field.d3Data.properties.groupData.length).toEqual(2);
        });

        it('properties should contain quota', function() {
            field.convertManagerDataToChartData();
            expect(field.d3Data.properties.quota).toEqual(5);
        });

        it('should disable the bar and line if they are disabled', function() {
            sandbox.stub(field, 'getDisabledChartKeys').callsFake(function() {
                return ['Likely'];
            });

            field.convertManagerDataToChartData();
            expect(field.d3Data.data.length).toEqual(4);

            // the first two should be bars
            var b = field.d3Data.data.slice(0, 2);
            // she second two should be lines
            var l = field.d3Data.data.slice(2);

            expect(b[0].disabled).toEqual(true);
            expect(b[1].disabled).toEqual(false);

            expect(l[0].disabled).toEqual(true);
            expect(l[1].disabled).toEqual(false);
        });
    });

    describe('convertRepDataToChartData', function() {
        beforeEach(function() {
            sandbox.stub(field, 'convertDataToChartData').callsFake(function() {
            });
            sandbox.stub(field, 'generateD3Chart').callsFake(function() {
            });

            field.model.set('dataset', 'likely', {silent: true});
            field.model.set('group_by', 'forecast');
            field.model.set('ranges', ['include']);
            field._serverData = {
                'title': 'Test',
                'labels': {
                    forecast: {
                        'include': 'Included',
                        'exclude': 'Excluded'
                    },
                    dataset: {
                        'likely': 'Likely',
                        'likely_adjusted': 'Likely (Adjusted)',
                        'best': 'Best',
                        'best_adjusted': 'Best (Adjusted)'
                    }
                },
                'x-axis': [
                    {
                        'label': 'G1',
                        'start_timestamp': 50,
                        'end_timestamp': 150
                    },
                    {
                        'label': 'G2',
                        'start_timestamp': 151,
                        'end_timestamp': 250
                    },
                    {
                        'label': 'G3',
                        'start_timestamp': 251,
                        'end_timestamp': 350
                    }
                ],
                'data': [
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'include',
                        'probability': 10,
                        'date_closed_timestamp': 100
                    },
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'include',
                        'probability': 15,
                        'date_closed_timestamp': 100
                    },
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'include',
                        'probability': 20,
                        'date_closed_timestamp': 200
                    },
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'include',
                        'probability': 10,
                        'date_closed_timestamp': 300
                    }
                ]
            };
        });

        afterEach(function() {
            sandbox.restore();
        });

        it('should contain one bar and one line', function() {
            field.convertRepDataToChartData('forecast');
            expect(field.d3Data.data.length).toEqual(2);

            expect(field.d3Data.data[0].type).toEqual('bar');
            expect(field.d3Data.data[1].type).toEqual('line');
        });

        it('bar should be disabled', function() {
            sandbox.stub(field, 'getDisabledChartKeys').callsFake(function() {
                return ['Included'];
            });
            field.convertRepDataToChartData('forecast');
            expect(field.d3Data.data.length).toEqual(2);

            expect(field.d3Data.data[0].disabled).toEqual(true);

        });
        it('should contain 3 x-axis groups', function() {
            field.convertRepDataToChartData('forecast');
            expect(field.d3Data.properties.groupData.length).toEqual(3);
        });

        it('bar should contain 3 values', function() {
            field.convertRepDataToChartData('forecast');
            expect(field.d3Data.data[0].values.length).toEqual(3);
            expect(field.d3Data.data[0].values[0].y).toEqual(100);
            expect(field.d3Data.data[0].values[1].y).toEqual(50);
            expect(field.d3Data.data[0].values[2].y).toEqual(50);
        });

        it('line should contain 3 values', function() {
            field.convertRepDataToChartData('forecast');
            expect(field.d3Data.data[1].values.length).toEqual(3);
            expect(field.d3Data.data[1].values[0].y).toEqual(100);
            expect(field.d3Data.data[1].values[1].y).toEqual(150);
            expect(field.d3Data.data[1].values[2].y).toEqual(200);
        });

        it('should not contain any NaNs', function() {
            field._serverData.data = [{
                'id': 'test_row_1',
                'sales_stage': 'test_1',
                'likely': NaN,
                'best': NaN,
                'worst': NaN,
                'forecast': 'include',
                'probability': 10,
                'date_closed_timestamp': 100
            }];

            field.convertRepDataToChartData('forecast');
            expect(field.d3Data.data[1].values[0].y).toEqual(0);
        });
    });

    describe('buildChartUrl', function() {
        beforeEach(function() {
        });

        afterEach(function() {
            field.model.unset('timeperiod_id', {silent: true});
            field.model.unset('user_id', {silent: true});
            field.model.unset('display_manager', {silent: true});
        });

        it('should return url for rep', function() {
            field.model.set({
                timeperiod_id: 'a',
                user_id: 'b',
                display_manager: false
            }, {silent: true});

            var result = field.buildChartUrl();
            result = result.split('/');
            expect(result[0]).toBe('ForecastWorksheets');
            expect(result[1]).toBe('chart');
            expect(result[2]).toBe('a');
            expect(result[3]).toBe('b');
        });

        it('should return url for manager', function() {
            field.model.set({
                timeperiod_id: 'a',
                user_id: 'b',
                display_manager: true
            }, {silent: true});

            var result = field.buildChartUrl();
            result = result.split('/');
            expect(result[0]).toBe('ForecastManagerWorksheets');
            expect(result[1]).toBe('chart');
            expect(result[2]).toBe('a');
            expect(result[3]).toBe('b');
        });
    });

    describe("when setServerData is called", function() {
        beforeEach(function() {
            sandbox.stub(field, 'convertDataToChartData').callsFake(function() {
            });
            sandbox.stub(field, 'generateD3Chart').callsFake(function() {
            });
        });

        afterEach(function() {
            sandbox.restore();
        });

        describe("when the pane visible and the preview panel is hidden", function() {
            beforeEach(function() {
                field.state = "open";
                field.preview_open = false;
                field.collapsed = false;
                field.setServerData({}, false);
            });
            it("should call convertDataToChartData", function() {
                expect(field.convertDataToChartData).toHaveBeenCalled();
            });
            it("should call generateD3Chart", function() {
                expect(field.generateD3Chart).toHaveBeenCalled();
            });
        });

        describe("when the pane hidden and the preview panel is hidden", function() {
            beforeEach(function() {
                field.state = "closed";
                field.preview_open = false;
                field.collapsed = false;
                field.setServerData({}, false);
            });
            it("should not call convertDataToChartData", function() {
                expect(field.convertDataToChartData).not.toHaveBeenCalled();
            });
            it("should not call generateD3Chart", function() {
                expect(field.generateD3Chart).not.toHaveBeenCalled();
            });
        });

        describe("when the preview panel is visible", function() {
            beforeEach(function() {
                field.state = "open";
                field.preview_open = true;
                field.collapsed = false;
                field.setServerData({}, false);
            });
            it("should not call convertDataToChartData", function() {
                expect(field.convertDataToChartData).not.toHaveBeenCalled();
            });
            it("should not call generateD3Chart", function() {
                expect(field.generateD3Chart).not.toHaveBeenCalled();
            });
        });

        describe("when the dashlet is collapsed", function() {
            beforeEach(function() {
                field.state = "open";
                field.preview_open = false;
                field.collapsed = true;
                field.setServerData({}, false);
            });
            it("should not call convertDataToChartData", function() {
                expect(field.convertDataToChartData).not.toHaveBeenCalled();
            });
            it("should not call generateD3Chart", function() {
                expect(field.generateD3Chart).not.toHaveBeenCalled();
            });
        });

        describe("when the dashlet is not collapsed", function() {
            beforeEach(function() {
                field.state = "open";
                field.preview_open = false;
                field.collapsed = false;
                field.setServerData({}, false);
            });
            it("should call convertDataToChartData", function() {
                expect(field.convertDataToChartData).toHaveBeenCalled();
            });
            it("should call generateD3Chart", function() {
                expect(field.generateD3Chart).toHaveBeenCalled();
            });
        });
    });

    using('isDashletVisible',
        [['open', false, true], ['open', true, false], ['closed', false, false], ['closed', true, true]],
        function(state, preview_open, collapsed) {
            it('should return false', function() {
                field.state = state;
                field.preview_open = preview_open;
                field.collapsed = collapsed;
                expect(field.isDashletVisible()).toBeFalsy();
            });
        }
    );

    using('isDashletVisible',
        [['open', false, false]],
        function(state, preview_open, collapsed) {
            it('should return true', function() {
                field.state = state;
                field.preview_open = preview_open;
                field.collapsed = collapsed;
                expect(field.isDashletVisible()).toBeTruthy();
            });
        }
    );

    describe("isDashletVisible()", function() {
        it("should be false if disposed", function() {
            field.state = "open";
            field.preview_open = false;
            field.collapsed = true;
            field.setServerData({}, false);
            field.disposed = true;

            expect(field.isDashletVisible()).toBeFalsy();
        });
    });

    describe('getDisabledChartKeys', function() {
        it('should return empty', function() {
            sandbox.stub(d3sugar, 'select').callsFake(function() {
                return {
                    data: function() {
                        return [];
                    }
                };
            });

            expect(field.getDisabledChartKeys()).toEqual([]);
        });
        it('should return one key', function() {
            sandbox.stub(d3sugar, 'select').callsFake(function() {
                return {
                    data: function() {
                        return [{
                            data: [{
                                disabled: true,
                                key: 'disabled'
                            }, {
                                disabled: false,
                                key: 'not disabled'
                            }, {
                                key: 'no disabled key'
                            }]
                        }];
                    }
                };
            });

            var keys = field.getDisabledChartKeys();
            expect(keys.length).toEqual(1);
            expect(keys[0]).toEqual('disabled');
        });
    });
});
