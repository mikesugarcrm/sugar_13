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
describe('Plugins.FindDuplicates', function() {
    var app,
        collection,
        model,
        module,
        sandbox,
        view;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        app.data.declareModels();
        sandbox = sinon.createSandbox();

        module = 'Contacts';
        SugarTest.loadPlugin('FindDuplicates');
        view = SugarTest.createView('base', module, 'create');
        SugarTest.app.plugins.attach(view, 'view');

        model = app.data.createBean(module, {first_name: 'Foo', last_name: 'Bar'});
        collection = view.createDuplicateCollection(model, module);
    });

    afterEach(function() {
        sandbox.restore();
        view.dispose();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        app.cache.cutAll();
        view = null;
    });

    it('should not call the duplicate check api', function() {
        var error, success;

        error = sandbox.spy();
        success = sandbox.spy();

        sandbox.stub(app.metadata, 'getModule').withArgs(module).returns({dupCheckEnabled: false});
        sandbox.stub(collection, 'endpoint');
        collection.on('duplicatecheck:error', error);
        collection.sync('create', model, {success: success});

        // The Duplicate Check endpoint is not hit.
        expect(collection.endpoint).not.toHaveBeenCalled();
        // The error event handler is called with an Error.
        expect(error).toHaveBeenCalled();
        expect(error.firstCall.args[0] instanceof Error).toBe(true);
        // The success callback is called.
        expect(success).toHaveBeenCalled();
        // The collection has been marked as fetched.
        expect(collection.dataFetched).toBe(true);
    });
});
