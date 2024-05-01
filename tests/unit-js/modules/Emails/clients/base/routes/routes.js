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

describe('Emails.Routes', function() {
    var app;
    var oldSync;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;

        app.routing.start();
        SugarTest.loadFile('../modules/Emails/clients/base/routes', 'routes', 'js', function(d) {
            app.events.off('router:init');
            eval(d);
            app.events.trigger('router:init');
        });

        oldSync = app.isSynched;
        app.isSynced = true;

        sandbox = sinon.createSandbox();
        sandbox.stub(app.api, 'isAuthenticated').returns(true);
    });

    afterEach(function() {
        app.router.navigate('', {trigger: true});
        Backbone.history.stop();
        sandbox.restore();
        app.isSynched = oldSync;
    });

    describe('compose a new email', function() {
        it('should open the compose drawer when routing from another page in the app', function() {
            // Routing from layout.
            app.controller.context.set('layout', 'foo');
            sandbox.stub(app.utils, 'openEmailCreateDrawer');

            app.router.navigate('Emails/compose', {trigger: true});

            expect(app.utils.openEmailCreateDrawer).toHaveBeenCalledWith('compose-email');
        });

        it('should open the full page composer when routing from login', function() {
            // Routing from login.
            app.controller.context.set('layout', 'login');
            sandbox.stub(app.controller, 'loadView');

            app.router.navigate('Emails/compose', {trigger: true});

            expect(app.controller.loadView).toHaveBeenCalledOnce();
            expect(app.controller.loadView.firstCall.args[0].layout).toBe('compose-email');
            expect(app.controller.loadView.firstCall.args[0].action).toBe('create');
        });

        it('should open the full page composer when routing directly', function() {
            // Routing from outside the app.
            app.controller.context.unset('layout');
            sandbox.stub(app.controller, 'loadView');

            app.router.navigate('Emails/compose', {trigger: true});

            expect(app.controller.loadView).toHaveBeenCalledOnce();
            expect(app.controller.loadView.firstCall.args[0].layout).toBe('compose-email');
            expect(app.controller.loadView.firstCall.args[0].action).toBe('create');
        });
    });

    describe('editing a draft', function() {
        var model;

        beforeEach(function() {
            model = app.data.createBean('Emails');
            sandbox.stub(app.data, 'createBean');
            app.data.createBean.returns(model);
            sandbox.stub(model, 'fetch').callsFake(function(options) {
                expect(options.params.erased_fields).toBe(true);
                options.success(model);
            });
        });

        it('should open the compose drawer when routing from another page in the app', function() {
            model.set('state', 'Draft');
            sandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit').returns(true);

            // Routing from layout.
            app.controller.context.set('layout', 'foo');
            sandbox.stub(app.utils, 'openEmailCreateDrawer');

            app.router.navigate('Emails/123/compose', {trigger: true});

            expect(app.utils.openEmailCreateDrawer).toHaveBeenCalledWith('compose-email');
        });

        it('should open the full page composer when routing from login', function() {
            model.set('state', 'Draft');
            sandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit').returns(true);

            // Routing from login.
            app.controller.context.set('layout', 'login');
            sandbox.stub(app.controller, 'loadView');

            app.router.navigate('Emails/123/compose', {trigger: true});

            expect(app.controller.loadView).toHaveBeenCalledOnce();
            expect(app.controller.loadView.firstCall.args[0].layout).toBe('compose-email');
            expect(app.controller.loadView.firstCall.args[0].action).toBe('edit');
        });

        it('should open the full page composer when routing directly', function() {
            model.set('state', 'Draft');
            sandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit').returns(true);

            // Routing from outside the app.
            app.controller.context.unset('layout');
            sandbox.stub(app.controller, 'loadView');

            app.router.navigate('Emails/123/compose', {trigger: true});

            expect(app.controller.loadView).toHaveBeenCalledOnce();
            expect(app.controller.loadView.firstCall.args[0].layout).toBe('compose-email');
            expect(app.controller.loadView.firstCall.args[0].action).toBe('edit');
        });

        it('should open the record view if the email is not a draft', function() {
            model.set('state', 'Archived');
            sandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit').returns(true);
            sandbox.stub(app.router, 'redirect');

            app.router.navigate('Emails/123/compose', {trigger: true});

            expect(app.router.redirect).toHaveBeenCalled();
        });

        it('should open the record view if the user cannot edit the draft', function() {
            model.set('state', 'Draft');
            sandbox.stub(app.acl, 'hasAccessToModel').withArgs('edit').returns(false);
            sandbox.stub(app.router, 'redirect');

            app.router.navigate('Emails/123/compose', {trigger: true});

            expect(app.router.redirect).toHaveBeenCalled();
        });
    });
});
