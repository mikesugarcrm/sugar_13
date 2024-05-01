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
describe('PortalLoginView', function() {
    var app;
    var view;
    var options;
    var viewName = 'login';

    beforeEach(function() {
        //Load base components before portal components
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('portal', 'view', viewName);

        app = SUGAR.App;
        var context = new app.Context();
        context.set('model', new Backbone.Model());
        var meta = {
            label: 'testLabel'
        };
        options = {
            context: context,
            meta: meta
        };
        view = SugarTest.createView('portal', null, viewName, meta, context);
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        view = null;
        sinon.restore();
    });

    describe('initialize', function() {
        it('should display forgot password', function() {
            app.config = {smtpServerSet: true};
            view.initialize(options);
            expect(view.showPortalPasswordReset).toBeTruthy();
        });
        it('should not display forgot password', function() {
            app.config = {smtpServerSet: false};
            view.initialize(options);
            expect(view.showPortalPasswordReset).toBeFalsy();
        });
        it('should not display forgot password', function() {
            app.config = {};
            view.initialize(options);
            expect(view.showPortalPasswordReset).toBeFalsy();
        });
    });

    describe('signup', function() {
        it('should properly route to sign up page', function() {
            app.router = app.router || {navigate: _.noop};
            sinon.stub(app.router, 'navigate');
            view.signup();
            expect(app.router.navigate).toHaveBeenCalledWith('#signup', {trigger: true});
        });
    });

    describe('setLanguage', function() {
        it('should select the correct language', function() {
            var langStub = sinon.stub(app.lang, 'setLanguage');
            var language = 'en_us';
            view.setLanguage(language);
            expect(langStub).toHaveBeenCalledWith(language);
        });
    });
});
