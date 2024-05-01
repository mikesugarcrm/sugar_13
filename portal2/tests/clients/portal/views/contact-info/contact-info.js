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
describe('PortalContactInfoView', function() {
    var app;
    var view;
    var viewName = 'contact-info';
    var mockContactInfo;

    beforeEach(function() {
        SugarTest.loadComponent('portal', 'view', viewName);
        app = SUGAR.App;

        // Add mock data to the app config settings
        mockContactInfo = {
            'contactPhone': '123-456-789',
            'contactEmail': 'fake@email.com',
            'contactURL': 'https://www.superfakewebsite.com'
        };
        app.config.contactInfo = mockContactInfo;

        view = SugarTest.createView('portal', '', viewName);
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        view = null;
        sinon.restore();
    });

    describe('initialize', function() {
        it('should get the correct contact information from config', function() {
            expect(view.contactInfo).toEqual(mockContactInfo);
        });
    });
});
