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
describe('DocuSignEnvelopes.Base.Layouts.TemplatesList', function() {
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
            'templates-list',
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

    describe('loadData', function() {
        it('should send correct parameters to load templates', function() {
            var superStub = sinon.stub(layout, '_super');
            var apiBuildUrlStub = sinon.stub(app.api, 'buildURL');
            var apiCallStub = sinon.stub(app.api, 'call');

            layout.context.set('contextModule', 'Accounts');
            layout.context.set('ctxModelId', '1');
            layout.collection = app.data.createBeanCollection();
            layout.collection.offset = 0;

            layout.loadData({});

            const expectedParams = {
                module: 'Accounts',
                id: '1',
                offset: 0
            };

            expect(apiBuildUrlStub).toHaveBeenCalledOnce();
            expect(apiBuildUrlStub.lastCall.args[0]).toEqual('DocuSign');
            expect(apiBuildUrlStub.lastCall.args[1]).toEqual('listTemplates');
            expect(JSON.stringify(apiBuildUrlStub.lastCall.args[3])).toEqual(JSON.stringify(expectedParams));

            superStub.restore();
            apiBuildUrlStub.restore();
            apiCallStub.restore();
        });
    });
});
