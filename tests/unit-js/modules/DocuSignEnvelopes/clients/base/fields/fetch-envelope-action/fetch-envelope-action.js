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
describe('Base.DocuSignEnvelopes.FetchEnvelopeActionField', function() {
    var app;
    var field;
    var model;
    var module = 'DocuSignEnvelopes';

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;

        model = app.data.createBean(module);

        field = SugarTest.createField(
            'base',
            'fetch-envelope-action',
            'fetch-envelope-action',
            'detail',
            {},
            module,
            model,
            null,
            true
        );
    });

    afterEach(function() {
        field.dispose();
        model = null;
        field = null;
    });

    describe('fetchEnvelope', function() {
        it('should fetch the envelope from DocuSign', function() {
            var appApiCallStub = sinon.stub(app.api, 'call');
            field.fetchEnvelope();

            expect(appApiCallStub).toHaveBeenCalledOnce();
            expect(appApiCallStub.lastCall.args[0]).toEqual('create');
            appApiCallStub.restore();
        });
    });
});