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
describe('DocuSignEnvelopes.Base.Layouts.RecipientsList', function() {
    var app;
    var context;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();

        SugarTest.app.data.declareModels();

        context = new app.Context();

        context.set('model', new Backbone.Model());

        context.parent = new app.Context({module: 'DocuSignEnvelopes'});

        layout = SugarTest.createLayout(
            'base',
            'DocuSignEnvelopes',
            'recipients-list',
            null,
            context,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        layout = null;
        context = null;
    });

    describe('initialize', function() {
        it('should config mass collection', function() {
            layout.initialize({});
            var massCollectionOnContext = layout.context.get('mass_collection') instanceof app.data.beanCollection;
            expect(massCollectionOnContext).toEqual(true);
        });
    });
});
