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
describe('Base.Field.Shareaction', function() {
    var app;
    var field;
    var sinonSandbox;
    var context;
    var model;

    beforeEach(function() {
        app = SugarTest.app;
        app.drawer = { open: $.noop };

        SugarTest.testMetadata.init();
        SugarTest.loadPlugin('EmailClientLaunch');
        SugarTest.loadHandlebarsTemplate('share', 'view', 'base', 'subject');
        SugarTest.loadHandlebarsTemplate('share', 'view', 'base', 'body');
        SugarTest.loadHandlebarsTemplate('share', 'view', 'base', 'body-html');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'emailaction');
        SugarTest.loadComponent('base', 'field', 'shareaction');
        SugarTest.testMetadata.set();

        sinonSandbox = sinon.createSandbox();

        context = app.context.getContext({module: 'Contacts'});
        context.prepare(true);
        model = context.get('model');
        model.set({
            id: _.uniqueId(),
            first_name: 'Bobby',
            last_name: 'Francis'
        });

        field = SugarTest.createField({
            name: 'share',
            type: 'shareaction',
            viewName: 'detail',
            module: model.module,
            model: model,
            context: context
        });
    });

    afterEach(function() {
        sinonSandbox.restore();
        app.drawer = undefined;
        SugarTest.testMetadata.dispose();
        field.dispose();
        field = null;
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    it('should use the emailaction templates', function() {
        expect(field.type).toBe('emailaction');
    });

    it('should set the email signature location to below', function() {
        expect(field.emailOptions.signature_location).toBe('below');
    });

    it('should set the email subject using the subject template', function() {
        expect(field.emailOptions.name).toContain('TPL_RECORD_SHARE_SUBJECT');
    });

    it('should set the email body using the body template', function() {
        expect(field.emailOptions.description).toContain('TPL_RECORD_SHARE_BODY');
    });

    it('should set the email HTML body using the HTML body template', function() {
        expect(field.emailOptions.description_html).toContain('TPL_RECORD_SHARE_BODY');
    });

    describe('getting the share params', function() {
        it('should use the model parameter', function() {
            var contact = app.data.createBean('Contacts', {
                id: _.uniqueId(),
                first_name: 'Tammy',
                last_name: 'Smith'
            });
            var params = field._getShareParams(contact);

            expect(params.appId).toBe(app.config.appId);
            expect(params.url).toBe(window.location.href);
            expect(params.id).toBe(contact.get('id'));
            expect(params.first_name).toBe('Tammy');
            expect(params.last_name).toBe('Smith');
            expect(params.name.toString()).toBe('Tammy Smith');
        });

        it('should fall back to this.model', function() {
            var params = field._getShareParams();

            expect(params.appId).toBe(app.config.appId);
            expect(params.url).toBe(window.location.href);
            expect(params.id).toBe(model.get('id'));
            expect(params.first_name).toBe('Bobby');
            expect(params.last_name).toBe('Francis');
            expect(params.name.toString()).toBe('Bobby Francis');
        });
    });
});
