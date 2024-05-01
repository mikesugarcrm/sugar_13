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
describe('DocuSignEnvelopes.Base.View.EnvelopeSetup', function() {
    var app;
    var view;
    var context;
    var templateName = 'template1';

    beforeEach(function() {
        app = SugarTest.app;

        context = new app.Context();
        context.set('model', new Backbone.Model());
        context.set('payload', {
            template: {
                name: templateName
            }
        });
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
            'envelope-setup',
            {},
            context,
            true,
            layout,
            true
        );
    });

    afterEach(function() {
        view.dispose();
        layout.dispose();
        layout = null;
        context = null;
    });

    describe('initialize', function() {
        it('should set the envelope name on the component', function() {
            view.initialize({
                context: context,
            });
            expect(view._envelopeName).toEqual(templateName);
        });
    });
});
