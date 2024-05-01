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
describe('Users.Routes', function() {
    let app;
    let oldIsSynced;

    beforeEach(function() {
        app = SugarTest.app;
        app.controller.loadAdditionalComponents(app.config.additionalComponents);

        oldIsSynced = app.isSynced;
        app.isSynced = true;

        SugarTest.loadFile('../modules/Users/clients/base/routes', 'routes', 'js', function(d) {
            eval(d);
            app.routing.start();
        });
    });

    afterEach(function() {
        app.isSynced = oldIsSynced;
        sinon.restore();
        app.router.stop();
    });

    describe('list', function() {
        beforeEach(function() {
            sinon.stub(app.router, 'hasAccessToModule').returns(true);
            sinon.stub(app.api, 'isAuthenticated').returns(true);
            sinon.stub(app.router, 'list');
            sinon.stub(app.controller, 'loadView');
        });

        it('should load the Users list view when the user is an admin or user have access to Users LV', function() {
            app.user.set('type', 'admin');
            sinon.stub(app.user, 'getAcls').returns({Users: {}});
            app.router.navigate('Users', {trigger: true});
            expect(app.router.list).toHaveBeenCalledWith('Users');
        });

        it('should load the Users list view if the user is not an admin but user have access to Users LV', function() {
            app.user.set('type', 'user');
            sinon.stub(app.user, 'getAcls').returns({Users: {}});
            app.router.navigate('Users', {trigger: true});
            expect(app.router.list).toHaveBeenCalledWith('Users');
        });

        it('should load "access-denied" view if the user is not an admin and have not access to Users LV', function() {
            app.user.set('type', 'user');
            sinon.stub(app.user, 'getAcls').returns({Users: {developer: 'no'}});
            app.router.navigate('Users', {trigger: true});
            expect(app.router.list).not.toHaveBeenCalledWith('Users');
            expect(app.controller.loadView).toHaveBeenCalledWith({layout: 'access-denied'});
        });
    });
});
