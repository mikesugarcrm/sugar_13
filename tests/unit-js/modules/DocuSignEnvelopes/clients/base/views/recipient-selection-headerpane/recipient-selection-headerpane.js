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
describe('DocuSignEnvelopes.Base.View.RecipientSelectionHeaderpane', function() {
    var app;
    var view;
    var context;
    var meta;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();

        context = new app.Context();

        layout = SugarTest.createLayout(
            'base',
            'DocuSignEnvelopes',
            'base',
            null,
            context
        );

        meta = {
            buttons: [
                {
                    name: 'back_button',
                },
                {
                    name: 'close',
                }
            ]
        };
        view = SugarTest.createView(
            'base',
            'DocuSignEnvelopes',
            'recipient-selection-headerpane',
            _.clone(meta),
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
        SugarTest.testMetadata.dispose();
        layout = null;
        context = null;
    });

    describe('initialize', function() {
        it('should keep back button', function() {
            context.set('templateDetails', {
                name: 'temp name'
            });
            view.initialize({
                meta: _.clone(meta),
                context: context
            });
            expect(view.meta.buttons.length).toEqual(2);
        });

        it('should remove back button', function() {
            context.unset('templateDetails');
            view.initialize({
                meta: _.clone(meta),
                context: context
            });
            expect(view.meta.buttons.length).toEqual(1);
        });
    });
});
