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
describe("Leads ConvertButton", function() {
    var app, field, context, hasAccessStub, mockAccess;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addLayoutDefinition('convert-main', {
            modules: [
                {module: 'Foo', required: true},
                {module: 'Bar', required: false}
            ]
        });
        SugarTest.testMetadata.set();
        mockAccess = {
            Foo: true,
            Bar: true
        };
        hasAccessStub = sinon.stub(app.acl, 'hasAccess').callsFake(function(access, module) {
            return (_.isUndefined(mockAccess[module])) ? true : mockAccess[module];
        });
        context = app.context.getContext();

        var def = {'name':'record-convert','type':'convertbutton', 'view':'detail'};
        var Lead = Backbone.Model.extend({});
        var model = new Lead({
            id: 'aaa',
            name: 'boo',
            module: 'Leads'
        });
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        field = SugarTest.createField("base", 'record-convert', "convertbutton", "detail", def, 'Leads', model, context, true);
    });

    afterEach(function() {
        hasAccessStub.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.model = null;
        field = null;
        context = null;
    });

    it('should show if not converted and user has access to all convert lead modules', function() {
        field.model.set('converted', false);
        field._render();
        expect(field.isHidden).toBeFalsy();
    });

    it('should show if not converted and user has access to required convert lead modules', function() {
        mockAccess.Bar = false;
        field.model.set('converted', false);
        field._render();
        expect(field.isHidden).toBeFalsy();
    });

    it('should be hidden if converted', function() {
        field.model.set('converted', true);
        field._render();
        expect(field.isHidden).toBeTruthy();
    });

    it('should be hidden if user does not have access to create a module required by lead convert', function() {
        field.model.set('converted', false);
        mockAccess.Foo = false;
        field._render();
        expect(field.isHidden).toBeTruthy();
    });
});
