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
describe('Core/Events', function() {
    var app;
    var eventHub;
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        eventHub = SugarTest.app.events;
        context = _.extend({}, Backbone.Events);
    });

    afterEach(function () {
        sinon.restore();
    });

    describe("when an event is registered", function() {
        it('should fire event and all listeners should call their callbacks', function() {
            var cb = sinon.spy();
            eventHub.register("testEvent", context);
            eventHub.on("testEvent", cb);
            context.trigger("testEvent");

            expect(cb).toHaveBeenCalled();
        });
    });

    describe("when an event is removed", function() {
        it("should not broadcast removed events to subscribers", function() {
            var cb1 = sinon.spy();
            var cb2 = sinon.spy();

            eventHub.register("testEvent", context);
            eventHub.on("testEvent", cb1);

            eventHub.register("nextEvent", context);
            eventHub.on("nextEvent", cb1);
            eventHub.on("nextEvent", cb2);
            eventHub.unregister(context, "nextEvent");

            context.trigger("nextEvent");
            context.trigger("testEvent");
            context.trigger("testEvent");

            expect(cb2).not.toHaveBeenCalled();
            expect(cb1).toHaveBeenCalledTwice();
        });
    });

    describe("when all events are removed", function() {
        it("should not broadcast any events", function() {
            var cb = sinon.spy();

            eventHub.register("testEvent", context);
            eventHub.on("testEvent", cb);
            eventHub.on("all", cb);
            eventHub.unregister(context);

            context.trigger("testEvent");

            expect(cb).not.toHaveBeenCalled();
        });
    });

    describe("should re-broadcast jquery ajax events", function() {
        it("it should trigger ajaxStart and ajaxStop on any ajax activity", function() {
            var callback1 = sinon.spy(),
                callback2 = sinon.spy();

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith([200, {}, ""]);

            eventHub.registerAjaxEvents();
            eventHub.on("ajaxStart", callback1);
            eventHub.on("ajaxStop", callback2);

            $.ajax({url: "/rest/v10/metadata"});
            SugarTest.server.respond();

            expect(callback1).toHaveBeenCalled();
            expect(callback2).toHaveBeenCalled();
        });
    });

    describe('when an event is deprecated', function () {

        it('should log a warning when any listener tries to execute their function', function () {
            var cb = sinon.spy();
            var warn = sinon.stub(app.logger, 'warn');

            eventHub.register('testEvent', context, { deprecated: true });
            eventHub.on('testEvent', cb);
            context.trigger('testEvent', 'arg1', 'arg2', 'arg3');

            expect(cb).toHaveBeenCalledWith('arg1', 'arg2', 'arg3');
            expect(warn).toHaveBeenCalledWith('The global event `testEvent` is deprecated.');

            eventHub.unregister(context);
        });

        it('should log a warning with listener (context) when supplied', function () {
            var cb = sinon.spy();
            var warn = sinon.stub(app.logger, 'warn');
            var listener = {};
            listener.toString = function () {
                return 'listener from test';
            };

            eventHub.register('testEvent', context, { deprecated: true });
            eventHub.on('testEvent', cb, listener);
            context.trigger('testEvent', 'arg1', 'arg2', 'arg3');

            expect(cb).toHaveBeenCalledWith('arg1', 'arg2', 'arg3');
            expect(warn).toHaveBeenCalledWith('The global event `testEvent` is deprecated.\n' +
                'listener from test should not listen to it anymore.');

            eventHub.unregister(context);
        });

        it('should log a warning with custom message', function () {
            var cb = sinon.spy();
            var warn = sinon.stub(app.logger, 'warn');
            var listener = {};
            listener.toString = function () {
                return 'listener from test';
            };

            eventHub.register('testEvent', context, {
                deprecated: true,
                message: 'event test message',
            });
            eventHub.on('testEvent', cb, listener);
            context.trigger('testEvent', 'arg1', 'arg2', 'arg3');

            expect(cb).toHaveBeenCalledWith('arg1', 'arg2', 'arg3');
            expect(warn).toHaveBeenCalledWith('event test message\n' +
                'listener from test should not listen to it anymore.');

            eventHub.unregister(context);
        });

    });
});
