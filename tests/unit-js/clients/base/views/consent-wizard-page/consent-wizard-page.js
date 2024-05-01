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

describe('View.Views.Base.ConsentWizardPageView', function() {
    var view;
    var viewName = 'consent-wizard-page';
    var app;

    beforeEach(function() {
        app = SUGAR.App;
        SugarTest.loadComponent('base', 'view', viewName);
        view = SugarTest.createView('base', null, viewName);
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        view = null;
        sinon.restore();
    });

    describe('continueConsent', function() {
        it('should update the user profile when cookie_consent is true', function() {
            view.model.set('cookie_consent', true);
            sinon.stub(view.model, 'doValidate').yields(true);
            var updateStub = sinon.stub(app.user, 'updateProfile');
            view.continueConsent();
            expect(updateStub).toHaveBeenCalled();
        });
    });
});
