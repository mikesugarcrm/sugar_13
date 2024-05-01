
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

describe('Forecasts.Layout.Filter', function() {

    let app;
    let layout;
    let context;
    let filterPanelLayout;
    let filterPanelContext;
    let moduleName = 'Forecasts';

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set({
            model: new Backbone.Model(),
            collection: app.data.createBeanCollection('Forecasts')
        });
        context.prepare();
        filterPanelContext = context;
        context.parent = {
            on: sinon.stub(),
            off: $.noop
        };

        SugarTest.loadFile('../include/javascript/sugar7', 'utils', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        filterPanelLayout = SugarTest.createLayout('base', 'Forecasts', 'filterpanel',
            {context: {listViewModule: 'Opportunities'}}, filterPanelContext);
        layout = SugarTest.createLayout('base', moduleName, 'filter', {}, context, true, {layout: filterPanelLayout});
    });

    afterEach(function() {
        // app.user.getAcls.restore();
        sinon.restore();
        layout = null;
        context = null;
    });

    describe('initialize', function() {
        beforeEach(function() {
            sinon.stub(layout, '_super');
            layout.initialize([]);
        });

        it('should call the parent initialize method', function() {
            expect(layout._super).toHaveBeenCalledWith('initialize', [[]]);
        });

        it('should initialize the activeMetrics', function() {
            expect(layout.activeMetrics).toEqual([]);
        });
    });

    describe('bindDataChange', function() {
        beforeEach(function() {
            layout.listenTo = sinon.stub();
        });

        it('should listen to events on layout.layout.context', function() {
            sinon.stub(layout, '_retriggerFilter');
            layout.bindDataChange();

            expect(layout.listenTo).toHaveBeenCalledWith(
                layout.layout.context,
                'filter:selectedTimePeriod:changed filter:selectedUser:changed',
                layout._retriggerFilter
            );
        });

        it('should listen to forecast:metric:active event on layout.layout', function() {
            sinon.stub(layout, '_handleActiveMetricsChange');
            layout.bindDataChange();

            expect(layout.listenTo).toHaveBeenCalledWith(
                layout.layout,
                'forecast:metric:active',
                layout._handleActiveMetricsChange
            );
        });
    });

    describe('_handleActiveMetricsChange', function() {
        beforeEach(function() {
            layout.activeMetrics = [];
            sinon.stub(layout, '_retriggerFilter');
            layout._handleActiveMetricsChange(['test1', 'test2']);
        });

        it('should set layout.activeMetrics', function() {
            expect(layout.activeMetrics).toEqual(['test1', 'test2']);
        });
    });

    describe('_retriggerFilter', function() {
        beforeEach(function() {
            sinon.stub(jQuery.fn, 'val').returns('test');
            sinon.stub(layout, 'trigger');
            layout._retriggerFilter();
        });

        it('should trigger filter:apply qith query', function() {
            expect(layout.trigger).toHaveBeenCalledWith('filter:apply', 'test');
        });
    });

    describe('_getCollectionParams', function() {
        beforeEach(function() {
            layout.activeMetrics = ['test1', 'test2'];
            sinon.stub(layout.layout.context, 'get')
                .withArgs('selectedUser').returns({id: 'testUser'})
                .withArgs('forecastType').returns('testType')
                .withArgs('selectedTimePeriod').returns('testPeriod');
        });

        it('should return forecasts specific data in an object', function() {
            expect(layout._getCollectionParams()).toEqual({
                user_id: 'testUser',
                type: 'testType',
                time_period: 'testPeriod',
                metrics: ['test1', 'test2']
            });
        });
    });
});
