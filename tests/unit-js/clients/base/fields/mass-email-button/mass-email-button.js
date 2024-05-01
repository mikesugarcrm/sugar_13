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
describe('Base.Fields.MassEmailButton', function() {
    var app, module, field, context, massCollection, sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        module = 'Contacts';

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('mass-email-button', 'field', 'base', 'list-header');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'mass-email-button');
        SugarTest.testMetadata.set();
        app.data.declareModels();
        app.data.declareModel('EmailParticipants');
        app.data.declareModel('EmailAddresses');
        SugarTest.loadPlugin('EmailClientLaunch');

        context = app.context.getContext();
        massCollection = app.data.createBeanCollection(module);
        context.set({
            mass_collection: massCollection
        });
        context.prepare();

        field = SugarTest.createField({
            name: 'mass_email_button',
            type: 'mass-email-button',
            viewName: 'list-header',
            context: context
        });
    });

    afterEach(function() {
        sandbox.restore();
        SugarTest.testMetadata.dispose();
        field.dispose();
        field = null;
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('sending email to all selected recipients', function() {
        var email1;
        var email2;
        var bean1;
        var bean2;

        beforeEach(function() {
            email1 = 'foo1@bar.com';
            email2 = 'foo2@bar.com';
            bean1 = app.data.createBean(module, {
                id: _.uniqueId(),
                name: 'Harold White',
                email: [
                    {
                        email_address: email1,
                        primary_address: true,
                        invalid_email: false,
                        opt_out: false
                    }
                ]
            });
            bean2 = app.data.createBean(module, {
                id: _.uniqueId(),
                name: 'Janice Kerling',
                email: [
                    {
                        email_address: email2,
                        primary_address: true,
                        invalid_email: false,
                        opt_out: false
                    }
                ]
            });

            massCollection.add([bean1, bean2]);
        });

        it('should construct a mailto link for external email clients', function() {
            sandbox.stub(field, 'useSugarEmailClient').returns(false);
            expect(field.$('a').attr('href')).toBe('mailto:' + email1 + ',' + email2);
        });

        it('should open the internal email client with all of the recipients', function() {
            var drawerOpenOptions;

            sandbox.stub(app.utils, 'openEmailCreateDrawer');
            sandbox.stub(field, 'useSugarEmailClient').returns(true);
            field.$('a').click();

            expect(app.utils.openEmailCreateDrawer.callCount).toBe(1);
            drawerOpenOptions = app.utils.openEmailCreateDrawer.lastCall.args[1];

            expect(drawerOpenOptions.to.length).toEqual(2);
            expect(drawerOpenOptions.to[0].get('parent').type).toBe(module);
            expect(drawerOpenOptions.to[0].get('parent').id).toBe(bean1.get('id'));
            expect(drawerOpenOptions.to[0].get('parent').name).toBe(bean1.get('name'));
            expect(drawerOpenOptions.to[0].get('parent_type')).toBe(module);
            expect(drawerOpenOptions.to[0].get('parent_id')).toBe(bean1.get('id'));
            expect(drawerOpenOptions.to[0].get('parent_name')).toBe(bean1.get('name'));
            expect(drawerOpenOptions.to[0].get('email_address_id')).toBeUndefined();
            expect(drawerOpenOptions.to[0].get('email_address')).toBeUndefined();
            expect(drawerOpenOptions.to[1].get('parent').type).toBe(module);
            expect(drawerOpenOptions.to[1].get('parent').id).toBe(bean2.get('id'));
            expect(drawerOpenOptions.to[1].get('parent').name).toBe(bean2.get('name'));
            expect(drawerOpenOptions.to[1].get('parent_type')).toBe(module);
            expect(drawerOpenOptions.to[1].get('parent_id')).toBe(bean2.get('id'));
            expect(drawerOpenOptions.to[1].get('parent_name')).toBe(bean2.get('name'));
            expect(drawerOpenOptions.to[1].get('email_address_id')).toBeUndefined();
            expect(drawerOpenOptions.to[1].get('email_address')).toBeUndefined();
        });
    });
});
