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
describe('DocuSignEnvelopes.Base.View.DocusignEnvelopesList', function() {
    var app;
    var view;
    var context;
    var meta;

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

        meta = {};
        view = SugarTest.createView(
            'base',
            'DocuSignEnvelopes',
            'docusign-envelopes-list',
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

    describe('_loadTemplate', function() {
        it('should load templates from parent', function() {
            view._loadTemplate({});
            expect(view.tplName).toEqual('recordlist');
        });
    });
    describe('_render', function() {
        it('should remove all left columns', function() {
            view._render();
            expect(view.leftColumns).toEqual([]);
        });
    });
    describe('handleDownloadClick', function() {
        it('should download the envelope documents', function() {
            var apiCallStub = sinon.stub(app.api, 'call');
            var model = new Backbone.Model();
            model.set('created_by_link', {id: '1'});
            app.user.id = '1';
            view.handleDownloadClick(model);

            expect(apiCallStub).toHaveBeenCalledOnce();
            expect(apiCallStub.lastCall.args[0]).toEqual('create');

            apiCallStub.restore();
        });
    });
});
