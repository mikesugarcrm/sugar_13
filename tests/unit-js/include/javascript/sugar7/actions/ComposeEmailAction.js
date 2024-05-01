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
describe('Actions.ComposeEmailAction', function() {
    var app;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.seedMetadata(true);
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        SugarTest.loadFile('../include/javascript/sugar7/actions', 'ComposeEmailAction', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sinon.restore();
        sandbox.restore();
    });

    describe('Action registration', function() {
        it('should have been added to the `app.actions` registry', function() {
            expect(typeof app.actions.ComposeEmail).toEqual('function');
        });
    });

    describe('run()', function() {
        var currentExecution;

        beforeEach(function() {
            currentExecution = {
                nextAction: sinon.stub()
            };

            app.drawer = {
                open: sandbox.stub()
            };
        });

        afterEach(function() {
            delete app.drawer;
        });

        it('should properly open the sugar compose email drawer', function() {
            var def = {
                properties: {
                    id: '1',
                    pmse: false,
                    emailToFormula: '$email'
                }
            };

            var opts = {
                recordModel: app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    email: 'to@foo.com'
                })
            };

            var action = new app.actions.ComposeEmail(def);

            sinon.stub(action, 'getEmailClientType').returns('sugar');

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('POST', /.*rest\/v10\/actionButton\/evaluateEmailTemplate.*/,
                [200, {'Content-Type': 'application/json'}, JSON.stringify({
                    emailTo: [{
                        email_address: 'test@foo.com',
                        email_address_id: '1'
                    }],
                    subject: 'TEST SUBJECT',
                    body: 'TEST BODY'
                })]);

            action.run(opts, currentExecution);

            SugarTest.server.respond();

            expect(app.drawer.open).toHaveBeenCalledOnce();
            expect(app.drawer.open.firstCall.args[0].layout).toBe('compose-email');
            expect(app.drawer.open.firstCall.args[0].context.create).toBe(true);
            expect(app.drawer.open.firstCall.args[0].context.module).toBe('Emails');

            var emailModel = app.drawer.open.firstCall.args[0].context.model;
            var emailAddress = emailModel.get('to_collection').models[0];

            expect(emailModel.get('name')).toBe('TEST SUBJECT');
            expect(emailModel.get('description_html')).toBe('TEST BODY');
            expect(emailModel.get('to_collection').length).toBe(1);
            expect(emailAddress.get('email_address')).toBe('test@foo.com');
            expect(emailAddress.get('email_address_id')).toBe('1');
        });

        it('should properly open an external email client', function() {
            var def = {
                properties: {
                    id: '1',
                    pmse: true,
                    emailToFormula: '$email'
                }
            };

            var opts = {
                recordModel: app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    email: 'to@foo.com'
                })
            };

            var action = new app.actions.ComposeEmail(def);

            sinon.stub(action, 'getEmailClientType').returns('external');
            sinon.stub(action, 'mailto');

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('POST', /.*rest\/v10\/actionButton\/evaluateBPMEmailTemplate.*/,
                [200, {'Content-Type': 'application/json'}, JSON.stringify({
                    emailTo: [{
                        email_address: 'test@foo.com',
                        email_address_id: '1'
                    }],
                    subject: 'TEST SUBJECT',
                    body: 'TEST BODY'
                })]);

            action.run(opts, currentExecution);

            SugarTest.server.respond();

            expect(action.mailto).toHaveBeenCalledOnce();
            expect(action.mailto.firstCall.args[0]).toBe('mailto:test@foo.com?subject=TEST%20SUBJECT&body=TEST%20BODY');
        });
    });
});
