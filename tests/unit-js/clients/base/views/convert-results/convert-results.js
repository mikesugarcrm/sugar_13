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
describe("Base.Views.ConvertResults", function() {
    var app, view, createBeanStub;

    beforeEach(function() {
        app = SugarTest.app;

        metadata = SugarTest.metadata;

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('convert-results', 'view', 'base');
        SugarTest.testMetadata.set();

        createBeanStub = sinon.stub(app.data, 'createBean').callsFake(function(moduleName, attributes) {
            return new app.Bean(attributes);
        });

        view = SugarTest.createView('base', null, 'convert-results', null, null, true);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        createBeanStub.restore();
    });

    it("should have no models in collection", function() {
        expect(view.associatedModels.length).toEqual(0);
    });
});
