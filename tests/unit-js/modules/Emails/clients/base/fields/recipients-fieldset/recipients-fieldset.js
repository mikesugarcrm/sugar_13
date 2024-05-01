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
describe('Emails.RecipientsFieldsetField', function() {
    var app;
    var field;
    var children;
    var context;
    var model;
    var sandbox;
    var to;
    var cc;
    var bcc;

    function eraseName(participant) {
        var parent = participant.get('parent');

        participant.set('parent_name', '');
        parent.name = '';
        parent._erased_fields = [
            'first_name',
            'last_name'
        ];
    }

    function eraseEmailAddress(participant) {
        var link = participant.get('email_addresses');

        participant.set('email_address', '');
        link.email_address = '';
        link._erased_fields = [
            'email_address',
            'email_address_caps'
        ];
    }

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');
        var parentId1 = _.uniqueId();
        var emailAddressId1 = _.uniqueId();
        var parentId2 = _.uniqueId();
        var emailAddressId2 = _.uniqueId();
        var parentId3 = _.uniqueId();
        var emailAddressId3 = _.uniqueId();
        var parentId4 = _.uniqueId();
        var emailAddressId4 = _.uniqueId();

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadHandlebarsTemplate('recipients-fieldset', 'field', 'base', 'edit', 'Emails');
        SugarTest.loadHandlebarsTemplate('recipients-fieldset', 'field', 'base', 'detail', 'Emails');
        SugarTest.loadHandlebarsTemplate('recipients-fieldset', 'field', 'base', 'recipient-options', 'Emails');
        SugarTest.loadPlugin('EmailParticipants');
        SugarTest.loadComponent('base', 'field', 'email-recipients', 'Emails');
        SugarTest.loadHandlebarsTemplate('email-recipients', 'field', 'base', 'edit', 'Emails');
        SugarTest.loadHandlebarsTemplate('email-recipients', 'field', 'base', 'detail', 'Emails');
        SugarTest.loadComponent('base', 'field', 'outbound-email', 'Emails');
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'detail');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        SugarTest.declareData('base', 'EmailParticipants', true, false);
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');

        children = [
            SugarTest.createField({
                name: 'outbound_email_id',
                type: 'outbound-email',
                viewName: 'detail',
                fieldDef: {
                    name: 'outbound_email_id',
                    type: 'enum',
                    label: 'LBL_FROM',
                    options: {
                        '1': 'SugarCRM Sales <sales@sugarcrm.com>'
                    }
                },
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            }),
            SugarTest.createField({
                name: 'to_collection',
                type: 'email-recipients',
                viewName: 'detail',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            }),
            SugarTest.createField({
                name: 'cc_collection',
                type: 'email-recipients',
                viewName: 'detail',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            }),
            SugarTest.createField({
                name: 'bcc_collection',
                type: 'email-recipients',
                viewName: 'detail',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            })
        ];

        to = [
            app.data.createBean('EmailParticipants', {
                _link: 'to',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId1,
                    name: 'Herbert Yates'
                },
                parent_type: 'Contacts',
                parent_id: parentId1,
                parent_name: 'Herbert Yates',
                email_addresses: {
                    id: emailAddressId1,
                    email_address: 'hyates@example.com'
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
                    type: 'Contacts',
                    id: parentId2,
                    name: 'Walter Quigley'
                },
                parent_type: 'Contacts',
                parent_id: parentId2,
                parent_name: 'Walter Quigley',
                email_addresses: {
                    id: emailAddressId2,
                    email_address: 'wquigley@example.com'
                },
                email_address_id: emailAddressId2,
                email_address: 'wquigley@example.com',
                invalid_email: false,
                opt_out: false
            })
        ];

        cc = [
            app.data.createBean('EmailParticipants', {
                _link: 'cc',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId3,
                    name: 'Wyatt Archer'
                },
                parent_type: 'Contacts',
                parent_id: parentId3,
                parent_name: 'Wyatt Archer',
                email_addresses: {
                    id: emailAddressId3,
                    email_address: 'warcher@example.com'
                },
                email_address_id: emailAddressId3,
                email_address: 'warcher@example.com',
                invalid_email: false,
                opt_out: false
            })
        ];

        bcc = [
            app.data.createBean('EmailParticipants', {
                _link: 'bcc',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId4,
                    name: 'Earl Hatcher'
                },
                parent_type: 'Contacts',
                parent_id: parentId4,
                parent_name: 'Earl Hatcher',
                email_addresses: {
                    id: emailAddressId4,
                    email_address: 'ehatcher@example.com'
                },
                email_address_id: emailAddressId4,
                email_address: 'ehatcher@example.com',
                invalid_email: false,
                opt_out: false
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

    describe('format', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'recipients',
                type: 'recipients-fieldset',
                viewName: 'detail',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.fields = children;
            field.model.set('outbound_email_id', '1');
        });

        it('should not add the label for TO', function() {
            var expected = 'Herbert Yates, Walter Quigley; Cc: Wyatt Archer; Bcc: Earl Hatcher';
            var actual;

            field.model.set('to_collection', to);
            field.model.set('cc_collection', cc);
            field.model.set('bcc_collection', bcc);
            field.render();

            actual = field.getFormattedValue();
            expect(actual).toBe(expected);
        });

        it('should only show TO', function() {
            var expected = 'Herbert Yates, Walter Quigley';
            var actual;

            field.model.set('to_collection', to);
            field.render();

            actual = field.getFormattedValue();
            expect(actual).toBe(expected);
        });

        it('should only show CC', function() {
            var expected = 'Cc: Wyatt Archer';
            var actual;

            field.model.set('cc_collection', cc);
            field.render();

            actual = field.getFormattedValue();
            expect(actual).toBe(expected);
        });

        it('should only show BCC', function() {
            var expected = 'Bcc: Earl Hatcher';
            var actual;

            field.model.set('bcc_collection', bcc);
            field.render();

            actual = field.getFormattedValue();
            expect(actual).toBe(expected);
        });

        it('should use "Value erased" for erased names and email addresses', function() {
            var expected = 'Value erased, Walter Quigley; Cc: Value erased; Bcc: Earl Hatcher';
            var actual;

            // Erase this recipient's name.
            eraseName(to[0]);

            // Erase this recipient's name and email address.
            eraseName(cc[0]);
            eraseEmailAddress(cc[0]);

            // Erase this recipient's email address.
            eraseEmailAddress(bcc[0]);

            field.model.set('to_collection', to);
            field.model.set('cc_collection', cc);
            field.model.set('bcc_collection', bcc);
            field.render();

            actual = field.getFormattedValue();
            expect(actual).toBe(expected);
        });
    });

    describe('rendering in edit mode', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'recipients',
                type: 'recipients-fieldset',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.fields = children;
            field.model.set('outbound_email_id', '1');

            sandbox.stub(field.view, 'getField');
            field.view.getField.withArgs('outbound_email_id').returns(
                _.findWhere(field.fields, {name: 'outbound_email_id'})
            );
        });

        it('should add the toggle buttons', function() {
            var $cc;
            var $bcc;

            sandbox.spy(app.template, 'getField').withArgs(field.type, 'recipient-options', field.module);
            field.render();

            $cc = field.$('button[data-toggle-field=cc_collection]');
            $bcc = field.$('button[data-toggle-field=bcc_collection]');

            expect(app.template.getField.withArgs(field.type, 'recipient-options', field.module)).toHaveBeenCalled();
            expect($cc.length).toBe(1);
            expect($cc.hasClass('active')).toBe(false);
            expect($bcc.length).toBe(1);
            expect($bcc.hasClass('active')).toBe(false);
        });

        using('without recipients', ['cc_collection', 'bcc_collection'], function(fieldName) {
            it('should not show the field on render', function() {
                var recipientField = _.findWhere(field.fields, {name: fieldName});
                var $recipientField;
                var $toggleButton;
                var spy = sandbox.spy();

                field.view.getField.withArgs(fieldName).returns(recipientField);
                field.view.on('email-recipients:toggled', spy);
                field.render();

                $recipientField = recipientField.$el.closest('.fieldset-group');
                $toggleButton = field.$('button[data-toggle-field=' + fieldName + ']');

                expect($recipientField.length).toBe(1);
                expect($recipientField.hasClass('hide')).toBe(true);
                expect($toggleButton.length).toBe(1);
                expect($toggleButton.hasClass('active')).toBe(false);
                expect(spy).toHaveBeenCalledTwice();
            });
        });

        using('with recipients', ['cc_collection', 'bcc_collection'], function(fieldName) {
            it('should show the field on render', function() {
                var recipientField = _.findWhere(field.fields, {name: fieldName});
                var $recipientField;
                var $toggleButton;
                var spy = sandbox.spy();
                var value = fieldName === 'cc_collection' ? cc : bcc;

                field.model.set(fieldName, value);
                field.view.getField.withArgs(fieldName).returns(recipientField);
                field.view.on('email-recipients:toggled', spy);
                field.render();

                $recipientField = recipientField.$el.closest('.fieldset-group');
                $toggleButton = field.$('button[data-toggle-field=' + fieldName + ']');

                expect($recipientField.length).toBe(1);
                expect($recipientField.hasClass('hide')).toBe(false);
                expect($toggleButton.length).toBe(1);
                expect($toggleButton.hasClass('active')).toBe(true);
                expect(spy).toHaveBeenCalledTwice();
            });
        });

        using('toggle buttons', ['cc_collection', 'bcc_collection'], function(fieldName) {
            it('should toggle the field when clicking the button', function() {
                var recipientField = _.findWhere(field.fields, {name: fieldName});
                var $recipientField;
                var $toggleButton;
                var spy = sandbox.spy();

                field.view.getField.withArgs(fieldName).returns(recipientField);
                field.view.on('email-recipients:toggled', spy);
                field.render();

                // Account for the event to have been triggered twice during
                // render.
                expect(spy.callCount).toBe(2);

                // Click the button to show the field.
                $toggleButton = field.$('button[data-toggle-field=' + fieldName + ']');
                $toggleButton.click();

                $recipientField = recipientField.$el.closest('.fieldset-group');
                $toggleButton = field.$('button[data-toggle-field=' + fieldName + ']');

                expect($recipientField.length).toBe(1);
                expect($recipientField.hasClass('hide')).toBe(false);
                expect($toggleButton.length).toBe(1);
                expect($toggleButton.hasClass('active')).toBe(true);
                expect(spy.callCount).toBe(3);

                // Click the button to hide the field.
                $toggleButton = field.$('button[data-toggle-field=' + fieldName + ']');
                $toggleButton.click();

                $recipientField = recipientField.$el.closest('.fieldset-group');
                $toggleButton = field.$('button[data-toggle-field=' + fieldName + ']');

                expect($recipientField.length).toBe(1);
                expect($recipientField.hasClass('hide')).toBe(true);
                expect($toggleButton.length).toBe(1);
                expect($toggleButton.hasClass('active')).toBe(false);
                expect(spy.callCount).toBe(4);
            });
        });
    });

    describe('rendering in detail mode', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'recipients',
                type: 'recipients-fieldset',
                viewName: 'detail',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.fields = children;
            field.model.set('outbound_email_id', '1');
            field.model.set('to_collection', to);
            field.model.set('cc_collection', cc);
            field.model.set('bcc_collection', bcc);

            sandbox.stub(field.view, 'getField');
            field.view.getField.withArgs('outbound_email_id').returns(
                _.findWhere(field.fields, {name: 'outbound_email_id'})
            );
            field.view.getField.withArgs('to_collection').returns(_.findWhere(field.fields, {name: 'to_collection'}));
            field.view.getField.withArgs('cc_collection').returns(_.findWhere(field.fields, {name: 'cc_collection'}));
            field.view.getField.withArgs('bcc_collection').returns(_.findWhere(field.fields, {name: 'bcc_collection'}));
        });

        it('should render the string', function() {
            var $scroll;
            var $cc;
            var $bcc;
            var $fieldsetGroups;

            field.render();
            $scroll = field.$('.scroll');
            $cc = field.$('button[data-toggle-field=cc]');
            $bcc = field.$('button[data-toggle-field=bcc]');
            $fieldsetGroups = field.$('.fieldset-group');

            expect($scroll.text()).toBe('Herbert Yates, Walter Quigley; Cc: Wyatt Archer; Bcc: Earl Hatcher');
            expect($cc.length).toBe(0);
            expect($bcc.length).toBe(0);
            expect($fieldsetGroups.length).toBe(0);
        });
    });

    describe('blur when focus goes away', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'recipients',
                type: 'recipients-fieldset',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });

            sandbox.stub(field, 'setMode');
        });

        describe('the tinymce:focus event', function() {
            beforeEach(function() {
                sandbox.stub(field.$el, 'toggleClass');
            });

            it('should not change the mode when the address book is open', function() {
                field.action = 'edit';
                field._addressBookState = 'open';
                field.view.trigger('tinymce:focus');

                expect(field.$el.toggleClass).not.toHaveBeenCalled();
            });
        });
    });
});
