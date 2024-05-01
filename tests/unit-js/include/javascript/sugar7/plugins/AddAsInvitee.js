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
describe('Plugins.AddAsInvitee', function() {
    var moduleName = 'Meetings',
        view,
        pluginsBefore,
        app,
        sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();

        view = SugarTest.createView('base', moduleName, 'record');
        pluginsBefore = view.plugins;
        view.plugins = ['AddAsInvitee'];
        SugarTest.loadPlugin('AddAsInvitee');
        SugarTest.app.plugins.attach(view, 'view');
        view.trigger('init');
        sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        sandbox.restore();
        view.plugins = pluginsBefore;
        view.dispose();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        app.cache.cutAll();
        view = null;
    });

    it('should check if parent is a possible invitee on render in create mode', function() {
        var isPossibleInviteeStub = sandbox.stub(view, 'isPossibleInvitee');
        view.model.set('parent_name', 'Foo Man', {silent: true});
        expect(isPossibleInviteeStub.callCount).toEqual(0);
        view.render();
        expect(isPossibleInviteeStub.callCount).toEqual(1);
    });

    it('should not check if parent is a possible invitee on render when not in create mode', function() {
        var isPossibleInviteeStub = sandbox.stub(view, 'isPossibleInvitee');
        view.model.set('id', '123');
        view.model.set('parent_name', 'Foo Man', {silent: true});
        expect(isPossibleInviteeStub.callCount).toEqual(0);
        view.render();
        expect(isPossibleInviteeStub.callCount).toEqual(0);
    });

    it('should check if parent is a possible invitee on change', function() {
        var isPossibleInviteeStub = sandbox.stub(view, 'isPossibleInvitee');
        view.render();
        view.model.set('parent_name', 'Foo Man');
        expect(isPossibleInviteeStub.callCount).toEqual(1);
    });

    using('different parent/invitee values', [
        [
            'is possible new invitee if parent has id and is a lead',
            'Leads',
            '123',
            [],
            true
        ],
        [
            'is possible new invitee if parent has id and is a contact',
            'Contacts',
            '456',
            [],
            true
        ],
        [
            'is not a possible new invitee if not a lead, contact, or user',
            'Foo',
            '789',
            [],
            false
        ],
        [
            'is not a possible new invitee if no parent_id',
            'Leads',
            '',
            [],
            false
        ],
        [
            'is not a possible new invitee if no invitee field to add to',
            'Leads',
            '123',
            undefined,
            false
        ],
        [
            'is possible new invitee even if invitee is already in the list',
            'Leads',
            '123',
            [{ id: '123', module: 'Leads' }],
            true
        ]
    ], function(expectation, parentType, parentId, invitees, isPossibleInvitee) {
        it(expectation, function() {
            var parent = {
                id: parentId,
                module: parentType
            };

            if (invitees) {
                view.model.set('invitees', new Backbone.Collection(invitees));
            }

            expect(view.isPossibleInvitee(parent)).toEqual(isPossibleInvitee);
        });
    });

    it('should add person as an invitee if person meets criteria', function() {
        var person = {
            id: '123',
            module: 'Contacts'
        };
        view.model.set('invitees', new Backbone.Collection());
        expect(view.model.get('invitees').get('123')).toBeUndefined();
        view.addAsInvitee(person);
        expect(view.model.get('invitees').get('123')).not.toBeUndefined();
    });

    it('should add person as a merged and default invitee if default flag is set', function() {
        var inviteeCollection,
            inviteeAddSpy,
            person = {
                id: '123',
                module: 'Contacts'
            };

        inviteeCollection = new Backbone.Collection();
        view.model.set('invitees', inviteeCollection);
        inviteeAddSpy = sandbox.spy(inviteeCollection, 'add');

        expect(view.model.get('invitees').get('123')).toBeUndefined();
        view.addAsInvitee(person, {default: true});
        expect(view.model.get('invitees').get('123')).not.toBeUndefined();
        expect(inviteeAddSpy.lastCall.args[1].merge).toEqual(true);
        expect(inviteeAddSpy.lastCall.args[1].default).toEqual(true);
    });

    describe("fetch the person's email field when adding", function() {
        beforeEach(function() {
            view.model.set('invitees', new Backbone.Collection());
        });

        it("should fetch the person's email field before adding when the person has an email field", function() {
            var person;

            person = app.data.createBean('Contacts', {id: '123'});
            person.fields = {
                id: {},
                name: {},
                email: {}
            };
            sandbox.stub(person, 'fetch').callsFake(function(options) {
                options.complete();
            });

            view.addAsInvitee(person);

            expect(view.model.get('invitees').get('123')).not.toBeUndefined();
            expect(person.fetch).toHaveBeenCalledOnce();
            expect(person.fetch.getCall(0).args[0].fields).toEqual(['email']);
        });

        it("should not fetch the person's email field before adding when the person has no fields", function() {
            var person;

            person = app.data.createBean('Contacts', {id: '123'});
            person.fields = {};
            sandbox.spy(person, 'fetch');

            view.addAsInvitee(person);

            expect(view.model.get('invitees').get('123')).not.toBeUndefined();
            expect(person.fetch).not.toHaveBeenCalledOnce();
        });

        it("should not fetch the person's email field before adding when the person has no email field", function() {
            var person;

            person = app.data.createBean('Contacts', {id: '123'});
            person.fields = {
                id: {},
                name: {}
            };
            sandbox.spy(person, 'fetch');

            view.addAsInvitee(person);

            expect(view.model.get('invitees').get('123')).not.toBeUndefined();
            expect(person.fetch).not.toHaveBeenCalledOnce();
        });
    });

    it('should invite the assigned user', function() {
        var user;

        user = new app.Bean({id: '123', _module: 'Users', name: 'Jack'});
        user.module = user.get('_module');
        view.render();
        sandbox.stub(app.data, 'createBean').withArgs('Users').returns(user);

        view.model.set('invitees', app.data.createBeanCollection());
        view.model.set('assigned_user_id', user.id);
        expect(view.model.get('invitees').get(user.id)).toBeUndefined();
        view.model.set('assigned_user_name', user.get('name'));
        expect(view.model.get('invitees').get(user.id)).not.toBeUndefined();
    });

    it('should invite the contact defined by contact_id field if populated on create', function() {
        var contact = new app.Bean({id: '123', _module: 'Contacts', name: 'Foo Contact'});
        contact.module = contact.get('_module');
        sandbox.stub(app.data, 'createBean').withArgs('Contacts').returns(contact);

        view.model.set('invitees', new Backbone.Collection(), {silent: true});
        view.model.set('contact_id', contact.id, {silent: true});
        view.model.set('contact_name', contact.get('name'), {silent: true});
        expect(view.model.get('invitees').get(contact.id)).toBeUndefined();
        view.render();
        expect(view.model.get('invitees').get(contact.id)).not.toBeUndefined();
    });

    it('should correctly check if invitee is going to be added automatically', function() {
        var id = '123',
            module = 'Contacts',
            invitee = new app.Bean({id: id}),
            model = new app.Bean();

        invitee.module = module;
        model.link = {
            bean: invitee
        };

        expect(view._isCreateAndLinkAction(invitee, model)).toBe(true);
    });

    it('should correctly check if invitee is not going to be added automatically', function() {
        var id = '123',
            module = 'Contacts',
            invitee = new app.Bean({id: id}),
            model = new app.Bean();

        invitee.module = module;

        expect(view._isCreateAndLinkAction(invitee, model)).toBe(false);
    });

    it('should set auto_invite_parent flag to false', function() {
        view.render();
        expect(view.model.get('auto_invite_parent')).toEqual(false);
    });
});
