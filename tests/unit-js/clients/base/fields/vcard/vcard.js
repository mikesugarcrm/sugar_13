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
describe('vcard field', function() {
    var app, field, model, callStub;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base', 'vcard', 'vcard', 'vcard', {});
        model = field.model;

        callStub = sinon.stub(SugarTest.app.api, 'fileDownload');
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;

        callStub.restore();
    });

    it('should download the vcard of current record', function() {
        field.model.set('id', '123456789');
        field.model.set('module', 'Leads');

        field.rowActionSelect();

        expect(callStub.called).toBeTruthy();
    });

    it('should log an error if uri is empty and not download vcard', function() {
        var error, buildURLStub;

        error = sinon.spy(SugarTest.app.logger, 'error');
        buildURLStub = sinon.stub(SugarTest.app.api, 'buildURL').callsFake(function() {
            return '';
        });

        field.rowActionSelect();

        expect(buildURLStub.called).toBeTruthy();
        expect(callStub.called).toBeFalsy();
        expect(error.called).toBeTruthy();

        error.restore();
        buildURLStub.restore();
    });
});
