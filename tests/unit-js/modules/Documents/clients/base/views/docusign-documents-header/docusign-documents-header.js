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
describe('Documents.Base.View.DocusignDocumentsHeaderView', function() {
    var app;
    var view;
    var model;
    var context;
    var meta;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();

        SugarTest.app.data.declareModels();

        context = new app.Context();

        context.set('model', new Backbone.Model());

        layout = SugarTest.createLayout(
            'base',
            'Documents',
            'base',
            null,
            context
        );

        meta = {};
        view = SugarTest.createView(
            'base',
            'Documents',
            'docusign-documents-header',
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
        model = null;
        layout = null;
        context = null;
    });

    describe('clear', function() {
        it('should reset the collection', function() {
            view.collection = app.data.createBeanCollection('Documents');

            var model = app.data.createBean('Documents');
            view.collection.add(model);

            var collectionResetStub = sinon.stub(view.collection, 'reset');

            view.clear();
            expect(collectionResetStub).toHaveBeenCalledOnce();
        });
    });
});
