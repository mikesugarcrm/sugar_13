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
describe('Base.Fields.Pdfaction', function() {
    var app;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('pdfaction', 'field', 'base', 'detail');
        SugarTest.testMetadata.set();

        var stubAppDataCreateBeanCollection = sinon.stub(app.data, 'createBeanCollection');
        stubAppDataCreateBeanCollection.withArgs('PdfManager').returns(new Backbone.Collection([{"name": "pdfaction"}]));

        sinon.stub(Backbone.Collection.prototype, 'fetch');
    });

    afterEach(function() {
        sinon.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('download button', function() {
        var download;

        beforeEach(function() {
            download = SugarTest.createField('base', 'download-pdf', 'pdfaction', 'detail', {
                label: 'LBL_PDF_VIEW',
                action: 'download',
                acl_action: 'view'
            });
        });

        afterEach(function() {
            download.dispose();
        });

        it('should render a download button', function() {
            download.render();
            expect(download.$el.hasClass('hide')).toBe(false);
        });
    });

    describe('email button', function() {
        var email;

        beforeEach(function() {
            email = SugarTest.createField('base', 'email-pdf', 'pdfaction', 'detail', {
                label: 'LBL_PDF_EMAIL',
                action: 'email',
                acl_action: 'view'
            });
        });

        afterEach(function() {
            email.dispose();
        });

        it('should render an email button when the user can use the sugar email client', function() {
            var stubAppUserGetPreference = sinon.stub(app.user, 'getPreference');
            stubAppUserGetPreference.withArgs('email_client_preference').returns({type:'sugar'});
            email.render();
            expect(email.$el.hasClass('hide')).toBe(false);
        });

        it('should not render an email button when the user cannot use the sugar email client', function() {
            var stubAppUserGetPreference = sinon.stub(app.user, 'getPreference');
            stubAppUserGetPreference.withArgs('email_client_preference').returns({type:'mailto'});
            email.render();
            expect(email.$el.hasClass('hide')).toBe(true);
        });
    });

    describe('downloadClicked', function() {

        it('should authenticate in bwc mode before triggering the download', function() {
            var download = SugarTest.createField('base', 'download-pdf', 'pdfaction', 'detail', {
                label: 'LBL_PDF_VIEW',
                action: 'download',
                acl_action: 'view'
            });

            var loginSpy = sinon.spy(app.bwc, 'login');
            var fileDownloadStub = sinon.stub(app.api, 'fileDownload');
            sinon.stub(app.api, 'call').callsFake(function(method, url, data, callbacks, options) {
                callbacks.success();
            });
            download.downloadClicked({});

            expect(loginSpy).toHaveBeenCalled();
            expect(fileDownloadStub).toHaveBeenCalled();
        });
    });
});
