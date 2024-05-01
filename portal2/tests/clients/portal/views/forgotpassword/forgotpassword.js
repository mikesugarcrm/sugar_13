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
describe('PortalForgotPasswordView', function() {
    var app;
    var view;
    var viewName = 'forgotpassword';

    beforeEach(function() {
        //Load base components before portal components
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('portal', 'view', viewName);
        app = SUGAR.App;
        view = SugarTest.createView('portal', '', viewName);
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        view = null;
        sinon.restore();
    });

    describe('forgotPassword', function() {
        it('should make the api call to send email', function() {
            sinon.stub(app.api, 'call');
            sinon.stub(view.model, 'doValidate').yields(true);
            view.forgotPassword();
            expect(app.api.call).toHaveBeenCalled();
        });
    });
});
