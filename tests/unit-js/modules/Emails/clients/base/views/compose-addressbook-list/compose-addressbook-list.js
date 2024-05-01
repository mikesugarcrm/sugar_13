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

describe('Emails.Views.ComposeAddressbookList', function() {
    var app;
    var context;
    var layout;
    var view;
    var sandbox;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');
        var viewMetadata = {
            template: 'flex-list',
            selection: {
                type: 'multi',
                actions: {},
                disable_select_all_alert: true
            },
            panels: [
                {
                    fields: [
                        {
                            name: 'name',
                            label: 'LBL_LIST_NAME',
                            enabled: true,
                            default: true
                        },
                        {
                            name: 'email',
                            label: 'LBL_LIST_EMAIL',
                            type: 'email',
                            sortable: true,
                            enabled: true,
                            default: true
                        },
                        {
                            name: '_module',
                            label: 'LBL_MODULE',
                            type: 'module',
                            sortable: false,
                            enabled: true,
                            default: true
                        }
                    ]
                }
            ]
        };

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadPlugin('MassCollection');
        SugarTest.loadHandlebarsTemplate('flex-list', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('flex-list', 'view', 'base', 'row-header');
        SugarTest.loadHandlebarsTemplate('flex-list', 'view', 'base', 'row');
        SugarTest.loadComponent('base', 'layout', 'list');
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'compose-addressbook-list', 'Emails');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);

        sandbox = sinon.createSandbox();

        layout = SugarTest.createLayout('base', 'Emails', 'list');
        view = SugarTest.createView('base', 'Emails', 'compose-addressbook-list', viewMetadata, context, true, layout);
    });

    afterEach(function() {
        sandbox.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    it('should add the selected recipient', function() {
        var collection;
        var recipient = app.data.createBean('Contacts', {
            id: _.uniqueId(),
            name: 'Aaron Fitzgerald',
            email: 'afitz@example.com'
        });

        view.collection.add(recipient);
        view.render();
        view.context.trigger('mass_collection:add', [recipient]);
        collection = view.model.get('to_collection');

        expect(collection.length).toBe(1);
        expect(collection.at(0).get('_link')).toBe('to');
        expect(collection.at(0).get('parent')).toEqual({
            _acl: {},
            _erased_fields: [],
            type: recipient.module,
            id: recipient.get('id'),
            name: recipient.get('name')
        });
        expect(collection.at(0).get('parent_type')).toBe(recipient.module);
        expect(collection.at(0).get('parent_id')).toBe(recipient.get('id'));
        expect(collection.at(0).get('parent_name')).toBe(recipient.get('name'));
        expect(collection.at(0).get('email_address_id')).toBeUndefined();
        expect(collection.at(0).get('email_address')).toBe(recipient.email);
    });

    it('should add the selected recipient with an erased name', function() {
        var collection;
        var recipient = app.data.createBean('Contacts', {
            id: _.uniqueId(),
            name: '',
            first_name: '',
            last_name: '',
            email: 'afitz@example.com',
            _erased_fields: [
                'first_name',
                'last_name'
            ]
        });

        view.collection.add(recipient);
        view.render();
        view.context.trigger('mass_collection:add', [recipient]);
        collection = view.model.get('to_collection');

        expect(collection.length).toBe(1);
        expect(collection.at(0).get('_link')).toBe('to');
        expect(collection.at(0).get('parent')).toEqual({
            _acl: {},
            _erased_fields: [
                'first_name',
                'last_name'
            ],
            type: recipient.module,
            id: recipient.get('id'),
            name: ''
        });
        expect(collection.at(0).get('parent_type')).toBe(recipient.module);
        expect(collection.at(0).get('parent_id')).toBe(recipient.get('id'));
        expect(collection.at(0).get('parent_name')).toBe('');
        expect(collection.at(0).get('email_address_id')).toBeUndefined();
        expect(collection.at(0).get('email_address')).toBeUndefined();
    });

    it('should remove the deselected recipient', function() {
        var recipient = app.data.createBean('Contacts', {
            id: _.uniqueId(),
            name: 'Aaron Fitzgerald',
            email: 'afitz@example.com'
        });

        view.collection.add(recipient);
        view.render();
        view.context.trigger('mass_collection:add', [recipient]);
        expect(view.model.get('to_collection').length).toBe(1);

        view.context.trigger('mass_collection:remove', [recipient]);
        expect(view.model.get('to_collection').length).toBe(0);
    });

    it('should add all of the recipients when selecting all', function() {
        var collection;
        var recipients = [
            app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Aaron Fitzgerald',
                email: 'afitz@example.com'
            }),
            app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Isaac Hopper',
                email: 'ihopper@example.com'
            }),
            app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Grace Beal',
                email: 'gbeal@example.com'
            })
        ];

        view.collection.add(recipients);
        view.render();
        view.context.trigger('mass_collection:add:all');
        collection = view.model.get('to_collection');

        expect(collection.length).toBe(3);
        expect(collection.at(0).get('_link')).toBe('to');
        expect(collection.at(0).get('parent')).toEqual({
            _acl: {},
            _erased_fields: [],
            type: recipients[0].module,
            id: recipients[0].get('id'),
            name: recipients[0].get('name')
        });
        expect(collection.at(0).get('parent_type')).toBe(recipients[0].module);
        expect(collection.at(0).get('parent_id')).toBe(recipients[0].get('id'));
        expect(collection.at(0).get('parent_name')).toBe(recipients[0].get('name'));
        expect(collection.at(0).get('email_address_id')).toBeUndefined();
        expect(collection.at(0).get('email_address')).toBeUndefined();
        expect(collection.at(1).get('_link')).toBe('to');
        expect(collection.at(1).get('parent')).toEqual({
            _acl: {},
            _erased_fields: [],
            type: recipients[1].module,
            id: recipients[1].get('id'),
            name: recipients[1].get('name')
        });
        expect(collection.at(1).get('parent_type')).toBe(recipients[1].module);
        expect(collection.at(1).get('parent_id')).toBe(recipients[1].get('id'));
        expect(collection.at(1).get('parent_name')).toBe(recipients[1].get('name'));
        expect(collection.at(1).get('email_address_id')).toBeUndefined();
        expect(collection.at(1).get('email_address')).toBeUndefined();
        expect(collection.at(2).get('_link')).toBe('to');
        expect(collection.at(2).get('parent')).toEqual({
            _acl: {},
            _erased_fields: [],
            type: recipients[2].module,
            id: recipients[2].get('id'),
            name: recipients[2].get('name')
        });
        expect(collection.at(2).get('parent_type')).toBe(recipients[2].module);
        expect(collection.at(2).get('parent_id')).toBe(recipients[2].get('id'));
        expect(collection.at(2).get('parent_name')).toBe(recipients[2].get('name'));
        expect(collection.at(2).get('email_address_id')).toBeUndefined();
        expect(collection.at(2).get('email_address')).toBeUndefined();
    });

    it('should remove only the recipients in the view when deselecting all', function() {
        var collection;
        var recipients = [
            app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Aaron Fitzgerald',
                email: 'afitz@example.com'
            }),
            app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Isaac Hopper',
                email: 'ihopper@example.com'
            }),
            app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Grace Beal',
                email: 'gbeal@example.com'
            })
        ];
        var recipient = app.data.createBean('Leads', {
            id: _.uniqueId(),
            name: 'Herman Davis',
            email: 'hdavis@example.com'
        });

        view.render();
        collection = view.model.get('to_collection');

        // Add a recipient that won't be in the view.
        view.context.trigger('mass_collection:add', [recipient]);

        // Add all of the recipients in the view.
        view.collection.add(recipients);
        view.context.trigger('mass_collection:add:all');
        expect(collection.length).toBe(4);

        view.context.trigger('mass_collection:remove:all');
        expect(collection.length).toBe(1);

        expect(collection.at(0).get('_link')).toBe('to');
        expect(collection.at(0).get('parent')).toEqual({
            _acl: {},
            _erased_fields: [],
            type: recipient.module,
            id: recipient.get('id'),
            name: recipient.get('name')
        });
        expect(collection.at(0).get('parent_type')).toBe(recipient.module);
        expect(collection.at(0).get('parent_id')).toBe(recipient.get('id'));
        expect(collection.at(0).get('parent_name')).toBe(recipient.get('name'));
        expect(collection.at(0).get('email_address_id')).toBeUndefined();
        expect(collection.at(0).get('email_address')).toBeUndefined();
    });

    it('should remove only the recipients in the view when clearing the mass collection', function() {
        var collection;
        var recipients = [
            app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Aaron Fitzgerald',
                email: 'afitz@example.com'
            }),
            app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Isaac Hopper',
                email: 'ihopper@example.com'
            }),
            app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Grace Beal',
                email: 'gbeal@example.com'
            })
        ];
        var recipient = app.data.createBean('Contacts', {
            id: _.uniqueId(),
            name: 'Herman Davis',
            email: 'afitz@example.com'
        });

        view.render();
        collection = view.model.get('to_collection');

        // Add a recipient that won't be in the view.
        view.context.trigger('mass_collection:add', [recipient]);

        // Add all of the recipients in the view.
        view.collection.add(recipients);
        view.context.trigger('mass_collection:add:all');
        expect(collection.length).toBe(4);

        view.context.trigger('mass_collection:clear');
        expect(collection.length).toBe(1);

        expect(collection.at(0).get('_link')).toBe('to');
        expect(collection.at(0).get('parent')).toEqual({
            _acl: {},
            _erased_fields: [],
            type: recipient.module,
            id: recipient.get('id'),
            name: recipient.get('name')
        });
        expect(collection.at(0).get('parent_type')).toBe(recipient.module);
        expect(collection.at(0).get('parent_id')).toBe(recipient.get('id'));
        expect(collection.at(0).get('parent_name')).toBe(recipient.get('name'));
        expect(collection.at(0).get('email_address_id')).toBeUndefined();
        expect(collection.at(0).get('email_address')).toBeUndefined();
    });

    it('should add all of the already selected recipients when rendering', function() {
        var collection;
        var parentId1 = _.uniqueId();
        var parentId2 = _.uniqueId();
        var parentId3 = _.uniqueId();
        var preselectedRecipients = [
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
            })
        ];
        var recipientsInList = [
            app.data.createBean('Contacts', {
                id: parentId1,
                name: 'Aaron Fitzgerald',
                email: 'afitz@example.com'
            }),
            app.data.createBean('Contacts', {
                id: parentId2,
                name: 'Isaac Hopper',
                email: 'ihopper@example.com'
            }),
            app.data.createBean('Contacts', {
                id: parentId3,
                name: 'Grace Beal',
                email: 'gbeal@example.com'
            })
        ];

        view.model.get('to_collection').add(preselectedRecipients);
        view.collection.add(recipientsInList);
        view.render();

        collection = view.context.get('mass_collection');
        expect(collection.length).toBe(2);
        expect(collection.at(0).module).toBe(recipientsInList[0].module);
        expect(collection.at(0).get('id')).toBe(recipientsInList[0].get('id'));
        expect(collection.at(0).get('name')).toBe(recipientsInList[0].get('name'));
        expect(collection.at(0).get('email')).toBe(recipientsInList[0].get('email'));
        expect(collection.at(1).module).toBe(recipientsInList[1].module);
        expect(collection.at(1).get('id')).toBe(recipientsInList[1].get('id'));
        expect(collection.at(1).get('name')).toBe(recipientsInList[1].get('name'));
        expect(collection.at(1).get('email')).toBe(recipientsInList[1].get('email'));
    });
});
