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

describe('Portal Routes', function() {
    let app;
    let oldSync;
    let sandbox;
    let oldBeforeRoute;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadFile('../portal2', 'portal', 'js', function(d) {
            eval(d);
            app.routing.start();
        });

        oldSync = app.isSynced;
        app.isSynced = true;
        sandbox = sinon.createSandbox();

        oldBeforeRoute = app.routing.beforeRoute;
        app.routing.beforeRoute = () => true;
    });

    afterEach(function() {
        app.isSynced = oldSync;
        app.routing.beforeRoute = oldBeforeRoute;
        app.router.stop();
        sandbox.restore();
    });

    it('should allow access to sign up if enableSelfSignUp is enabled', function() {
        app.config.enableSelfSignUp = 'enabled';

        sandbox.stub(app.controller, 'loadView');

        app.router.navigate('signup', {trigger: true});
        expect(app.controller.loadView).toHaveBeenCalledWith({
            module: 'Signup',
            layout: 'signup',
            create: true
        });
    });

    it('should block access to sign up if enableSelfSignUp is disabled', function() {
        app.config.enableSelfSignUp = 'disabled';

        sandbox.stub(app.router, 'redirect');

        app.router.navigate('signup', {trigger: true});
        expect(app.router.redirect).toHaveBeenCalledWith('/');
    });
});
