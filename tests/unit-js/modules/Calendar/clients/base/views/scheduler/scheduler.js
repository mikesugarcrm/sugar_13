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
describe('View.Views.Base.Calendar.SchedulerView', function() {
    var app;
    var view;
    var model;
    var module = 'Calendar';
    var context;
    var listenToStub;
    var fixture;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'scheduler', module);
        SugarTest.declareData('base', 'Calendar', true, true);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        model = app.data.createBean(module);

        context = new app.Context();

        context.set({
            module: module,
            model: model,
        });

        app.Calendar = {
            utils: {
                buildUserKeyForStorage: function() {}
            }
        };

        window.kendo = {
            ui: {
                Scheduler: function() {
                    this.refresh = function() {};
                    this.destroy = function() {};
                    return this;
                }
            },
            data: {
                SchedulerEvent: function(data) {
                    return data;
                }
            },
        };

        view = SugarTest.createView(
            'base',
            'Calendar',
            'scheduler',
            null,
            context,
            true,
            model,
            true
        );

        fixture = SugarTest.loadFixture('calendar');

        listenToStub = sinon.stub(view, 'listenTo');

        if (!$.fn.modal) {
            $.fn.modal = function() {};
        }
    });

    afterEach(function() {
        model = null;
        view = null;
        $.fn.modal = null;
    });

    describe('initialize', function() {
        it('should set initial parameters', function() {
            expect(view.scheduler).toBe(null);
            expect(view.DAY_VIEW).toBe('day');
            expect(view.WEEK_VIEW).toBe('week');
            expect(view.WORK_WEEK_VIEW).toBe('workWeek');
            expect(view.EXPANDED_MONTH_VIEW).toBe('expandedMonth');
            expect(view.AGENDA_VIEW).toBe('agenda');
            expect(view.TIMELINE_VIEW).toBe('timeline');
            expect(view.MONTH_SCHEDULE_VIEW).toBe('monthSchedule');
        });

        it('should set scheduler events', function() {
            view.initialize({name: 'scheduler', type: 'scheduler'});

            expect(listenToStub.getCall(0).args[1]).toEqual('calendar:reload');
            expect(listenToStub.getCall(1).args[1]).toEqual('calendar:reload');
            expect(listenToStub.getCall(2).args[1]).toEqual('calendar:reconfigure');

            expect(view.events['click .previewEvent']).toEqual('_previewEvent');
        });
    });

    describe('populateCalendarWithData', function() {
        it('should set _eventsLoaded', function() {
            var res = fixture.events;
            res.data = _.map(res.data, function(event) {
                event.start = moment(event.start).format();
                event.end = moment(event.end).format();
                return event;
            });
            view.scheduler = {
                _selectedView: {
                    options: {
                        dataSource: {
                            data: function() {}
                        }
                    }
                }
            };

            sinon.stub(view, 'updateUsersLegend');

            view.populateCalendarWithData(res);

            view._eventsLoaded.events = _.map(view._eventsLoaded.events, function(event) {
                event.start = moment(event.start).format();
                event.end = moment(event.end).format();
                return event;
            });
            fixture.eventsLoaded.events = _.map(fixture.eventsLoaded.events, function(event) {
                event.start = moment(event.start).format();
                event.end = moment(event.end).format();
                return event;
            });

            expect(view._eventsLoaded).toEqual(fixture.eventsLoaded);
        });
    });

    describe('_render', function() {
        it('should render the scheduler component', function() {
            view.calendars = app.data.createBeanCollection('Calendar');
            var createCalendarStub = sinon.stub(view, '_createCalendar');

            view._render();

            expect(createCalendarStub).toHaveBeenCalledOnce();
        });
    });

    describe('_calendarIsCreated', function() {
        it('should return the current state of the calendar (created or not)', function() {
            var calendarCreatedRes = view._calendarIsCreated();

            expect(calendarCreatedRes).toBe(false);

            view.scheduler = new kendo.ui.Scheduler();
            calendarCreatedRes = view._calendarIsCreated();

            expect(calendarCreatedRes).toBe(true);
        });
    });

    using('different allow checks',
        [
            {
                action: 'allow_create',
                event: {
                    calendarId: 'cal1',
                },
                result: true
            },
            {
                action: 'allow_update',
                event: {
                    calendarId: 'cal1',
                },
                result: false
            },
            {
                action: 'allow_delete',
                event: {
                    calendarId: 'cal1',
                },
                result: false
            },
        ],
        function(values) {
            it('should give the proper access', function() {
                view.calendarDefs = [
                    {
                        id: 'cal1',
                        calendarId: 'cal1',
                        allow_create: true,
                        allow_update: false,
                        allow_delete: false,
                    }
                ];

                var result = view.isAllowed(values.action, values.event);
                expect(result).toBe(values.result);
            });
        }
    );

    using('different view types',
        [
            {
                given: 'day',
                expected: 'day'
            },
            {
                given: 'DayView',
                expected: 'day'
            },
            {
                given: 'month',
                expected: 'expandedMonth'
            },
            {
                given: 'expandedMonth',
                expected: 'expandedMonth'
            },
            {
                given: 'Month',
                expected: 'expandedMonth'
            },
        ],
        function(values) {
            it('should return the proper view type', function() {
                var result = view.getViewType(values.given);
                expect(result).toBe(values.expected);
            });
        }
    );

    using('different view titles',
        [
            {
                viewType: 'day',
                label: 'LBL_CALENDAR_VIEW_DAY'
            },
            {
                viewType: 'monthSchedule',
                label: 'LBL_CALENDAR_VIEW_SCHEDULERMONTH'
            },
            {
                viewType: 'Scheduler',
                label: 'LBL_CALENDAR_VIEW_SCHEDULERMONTH'
            },
        ],
        function(values) {
            it('should get the proper language', function() {
                var langStub = sinon.stub(app.lang, 'getModString');
                view.getViewTitle(values.viewType);

                expect(langStub.getCall(0).args[0]).toEqual(values.label);
                expect(langStub.getCall(0).args[1]).toEqual('Calendar');
                langStub.restore();
            });
        }
    );

    describe('_userSettings', function() {
        it('should use user time pref', function() {
            const addBusinessHoursStub = sinon.stub(view, 'addBusinessHoursElementOnDom');
            const getPrefStub = sinon.stub(app.user, 'getPreference');

            view._userSettings();

            const preferenceValueExpected = 'timepref';
            expect(getPrefStub.getCall(0).args[0]).toEqual(preferenceValueExpected);
            expect(getPrefStub.getCall(1).args[0]).toEqual(preferenceValueExpected);

            addBusinessHoursStub.restore();
            getPrefStub.restore();
        });
    });

    describe('_setBusinessHours', function() {
        it('should set business hours', function() {
            view._setBusinessHours();

            const cachedBusinessHours = app.user.lastState.get(view.keyToStoreCalendarBusinessHours);
            expect(cachedBusinessHours).toEqual({
                start: '09:00',
                end: '17:00'
            });
        });
    });

    describe('_getBusinessHours', function() {
        it('should get business hours', function() {
            app.user.lastState.set(view.keyToStoreCalendarBusinessHours, {
                start: '9:00',
                end: '17:00'
            });

            const start = view._getBusinessHours('start');
            const expectedBusinessHours = new Date('2000-01-01 9:00');

            expect(start).toEqual(expectedBusinessHours);
        });
    });

    describe('_navigateHandler', function() {
        let navigationLoadDataStub;

        beforeEach(function() {
            navigationLoadDataStub = sinon.stub(view, '_navigationLoadData');

            view.scheduler = {
                options: {}
            };
        });

        afterEach(function() {
            navigationLoadDataStub.restore();
        });

        it('should update options for formatting selected date', function() {
            const headerReturn = 'dd-MM-yyyy';
            const selectedReturn = 'dd MM yyyy';
            const getDateHeaderStub = sinon.stub(view, '_getDateHeaderTemplate').returns(headerReturn);
            const getSelectedDateStub = sinon.stub(view, '_getSelectedDateFormat').returns(selectedReturn);

            view._navigateHandler({
                action: 'changeView',
                view: view.DAY_SCHEDULE_VIEW
            });
            expect(view.scheduler.options.dateHeaderTemplate).toEqual(headerReturn);
            expect(view.scheduler.options.selectedDateFormat).toEqual(selectedReturn);

            getDateHeaderStub.restore();
            getSelectedDateStub.restore();
        });

        it('should load events for current view', function() {
            view._navigateHandler({
                action: 'changeDate',
                view: view.MONTH_SCHEDULE_VIEW
            });

            expect(navigationLoadDataStub).toHaveBeenCalledOnce();
        });
    });

    describe('_dispose', function() {
        it('should destroy the scheduler component', function() {
            view.scheduler = new kendo.ui.Scheduler();
            var destroyCalendarStub = sinon.stub(view.scheduler, 'destroy');
            sinon.stub(view, '_super');

            view._dispose();

            expect(destroyCalendarStub).toHaveBeenCalledOnce();
        });
    });
});
