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

describe('Base.Views.ForecastPareto', function() {

    var app, view, context, sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();

        app.user.setPreference('decimal_precision', 2);
        app.user.setPreference('decimal_separator', '.');
        app.user.setPreference('number_grouping_separator', ',');

        SugarTest.loadPlugin('Dashlet');

        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        context.parent = app.context.getContext({
            selectedUser: {
                id: 'test_user',
                is_manager: false
            },
            selectedTimePeriod: 'test_timeperiod',
            module: 'Forecasts'
        });
        context.parent.children = [];

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        sandbox.stub(app.metadata, 'getView').callsFake(function() {
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
            };
        });

        sandbox.stub(app.utils, 'checkForecastConfig').callsFake(function() {
            return true;
        });

        sandbox.stub(app.lang, 'getAppListStrings').callsFake(function() {
            return {
                'best': 1,
                'likely': 1
            };
        });

        var meta = {
                config: false
            },
            layout = SugarTest.createLayout('base', 'Forecasts', 'list', null, context.parent),
            initCallback = {
                defaultSelections: {
                    timeperiod_id: {
                        id: 'test_id',
                        label: 'test timeperiod'
                    },
                    dataset: [],
                    group_by: []
                },
                initData: {
                    userData: {
                        is_manager: 0
                    }
                }
            };

        sandbox.stub(app.api, 'call').callsFake(function() {
        });

        layout.setTitle = function() {};

        view = SugarTest.createView('base', 'Forecasts', 'forecast-pareto', meta, context, false, layout, true);
        view.forecastInitCallback(initCallback);
    });

    afterEach(function() {
        // delete the plug so it doesn't affect other dashlets unless they load the plugin
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

    describe('findModelToListen', function() {
        it('listenModel should equal model', function() {
            view.findModelToListen();
            expect(view.listenModel).toEqual(view.model);
        });

        it('when on opps and forecasting by RLI, it should equal collection', function() {
            view.context.set('module', 'Opportunities');
            view.context.parent = undefined;
            var c1 = new Backbone.Model();
            c1.set('collection', new Backbone.Collection());
            c1.set('module', 'RevenueLineItems');
            view.context.children = [c1];
            view.findModelToListen();
            expect(view.listenModel).toEqual(c1.get('collection'));
        });

        it('when on opps and forecasting by RLI, it should equal collection from the view.context.parent.children', function() {
            view.context.set('module', 'Forecasts');
            view.context.parent = app.context.getContext({module: 'Opportunities'});
            var c1 = new Backbone.Model();
            c1.set('collection', new Backbone.Collection());
            c1.set('module', 'RevenueLineItems');
            view.context.parent.children = [c1];
            view.findModelToListen();
            expect(view.listenModel).toEqual(c1.get('collection'));
        });
    });

    describe('addRowToChart', function() {
        var field, getFieldStub, serverData;
        beforeEach(function() {
            field = {
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
                    serverData = data;
                }
            }
            getFieldStub = sandbox.stub(view, 'getField').callsFake(function() {
                return field;
            });
        });

        afterEach(function() {
            app.user.unset('id');
            getFieldStub.restore();
        });

        it('when model is not assigned to current user, not call anything', function() {
            view.addRowToChart(new Backbone.Model({'assigned_user_id': 'jasmine_test'}));
            expect(getFieldStub).not.toHaveBeenCalled();
        });

        it('when model is assigned, call the field methods', function() {
            app.user.set({'id': 'jasmine_test'});
            var s1 = sandbox.spy(field, 'getServerData'),
                s2 = sandbox.spy(field, 'setServerData');

            view.addRowToChart(new Backbone.Model({'assigned_user_id': 'jasmine_test', id: 'test_1'}));
            expect(getFieldStub).toHaveBeenCalled();
            expect(s1).toHaveBeenCalled();
            expect(s2).toHaveBeenCalled();
        });

        it('should add row', function() {
            app.user.set({'id': 'jasmine_test'});
            var s1 = sandbox.spy(field, 'getServerData'),
                s2 = sandbox.spy(field, 'setServerData');

            view.addRowToChart(new Backbone.Model({'assigned_user_id': 'jasmine_test', id: 'test_1'}));
            expect(getFieldStub).toHaveBeenCalled();
            expect(s1).toHaveBeenCalled();
            expect(s2).toHaveBeenCalled();
            expect(serverData.data.length).toEqual(2);
        });
    });
    describe('removeRowFromChart', function() {
        var field, getFieldStub, serverData;
        beforeEach(function() {
            field = {
                getServerData: function() {
                    return {
                        'title': 'Test',
                        'labels': [],
                        'data': [
                            {
                                'id': 'test_row_1',
                                'record_id': 'test_row_1',
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
                    serverData = data;
                }
            }
            getFieldStub = sandbox.stub(view, 'getField').callsFake(function() {
                return field;
            });
        });

        afterEach(function() {
            getFieldStub.restore();
            app.user.unset('id');
        });

        it('when model is assigned, call the field methods', function() {
            app.user.set({'id': 'jasmine_test'});
            var s1 = sandbox.spy(field, 'getServerData'),
                s2 = sandbox.spy(field, 'setServerData');

            view.removeRowFromChart(new Backbone.Model({'assigned_user_id': 'jasmine_test'}));
            expect(getFieldStub).toHaveBeenCalled();
            expect(s1).toHaveBeenCalled();
            expect(s2).toHaveBeenCalled();
        });

        it('should remove the row', function() {
            app.user.set({'id': 'jasmine_test'});
            var s1 = sandbox.spy(field, 'getServerData'),
                s2 = sandbox.spy(field, 'setServerData');

            view.removeRowFromChart(new Backbone.Model({'assigned_user_id': 'jasmine_test', 'id': 'test_row_1'}));
            expect(getFieldStub).toHaveBeenCalled();
            expect(s1).toHaveBeenCalled();
            expect(s2).toHaveBeenCalled();
            expect(serverData.data.length).toEqual(0);
        });
    });

    describe('handleDataChange', function() {
        var field, getFieldStub, serverData, model;
        beforeEach(function() {
            field = {
                getServerData: function() {
                    return {
                        'title': 'Test',
                        'labels': [],
                        'x-axis': [
                            {
                                'start_timestamp': 50,
                                'end_timestamp': 150
                            }
                        ],
                        'data': [
                            {
                                'id': 'test_row_1',
                                'record_id': 'test_row_1',
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
                    serverData = data;
                },
                hasServerData: function() {
                    return true;
                }
            }
            getFieldStub = sandbox.stub(view, 'getField').callsFake(function() {
                return field;
            });
            app.user.set({'id': 'jasmine_test'});
            model = new Backbone.Model({
                'assigned_user_id' : 'jasmine_test',
                'date_closed_timestamp': 100,
                'id': 'test_row_1',
                'base_rate': 1.0
            });
        });

        afterEach(function() {
            getFieldStub.restore();
            app.user.unset('id');
        });

        it('should not run when display_manager is true', function() {
            view.settings.set({'display_manager': true}, {silent: false});
            view.handleDataChange(model);
            expect(getFieldStub).not.toHaveBeenCalled();
            view.settings.set({'display_manager': false}, {silent: false});
        });

        it('should update sales_stage', function() {
            model.set({'sales_stage': 'fake_stage'});
            view.handleDataChange(model);
            expect(serverData.data[0].sales_stage).toEqual('fake_stage');
        });

        it('should update forecast', function() {
            model.set({'commit_stage': 'fake_stage'});
            view.handleDataChange(model);
            expect(serverData.data[0].forecast).toEqual('fake_stage');
        });

        it('should update likely', function() {
            model.set({'likely_case': 60});
            view.handleDataChange(model);
            expect(serverData.data[0].likely).toEqual('60.000000');
        });

        it('should update likely when amount is changed', function() {
            model.set({'amount': 60});
            view.handleDataChange(model);
            expect(serverData.data[0].likely).toEqual('60.000000');
        });

        it('should update best', function() {
            model.set({'best_case': 60});
            view.handleDataChange(model);
            expect(serverData.data[0].best).toEqual('60.000000');
        });

        it('should update worst', function() {
            model.set({'worst_case': 60});
            view.handleDataChange(model);
            expect(serverData.data[0].worst).toEqual('60.000000');
        });

        it('should update view.settings when timeperiod not found', function() {
            field.once = function() {};
            onceSpy = sandbox.spy(field, 'once');
            viewStub = sandbox.stub(view.settings, 'set').callsFake(function() {});
            model.set('date_closed_timestamp', 250);
            view.handleDataChange(model);
            expect(onceSpy).toHaveBeenCalled();
            expect(viewStub).toHaveBeenCalled();
            onceSpy.restore();
            viewStub.restore();
        });

        it('should fail out when field we are watching is changed', function() {
            model.set({'batman': 'dark knight'});
            view.handleDataChange(model);
            expect(getFieldStub).not.toHaveBeenCalled();
        });
    });
});
