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
describe('DocuSignEnvelopes.Base.View.EnvelopeSetupHeaderpane', function() {
    var app;
    var view;
    var context;
    var openStub;
    var testPayload = {
        test: 123
    };

    beforeEach(function() {
        app = SugarTest.app;

        app.drawer = app.drawer || {};
        app.drawer.close = app.drawer.close || $.noop;

        app.DocuSign = app.DocuSign || {
            utils: {}
        };
        app.DocuSign.utils.shouldShowRecipients = app.DocuSign.utils.shouldShowRecipients || $.noop;

        openStub = sinon.stub(app.drawer, 'close');

        context = new app.Context();

        layout = SugarTest.createLayout(
            'base',
            'DocuSignEnvelopes',
            'base',
            null,
            context
        );

        view = SugarTest.createView(
            'base',
            'DocuSignEnvelopes',
            'envelope-setup-headerpane',
            {},
            context,
            true,
            layout,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        layout.dispose();
        layout = null;
        context = null;
    });

    describe('closeDrawer', function() {
        it('should close the drawer', function() {
            view.closeDrawer();

            expect(openStub).toHaveBeenCalledOnce();
        });
    });

    describe('navigateBack', function() {
        it('should store the payload', function() {
            context.set('payload', testPayload);

            view.navigateBack();

            expect(view._sendingPayload).toBe(testPayload);
        });
    });

    describe('_validateEnvelopeSetup', function() {
        it('should validate values given', function() {
            context.set('_envelopeName', 'env test');

            const validationRes = view._validateEnvelopeSetup();

            expect(validationRes).toBe(true);
        });
    });
});
