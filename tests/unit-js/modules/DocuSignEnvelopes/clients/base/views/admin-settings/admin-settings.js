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
describe('DocuSignEnvelopes.Base.View.AdminSettings', function() {
    var app;
    var view;
    var context;
    var meta;
    let layout;
    let apiCallStub;

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
            'base',
            null,
            context
        );

        apiCallStub = sinon.stub(app.api, 'call');

        meta = {};
        view = SugarTest.createView(
            'base',
            'DocuSignEnvelopes',
            'admin-settings',
            meta,
            context,
            true,
            layout,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        layout = null;
        context = null;
    });

    describe('initialize', function() {
        it('should make a call to get settings', function() {
            apiCallStub.resetHistory();
            view.initialize({});

            expect(apiCallStub).toHaveBeenCalledOnce();
            expect(apiCallStub.lastCall.args[0]).toEqual('read');

            apiCallStub.restore();
        });
    });
});
