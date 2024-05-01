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

describe('Forecasts.Base.Views.ForecastsChart', function() {

    var app, view, context, parent, sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();

        app.user.setPreference('decimal_precision', 2);
        app.user.setPreference('decimal_separator', '.');
        app.user.setPreference('number_grouping_separator', ',');

        SugarTest.loadPlugin('Dashlet');

        context = app.context.getContext();
        context.parent = app.context.getContext({
            selectedUser: {
                id: 'test_user',
                is_manager: false
            },
            selectedTimePeriod: 'test_timeperiod',
            module: 'Forecasts',
            model: new Backbone.Model()
        });
        context.parent.children = [];

        getViewStub = sandbox.stub(app.metadata, 'getView').callsFake(function() {
            return {
                'chart': {
                    'name': 'paretoChart',
                    'label': 'Pareto Chart',
                    'type': 'forecast-pareto-chart'
                },
                'group_by': {
                    'name': 'group_by',
                    'label': 'LBL_DASHLET_FORECASTS_GROUPBY',
                    'type': 'enum',
                    'searchBarThreshold': 5,
                    'default': true,
                    'enabled': true,
                    'view': 'edit',
                    'options': 'forecasts_chart_options_group'
                },
                'dataset': {
                    'name': 'dataset',
                    'label': 'LBL_DASHLET_FORECASTS_DATASET',
                    'type': 'enum',
                    'searchBarThreshold': 5,
                    'default': true,
                    'enabled': true,
                    'view': 'edit',
                    'options': 'forecasts_options_dataset'
                }
            }
        });

        sandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                'show_worksheet_worst': 0,
                'show_worksheet_likely': 1,
                'show_worksheet_best': 1
            }
        });

        sandbox.stub(app.lang, 'getAppListStrings').callsFake(function() {
            return {
                'worst': 1,
                'best': 1,
                'likely': 1
            }
        });

        var meta = {
                config: false
            },
            layout = SugarTest.createLayout("base", 'Forecasts', "list", null, context.parent);

        view = SugarTest.createView('base', 'Forecasts', 'forecasts-chart', meta, context, true, layout, true);

        view.serverData = {
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
        delete app.plugins.plugins['view']['Dashlet'];
        sandbox.restore();
    });

    describe('initDashlet', function() {
        it('dashletConfig should not have worst', function() {
            expect(view.dashletConfig.dataset.options['worst']).toBeUndefined();
        });
        it('dashletConfig should have best and likely', function() {
            expect(view.dashletConfig.dataset.options['likely']).toBeDefined();
            expect(view.dashletConfig.dataset.options['best']).toBeDefined();
        });
    });

    describe('findWorksheetContexts', function() {
        var c1, c2, m1, m2, c1stub, c2stub;
        beforeEach(function() {
            // set the parent context collection
            c1 = new Backbone.Collection();
            m1 = new Backbone.Model({'module': 'ForecastWorksheets', 'collection': c1});
            c1stub = sandbox.stub(c1, 'on').callsFake(function() {
            });

            c2 = new Backbone.Collection();
            m2 = new Backbone.Model({'module': 'ForecastManagerWorksheets', 'collection': c2});
            sandbox.stub(c2, 'on').callsFake(function() {
            });
            c2stub = view.context.parent.children = [m1, m2];
        });

        afterEach(function() {
            sandbox.restore();
            view.context.parent.children = [];
        });

        it('should find and call on on collections', function() {
            view.findWorksheetContexts();
            expect(c1stub).toHaveBeenCalled();
            expect(c1stub).toHaveBeenCalled();
        });
    });

    describe('repWorksheetChanged', function() {
        var m;
        beforeEach(function() {
            m = new Backbone.Model({'id': 'test_row_1', 'base_rate': 1.0});
            sandbox.stub(view, 'getField').callsFake(function() {
                return {
                    getServerData: function() {
                        return {
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
                        }
                    },
                    setServerData: function(data) {
                        view.serverData = data;
                    }
                }
            });
        });

        afterEach(function() {
            m = null;
            sandbox.restore();
        });

        it('should update sales_stage', function() {
            m.set({'sales_stage': 'fake_stage'});
            view.repWorksheetChanged(m);
            expect(view.serverData.data[0].sales_stage).toEqual('fake_stage');
        });

        it('should update forecast', function() {
            m.set({'commit_stage': 'fake_stage'});
            view.repWorksheetChanged(m);
            expect(view.serverData.data[0].forecast).toEqual('fake_stage');
        });

        it('should update likely', function() {
            m.set({'likely_case': 60});
            view.repWorksheetChanged(m);
            expect(view.serverData.data[0].likely).toEqual('60.000000');
        });

        it('should update best', function() {
            m.set({'best_case': 60});
            view.repWorksheetChanged(m);
            expect(view.serverData.data[0].best).toEqual('60.000000');
        });

        it('should update worst', function() {
            m.set({'worst_case': 60});
            view.repWorksheetChanged(m);
            expect(view.serverData.data[0].worst).toEqual('60.000000');
        });
    });

    describe('mgrWorksheetChanged', function() {
        var m;
        beforeEach(function() {
            m = new Backbone.Model({'user_id': 'test_1', 'base_rate': 1.0, 'quota': 5});
            sandbox.stub(view, 'getField').callsFake(function() {
                return {
                    getServerData: function() {
                        return  {
                            'title': 'Test',
                            'quota': 5,
                            'labels': [],
                            'data': [
                                {
                                    'id': 'test_row_1',
                                    'user_id': 'test_1',
                                    'likely': 50,
                                    'likely_adjusted': 55,
                                    'best': 50,
                                    'best_adjusted': 55,
                                    'worst': 50,
                                    'worst_adjusted': 55
                                }
                            ]
                        };
                    },
                    setServerData: function(data) {
                        view.serverData = data;
                    },
                    hasServerData: function() {
                        return true;
                    }
                }
            });
        });

        it('should change likely_adjusted', function() {
            m.set({likely_case_adjusted: 45});
            view.mgrWorksheetChanged(m);
            expect(view.serverData.data[0].likely_adjusted).toEqual(45);
        });
        it('should change worst_adjusted', function() {
            m.set({worst_case_adjusted: 45});
            view.mgrWorksheetChanged(m);
            expect(view.serverData.data[0].worst_adjusted).toEqual(45);
        });
        it('should change best_adjusted', function() {
            m.set({best_case_adjusted: 45});
            view.mgrWorksheetChanged(m);
            expect(view.serverData.data[0].best_adjusted).toEqual(45);
        });

        it('should change quota', function() {
            m.set({quota: 45});
            view.mgrWorksheetChanged(m);
            expect(view.serverData.quota).toEqual('45');
        });
    });

    describe('parseManagerWorksheet', function() {
        let c = new Backbone.Collection();
        let sandbox = sinon.createSandbox();

        beforeEach(function() {
            c.add([
                {
                    likely_case: '500.00',
                    likely_case_adjusted: '500.00',
                    base_rate: 1.0,
                    quota: '500.00',
                    id: 'test_1_id',
                    name: 'test 1',
                    user_id: 'test_1'
                },
                {
                    likely_case: '500.00',
                    likely_case_adjusted: '500.00',
                    base_rate: 1.0,
                    quota: '500.00',
                    id: 'test_2_id',
                    name: 'test 2',
                    user_id: 'test_2'
                }
            ]);
        });

        afterEach(function() {
            c.reset({}, {silent: true});
            sandbox.restore();
        });

        it('quota should be 1000.00', function() {
            var server_data;
            sandbox.stub(view, 'getField').callsFake(function() {
                return {
                    getServerData: function() {
                        return {};
                    },
                    setServerData: function(serverData) {
                        server_data = serverData;
                    }
                };
            });

            view.parseManagerWorksheet(c);
            expect(server_data.quota).toEqual('1000.000000');
        });
    });
});
