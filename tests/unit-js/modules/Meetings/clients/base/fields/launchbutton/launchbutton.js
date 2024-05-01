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
describe('View.Fields.Base.Meetings.LaunchbuttonField', function() {
    var app, field, sandbox, createFieldProperties,
        module = 'Meetings';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        createFieldProperties = {
            client: 'base',
            name: 'launchbutton',
            type: 'launchbutton',
            viewName: 'detail',
            fieldDef: { host: false },
            module: module,
            loadFromModule: true
        };
    });

    afterEach(function() {
        sandbox.restore();
        if (field) {
            field.dispose();
        }
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('show/hide the launch button', function() {
        it('should hide the launch button if the meeting already happened', function() {
            field = SugarTest.createField(createFieldProperties);
            field.model.set({
                status: 'Held',
                type: 'WebEx'
            });
            field.render();
            expect(field.isVisible()).toBe(false);
        });

        it('should hide the launch button if not an external meeting', function() {
            field = SugarTest.createField(createFieldProperties);
            field.model.set({
                status: 'Planned',
                type: 'Sugar'
            });
            field.render();
            expect(field.isVisible()).toBe(false);
        });

        it('should hide the launch button if it is a host type and user does not have host permission', function() {
            sandbox.stub(app.acl, 'hasAccess').callsFake(function() { return false; });
            createFieldProperties.fieldDef.host = true;
            field = SugarTest.createField(createFieldProperties);
            field.model.set({
                status: 'Planned',
                type: 'WebEx',
                assigned_user_id: 'not_current_user'
            });
            field.render();
            expect(field.isVisible()).toBe(false);
        });

        it('should show the button if it is a host type, meeting is planned/external and user has host permission', function() {
            sandbox.stub(app.acl, 'hasAccess').callsFake(function() { return true; });
            createFieldProperties.fieldDef.host = true;
            field = SugarTest.createField(createFieldProperties);
            field.model.set({
                status: 'Planned',
                type: 'WebEx',
                assigned_user_id: 'not_current_user'
            });
            field.render();
            expect(field.isVisible()).toBe(true);
        });

        it('should show the button if it is a join type, meeting is planned/external even if user does not have host permission', function() {
            sandbox.stub(app.acl, 'hasAccess').callsFake(function() { return false; });
            field = SugarTest.createField(createFieldProperties);
            field.model.set({
                status: 'Planned',
                type: 'WebEx',
                assigned_user_id: 'not_current_user'
            });
            field.render();
            expect(field.isVisible()).toBe(true);

        });

        it('should hide the button if model changes to meet hide rules', function() {
            field = SugarTest.createField(createFieldProperties);
            field.model.set({
                status: 'Planned',
                type: 'WebEx'
            });
            field.render();
            expect(field.isVisible()).toBe(true);
            field.model.set('status', 'Held');
            expect(field.isVisible()).toBe(false);
        });

        it('should show the button if model changes to meet show rules', function() {
            field = SugarTest.createField(createFieldProperties);
            field.model.set({
                status: 'Planned',
                type: 'Sugar'
            });
            field.render();
            expect(field.isVisible()).toBe(false);
            field.model.set('type', 'WebEx');
            expect(field.isVisible()).toBe(true);
        });
    });

    describe('launching the external meeting', function() {
        var windowOpenStub, alertStub,
            hostUrl = 'http://hosturl',
            joinUrl = 'http://joinurl';

        beforeEach(function() {
            field = SugarTest.createField(createFieldProperties);
            windowOpenStub = sandbox.stub(window, 'open');
            alertStub = sandbox.stub(app.alert, 'show');
        });

        using('external meeting permissions',
            [
                ['should start/host the meeting if user clicks button and has host permissions',
                    hostUrl, joinUrl,
                    true, true, true, 1, false],
                ['should display error if user clicks start/host button and does not have host permissions',
                    hostUrl, joinUrl,
                    true, false, true, 0, 'LBL_EXTNOSTART_MAIN'],
                ['should join the meeting if user clicks button and has join permissions',
                    hostUrl, joinUrl,
                    false, true, true, 1, false],
                ['should display error if user clicks join button and does not have join permissions',
                    hostUrl, joinUrl,
                    false, false, false, 0, 'LBL_EXTNOT_MAIN'],
                ['should display error if user clicks host button and there is no host url',
                    '', joinUrl,
                    true, true, true, 0, 'LBL_EXTERNAL_MEETING_NO_URL'],
                ['should display error if user clicks join button and there is no join url',
                    hostUrl, '',
                    false, true, true, 0, 'LBL_EXTERNAL_MEETING_NO_URL']
            ],
            function(expectation, hostUrl, joinUrl, isHost, hostAllowed, joinAllowed, launchCount, alert) {
                it(expectation, function() {
                    field.isHost = isHost;
                    field._launchMeeting({
                        is_host_option_allowed: hostAllowed,
                        host_url: hostUrl,
                        is_join_option_allowed: joinAllowed,
                        join_url: joinUrl
                    });
                    expect(windowOpenStub.callCount).toEqual(launchCount);
                    if (alert) {
                        expect(alertStub.lastCall.args[1].messages).toEqual(alert);
                    } else {
                        expect(alertStub.callCount).toEqual(0);
                    }
                });
            });
    });
});
