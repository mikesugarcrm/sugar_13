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
describe("Leads.Views.Record", function() {
    var app, view;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', 'Leads', 'record', null, null, true);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    it("should remove blackout fields when copying record and no default", function() {
        var prefill = app.data.createBean('Leads');
        prefill.fields = { status: {}}; //no default
        prefill.set('status', 'Converted');
        view.setupDuplicateFields(prefill);
        expect(prefill.get('status')).toBeUndefined();
    });

    it("should replace blackout field value with default if it exists", function() {
        var prefill = app.data.createBean('Leads');
        prefill.fields = { status: { 'default': 'Foo' }}; //default
        prefill.set('status', 'Converted');
        view.setupDuplicateFields(prefill);
        expect(prefill.get('status')).toEqual('Foo');
    });

    it("should leave non-blackout fields alone", function() {
        var prefill = app.data.createBean('Leads');
        prefill.set('foo', 'Bar');
        view.setupDuplicateFields(prefill);
        expect(prefill.get('foo')).toEqual('Bar');
    });

});
