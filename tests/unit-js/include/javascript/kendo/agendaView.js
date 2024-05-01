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
// Test agendaView.js
describe('includes.javascript.kendo.agendaView', function() {
    let app;

    beforeEach(function() {
        app = SugarTest.app;
        app.user.setPreference('timepref', 'H.i');

        const kendoPath = '../include/javascript/kendo';

        SugarTest.loadFile(kendoPath, 'kendo', 'min.js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
        SugarTest.loadFile(kendoPath, 'agendaView', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
        SugarTest.loadFile('../include/javascript/calendar', 'utils', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
    });

    describe('_renderTaskGroups', function() {
        it('should use user\'s time date format', function() {
            const schedulerEvent = new kendo.data.SchedulerEvent({
                'recordId': 'ce1e4048-c24c-11ed-8834-0242ac140008',
                'calendarId': '9e7a7edc-c243-11ed-84f6-0242ac140008',
                'id': '9e7a7edc-c243-11ed-84f6-0242ac140008_ce1e4048-c24c-11ed-8834-0242ac140008',
                'eventUsers': [
                    '1'
                ],
                'name': 'c1',
                'start': new Date('2023-03-13 22:00:00'),
                'end': new Date('2023-03-13 22:30:00'),
                'title': 'c1',
                'module': 'Calls',
                'event_tooltip': '<p></p>',
                'day_event_template': '<p><strong>c1</strong></p><p></p>',
                'week_event_template': '<p><strong>c1</strong></p><p></p>',
                'month_event_template': '<p>c1</p>',
                'agenda_event_template': '<p><strong>c1</strong></p><p></p>',
                'timeline_event_template': '<p>c1 </p>',
                'schedulermonth_event_template': '<p>c1 </p>',
                'dbclickRecordId': 'ce1e4048-c24c-11ed-8834-0242ac140008',
                'color': '#c0edff',
                'assignedUserName': 'Administrator',
                'assignedUserId': '1',
                'invitees': [
                    {
                        'id': '1',
                        'name': 'Administrator',
                        'module': 'Users',
                        'acceptStatus': 'accept'
                    }
                ],
                'defaults': {
                    'id': 0,
                    'title': '',
                    'start': '2023-03-14T09:44:49.132Z',
                    'startTimezone': '',
                    'end': '2023-03-14T09:44:49.132Z',
                    'endTimezone': '',
                    'recurrenceRule': '',
                    'recurrenceException': '',
                    'isAllDay': false,
                    'description': ''
                },
                'fields': {
                    'id': {
                        'type': 'number'
                    },
                    'title': {
                        'defaultValue': '',
                        'type': 'string'
                    },
                    'start': {
                        'type': 'date',
                        'validation': {
                            'required': true,
                            'validDate': {}
                        }
                    },
                    'startTimezone': {
                        'type': 'string'
                    },
                    'end': {
                        'type': 'date',
                        'validation': {
                            'required': true,
                            'validDate': {},
                            'dateCompare': {}
                        }
                    },
                    'endTimezone': {
                        'type': 'string'
                    },
                    'recurrenceRule': {
                        'defaultValue': '',
                        'type': 'string',
                        'validation': {
                            'validDate': {},
                            'untilDateCompare': {}
                        }
                    },
                    'recurrenceException': {
                        'defaultValue': '',
                        'type': 'string'
                    },
                    'isAllDay': {
                        'type': 'boolean',
                        'defaultValue': false
                    },
                    'description': {
                        'type': 'string'
                    }
                },
                'idField': 'id',
                'startDate': new Date('2023-03-13 22:00:00'),
                'uid': 'c500bb5b-ca14-402b-80ab-fe14d88fb7d8'
            });
            const taskGroups = [
                {
                    field: 'startDate',
                    items: [schedulerEvent],
                    value: new Date('Tue Mar 14 2023 00:00:00')
                }
            ];
            const groups = [];
            const agendaView = new kendo.ui.AgendaView($('<div>'), {date: new Date()});
            const taskGroupsRendered = agendaView._renderTaskGroups(taskGroups, groups);
            const timeColumnRendered = $(taskGroupsRendered).find('.k-scheduler-timecolumn').text();
            const timeColumnExpected = '22.00-22.30';
            expect(timeColumnRendered).toBe(timeColumnExpected);
        });
    });
});
