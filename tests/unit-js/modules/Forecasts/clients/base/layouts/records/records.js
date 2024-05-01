
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

describe("Forecasts.Layout.Records", function() {

    var app, layout, stubs = [], apiCallStub, moduleName = 'Forecasts';

    beforeEach(function() {
        app = SUGAR.App;
        SugarTest.testMetadata.init();
        SugarTest.loadFile("../include/javascript/sugar7", "utils", "js", function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        // FIXME SC-4484 should remove getCloseSelector and review this
        SugarTest.loadComponent('base', 'view', 'alert');
        SugarTest.loadHandlebarsTemplate('alert', 'view', 'base', 'error');

        SugarTest.testMetadata.set();

        app.alert.init();


        app.user.setPreference('datepref', 'm/d/Y');

        sinon.stub(app.user, 'getAcls').callsFake(function() {
            var acls = {};
            acls['Forecasts'] = {};
            return acls;
        });
        sinon.stub(app.lang, 'getAppListStrings').callsFake(function() {
            return {
                'Prospecting': 'Prospecting',
                'Qualification': 'Qualification',
                'Needs Analysis': 'Needs Analysis',
                'Value Proposition': 'Value Proposition',
                'Id. Decision Makers': 'Id. Decision Makers',
                'Perception Analysis': 'Perception Analysis',
                'Proposal/Price Quote': 'Proposal/Price Quote',
                'Negotiation/Review': 'Negotiation/Review',
                'Closed Won': 'Closed Won',
                'Closed Lost': 'Closed Lost'
            };
        });
        app.data.reset();
        app.data.declareModel(moduleName, SugarTest.app.metadata.getModule(moduleName));

        app.user.set({'id': 'test_userid', full_name: 'Selected User'});

        apiCallStub = sinon.stub(app.api, 'call').callsFake(function() {});
        stubs.push(sinon.stub(app.api, 'buildURL').callsFake(function() {}));
        stubs.push(sinon.stub(app.data, 'getSyncCallbacks').callsFake(function() {}));

        layout = SugarTest.createLayout('base', moduleName, 'records', null, null, true);
        sinon.stub(layout, 'syncInitData');
    });

    afterEach(function() {

        app.user.setPreference('datepref', null);
        sinon.restore();
        stubs = [];
        layout.dispose();
    });

    it('should have initOptions set', function() {
        expect(_.isUndefined(layout.initOptions)).toBeFalsy();
    });

    it('should have called all stubs', function() {
        _.each(stubs, function(stub) {
            expect(stub).toHaveBeenCalled();
        });
    });

    describe('checkSalesWonLost', function() {
        beforeEach(function() {
        });

        afterEach(function() {
            app.metadata.getModule.restore();
        });

        it('config set correctly, should continue on to syncInitData', function() {
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    sales_stage_won: ['Closed Won'],
                    sales_stage_lost: ['Closed Lost']
                }
            });

            layout.initialize({});

            expect(layout.syncInitData).toHaveBeenCalled();
        });
    });

    describe('initForecastsModule', function() {
        var initData = {}, getSelectedUsersReporteesStub, ctxOnceStub;
        beforeEach(function() {
            getSelectedUsersReporteesStub = sinon.stub(app.utils, 'getSelectedUsersReportees').callsFake(function() {
            });
            ctxOnceStub = sinon.stub(layout.initOptions.context, 'once').callsFake(function() {
            });
            initData = {
                "initData": {
                    "userData": {"is_manager": false, "showOpps": false, "first_name": "Max", "last_name": "Jensen"},
                    "forecasts_setup": 1
                },
                "defaultSelections": {
                    'timeperiod_id': {
                        'id': 'test_tp_id',
                        'label': 'Q2 (04/01/2013 - 06/30/2013)',
                        'start' : '2013-04-01',
                        'end': '2013-06-30'
                    },
                    "ranges": ["include"],
                    "group_by": "forecast",
                    "dataset": "likely"}
            };
        });

        afterEach(function() {
            initData = {};
            getSelectedUsersReporteesStub.restore();
            ctxOnceStub.restore();
        });

        it('should set default values on the initOptions Context', function() {
            layout.initForecastsModule(initData, {});

            expect(layout.initOptions.context.get('currentForecastCommitDate')).toEqual(null);
            expect(layout.initOptions.context.get('selectedTimePeriod')).toEqual(initData.defaultSelections.timeperiod_id.id);
            expect(layout.initOptions.context.get('selectedRanges')).toEqual(initData.defaultSelections.ranges);
            expect(layout.initOptions.context.get('selectedTimePeriodStartEnd')).toEqual({
                start: '2013-04-01',
                end: '2013-06-30'
            });

            expect(getSelectedUsersReporteesStub).toHaveBeenCalled();
        });

        it('should set a once trigger on initOptions.context', function() {
            layout.initForecastsModule(initData, {});
            expect(ctxOnceStub).toHaveBeenCalled();
        });

        it('will use the users last selected ranges when set', function() {
            sinon.stub(app.user.lastState, 'get').callsFake(function() {
                return ['include', 'exclude'];
            });

            layout.initForecastsModule(initData, {});

            expect(layout.initOptions.context.get('selectedRanges')).toEqual(['include', 'exclude']);

            sinon.restore();
        });
    });

    describe('_onceInitSelectedUser', function() {
        var renderStub, changedOptions = {};
        beforeEach(function() {
            changedOptions = {
                id: 'test_id',
                is_manager: false,
                showOpps: false
            };
            renderStub = sinon.stub(layout, 'render').callsFake(function() {
            });
        });

        afterEach(function() {
            changedOptions = {};
            renderStub.restore();
        });

        describe('layout.render', function() {
            it('should be called', function() {
                layout._onceInitSelectedUser(layout.initOptions.context.model, changedOptions);
                expect(renderStub).toHaveBeenCalled();
            });

            it('should not be called when disposed', function() {
                layout.disposed = true;
                layout._onceInitSelectedUser(layout.initOptions.context.model, changedOptions);
                expect(renderStub).not.toHaveBeenCalled();
            });
        });

        describe('model should have correct forecast type', function() {
            it('when sales rep', function() {
                layout._onceInitSelectedUser(layout.initOptions.context.model, changedOptions);
                expect(layout.model.get('forecastType')).toEqual('Direct');
            });
            it('when manager viewing rep worksheet', function() {
                changedOptions.is_manager = true;
                changedOptions.showOpps = true;
                layout._onceInitSelectedUser(layout.initOptions.context.model, changedOptions);
                expect(layout.model.get('forecastType')).toEqual('Direct');
            });
            it('when manager viewing manager worksheet', function() {
                changedOptions.is_manager = true;
                changedOptions.showOpps = false;
                layout._onceInitSelectedUser(layout.initOptions.context.model, changedOptions);
                expect(layout.model.get('forecastType')).toEqual('Rollup');
            });
        });

        describe('bindDataChange', function() {
            beforeEach(function() {
                sinon.stub(layout, 'bindDataChange');
            });
            it('is called when eventsBound is false', function() {
                layout.eventsBound = false;
                layout._onceInitSelectedUser(layout.initOptions.context.model, changedOptions);
                expect(layout.bindDataChange).toHaveBeenCalled();
            });

            it('is not called when eventsBound is true', function() {
                layout.eventsBound = true;
                layout._onceInitSelectedUser(layout.initOptions.context.model, changedOptions);
                expect(layout.bindDataChange).not.toHaveBeenCalled();
            });
        });
    });

    describe('sync', function() {
        var filters = undefined;
        beforeEach(function() {
            apiCallStub.restore();
            apiCallStub = sinon.stub(app.api, 'call').callsFake(function(method, url, payload, callback) {
                filters = payload;
            });
            layout._onceInitSelectedUser(layout.initOptions.context.model, {
                id: 'test_id',
                is_manager: false,
                showOpps: false
            });
        });

        afterEach(function() {
            apiCallStub.restore();
            apiCallStub = sinon.stub(app.api, 'call').callsFake(function() {
            });
        });

        it('should have user_id and forecast_type defined in the filters', function() {
            layout.sync('read', layout.collection, {});
            expect(_.isObject(filters)).toBeTruthy();
            expect(_.isUndefined(filters['filter'][0])).toBeFalsy();
            expect(filters['filter'][0]['user_id']).toEqual(layout.model.get('selectedUserId'));
            expect(_.isUndefined(filters['filter'][1])).toBeFalsy();
            expect(filters['filter'][1]['forecast_type']).toEqual(layout.model.get('forecastType'));
        });

        it('should have timeperiod_id defined in the filters', function(){
            layout.model.unset('selectedUserId');
            layout.context.set('selectedTimePeriod', 'jasmin_test');
            layout.sync('read', layout.collection, {});
            expect(_.isObject(filters)).toBeTruthy();
            expect(_.isUndefined(filters['filter'][0])).toBeFalsy();
            expect(filters['filter'][0]['timeperiod_id']).toEqual(layout.context.get('selectedTimePeriod'));
        });
    });

    describe('syncInitData', function() {

        beforeEach(function() {
            apiCallStub.restore();
            apiCallStub = sinon.stub(app.api, 'call').callsFake(function() {
            });
            sinon.stub(app.utils, 'checkForecastConfig').callsFake(function() {
                return true;
            });
        });

        afterEach(function() {
            sinon.restore();
        });

        it('should pass the no-cache header when forecast is not setup', function() {
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    forecast_by: 'RevenueLineItems',
                    sales_stage_won: [],
                    sales_stage_lost: [],
                    is_setup: 0
                };
            });

            // create a new layout, so we can test the call here
            SugarTest.createLayout('base', moduleName, 'records', null, null, true);

            expect(apiCallStub).toHaveBeenCalled();
            expect(apiCallStub.args[0][4]).toEqual({
                headers: {
                    'Cache-Control': 'no-cache'
                }
            });
        });

        it('should not pass the no-cache header when forecast is setup', function() {
            sinon.stub(app.metadata, 'getModule').callsFake(function() {
                return {
                    forecast_by: 'RevenueLineItems',
                    sales_stage_won: [],
                    sales_stage_lost: [],
                    is_setup: 1
                };
            });

            // create a new layout, so we can test the call here
            SugarTest.createLayout('base', moduleName, 'records', null, null, true);

            expect(apiCallStub).toHaveBeenCalled();
            expect(apiCallStub.args[0][4]).toNotEqual({
                headers: {
                    'Cache-Control': 'no-cache'
                }
            });
        });
    });
});
