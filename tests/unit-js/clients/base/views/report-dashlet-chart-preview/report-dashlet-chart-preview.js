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
describe('Base.View.ReportDashletChartPreview', function() {
    var app;
    var context;
    var layout;
    var view;
    var initOptions;
    var sandbox = sinon.createSandbox();
    var viewName = 'report-dashlet-chart-preview';

    beforeEach(function() {
        app = SugarTest.app;

        context = new app.Context();
        context.set({
            model: new Backbone.Model(),
            module: 'Home',
        });

        context.parent = new app.Context({
            module: 'Home',
            model: new Backbone.Model({
                defaultSelectView: 'list',
                chartType: 'pieF',
                label: 'testLabel',
                showXLabel: true,
            }),
        });

        layout = SugarTest.createLayout(
            'base',
            'Home',
            'list',
            null,
            context.parent
        );

        initOptions = {
            context: context.parent
        };

        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', viewName);
        SugarTest.loadComponent('base', 'view', viewName);
    });

    afterEach(function() {
        sinon.restore();
        sandbox.restore();
        layout.dispose();
        app = null;
        view = null;
        layout = null;
    });

    describe('initialize()', function() {
        var testView;
        beforeEach(function() {
            // createView() implicitly calls initialize() through the class constructor,
            // so theoretically no need to call it independently, however, in order to spy functions
            // that are called in the initialize, we'll have to reinit it anyways
            testView = SugarTest.createView(
                'base',
                null,
                viewName,
                null,
                context,
                true,
                layout,
                true
            );

            sandbox.spy(testView, '_initProperties');

            testView.initialize(initOptions);

        });

        it('should init the default properties, also the properties from layout model', function() {
            expect(testView._chartTypeMapping).toEqual({
                pieF: 'pie-chart-skeleton-loader',
                donutF: 'donut-chart-skeleton-loader',
                funnelF: 'funnel-chart-skeleton-loader',
                treemapF: 'treemap-chart-skeleton-loader',
                lineF: 'line-chart-skeleton-loader',
                hBarF: 'h-bar-grouped-chart-skeleton-loader',
                vBarF: 'v-bar-grouped-chart-skeleton-loader',
                vGBarF: 'v-bar-chart-skeleton-loader',
                hGBarF: 'h-bar-chart-skeleton-loader'
            });

            expect(testView._chartType).toEqual('pie-chart-skeleton-loader');
            expect(testView._showXLabel).toEqual(true);
            expect(testView._dashletTitle).toEqual('testLabel');
        });

        it('it should call the _initProperties', function() {
            expect(testView._initProperties.called).toEqual(true);
        });

        afterEach(function() {
            testView.dispose();
        });
    });
});
