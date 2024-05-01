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
describe('Portal User extensions', function() {

    var app;

    beforeEach(function() {
        app = SUGAR.App;
        app.user.clear();

        SugarTest.loadFile('../portal2', 'user', 'js', function(d) {
            eval(d);
        });
    });

    describe('app.user.isSupportPortalUser', function() {

        it('should be a portal user', function() {
            app.user.set('type', 'support_portal');
            expect(app.user.isSupportPortalUser()).toBeTruthy();
        });

        it('should not be a portal user', function() {
            expect(app.user.isSupportPortalUser()).toBeFalsy();
        });
    });

});
