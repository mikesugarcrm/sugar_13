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
describe('Emails.BaseEmailRecipientsField', function() {
    var app;
    var context;
    var field;
    var to;
    var model;
    var sandbox;
    var parentId1;
    var emailAddressId1;
    var parentId2;
    var emailAddressId2;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');
        parentId1 = _.uniqueId();
        emailAddressId1 = _.uniqueId();
        parentId2 = _.uniqueId();
        emailAddressId2 = _.uniqueId();

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadPlugin('EmailParticipants');
        SugarTest.loadPlugin('DragdropSelect2');
        SugarTest.loadHandlebarsTemplate('email-recipients', 'field', 'base', 'detail', 'Emails');
        SugarTest.loadHandlebarsTemplate('email-recipients', 'field', 'base', 'edit', 'Emails');
        SugarTest.loadHandlebarsTemplate('email-recipients', 'field', 'base', 'select2-result', 'Emails');
        SugarTest.loadHandlebarsTemplate('email-recipients', 'field', 'base', 'select2-selection', 'Emails');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        SugarTest.declareData('base', 'EmailParticipants', true, false);
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');

        to = [
            app.data.createBean('EmailParticipants', {
                _link: 'to',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    _erased_fields: [],
                    type: 'Contacts',
                    id: parentId1,
                    name: 'Herbert Yates'
                },
                parent_type: 'Contacts',
                parent_id: parentId1,
                parent_name: 'Herbert Yates',
                email_addresses: {
                    _acl: {},
                    _erased_fields: [],
                    id: emailAddressId1,
                    email_address: 'hyates@example.com',
                    invalid_email: false,
                    opt_out: false
                },
                email_address_id: emailAddressId1,
                email_address: 'hyates@example.com',
                invalid_email: false,
                opt_out: false
            }),
            app.data.createBean('EmailParticipants', {
                _link: 'to',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    _erased_fields: [],
                    type: 'Contacts',
                    id: parentId2,
                    name: 'Walter Quigley'
                },
                parent_type: 'Contacts',
                parent_id: parentId2,
                parent_name: 'Walter Quigley',
                email_addresses: {
                    _acl: {},
                    _erased_fields: [],
                    id: emailAddressId2,
                    email_address: 'wquigley@example.com',
                    invalid_email: false,
                    opt_out: true
                },
                email_address_id: emailAddressId2,
                email_address: 'wquigley@example.com',
                invalid_email: false,
                opt_out: true
            })
        ];

        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('responding to data changes', function() {
        it('should render the field', function() {
            field = SugarTest.createField({
                name: 'to_collection',
                type: 'email-recipients',
                viewName: 'detail',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.render();

            sandbox.stub(field, 'render');
            field.model.set('to_collection', to);
            field.model.trigger('sync');

            expect(field.render).toHaveBeenCalledOnce();
        });

        it('should set data on Select2', function() {
            field = SugarTest.createField({
                name: 'to_collection',
                type: 'email-recipients',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.render();

            sandbox.stub(field, 'render');
            sandbox.spy(field, 'getFormattedValue');
            sandbox.spy(field, '_decorateOptedOutRecipients');
            sandbox.spy(field, '_decorateInvalidRecipients');
            sandbox.spy(field, '_enableDragDrop');
            field.model.set('to_collection', to);
            field.model.trigger('sync');

            expect(field.render).not.toHaveBeenCalled();
            expect(field.getFormattedValue).toHaveBeenCalledOnce();
            expect(field._decorateOptedOutRecipients).toHaveBeenCalledOnce();
            expect(field._decorateInvalidRecipients).toHaveBeenCalledOnce();
            expect(field._enableDragDrop).toHaveBeenCalledOnce();
            expect(field.$(field.fieldTag).select2('data').length).toBe(to.length);
        });
    });

    describe('build filter definition', function() {
        it('should return the value segment of a filter definition', function() {
            var emailAddressId3 = _.uniqueId();
            var emailAddressId4 = _.uniqueId();
            var parentId4 = _.uniqueId();
            var beanWithNoParent = app.data.createBean('EmailParticipants', {
                _link: 'to',
                email_address_id: emailAddressId3,
                email_address: 'foo@test.com',
                invalid_email: false,
                opt_out: true
            });
            var beanWithNoEmailAddress = app.data.createBean('EmailParticipants', {
                _link: 'to',
                parent: {
                    _acl: {},
                    type: 'Leads',
                    id: parentId4,
                    name: 'Tim'
                },
                parent_type: 'Leads',
                parent_id: parentId4,
                parent_name: 'Tim',
            });
            var beanWithInvalidEmailAddress = app.data.createBean('EmailParticipants', {
                _link: 'to',
                email_address_id: emailAddressId4,
                email_address: 'fizz@buzz.com',
                invalid_email: true,
                opt_out: false
            });
            var filterDef;

            field = SugarTest.createField({
                name: 'to_collection',
                type: 'email-recipients',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });

            field.model.set('to_collection', to);
            field.model.get('to_collection').add(beanWithNoParent);
            field.model.get('to_collection').add(beanWithNoEmailAddress);
            field.model.get('to_collection').add(beanWithInvalidEmailAddress);
            field.model.trigger('sync');

            filterDef = field.delegateBuildFilterDefinition();

            expect(filterDef.length).toEqual(5);

            expect(filterDef[0].parent_id).toBe(parentId1);
            expect(filterDef[0].parent_type).toBe('Contacts');
            expect(filterDef[0].email_address_id).toBe(emailAddressId1);

            expect(filterDef[1].parent_id).toBe(parentId2);
            expect(filterDef[1].parent_type).toBe('Contacts');
            expect(filterDef[1].email_address_id).toBe(emailAddressId2);

            expect(filterDef[2].parent_id).toBeUndefined();
            expect(filterDef[2].parent_type).toBeUndefined();
            expect(filterDef[2].email_address_id).toBe(emailAddressId3);

            expect(filterDef[3].parent_id).toBe(parentId4);
            expect(filterDef[3].parent_type).toBe('Leads');
            expect(filterDef[3].email_address_id).toBeUndefined();

            expect(filterDef[4].parent_id).toBeUndefined();
            expect(filterDef[4].parent_type).toBeUndefined();
            expect(filterDef[4].email_address_id).toBe(emailAddressId4);
        });
    });

    describe('responding to DOM changes', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'to_collection',
                type: 'email-recipients',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.render();
        });

        it('should not complete the selection when it is a duplicate bean', function() {
            var event = new $.Event('select2-selecting');

            sandbox.spy(event, 'preventDefault');
            field.model.set('to_collection', to);
            field.model.trigger('sync');
            event.choice = app.data.createBean('EmailParticipants', {
                _link: 'to',
                parent: {
                    _acl: {},
                    type: to[1].get('parent_type'),
                    id: to[1].get('parent_id'),
                    name: to[1].get('parent_name')
                },
                parent_type: to[1].get('parent_type'),
                parent_id: to[1].get('parent_id'),
                parent_name: to[1].get('parent_name'),
                email_address_id: to[1].get('email_address_id'),
                email_address: to[1].get('email_address'),
                invalid_email: to[1].get('invalid_email'),
                opt_out: to[1].get('opt_out')
            });

            field.$(field.fieldTag).trigger(event);

            expect(event.preventDefault).toHaveBeenCalled();
            expect(field.model.get('to_collection').length).toBe(2);
        });

        it('should not complete the selection when it is a duplicate email address ID', function() {
            var event = new $.Event('select2-selecting');

            sandbox.spy(event, 'preventDefault');
            field.model.set('to_collection', to);
            field.model.trigger('sync');
            event.choice = app.data.createBean('EmailParticipants', {
                _link: 'to',
                email_address_id: to[1].get('email_address_id'),
                email_address: to[1].get('email_address'),
                invalid_email: to[1].get('invalid_email'),
                opt_out: to[1].get('opt_out')
            });

            field.$(field.fieldTag).trigger(event);

            expect(event.preventDefault).toHaveBeenCalled();
            expect(field.model.get('to_collection').length).toBe(2);
        });

        it('should not complete the selection when it is a duplicate email address', function() {
            var event = new $.Event('select2-selecting');

            sandbox.spy(event, 'preventDefault');
            field.model.set('to_collection', to);
            field.model.trigger('sync');
            event.choice = app.data.createBean('EmailParticipants', {
                _link: 'to',
                email_address: to[1].get('email_address')
            });

            field.$(field.fieldTag).trigger(event);

            expect(event.preventDefault).toHaveBeenCalled();
            expect(field.model.get('to_collection').length).toBe(2);
        });

        it('should add to the collection', function() {
            var parentId = _.uniqueId();
            var event = new $.Event('change');
            var actual;

            field.model.set('to_collection', to);
            field.model.trigger('sync');
            event.added = [
                app.data.createBean('EmailParticipants', {
                    _link: 'to',
                    parent: {
                        _acl: {},
                        type: 'Contacts',
                        id: parentId,
                        name: 'Ira Carr'
                    },
                    parent_type: 'Contacts',
                    parent_id: parentId,
                    parent_name: 'Ira Carr',
                    email_address_id: _.uniqueId(),
                    email_address: 'icarr@example.com',
                    invalid_email: false,
                    opt_out: false
                })
            ];

            field.$(field.fieldTag).trigger(event);
            actual = field.model.get('to_collection');

            expect(actual.length).toBe(3);
        });

        it('should remove the recipient', function() {
            var event = new $.Event('change');

            field.model.set('to_collection', to);
            field.model.trigger('sync');
            event.removed = [to[1]];

            field.$(field.fieldTag).trigger(event);

            expect(field.model.get('to_collection').length).toBe(1);
        });
    });

    it('should format the models in the collection', function() {
        var field = SugarTest.createField({
            name: 'to_collection',
            type: 'email-recipients',
            viewName: 'detail',
            module: model.module,
            model: model,
            context: context,
            loadFromModule: true
        });
        var actual;

        field.model.set('to_collection', to);
        field.model.trigger('sync');
        actual = field.getFormattedValue();

        expect(actual.length).toBe(to.length);
        expect(actual[0].locked).toBe(false);
        expect(actual[0].invalid).toBe(false);
        expect(actual[0].get('parent_name')).toBe('Herbert Yates');
        expect(actual[0].get('email_address')).toBe('hyates@example.com');
        expect(actual[0].get('opt_out')).toBe(false);
        expect(actual[1].locked).toBe(false);
        expect(actual[1].invalid).toBe(false);
        expect(actual[1].get('parent_name')).toBe('Walter Quigley');
        expect(actual[1].get('email_address')).toBe('wquigley@example.com');
        expect(actual[1].get('opt_out')).toBe(true);
        expect(field.tooltip).toBe('Herbert Yates <hyates@example.com>, Walter Quigley <wquigley@example.com>');
    });

    describe('decorating pills', function() {
        it('should decorate invalid recipients', function() {
            var parentId = _.uniqueId();
            var invalid = app.data.createBean('EmailParticipants', {
                _link: 'to',
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId,
                    name: 'Francis Humphrey'
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: 'Francis Humphrey',
                email_address_id: _.uniqueId(),
                email_address: 'foo',
                invalid_email: true,
                opt_out: false
            });
            var invalidSelector = '.select2-search-choice [data-invalid="true"]';

            field = SugarTest.createField({
                name: 'to_collection',
                type: 'email-recipients',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });

            field.model.set('to_collection', to);
            field.model.trigger('sync');
            expect(field.$(invalidSelector).length).toBe(0);

            field.model.get('to_collection').add(invalid);
            expect(field.$(invalidSelector).length).toBe(1);
            expect(field.$('.select2-choice-danger').length).toBe(1);
            expect(field.$('[data-title=ERR_INVALID_EMAIL_ADDRESS]').length).toBe(1);

            // Make sure it is still decorated after a full render.
            field.render();
            expect(field.$(invalidSelector).length).toBe(1);
            expect(field.$('.select2-choice-danger').length).toBe(1);
            expect(field.$('[data-title=ERR_INVALID_EMAIL_ADDRESS]').length).toBe(1);
        });

        it('should not decorate invalid recipients', function() {
            var parentId = _.uniqueId();
            var invalid = app.data.createBean('EmailParticipants', {
                _link: 'to',
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId,
                    name: 'Francis Humphrey'
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: 'Francis Humphrey',
                email_address_id: _.uniqueId(),
                email_address: 'foo',
                invalid_email: true,
                opt_out: false
            });
            var invalidSelector = '.select2-search-choice [data-invalid="true"]';

            field = SugarTest.createField({
                name: 'to_collection',
                type: 'email-recipients',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.def.decorate_invalid = false;

            field.model.set('to_collection', to);
            field.model.trigger('sync');

            field.model.get('to_collection').add(invalid);
            expect(field.$(invalidSelector).length).toBe(1);
            expect(field.$('.select2-choice-danger').length).toBe(0);
            expect(field.$('[data-title=ERR_INVALID_EMAIL_ADDRESS]').length).toBe(0);

            // Make sure it is still not decorated after a full render.
            field.render();
            expect(field.$(invalidSelector).length).toBe(1);
            expect(field.$('.select2-choice-danger').length).toBe(0);
            expect(field.$('[data-title=ERR_INVALID_EMAIL_ADDRESS]').length).toBe(0);
        });

        it('should use the "Value erased" tooltip when the email address is erased', function() {
            var invalidSelector = '.select2-search-choice [data-invalid="true"]';

            field = SugarTest.createField({
                name: 'to_collection',
                type: 'email-recipients',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });

            // Erase the recipient's email address.
            to[0].set('email_address', '');
            to[0].set('email_addresses', {
                _acl: {},
                _erased_fields: [
                    'email_address',
                    'email_address_caps'
                ],
                id: to[0].get('email_address_id'),
                email_address: '',
                invalid_email: false,
                opt_out: true
            });

            field.model.set('to_collection', to);
            field.model.trigger('sync');

            expect(field.$(invalidSelector).length).toBe(1);
            expect(field.$('.select2-choice-optout').length).toBe(1);
            expect(field.$('[data-title="Value erased"]').length).toBe(1);

            // Make sure it is still decorated after a full render.
            field.render();
            expect(field.$(invalidSelector).length).toBe(1);
            expect(field.$('.select2-choice-optout').length).toBe(1);
            expect(field.$('[data-title="Value erased"]').length).toBe(1);
        });

        it('should decorate opted out recipients', function() {
            var optOutSelector = '.select2-search-choice [data-optout="true"]';

            field = SugarTest.createField({
                name: 'to_collection',
                type: 'email-recipients',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });

            field.model.set('to_collection', to);
            field.model.trigger('sync');

            expect(field.$(optOutSelector).length).toBe(1);
            expect(field.$('.select2-choice-optout').length).toBe(1);
            expect(field.$('[data-title=LBL_EMAIL_ADDRESS_OPTED_OUT]').length).toBe(1);

            // Make sure it is still decorated after a full render.
            field.render();
            expect(field.$(optOutSelector).length).toBe(1);
            expect(field.$('.select2-choice-optout').length).toBe(1);
            expect(field.$('[data-title=LBL_EMAIL_ADDRESS_OPTED_OUT]').length).toBe(1);
        });

        it('should not decorate opted out recipients', function() {
            var optOutSelector = '.select2-search-choice [data-optout="true"]';

            field = SugarTest.createField({
                name: 'to_collection',
                type: 'email-recipients',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.def.decorate_opt_out = false;

            field.model.set('to_collection', to);
            field.model.trigger('sync');

            expect(field.$(optOutSelector).length).toBe(1);
            expect(field.$('.select2-choice-optout').length).toBe(0);
            expect(field.$('[data-title=LBL_EMAIL_ADDRESS_OPTED_OUT]').length).toBe(0);

            // Make sure it is still not decorated after a full render.
            field.render();
            expect(field.$(optOutSelector).length).toBe(1);
            expect(field.$('.select2-choice-optout').length).toBe(0);
            expect(field.$('[data-title=LBL_EMAIL_ADDRESS_OPTED_OUT]').length).toBe(0);
        });

        it('should decorate recipients that are invalid and opted out as just invalid', function() {
            var parentId = _.uniqueId();
            var invalid = app.data.createBean('EmailParticipants', {
                _link: 'to',
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId,
                    name: 'Francis Humphrey'
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: 'Francis Humphrey',
                email_address_id: _.uniqueId(),
                email_address: 'foo',
                invalid_email: true,
                opt_out: true
            });
            var selector = '.select2-search-choice [data-invalid="true"][data-optout="true"]';

            field = SugarTest.createField({
                name: 'to_collection',
                type: 'email-recipients',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });

            field.model.set('to_collection', to);
            field.model.trigger('sync');
            expect(field.$(selector).length).toBe(0);

            field.model.get('to_collection').add(invalid);
            expect(field.$(selector).length).toBe(1);
            expect(field.$('.select2-choice-danger').length).toBe(1);
            expect(field.$('[data-title=ERR_INVALID_EMAIL_ADDRESS]').length).toBe(1);
            // The second recipient in "to" is opted out but not invalid. That
            // recipient will have the select2-choice-optout class and opt-out
            // title, but the invalid recipient will not. As evidence by the count
            // being 1 instead of 2.
            expect(field.$('.select2-choice-optout').length).toBe(1);
            expect(field.$('[data-title=LBL_EMAIL_ADDRESS_OPTED_OUT]').length).toBe(1);

            // Make sure it is still decorated after a full render.
            field.render();
            expect(field.$(selector).length).toBe(1);
            expect(field.$('.select2-choice-danger').length).toBe(1);
            expect(field.$('[data-title=ERR_INVALID_EMAIL_ADDRESS]').length).toBe(1);
            // The second recipient in "to" is opted out but not invalid. That
            // recipient will have the select2-choice-optout class and opt-out
            // title, but the invalid recipient will not. As evidence by the count
            // being 1 instead of 2.
            expect(field.$('.select2-choice-optout').length).toBe(1);
            expect(field.$('[data-title=LBL_EMAIL_ADDRESS_OPTED_OUT]').length).toBe(1);
        });
    });

    it('should open the address book and add the selected recipients', function() {
        // First change the links so the CC field can be initialized.
        var cc = _.map(to, function(model) {
            model.set('_link', 'cc');

            return model;
        });
        var parentId1 = _.uniqueId();
        var parentId2 = _.uniqueId();
        var parentId3 = _.uniqueId();
        var recipients = [
            app.data.createBean('EmailParticipants', {
                _link: 'to',
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId1,
                    name: 'Aaron Fitzgerald'
                },
                parent_type: 'Contacts',
                parent_id: parentId1,
                parent_name: 'Aaron Fitzgerald'
            }),
            app.data.createBean('EmailParticipants', {
                _link: 'to',
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId2,
                    name: 'Isaac Hopper'
                },
                parent_type: 'Contacts',
                parent_id: parentId2,
                parent_name: 'Isaac Hopper'
            }),
            app.data.createBean('EmailParticipants', {
                _link: 'to',
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId3,
                    name: 'Grace Beal'
                },
                parent_type: 'Contacts',
                parent_id: parentId3,
                parent_name: 'Grace Beal'
            })
        ];
        var spy = sandbox.spy();
        var collection;

        app.drawer = {
            open: function(def, onClose) {
                onClose(recipients);
            }
        };

        field = SugarTest.createField({
            name: 'cc_collection',
            type: 'email-recipients',
            viewName: 'edit',
            module: model.module,
            model: model,
            context: context,
            loadFromModule: true
        });
        field.view.on('address-book-state', spy);
        field.model.set('cc_collection', cc);
        field.model.trigger('sync');

        field.$('.btn').click();
        collection = field.model.get('cc_collection');

        expect(collection.length).toBe(5);
        expect(collection.at(2).get('_link')).toBe('cc');
        expect(collection.at(2).get('parent')).toEqual({
            _acl: {},
            type: recipients[0].get('parent_type'),
            id: recipients[0].get('parent_id'),
            name: recipients[0].get('parent_name')
        });
        expect(collection.at(2).get('parent_type')).toBe(recipients[0].get('parent_type'));
        expect(collection.at(2).get('parent_id')).toBe(recipients[0].get('parent_id'));
        expect(collection.at(2).get('parent_name')).toBe(recipients[0].get('parent_name'));
        expect(collection.at(2).get('email_address_id')).toBeUndefined();
        expect(collection.at(2).get('email_address')).toBeUndefined();
        expect(collection.at(3).get('_link')).toBe('cc');
        expect(collection.at(3).get('parent')).toEqual({
            _acl: {},
            type: recipients[1].get('parent_type'),
            id: recipients[1].get('parent_id'),
            name: recipients[1].get('parent_name')
        });
        expect(collection.at(3).get('parent_type')).toBe(recipients[1].get('parent_type'));
        expect(collection.at(3).get('parent_id')).toBe(recipients[1].get('parent_id'));
        expect(collection.at(3).get('parent_name')).toBe(recipients[1].get('parent_name'));
        expect(collection.at(3).get('email_address_id')).toBeUndefined();
        expect(collection.at(3).get('email_address')).toBeUndefined();
        expect(collection.at(4).get('_link')).toBe('cc');
        expect(collection.at(4).get('parent')).toEqual({
            _acl: {},
            type: recipients[2].get('parent_type'),
            id: recipients[2].get('parent_id'),
            name: recipients[2].get('parent_name')
        });
        expect(collection.at(4).get('parent_type')).toBe(recipients[2].get('parent_type'));
        expect(collection.at(4).get('parent_id')).toBe(recipients[2].get('parent_id'));
        expect(collection.at(4).get('parent_name')).toBe(recipients[2].get('parent_name'));
        expect(collection.at(4).get('email_address_id')).toBeUndefined();
        expect(collection.at(4).get('email_address')).toBeUndefined();
        expect(spy).toHaveBeenCalledTwice();
        expect(spy).toHaveBeenCalledWith('open');
        expect(spy).toHaveBeenCalledWith('closed');

        delete app.drawer;
    });

    describe('rendering in disabled mode', function() {
        it('should disable the select2 element', function() {
            field = SugarTest.createField({
                name: 'to_collection',
                type: 'email-recipients',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });

            field.render();
            expect(field.$(field.fieldTag).select2('container').hasClass('select2-container-disabled')).toBe(false);

            field.setDisabled();
            expect(field.$(field.fieldTag).select2('container').hasClass('select2-container-disabled')).toBe(true);
        });
    });

    describe('dragging and dropping recipients between fields', function() {
        var ccField;
        var cc;
        var dropHandler;
        var $helper;

        beforeEach(function() {
            var ccParentId = _.uniqueId();

            cc = [
                app.data.createBean('EmailParticipants', {
                    _link: 'cc',
                    id: _.uniqueId(),
                    parent: {
                        _acl: {},
                        type: 'Contacts',
                        id: ccParentId,
                        name: 'Tom Frank'
                    },
                    parent_type: 'Contacts',
                    parent_id: ccParentId,
                    parent_name: 'Tom Frank',
                    email_address_id: _.uniqueId(),
                    email_address: 'tfrank@example.com',
                    invalid_email: false,
                    opt_out: false
                })
            ];

            field = SugarTest.createField({
                name: 'to_collection',
                type: 'email-recipients',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            model.set('to_collection', to);

            ccField = SugarTest.createField({
                name: 'cc_collection',
                type: 'email-recipients',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            model.set('cc_collection', cc);

            model.trigger('sync');

            dropHandler = ccField.$(ccField.fieldTag).select2('container').droppable('option', 'drop');
            $helper = $('<div data-source-field="' + field.name + '"></div>');
        });

        afterEach(function() {
            ccField.dispose();
        });

        it('should move a recipient from the To field to the CC field', function() {
            var json;

            // Simulate dragging the second recipient in the To field.
            $helper.append('<span data-id="' + to[1].cid + '"></span>');

            // Simulate dropping the recipient in the CC field.
            dropHandler($.Event(), {helper: $helper.get(0)});

            expect(field.model.get('to_collection').length).toBe(1);
            expect(field.model.get('cc_collection').length).toBe(2);

            json = model.toJSON();
            expect(json.to.create.length).toBe(0);
            expect(json.to.add.length).toBe(0);
            expect(json.to.delete).toEqual([to[1].get('id')]);
            expect(json.cc.create).toEqual([{
                _link: 'cc',
                email_address_id: to[1].get('email_address_id'),
                email_address: to[1].get('email_address'),
                email_addresses: {
                    _acl: {},
                    _erased_fields: [],
                    id: to[1].get('email_address_id'),
                    email_address: to[1].get('email_address'),
                    invalid_email: to[1].get('invalid_email'),
                    opt_out: to[1].get('opt_out')
                },
                parent_type: to[1].get('parent_type'),
                parent_id: to[1].get('parent_id'),
                parent_name: to[1].get('parent_name'),
                parent: {
                    _acl: {},
                    _erased_fields: [],
                    id: to[1].get('parent_id'),
                    name: to[1].get('parent_name'),
                    type: to[1].get('parent_type')
                },
                invalid_email: to[1].get('invalid_email'),
                opt_out: to[1].get('opt_out')
            }]);
            expect(json.cc.add.length).toBe(0);
            expect(json.cc.delete.length).toBe(0);
        });

        it('should move a yet-to-be-saved recipient from the To field to the CC field', function() {
            var json;
            // Add an unsaved recipient to the To field.
            var newParentId = _.uniqueId();
            var newRecipient = app.data.createBean('EmailParticipants', {
                _link: 'to',
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: newParentId,
                    name: 'Charles Brohm'
                },
                parent_type: 'Contacts',
                parent_id: newParentId,
                parent_name: 'Charles Brohm',
                email_address_id: _.uniqueId(),
                email_address: 'cbrohm@example.com',
                invalid_email: false,
                opt_out: false
            });
            field.model.get('to_collection').add(newRecipient);

            // Simulate dragging the second recipient in the To field.
            $helper.append('<span data-id="' + newRecipient.cid + '"></span>');

            // Simulate dropping the recipient in the CC field.
            dropHandler($.Event(), {helper: $helper.get(0)});

            expect(field.model.get('to_collection').length).toBe(2);
            expect(field.model.get('cc_collection').length).toBe(2);

            json = model.toJSON();
            expect(json.to).toBeUndefined();
            expect(json.cc.create).toEqual([{
                _link: 'cc',
                deleted: 0,
                email_address_id: newRecipient.get('email_address_id'),
                email_address: newRecipient.get('email_address'),
                parent_type: newRecipient.get('parent_type'),
                parent_id: newRecipient.get('parent_id'),
                parent_name: newRecipient.get('parent_name'),
                parent: {
                    _acl: {},
                    id: newRecipient.get('parent_id'),
                    name: newRecipient.get('parent_name'),
                    type: newRecipient.get('parent_type')
                },
                invalid_email: newRecipient.get('invalid_email'),
                opt_out: newRecipient.get('opt_out')
            }]);
            expect(json.cc.add.length).toBe(0);
            expect(json.cc.delete.length).toBe(0);
        });
    });
});
