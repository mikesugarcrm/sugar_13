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
describe('View.Layouts.Base.Calendar.AddCalendarLayout', function() {
    var app;
    var layout;
    var model;
    var module = 'Calendar';
    var context;
    var collection;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'add-headerpane', module);
        SugarTest.loadComponent('base', 'view', 'add-calendarcontainer', module);
        SugarTest.loadComponent('base', 'view', 'add-filter', module);
        SugarTest.loadComponent('base', 'layout', 'add-calendar', module);
        SugarTest.declareData('base', module, true, true);
        SugarTest.testMetadata.set();

        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        model = app.data.createBean(module);

        context = new app.Context();

        collection = app.data.createBeanCollection('Calendar');
        context.set({
            module: module,
            model: model,
            collection: collection,
        });
        var meta = {};
        layout = SugarTest.createLayout(
            'base',
            module,
            'add-calendar',
            meta,
            context,
            true,
            {}
        );
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        model = null;
        layout = null;
        context = null;
        collection = null;
        app = null;
    });

    describe('initialize', function() {
        it('should set initial parameters', function() {
            layout.initialize({
                type: 'add-calendar',
                module: module,
                context: context,
            });

            expect(layout.collection.allowed_modules).toEqual(['Users', 'Teams']);
            expect(layout.collection.sync).toBe(layout.sync);

            layout.context.off('calendar:add:search');
        });
    });

    describe('collection sync', function() {
        it('should make a call with expected parameters', function() {
            var appApiCallStub = sinon.stub(app.api, 'call');

            var syncModel = app.data.createBean('Calendar');
            syncModel.teams_offset = true;
            var syncOptions = {};

            var syncCallbacks = {
                success: null,
                error: null,
                complete: null,
                abort: null
            };
            var syncCallbacksMock = sinon.stub(layout, 'getSyncCallbacks').returns(syncCallbacks);

            layout.sync('read', syncModel, syncOptions);

            var urlReceived = appApiCallStub.getCall(0).args[1];
            urlReceived = urlReceived.replace(/\.\.\//g, '');

            expect(appApiCallStub.getCall(0).args[0]).toEqual('read');
            expect(urlReceived).toEqual('rest/v10/Calendar/usersAndTeams');
            expect(appApiCallStub.getCall(0).args[2]).toEqual(null);
            expect(appApiCallStub.getCall(0).args[3]).toEqual(syncCallbacks);

            syncModel = null;
            appApiCallStub.restore();
            syncCallbacksMock.restore();
        });
    });
});
