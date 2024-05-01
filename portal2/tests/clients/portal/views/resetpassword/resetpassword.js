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
describe('PortalResetpasswordView', function() {
    var app;
    var view;
    var viewName = 'resetpassword';

    beforeEach(function() {
        SugarTest.loadComponent('portal', 'view', viewName);

        app = SUGAR.App;
        view = SugarTest.createView('portal', '', viewName);

        sinon.stub(app.api, 'call');
        view.context.set('resetID', '1234');
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        view = null;
        sinon.restore();
    });

    describe('resetPassword', function() {
        it('should include field validation', function() {
            sinon.stub(view.model, 'doValidate');
            view.resetPassword();
            expect(view.model.doValidate).toHaveBeenCalled();
        });

        it('should call the reset portal password API if the password fields are valid', function() {
            sinon.stub(view.model, 'doValidate').callsFake(function(fields, callback) {
                callback(true);
            });

            // Build the expected URL, and test that it is called correctly
            var expectedPassword = 'test';
            var expectedURL = app.api.buildURL('portal_password/reset/?platform=portal');
            var expectedParams = {
                resetID: '1234',
                newPassword: expectedPassword
            };

            view.model.set('password1', expectedPassword);
            view.model.set('password2', expectedPassword);
            view.resetPassword();
            expect(app.api.call).toHaveBeenCalledWith('update', expectedURL, expectedParams, jasmine.any(Object));
        });

        it('should not call any API if the password fields are invalid', function() {
            sinon.stub(view.model, 'doValidate').callsFake(function(fields, callback) {
                callback(false);
            });
            view.resetPassword();
            expect(app.api.call).not.toHaveBeenCalled();
        });
    });
});
