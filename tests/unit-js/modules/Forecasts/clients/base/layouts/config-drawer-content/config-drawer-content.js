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
describe('Forecasts.Layout.ConfigDrawerContent', function() {
    var app,
        layout;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Forecasts', 'config-drawer-content', null, null, true);

        sinon.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                forecast_by: 'RevenueLineItems'
            }
        })
    });

    afterEach(function() {
        sinon.restore();
        layout = null;
    });

    describe('_initHowTo()', function() {
        it('should set all Forecast howto text properly', function() {
            layout._initHowTo();
            expect(layout.timeperiodsTitle).toEqual('LBL_FORECASTS_CONFIG_TITLE_TIMEPERIODS');
            expect(layout.timeperiodsText).toEqual('LBL_FORECASTS_CONFIG_HELP_TIMEPERIODS');
            expect(layout.scenariosTitle).toEqual('LBL_FORECASTS_CONFIG_TITLE_SCENARIOS');
            expect(layout.scenariosText).toEqual('LBL_FORECASTS_CONFIG_HELP_SCENARIOS');
            expect(layout.rangesTitle).toEqual('LBL_FORECASTS_CONFIG_TITLE_RANGES');
            expect(layout.rangesText).toEqual('LBL_FORECASTS_CONFIG_HELP_RANGES');
            expect(layout.forecastByTitle).toEqual('LBL_FORECASTS_CONFIG_HOWTO_TITLE_FORECAST_BY');
            expect(layout.forecastByText).toEqual('LBL_FORECASTS_CONFIG_HELP_FORECAST_BY');
            expect(layout.wkstColumnsTitle).toEqual('LBL_FORECASTS_CONFIG_TITLE_WORKSHEET_COLUMNS');
            expect(layout.wkstColumnsText).toEqual('LBL_FORECASTS_CONFIG_HELP_WORKSHEET_COLUMNS');
        });
    });

    describe('_switchHowToData()', function() {
        beforeEach(function() {
            layout._initHowTo();
            layout.currentHowToData = {};
        });
        it('should set currentHowToData properly for Timeperiods', function() {
            layout._switchHowToData('config-timeperiods');
            expect(layout.currentHowToData.title).toEqual('LBL_FORECASTS_CONFIG_TITLE_TIMEPERIODS');
            expect(layout.currentHowToData.text).toEqual('LBL_FORECASTS_CONFIG_HELP_TIMEPERIODS');
        });

        it('should set currentHowToData properly for Ranges', function() {
            layout._switchHowToData('config-ranges');
            expect(layout.currentHowToData.title).toEqual('LBL_FORECASTS_CONFIG_TITLE_RANGES');
            expect(layout.currentHowToData.text).toEqual('LBL_FORECASTS_CONFIG_HELP_RANGES');
        });

        it('should set currentHowToData properly for Scenarios', function() {
            layout._switchHowToData('config-scenarios');
            expect(layout.currentHowToData.title).toEqual('LBL_FORECASTS_CONFIG_TITLE_SCENARIOS');
            expect(layout.currentHowToData.text).toEqual('LBL_FORECASTS_CONFIG_HELP_SCENARIOS');
        });

        it('should set currentHowToData properly for ForecastBy', function() {
            layout._switchHowToData('config-forecast-by');
            expect(layout.currentHowToData.title).toEqual('LBL_FORECASTS_CONFIG_HOWTO_TITLE_FORECAST_BY');
            expect(layout.currentHowToData.text).toEqual('LBL_FORECASTS_CONFIG_HELP_FORECAST_BY');
        });

        it('should set currentHowToData properly for WorksheetColumns', function() {
            layout._switchHowToData('config-worksheet-columns');
            expect(layout.currentHowToData.title).toEqual('LBL_FORECASTS_CONFIG_TITLE_WORKSHEET_COLUMNS');
            expect(layout.currentHowToData.text).toEqual('LBL_FORECASTS_CONFIG_HELP_WORKSHEET_COLUMNS');
        });
    });
});
