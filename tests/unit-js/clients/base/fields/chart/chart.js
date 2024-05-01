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
describe('Base.Field.Chart', function() {
    var field, app;


    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base','chart', 'chart', 'detail');
        sinon.stub(field, 'bindDataChange').callsFake(function() {});
        sinon.stub(field, 'generateD3Chart').callsFake(function() {});
    });

    afterEach(function() {
        app = undefined;
        field = undefined;
    });

    describe('getChartConfig()', function() {
        var rawChartData;
        var rawChartParams;

        beforeEach(function() {
            rawChartData = {};
            rawChartData.values = [0, 1, 2];
            rawChartData.properties = [];
            rawChartData.properties.push({type: ''});
            rawChartParams = {};
            rawChartParams.chart_type = '';
        });

        afterEach(function() {
            rawChartData = undefined;
            rawChartParams = undefined;
        });

        it('should return proper chart config -- pie chart', function() {
            rawChartParams.chart_type = 'pie chart';
            var cfg = field.getChartConfig(rawChartData, rawChartParams);

            expect(cfg.pieType).toEqual('basic');
            expect(cfg.chartType).toEqual('pieChart');
            expect(rawChartData.properties[0].type).toEqual(rawChartParams.chart_type);
        });

        it('should return proper chart config -- line chart', function() {
            rawChartParams.chart_type = 'line chart';
            var cfg = field.getChartConfig(rawChartData, rawChartParams);

            expect(cfg.lineType).toEqual('grouped');
            expect(cfg.chartType).toEqual('lineChart');
            expect(rawChartData.properties[0].type).toEqual(rawChartParams.chart_type);
        });

        it('should return proper chart config -- funnel chart 3D', function() {
            rawChartParams.chart_type = 'funnel chart 3D';
            var cfg = field.getChartConfig(rawChartData, rawChartParams);

            expect(cfg.funnelType).toEqual('basic');
            expect(cfg.chartType).toEqual('funnelChart');
            expect(rawChartData.properties[0].type).toEqual(rawChartParams.chart_type);
        });

        it('should return proper chart config -- gauge chart', function() {
            rawChartParams.chart_type = 'gauge chart';
            var cfg = field.getChartConfig(rawChartData, rawChartParams);

            expect(cfg.gaugeType).toEqual('basic');
            expect(cfg.chartType).toEqual('gaugeChart');
            expect(rawChartData.properties[0].type).toEqual(rawChartParams.chart_type);
        });

        it('should return proper chart config -- stacked group by chart', function() {
            rawChartParams.chart_type = 'stacked group by chart';
            var cfg = field.getChartConfig(rawChartData, rawChartParams);

            expect(cfg.orientation).toEqual('vertical');
            expect(cfg.barType).toEqual('stacked');
            expect(cfg.chartType).toEqual('barChart');
            expect(rawChartData.properties[0].type).toEqual(rawChartParams.chart_type);
        });

        it('should return proper chart config -- group by chart', function() {
            rawChartParams.chart_type = 'group by chart';
            var cfg = field.getChartConfig(rawChartData, rawChartParams);

            expect(cfg.orientation).toEqual('vertical');
            expect(cfg.barType).toEqual('grouped');
            expect(cfg.chartType).toEqual('barChart');
            expect(rawChartData.properties[0].type).toEqual(rawChartParams.chart_type);
        });

        it('should return proper chart config -- bar chart', function() {
            rawChartParams.chart_type = 'bar chart';
            var cfg = field.getChartConfig(rawChartData, rawChartParams);

            expect(cfg.orientation).toEqual('vertical');
            expect(cfg.barType).toEqual('basic');
            expect(cfg.chartType).toEqual('barChart');
            expect(rawChartData.properties[0].type).toEqual(rawChartParams.chart_type);
        });

        it('should return proper chart config -- horizontal group by chart', function() {
            rawChartParams.chart_type = 'horizontal group by chart';
            var cfg = field.getChartConfig(rawChartData, rawChartParams);

            expect(cfg.orientation).toEqual('horizontal');
            expect(cfg.barType).toEqual('stacked');
            expect(cfg.chartType).toEqual('barChart');
            expect(rawChartData.properties[0].type).toEqual(rawChartParams.chart_type);
        });

        it('should return proper chart config -- horizontal bar chart', function() {
            rawChartParams.chart_type = 'horizontal bar chart';
            var cfg = field.getChartConfig(rawChartData, rawChartParams);

            expect(cfg.orientation).toEqual('horizontal');
            expect(cfg.barType).toEqual('basic');
            expect(cfg.chartType).toEqual('barChart');
            expect(rawChartData.properties[0].type).toEqual(rawChartParams.chart_type);
        });

        it('should return proper chart config -- horizontal', function() {
            rawChartParams.chart_type = 'horizontal';
            var cfg = field.getChartConfig(rawChartData, rawChartParams);

            expect(cfg.orientation).toEqual('horizontal');
            expect(cfg.barType).toEqual('basic');
            expect(cfg.chartType).toEqual('barChart');
            expect(rawChartData.properties[0].type).toEqual(rawChartParams.chart_type);
        });

        it('should return proper chart config -- default', function() {
            rawChartParams.chart_type = '';
            var cfg = field.getChartConfig(rawChartData, rawChartParams);

            expect(cfg.orientation).toEqual('vertical');
            expect(cfg.barType).toEqual('stacked');
            expect(cfg.chartType).toEqual('barChart');
            expect(rawChartData.properties[0].type).toEqual(rawChartParams.chart_type);
        });
    });
});
