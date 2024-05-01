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
describe('Notifications', function() {
    var app, view,
        moduleName = 'Notifications',
        viewName = 'notifications';

    beforeEach(function() {
        app = SugarTest.app;
    });

    describe('Initialization with default values', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function() {
            sinon.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should bootstrap', function() {
            let _initOptions = sinon.stub(view, '_initOptions').callsFake($.noop());
            let _initCollection = sinon.stub(view, '_initCollection').callsFake($.noop());
            let _initReminders = sinon.stub(view, '_initReminders').callsFake($.noop());

            view._bootstrap();

            expect(_initOptions).toHaveBeenCalledOnce();
            expect(_initCollection).toHaveBeenCalledOnce();
            expect(_initReminders).toHaveBeenCalledOnce();
        });

        it('should initialize options with default values', function() {
            view._initOptions();

            expect(view.delay / 60 / 1000).toBe(view._defaultOptions.delay);
            expect(view.limit).toBe(view._defaultOptions.limit);
        });

        it('should initialize collection options with default values', function() {
            var createBeanCollection = sinon.stub(app.data, 'createBeanCollection').callsFake(function() {
                return {
                    options: {},
                    off: function() {
                    }
                };
            });

            view._initCollection();

            expect(view.collection.options).toEqual({
                params: {
                    order_by: 'date_entered:desc'
                },
                limit: view.limit,
                myItems: true,
                fields: [
                    'date_entered',
                    'id',
                    'is_read',
                    'name',
                    'severity'
                ],
                apiOptions: {
                    skipMetadataHash: true
                }
            });
        });
    });

    describe('Initialization with metadata overridden values', function() {
        var customOptions = {
            delay: 10,
            limit: 8
        };

        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.testMetadata.addViewDefinition(viewName, customOptions, moduleName);
            SugarTest.testMetadata.set();

            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function() {
            sinon.restore();
            SugarTest.testMetadata.dispose();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should initialize options with metadata overridden values', function() {
            view._initOptions();

            expect(view.delay / 60 / 1000).toBe(customOptions.delay);
            expect(view.limit).toBe(customOptions.limit);
        });

        it('should initialize collection options with metadata overridden values', function() {
            var createBeanCollection = sinon.stub(app.data, 'createBeanCollection').callsFake(function() {
                return {
                    options: {},
                    off: function() {
                    }
                };
            });

            view._initCollection();

            expect(view.collection.options).toEqual({
                params: {
                    order_by: 'date_entered:desc'
                },
                limit: view.limit,
                myItems: true,
                fields: [
                    'date_entered',
                    'id',
                    'is_read',
                    'name',
                    'severity'
                ],
                apiOptions: {
                    skipMetadataHash: true
                }
            });
        });
    });

    describe('Initialization with metadata overridden bad delay values', function() {
        var customOptions;

        afterEach(function() {
            sinon.restore();
            SugarTest.testMetadata.dispose();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should initialize delay to delayMax when metadata overridden values are too big', function() {
            customOptions = {
                delay: 40000
            };
            SugarTest.testMetadata.init();
            SugarTest.testMetadata.addViewDefinition(viewName, customOptions, moduleName);
            SugarTest.testMetadata.set();

            view = SugarTest.createView('base', moduleName, viewName);
            view._initOptions();

            expect(view.delay / 60 / 1000).toBe(view.delayMax);
        });

        it('should initialize delay to delayMin when metadata overridden values are too low', function() {
            customOptions = {
                delay: 0
            };
            SugarTest.testMetadata.init();
            SugarTest.testMetadata.addViewDefinition(viewName, customOptions, moduleName);
            SugarTest.testMetadata.set();

            view = SugarTest.createView('base', moduleName, viewName);
            view._initOptions();

            expect(view.delay / 60 / 1000).toBe(view.delayMin);
        });
    });

    describe('Pulling mechanism', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function() {
            sinon.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should not pull notifications if disposed', function() {
            // not calling dispose() directly due to it setting inherently the
            // collection to null
            view.disposed = true;
            view.pull();

            expect(view.collection.fetch).not.toHaveBeenCalled();
            view.disposed = false;
        });

        it('should not pull notifications if disposed after fetch', function() {
            var fetch = sinon.stub(view.collection, 'fetch').callsFake(function(o) {
                // not calling dispose() directly due to it setting inherently the
                // collection to null
                view.disposed = true;
                o.success();
            });

            view.pull();

            expect(fetch).toHaveBeenCalledOnce();
            expect(view.render).not.toHaveBeenCalled();
            view.disposed = false;
        });

        it('should not pull notifications if open', function() {
            var isOpen = sinon.stub(view, 'isOpen').callsFake(function() {
                return true;
            });

            view.pull();

            expect(view.collection.fetch).not.toHaveBeenCalled();
        });

        it('should not pull notifications if open after fetch', function() {
            var fetch = sinon.stub(view.collection, 'fetch').callsFake(function(o) {
                var isOpen = sinon.stub(view, 'isOpen').callsFake(function() {
                    return true;
                });

                o.success();
            });

            view.pull();

            expect(fetch).toHaveBeenCalledOnce();
            expect(view.render).not.toHaveBeenCalled();
        });

        it('should set timeout twice once on multiple start pulling calls', function() {
            let pull = sinon.stub(view, 'pull').callsFake($.noop());
            let setTimeout = sinon.stub(window, 'setTimeout').callsFake($.noop());

            view.startPulling().startPulling();

            expect(pull).toHaveBeenCalledOnce();
            expect(setTimeout).toHaveBeenCalledTwice();
        });

        it('should clear intervals on stop pulling', function() {
            sinon.stub(view, 'pull').callsFake($.noop());
            sinon.stub(view, '_pullReminders').callsFake($.noop());
            sinon.stub(window, 'setTimeout').callsFake(function() {
                return intervalId;
            });
            let clearTimeout = sinon.stub(window, 'clearTimeout').callsFake($.noop());
            let intervalId = 1;

            view.startPulling().stopPulling();

            expect(clearTimeout).toHaveBeenCalledTwice();
            expect(view._intervalId).toBeNull();
            expect(view._remindersIntervalId).toBeNull();
        });

        it('should stop pulling on dispose', function() {
            var stopPulling = sinon.stub(view, 'stopPulling').callsFake($.noop());

            view.dispose();

            expect(stopPulling).toHaveBeenCalledOnce();
        });

        it('should stop pulling if authentication expires', function() {
            let pull = sinon.stub(view, 'pull').callsFake($.noop());
            let setTimeout = sinon.stub(window, 'setTimeout').callsFake(function(fn) {
                fn();
            });
            let stopPulling = sinon.stub(view, 'stopPulling').callsFake($.noop());
            let isAuthenticated = sinon.stub(app.api, 'isAuthenticated').returns(false);
            sinon.stub(view, '_pullReminders').callsFake($.noop());

            view.startPulling();

            expect(pull).toHaveBeenCalledOnce();
            expect(setTimeout).toHaveBeenCalledTwice();
            expect(isAuthenticated).toHaveBeenCalledTwice();
            expect(stopPulling).toHaveBeenCalledTwice();
        });
    });

    describe('Reminders', function() {
        beforeEach(function() {
            var meta = {
                remindersFilterDef: {
                    reminder_time: {$gte: 0},
                    status: {$equals: 'Planned'}
                },
                remindersLimit: 100
            };

            view = SugarTest.createView('base', moduleName, viewName, meta);
        });

        afterEach(function() {
            sinon.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should initialize collections for Meetings and Calls', function() {

            sinon.stub(app.data, 'createBeanCollection').callsFake(function() {
                return {
                    options: {},
                    off: function() {
                    }
                };
            });
            sinon.stub(app.lang, 'getAppListStrings').callsFake(function() {
                return {
                    '60': '1 minute prior',
                    '300': '5 minutes prior',
                    '600': '10 minutes prior',
                    '900': '15 minutes prior',
                    '1800': '30 minutes prior',
                    '3600': '1 hour prior',
                    '7200': '2 hours prior',
                    '10800': '3 hours prior',
                    '18000': '5 hours prior',
                    '86400': '1 day prior'
                };
            });

            view.delay = 300000; // 5 minutes for each pull;
            view._initReminders();

            _.each(['Calls', 'Meetings'], function(module) {
                expect(view._alertsCollections[module].options).toEqual({
                    limit: 100,
                    fields: ['date_start', 'id', 'name', 'reminder_time', 'location', 'parent_name']
                });
            });

            expect(view.reminderMaxTime).toBe(86700); // 1 day + 5 minutes
        });

        describe('Check reminders', function() {

            var reminderModule = 'Meetings';

            beforeEach(function() {

                var meta = {
                    fields: [],
                    views: [],
                    layouts: []
                };
                app.data.declareModel(reminderModule, meta);
                SugarTest.testMetadata.init();
                SugarTest.loadHandlebarsTemplate('notifications', 'view', 'base', 'notifications-alert');
                SugarTest.testMetadata.set();

            });

            afterEach(function() {
                app.data.reset(reminderModule);
                SugarTest.testMetadata.dispose();
                Handlebars.templates = {};
            });

            it('Shouldn\'t check reminders if authentication expires', function() {
                var isAuthenticated = sinon.stub(app.api, 'isAuthenticated').callsFake(function() {
                        return false;
                    });
                var setTimeout = sinon.stub(window, 'setTimeout').callsFake($.noop());
                var stopPulling = sinon.stub(view, 'stopPulling').callsFake($.noop());

                view.checkReminders();

                expect(setTimeout).not.toHaveBeenCalled();
                expect(isAuthenticated).toHaveBeenCalledOnce();
                expect(stopPulling).toHaveBeenCalledOnce();
            });

            it('Should show reminder if need', function() {

                var now = new Date('2013-09-04T22:45:56+02:00');
                var dateStart = new Date('2013-09-04T23:15:16+02:00');
                let clock = sinon.useFakeTimers({
                    now: now.getTime(),
                    toFake: ['Date']
                });
                var setTimeout = sinon.stub(window, 'setTimeout').callsFake($.noop());
                var _showReminderAlert = sinon.stub(view, '_showReminderAlert');
                var isAuthenticated = sinon.stub(app.api, 'isAuthenticated').callsFake(function() {
                        return true;
                    });
                var model = new app.data.createBean(reminderModule, {
                    'id': '105b0b4a-1337-e0db-b448-522784b92270',
                    'name': 'Discuss pricing',
                    'date_modified': '2013-09-05T00:59:00+02:00',
                    'description': 'Meeting',
                    'date_start': dateStart.toISOString(),
                    'reminder_time': '1800'
                });

                view._initReminders();
                view._alertsCollections[reminderModule].add(model);
                view.dateStarted = now.getTime();
                view._remindersIntervalStamp = view.dateStarted - 60000;
                view.checkReminders();

                expect(_showReminderAlert).toHaveBeenCalledWith(model);

                clock.restore();
            });
        });
    });

    describe('Notification alert content', function() {
        var message;
        var template;
        var parentName = 'Parent Company';
        var model;

        beforeEach(function() {
            template = app.template.getView('notifications.notifications-alert'),
            model = new app.data.createBean('Meetings', {
                'id': '105b0b4a-1337-e0db-b448-522784b92270',
                'name': 'Discuss pricing',
                'date_modified': '2013-09-05T00:59:00+02:00',
                'description': 'Meeting',
                'location': 'GoTo',
                'date_start': '2013-09-05T00:59:00+02:00',
                'reminder_time': '1800',
                'module': 'Meetings'
            });
        });

        afterEach(function() {
            template = null;
            model = null;
        });

        it('Should show the parent name if exists on the model', function() {
            model.set('parent_name', parentName);
            message = template({
                title: 'Test',
                module: model.module,
                model: model,
                location: model.get('location'),
                description: model.get('description'),
                dateStart: model.get('date_start'),
                parentName: model.get('parent_name')
            });

            expect(message).toContain(app.lang.get('LBL_RELATED_TO', model.module));
            expect(message).toContain(parentName);
        });

        it('Should not show the parent name if the model does not have a parent record', function() {
            message = template({
                title: 'Test',
                module: model.module,
                model: model,
                location: model.get('location'),
                description: model.get('description'),
                dateStart: model.get('date_start'),
                parentName: model.get('parent_name')
            });

            expect(message).not.toContain(app.lang.get('LBL_RELATED_TO', model.module));
            expect(message).not.toContain(parentName);
        });
    });

    describe('Notification favicon badge', function() {
        beforeEach(function() {

            // Library mock
            Favico = function() {
                return {
                    badge: $.noop,
                    reset: $.noop
                };
            };

            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function() {
            sinon.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;

            // remove Libarary mock
            delete Favico;
        });

        using('different counts', [
                [23, -1, 23],
                [7, 7, '7+']
            ], function(length, offset, expected) {
                it('should update favicon badge with the correct unread notifications', function() {
                    view._bootstrap();

                    var badge = sinon.stub(view.favicon, 'badge');
                    view.collection.length = length;
                    view.collection.next_offset = offset;
                    view.collection.trigger('reset');

                    expect(badge).toHaveBeenCalledWith(expected);
                });
            }
        );

        it('should reset favicon badge if authentication expires or user logout', function() {
            view._bootstrap();

            var resetStub = sinon.stub(view.favicon, 'reset');
            sinon.stub(app.api, 'isAuthenticated').callsFake(function() {
                return false;
            });

            view.render();

            expect(resetStub).toHaveBeenCalledOnce();
        });
    });
});
