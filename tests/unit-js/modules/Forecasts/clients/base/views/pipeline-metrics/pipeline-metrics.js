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
describe('Forecasts.View.PipelineMetrics', function() {
    let app;
    let layout;
    let view;
    let settings;
    let meta;

    beforeEach(function() {
        app = SugarTest.app;
        meta = {
            config: false
        };

        sinon.stub(app.metadata, 'getModule')
            .withArgs('Forecasts', 'config')
            .returns({
                is_setup: 1
            });
        sinon.stub(app.acl, 'hasAccess')
            .withArgs('read', 'Forecasts')
            .returns(true);
        sinon.stub(window, 'setInterval');

        SugarTest.testMetadata.init();
        SugarTest.loadPlugin('Dashlet');
        SugarTest.loadHandlebarsTemplate('pipeline-metrics', 'view', 'base', 'pipeline-metrics', 'Forecasts');
        SugarTest.testMetadata.set();

        layout = SugarTest.createLayout('base', 'Forecasts', 'base');
        view = SugarTest.createView('base', 'Forecasts', 'pipeline-metrics', meta, null, true, layout);
        settings = new Backbone.Model();
        view.settings = settings;

        view.dashletConfig = {
            panels: {
                dashlet_settings: {
                    fields: [
                        {
                            name: 'metrics',
                            options: {},
                            maximumSelectionSize: 2
                        }
                    ]
                }
            }
        };
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        layout.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    describe('_getAvailableForecastMetrics', function() {
        let forecastMetricsMeta;

        beforeEach(function() {
            forecastMetricsMeta = {
                'forecast-metrics': {
                    fakeMetric: {
                        name: 'fakeMetric',
                        label: 'LBL_FAKE',
                        helpText: 'LBL_FAKE_HELP',
                        commitStageDom: 'commit_dropdown',
                        commitStageDomOption: 'won'
                    }
                }
            }
            sinon.stub(app.metadata, 'getView')
                .withArgs('Forecasts', 'forecast-metrics')
                .returns(forecastMetricsMeta);
            sinon.stub(app.lang, 'getAppListStrings')
                .withArgs('commit_dropdown')
                .returns({
                    won: 'Won'
                })
            sinon.stub(app.lang, 'get')
                .withArgs('LBL_COMMIT_STAGE_FORECAST', 'Opportunities')
                .returns('Forecast Stage')
                .withArgs('LBL_FAKE_HELP', 'Forecasts', {
                    forecastStage: 'Forecast Stage',
                    commitStageValue: 'Won'
                })
                .returns('A Forecast stage of Won')
                .withArgs('LBL_FAKE')
                .returns('Fake Metric');
        });

        it('should format the labels of the metrics based on field and DOM labels', function() {
            expect(view._getAvailableForecastMetrics()).toEqual({
                fakeMetric: {
                    name: 'fakeMetric',
                    label: 'Fake Metric',
                    helpText: 'A Forecast stage of Won',
                    commitStageDom: 'commit_dropdown',
                    commitStageDomOption: 'won'
                }
            });
        });
    });

    describe('getSelectedMetrics', function() {
        beforeEach(function() {
            view.availableMetrics = {
                metric1: {
                    name: 'metric1'
                },
                metric2: {
                    name: 'metric2'
                },
                metric3: {
                    name: 'metric3'
                }
            };
        });

        it('should match up the metric definitions with the selected metric names', function() {
            view.settings.set('metrics', ['metric1', 'metric2']);
            expect(view.getSelectedMetrics()).toEqual({
                metric1: {
                    name: 'metric1'
                },
                metric2: {
                    name: 'metric2'
                }
            });
        });

        it('should not include metrics that are not in the available set', function() {
            view.settings.set('metrics', ['metric1', 'notAMetric']);
            expect(view.getSelectedMetrics()).toEqual({
                metric1: {
                    name: 'metric1'
                }
            });
        });

        it('should show max number of metrics allowed if none are selected', function() {
            view.settings.set('metrics', undefined);
            expect(view.getSelectedMetrics()).toEqual({
                metric1: {
                    name: 'metric1'
                },
                metric2: {
                    name: 'metric2'
                },
            });
        });
    });

    describe('_initDashletConfig', function() {
        beforeEach(function() {
            view.availableMetrics = {
                metric1: {
                    name: 'metric1',
                    label: 'LBL_METRIC_1'
                },
                metric2: {
                    name: 'metric2',
                    label: 'LBL_METRIC_2'
                }
            }

            sinon.stub(app.lang, 'get')
                .withArgs('LBL_METRIC_1', 'Forecasts')
                .returns('Metric 1')
                .withArgs('LBL_METRIC_2', 'Forecasts')
                .returns('Metric 2');
            sinon.stub(view.layout, 'before');
            sinon.stub(view, 'listenTo');
        });

        it('should set the options of the Metrics dropdown', function() {
            expect(view.dashletConfig.panels.dashlet_settings.fields[0].options).toEqual({});
            view._initDashletConfig();
            expect(view.dashletConfig.panels.dashlet_settings.fields[0].options).toEqual({
                metric1: 'Metric 1',
                metric2: 'Metric 2'
            });
        });

        it('should initialize the necessary config listeners', function() {
            view._initDashletConfig();
            expect(view.layout.before).toHaveBeenCalledWith('dashletconfig:save', view._validateConfig, view);
            expect(view.listenTo).toHaveBeenCalledWith(view.settings, 'change:metrics',
                view._handleConfigMetricsChange);
        });
    });

    describe('_initDashletDisplay', function() {
        beforeEach(function() {
            sinon.stub(view, '_startAutoRefresh');
            sinon.stub(view, 'loadData');
        });

        it('should start the auto refresh timer', function() {
           view._initDashletDisplay();
           expect(view._startAutoRefresh).toHaveBeenCalled();
        });

        it('should set a listener to reload data when the selected user ID changes', function() {
            view._initDashletDisplay();
            view.context.set('selectedUserId', '12345');
            expect(view.loadData).toHaveBeenCalled();
        });

        it('should set a listener to reload data when the selected user type changes', function() {
            view._initDashletDisplay();
            view.context.set('selectedUserType', 'NewValue');
            expect(view.loadData).toHaveBeenCalled();
        });

        it('should set a listener to reload data when the selected time period changes', function() {
            view._initDashletDisplay();
            view.context.set('selectedTimePeriodId', '12345');
            expect(view.loadData).toHaveBeenCalled();
        });

        describe('when on the Forecasts view', function() {
            beforeEach(function() {
                sinon.stub(app.controller.context, 'get')
                    .withArgs('module').returns('Forecasts')
                    .withArgs('layout').returns('records')
                    .withArgs('selectedUser').returns({
                        id: '1234'
                    })
                    .withArgs('forecastType').returns('Rollup')
                    .withArgs('selectedTimePeriod').returns('4321');
            });

            it('should initialize the forecast parameters to the values on the Forecast page', function() {
                view._initDashletDisplay();
                expect(view.context.get('selectedUserId')).toEqual('1234');
                expect(view.context.get('selectedUserType')).toEqual('Rollup');
                expect(view.context.get('selectedTimePeriodId')).toEqual('4321');
            });

            it('should update the dashlet\'s user ID when the forecast view user ID changes', function() {
                view._initDashletDisplay();
                app.controller.context.get.withArgs('selectedUser').returns({
                    id: 'aaaa'
                });
                app.controller.context.trigger('filter:selectedUser:changed');
                expect(view.context.get('selectedUserId')).toEqual('aaaa');
                expect(view.loadData).toHaveBeenCalled();
            });

            it('should update the dashlet\'s user type when the forecast view user type changes', function() {
                view._initDashletDisplay();
                app.controller.context.get.withArgs('forecastType').returns('NewType');
                app.controller.context.trigger('filter:selectedUser:changed');
                expect(view.context.get('selectedUserType')).toEqual('NewType');
                expect(view.loadData).toHaveBeenCalled();
            });

            it('should update the dashlet\'s time period when the forecast view time period changes', function() {
                view._initDashletDisplay();
                app.controller.context.get.withArgs('selectedTimePeriod').returns('9999');
                app.controller.context.trigger('filter:selectedTimePeriod:changed');
                expect(view.context.get('selectedTimePeriodId')).toEqual('9999');
                expect(view.loadData).toHaveBeenCalled();
            });
        });

        describe('when not on the Forecasts view', function() {
            beforeEach(function() {
                sinon.stub(app.user, 'get')
                    .withArgs('id').returns('1111')
                    .withArgs('is_manager').returns(true);
            });

            it('should initialize the forecast paramaters to the current user and current time period', function() {
                view._initDashletDisplay();
                expect(view.context.get('selectedUserId')).toEqual('1111');
                expect(view.context.get('selectedUserType')).toEqual('Rollup');
                expect(view.context.get('selectedTimePeriodId')).toEqual('');
            });
        });
    });

    describe('_startAutoRefresh', function() {
        beforeEach(function() {
            sinon.stub(view, '_stopAutoRefresh');
        });

        it('should remove the old timer first', function() {
            view.settings.set('refresh_interval', 100000);
            view._startAutoRefresh();
            expect(view._stopAutoRefresh).toHaveBeenCalledBefore(window.setInterval);
            expect(window.setInterval).toHaveBeenCalled();
        });

        it('should not add a new timer if no interval is set', function() {
            view.settings.set('refresh_interval', 0);
            view._startAutoRefresh();
            expect(view._stopAutoRefresh).toHaveBeenCalled();
            expect(window.setInterval).not.toHaveBeenCalled();
        });
    });

    describe('_stopAutoRefresh', function() {
        beforeEach(function() {
            sinon.stub(window, 'clearInterval');
        });

        it('should clear the interval if there is one set', function() {
           view._autoRefreshId = '1234';
           view._stopAutoRefresh();
           expect(window.clearInterval).toHaveBeenCalledWith('1234');
        });
    });

    describe('toggleMetricDefinitions', function() {
        let addClassStub;
        let removeClassStub;

        beforeEach(function() {
            addClassStub = sinon.stub();
            removeClassStub = sinon.stub();
            sinon.stub(view.layout.$el, 'find').returns({
                addClass: addClassStub,
                removeClass: removeClassStub
            });
            sinon.stub(app.acl, 'hasAccessToModel').returns(true);
        });

        it('should show the definitions if they are hidden', function() {
            view.render();
            view.$el.find('.metric-descriptions-container').addClass('hide');
            expect(view.$el.find('.metric-descriptions-container').hasClass('hide')).toEqual(true);
            view.toggleMetricDefinitions();
            expect(view.$el.find('.metric-descriptions-container').hasClass('hide')).toEqual(false);
        });

        it('should add the active color on the button when showing', function() {
            view.render();
            view.$el.find('.metric-descriptions-container').addClass('hide');
            view.toggleMetricDefinitions();
            expect(addClassStub).toHaveBeenCalled();
        });

        it('should remove the active color on the button when hiding', function() {
            view.render();
            view.$el.find('.metric-descriptions-container').removeClass('hide');
            view.toggleMetricDefinitions();
            expect(removeClassStub).toHaveBeenCalled();
        });

        it('should hide the definitions if they are shown', function() {
            view.render();
            view.$el.find('.metric-descriptions-container').removeClass('hide');
            expect(view.$el.find('.metric-descriptions-container').hasClass('hide')).toEqual(false);
            view.toggleMetricDefinitions();
            expect(view.$el.find('.metric-descriptions-container').hasClass('hide')).toEqual(true);
        });
    });

    describe('loadData', function() {
        beforeEach(function() {
            view._forecastsIsAvailable = true;
            view._isConfig = false;
            view.metrics = {
                metric1: {name: 'metric1'}
            };
            sinon.stub(view, '_loadMetrics');
        });

        it('should pass the complete callback to _loadMetrics', function() {
            let completeFn = function(){};
            view.loadData({
                complete: completeFn
            });
            expect(view._loadMetrics).toHaveBeenCalledWith(completeFn);
        });

        it('should not try to load data if Forecasts is not available', function() {
            view._forecastsIsAvailable = false;
            view.loadData();
            expect(view._loadMetrics).not.toHaveBeenCalled();
        });

        it('should not try to load data in config mode', function() {
            view._isConfig = true;
            view.loadData();
            expect(view._loadMetrics).not.toHaveBeenCalled();
        });

        it('should not try to load data if no metrics are specified', function() {
            view.metrics = {};
            view.loadData();
            expect(view._loadMetrics).not.toHaveBeenCalled();
        });
    });

    describe('_loadMetrics', function() {
        beforeEach(function() {
            view.metrics = {
                metric1: {name: 'metric1'}
            };
            sinon.stub(view, '_loadMetric');
            sinon.stub(app.api, 'abortRequest');
        });

        it('should cancel any open metric requests first', function() {
            view._activeMetricRequests = {
                123: {uid: 123}
            };
            view._loadMetrics();
            expect(app.api.abortRequest).toHaveBeenCalledWith(123);
            expect(app.api.abortRequest).toHaveBeenCalledBefore(view._loadMetric);
        });

        it('should pass the complete callback to each metric', function() {
            let completeFn = sinon.stub();
            view._loadMetrics(completeFn);
            expect(view._loadMetric).toHaveBeenCalledWith(view.metrics.metric1, completeFn);
        });
    });

    describe('_formatCurrencyMetricResult', function() {
        beforeEach(function() {
            sinon.stub(app.currency, 'getBaseCurrencyId').returns('-99');
            sinon.stub(app.currency, 'getCurrencySymbol')
                .withArgs('-99').returns('$')
                .withArgs('1').returns('!')
            sinon.stub(app.user, 'getPreference');
        });

        describe('when the user preferred currency matches the system one', function() {
            beforeEach(function() {
                app.user.getPreference.withArgs('currency_id').returns('-99');
            });

            it('should return the value and tooltip', function() {
                expect(view._formatCurrencyMetricResult(1234.56)).toEqual({
                    value: '$1,235',
                    tooltip: '$1,234.56'
                });
            });
        });

        describe('when the user preferred currency does not match the system one', function() {
            beforeEach(function() {
                app.user.getPreference.withArgs('currency_id').returns('1');
                sinon.stub(app.currency, 'convertAmount').callsFake(function(amount) {
                    return amount * 2;
                });
            });

            it('should return the value, converted values, and tooltip', function() {
                expect(view._formatCurrencyMetricResult(1234.56)).toEqual({
                    value: '$1,235',
                    convertedValue: '!2,469',
                    tooltip: '$1,234.56 | !2,469.12'
                });
            });
        });
    });

    describe('_formatRatioMetricResult', function() {
        it('should return the value and tooltip', function() {
            expect(view._formatRatioMetricResult(1.2345)).toEqual({
                value: '123%',
                tooltip: '123.45%'
            });
        });
    });

    describe('_formatFloatMetricResult', function() {
        it('should return the value and tooltip', function() {
            expect(view._formatFloatMetricResult(0)).toEqual({
                value: '0x',
                tooltip: '0.00x'
            });
            expect(view._formatFloatMetricResult(12.34)).toEqual({
                value: '12.3x',
                tooltip: '12.34x'
            });
        });
    });

    describe('_formatNumberMetricResult', function() {
        it('should return the value and tooltip', function() {
            expect(view._formatNumberMetricResult(12.34)).toEqual({
                value: '12',
                tooltip: '12.34'
            });
        });
    });
});
