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
describe('Emails.Fields.EmailactionPaneltop', function() {
    var app, field, sandbox;
    var context;
    var model;

    function createField(fieldDef) {
        var def = {
            icon: 'sicon-plus',
            name: 'email_compose_button',
            acl_action: 'create',
            tooltip: 'LBL_CREATE_BUTTON_LABEL'
        };

        def = _.extend(def, fieldDef || {});

        return SugarTest.createField({
            client: 'base',
            name: 'paneltop',
            type: 'emailaction-paneltop',
            viewName: 'detail',
            module: 'Emails',
            model: model,
            context: context,
            loadFromModule: true,
            fieldDef: def
        });
    }

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadPlugin('EmailClientLaunch');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'emailaction');
        SugarTest.loadComponent('base', 'field', 'emailaction-paneltop', 'Emails');
        SugarTest.testMetadata.set();

        app.data.declareModels();

        context = app.context.getContext({module: 'Contacts'});
        context.prepare(true);
        model = context.get('model');
        model.set({
            id: _.uniqueId(),
            first_name: 'Bobby',
            last_name: 'Francis'
        });

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        field = null;
    });

    describe('closing the email client', function() {
        it('should trigger paneltop:refresh events on the context', function() {
            field = createField();
            field.context.parent = undefined;
            sandbox.spy(field.context, 'trigger');

            field.trigger('emailclient:close');

            expect(field.context.trigger.callCount).toBe(3);
            expect(field.context.trigger.getCall(0).args[0]).toEqual('panel-top:refresh');
            expect(field.context.trigger.getCall(0).args[1]).toEqual('emails');
            expect(field.context.trigger.getCall(1).args[0]).toEqual('panel-top:refresh');
            expect(field.context.trigger.getCall(1).args[1]).toEqual('archived_emails');
            expect(field.context.trigger.getCall(2).args[0]).toEqual('panel-top:refresh');
            expect(field.context.trigger.getCall(2).args[1]).toEqual('contacts_activities_1_emails');
        });

        it('should trigger the events on the parent context', function() {
            var parentContext = app.context.getContext({module: 'Contacts'});

            parentContext.prepare(true);
            sandbox.spy(parentContext, 'trigger');

            field = createField();
            field.context.parent = parentContext;

            field.trigger('emailclient:close');

            expect(field.context.parent.trigger.callCount).toBe(3);
        });
    });

    describe('Email Options', function() {
        using(
            'field defs',
            [
                {set_recipient_to_parent: false},
                {set_related_to_parent: false},
                {}
            ],
            function(def) {
                it('should not add the model as a recipient', function() {
                    field = createField(def);
                    expect(field.emailOptions.to).toBeUndefined();
                });

                it('should not add the model as the related record', function() {
                    field = createField(def);
                    expect(field.emailOptions.related).toBeUndefined();
                });
            }
        );

        it('should add the model as a recipient', function() {
            field = createField({set_recipient_to_parent: true});
            expect(field.emailOptions.to.length).toBe(1);
            expect(field.emailOptions.to[0].bean.get('id')).toBe(model.get('id'));
        });

        it('should add the model as the related record', function() {
            field = createField({set_related_to_parent: true});
            expect(field.emailOptions.related.get('id')).toBe(model.get('id'));
        });
    });
});
