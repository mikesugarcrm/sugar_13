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

const Bean = require('../../../src/data/bean');
const BeanCollection = require('../../../src/data/bean-collection');

describe('Data/MixedBeanCollection', function() {
    var metadata;
    var app;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();
        metadata = SugarTest.loadFixture("metadata");
        SugarTest.seedMetadata();
    });

    afterEach(function() {
        sandbox.restore();
        app.metadata.reset();
    });

    it("should be able to fetch records that belong to different modules", function() {
        app.config.maxQueryResult = 2;
        app.data.declareModels(metadata.modules);
        var records = app.data.createMixedBeanCollection();

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/globalsearch\?max_num=2&module_list=Accounts%2CContacts.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(fixtures.api["rest/v10/search"].GET.response)]);

        records.fetch({
            module_list: ["Accounts","Contacts"],
            apiOptions: {
                useNewApi: true
            }

        });
        SugarTest.server.respond();

        expect(records.module_list).toEqual(["Accounts","Contacts"]);
        expect(records.length).toEqual(2);

        records.each(function(record) {
            expect(record instanceof Bean).toBeTruthy();
        });

        expect(records.models[0].module).toEqual("Contacts");
        expect(records.models[1].module).toEqual("Accounts");

    });

    it("should be able to group models by module", function() {

        app.data.declareModels(metadata.modules);
        var records = app.data.createMixedBeanCollection();

        records.add(app.data.createBean("Accounts", { name: "Apple" }));
        records.add(app.data.createBean("Cases", { subject: "A" }));
        records.add(app.data.createBean("Cases", { subject: "B" }));
        records.add(app.data.createBean("Contacts", { name: "John Smith" }));
        records.add(app.data.createBean("Accounts", { name: "Microsoft" }));
        records.add(app.data.createBean("Cases", { subject: "C" }));

        var groups = records.groupByModule();

        expect(groups["Accounts"]).toBeDefined();
        expect(groups["Accounts"].length).toEqual(2);

        expect(groups["Contacts"]).toBeDefined();
        expect(groups["Contacts"].length).toEqual(1);

        expect(groups["Cases"]).toBeDefined();
        expect(groups["Cases"].length).toEqual(3);

    });

    describe('keeping the collection and its related link collections in sync', function() {
        let leads;
        let contacts;
        let collection;
        let bean;
        beforeEach(function() {
            app.data.declareModels(metadata.modules);
            bean = app.data.createBean('Meetings', {id: '12345'});
            collection = bean.get('invitees');
            contacts = bean.getRelatedCollection('contacts');
            leads = bean.getRelatedCollection('leads');
        });

        it('should have a reference to its links collections if links are passed when creating the collection', function() {
            expect(collection._linkedCollections['Contacts'] instanceof BeanCollection);
            expect(collection._linkedCollections['Leads'] instanceof BeanCollection);
        });

        describe('Listening to its linked collections changes', function() {
            it('should update its models when `add` or `remove` is triggered in one of its linked collection', function() {
                sandbox.spy(collection, 'trigger');

                expect(collection.length === 0).toBe(true);

                leads.add({name: 'blah'});

                expect(collection.length === 1).toBe(true);

                expect(collection.trigger).toHaveBeenCalledWith('add');
                expect(collection.trigger.withArgs('update').calledOnce).toBe(true);

                leads.remove(leads.at(0));

                expect(collection.length === 0).toBe(true);

                expect(collection.trigger).toHaveBeenCalledWith('remove');
                expect(collection.trigger.withArgs('update').calledTwice).toBe(true);
            });

            it('should update its models when `reset` is triggered on one of its linked collection', function() {
                leads.add({id:'1234', name: 'foo', _link: 'leads'});
                leads.add({id:'2345', name: 'bar', _link: 'leads'});
                contacts.add({id:'3456', name: 'bar', _link: 'contacts'});

                expect(collection.length === 3).toBe(true);
                leads.reset();

                expect(collection.length === 1).toBe(true)
                expect(collection.get('1234')).not.toBeDefined();
                expect(collection.get('3456')).toBeDefined();

                sandbox.spy(collection, 'trigger');
                contacts.reset([{id: '4567', _link: 'contacts'}, {id: '5678', _link: 'leads'}]);

                expect(collection.length === 2).toBe(true);
                expect(collection.get('3456')).not.toBeDefined();
                expect(collection.get('4567')).toBeDefined();

                // Making sure the proper events were triggered.
                expect(collection.trigger).toHaveBeenCalledWith('remove');
                expect(collection.trigger).toHaveBeenCalledWith('add');
                expect(collection.trigger).not.toHaveBeenCalledWith('reset');
            });

            it('should remove a model from the delta when it gets created and then removed from the collection', function() {
                let newLead = app.data.createBean('Leads', {name: 'foo', _link: 'leads'});

                // Adding to a linked collection
                leads.add(newLead);

                let expectedDelta = {
                    leads: {
                        create: [
                            {name: 'foo', _link: 'leads'},
                        ],
                        add: [],
                        delete: [],
                    },
                };

                expect(collection.getDelta()).toEqual(expectedDelta);

                leads.remove(newLead);

                expect(leads.length).toEqual(0);
                expect(collection.getDelta()).toEqual({});

                // Adding to the mixed bean collection
                collection.add({name: 'foo', _link: 'leads'});

                expect(collection.getDelta()).toEqual(expectedDelta);

                collection.remove(collection.at(0));
                expect(collection.getDelta()).toEqual({});

                let contact1 = app.data.createBean('Contacts', {name: 'bar', _link: 'contacts', id: '1234'});
                let contact2 = app.data.createBean('Contacts', {name: 'baz', _link: 'contacts', id: '2345'});

                // Making sure it also works with several records.
                collection.add([newLead, contact1, contact2]);
                collection.remove(contact2);

                expectedDelta = {
                    leads: {
                        create: [
                            {name: 'foo', _link: 'leads'},
                        ],
                        add: [],
                        delete: [],
                    },
                    contacts: {
                        create: [],
                        add: [{id: '1234'}],
                        delete: []
                    }
                };

                expect(collection.getDelta()).toEqual(expectedDelta);

            });

            it('should not trigger any event if an already existing model is added to a linked collection', function() {
                leads.add({id:'1234', name: 'foo', _link: 'leads'});
                sandbox.spy(leads, 'trigger');
                sandbox.spy(collection, 'trigger');
                expect(collection.length).toEqual(1);

                let account = collection.at(0);
                leads.add(account);

                expect(collection.length).toEqual(1);
                expect(leads.trigger).not.toHaveBeenCalledWith('add');
                expect(collection.trigger).not.toHaveBeenCalledWith('add');
            });
        });

        describe('adding a model', function() {
            it('should add the model to the matching link collection', function() {
                collection.add({name: 'blah', _link: 'leads'});
                expect(leads.length).toEqual(1);
            });

            it('should not do anything if the model already exists in the collection', function() {
                collection.add({name: 'blah', _link: 'leads'});
                sandbox.spy(collection, 'trigger');
                expect(leads.length).toEqual(1);

                let account = collection.at(0);
                collection.add(account);

                expect(leads.length).toEqual(1);
                expect(collection.trigger).not.toHaveBeenCalledWith('add');
            });
        });

        describe('removing a model', function() {
            it('should remove the model from the corresponding link collection', function() {
                collection.add({name: 'blah', _link: 'contacts', _module: 'Contacts'});
                collection.add({name: 'blou', _link: 'contacts', _module: 'Contacts'});
                collection.add({name: 'bloff', _link: 'leads', _module: 'Leads'});

                expect(collection.length).toEqual(3);
                expect(contacts.length).toEqual(2);
                expect(leads.length).toEqual(1);

                collection.remove(collection.at(0));

                expect(collection.length).toEqual(2);
                expect(contacts.length).toEqual(1);

                collection.remove(collection.models);

                expect(collection.length).toEqual(0);
                expect(contacts.length).toEqual(0);
                expect(leads.length).toEqual(0);
            });

            it('should return the same as `Backbone.Collection#remove` does when calling remove with a null or empty argument', function() {
                collection.add({name: 'blah', _link: 'contacts', _module: 'Contacts'});

                let res = collection.remove();

                expect(collection.length).toEqual(1);
                expect(res).toBeUndefined();

                res = collection.remove(null);

                expect(collection.length).toEqual(1);
                expect(res).toBeUndefined();

                res = collection.remove([]);

                expect(collection.length).toEqual(1);
                expect(res).toEqual([]);

                res = collection.remove({});

                expect(collection.length).toEqual(1);
                expect(res).toBeUndefined();
            });
        });

        describe('resetting the collection', function() {
            it('should reset the corresponding link collections the same way', function() {
                collection.add({name: 'blah', _link: 'contacts', _module: 'Contacts'});
                collection.add({name: 'blou', _link: 'contacts', _module: 'Contacts'});
                sandbox.spy(contacts, 'trigger');
                sandbox.spy(collection, 'trigger');

                expect(contacts.length).toEqual(2);

                collection.reset();

                expect(contacts.length).toEqual(0);
                expect(contacts.trigger).toHaveBeenCalledWith('reset');
                expect(contacts.trigger).toHaveBeenCalledOnce();
                expect(collection.trigger).toHaveBeenCalledWith('reset');
                expect(collection.trigger).toHaveBeenCalledOnce();

                sandbox.spy(leads, 'trigger');
                contacts.trigger.resetHistory();
                collection.trigger.resetHistory();

                collection.reset([{name: 'blah', _link: 'contacts', _module: 'Contacts'}, {name: 'blou', _link: 'contacts', _module: 'Contacts'}, {name: 'bloff', _link: 'leads', _module: 'Leads'}]);

                expect(contacts.length).toEqual(2);
                expect(leads.length).toEqual(1);

                expect(contacts.trigger).toHaveBeenCalledWith('reset');
                expect(contacts.trigger).toHaveBeenCalledOnce();
                expect(leads.trigger).toHaveBeenCalledWith('reset');
                expect(leads.trigger).toHaveBeenCalledOnce();
                expect(collection.trigger).toHaveBeenCalledWith('reset');
                expect(collection.trigger).toHaveBeenCalledOnce();
                expect(collection.length === 3).toBe(true);
            });
        });

        describe('getDelta', function() {
            it('should return a hash containing the changes made on the linked collections', function() {
                collection.add({name: 'blah', _link: 'contacts', _module: 'Contacts'});
                collection.add({name: 'blou', _link: 'contacts', _module: 'Contacts'});
                collection.add({name: 'blou', _link: 'leads', _module: 'Leads'});
                leads.add({name: 'foo', _link: 'leads'});
                contacts.add({name: 'bar', _link: 'contacts'});

                var delta = collection.getDelta();
                var expectedDelta = {
                    leads: {
                        create: [
                            {name: 'blou', _link: 'leads', _module: 'Leads'},
                            {name: 'foo', _link: 'leads'}
                        ],
                        add: [],
                        delete: [],
                    },
                    contacts: {
                        create: [
                            // '`field_0: 100` is a default field from the Contacts metadata fixture.
                            {name: 'blah', _link: 'contacts', _module: 'Contacts', field_0: 100},
                            {name: 'blou', _link: 'contacts', _module: 'Contacts', field_0: 100},
                            {name: 'bar', _link: 'contacts', field_0: 100}
                        ],
                        add: [],
                        delete: [],
                    }
                };

                expect(delta).toEqual(expectedDelta);

                collection.reset();

                expect(collection.getDelta()).toEqual({});
            });
        });

        describe('resetDelta', function () {
            it('should resets the delta of all its links', function () {
                collection.add({ name: 'blah', _link: 'contacts', _module: 'Contacts' });
                collection.add({ name: 'blou', _link: 'contacts', _module: 'Contacts' });
                collection.add({ name: 'blou', _link: 'leads', _module: 'Leads' });

                expect(collection.hasDelta()).toBe(true);
                expect(contacts.hasDelta()).toBe(true);
                expect(leads.hasDelta()).toBe(true);

                collection.resetDelta();

                expect(collection.hasDelta()).toBe(false);
                expect(contacts.hasDelta()).toBe(false);
                expect(leads.hasDelta()).toBe(false);
            });
        });

        describe('Paginate the collection', function() {
            it('should pass the correct options to get the next records', function() {
                sandbox.stub(collection, 'fetch');
                // Let's assume the collection offset is the following.
                let offset = {contacts: 2, leads: 2};
                collection.offset = offset;

                collection.paginate({option1: 'example_option'});

                expect(collection.fetch).toHaveBeenCalledOnce();

                expect(collection.fetch.getCall(0).args[0].offset).toEqual(offset);
                expect(collection.fetch.getCall(0).args[0].beanId).toEqual(bean.id);
                expect(collection.fetch.getCall(0).args[0].module).toEqual(bean.module);
                expect(collection.fetch.getCall(0).args[0].collectionField).toEqual('invitees');
                expect(collection.fetch.getCall(0).args[0].option1).toEqual('example_option');
            });

            it('should keep the deltas without adding the fetched records to the `_add` array', function() {
                collection.add({name: 'blah', _link: 'contacts', _module: 'Contacts', id: '12345'});
                collection.add({name: 'blou', _link: 'contacts', _module: 'Contacts'});
                collection.add({name: 'blarf', _link: 'leads', _module: 'Leads'});

                let expectedDelta = {
                    leads: {
                        create: [
                            {name: 'blarf', _link: 'leads', _module: 'Leads'},
                        ],
                        add: [],
                        delete: [],
                    },
                    contacts: {
                        create: [
                            // '`field_0: 100` is a default field from the Contacts metadata fixture.
                            {name: 'blou', _link: 'contacts', _module: 'Contacts', field_0: 100},
                        ],
                        add: [{id: '12345'}],
                        delete: [],
                    }
                };

                // Based on the records added above in the collection, the
                // offset would be the following.
                let offset = {contacts: 2, leads: 1};
                collection.offset = offset;
                let response = SugarTest.loadFixture('2_contacts_collection_API');
                SugarTest.seedFakeServer();
                SugarTest.server.respondWith('GET', /.*\/rest\/v10\/Meetings\/12345\/collection\/invitees[?].*/,
                    [200, {'Content-Type': 'application/json'},
                    JSON.stringify(response)]);

                collection.paginate({add: true});
                SugarTest.server.respond();

                expect(collection.length).toEqual(5);
                expect(contacts.length).toEqual(4);
                expect(collection.getDelta()).toEqual(expectedDelta);
            });
        });

        describe('Reset the pagination', function() {
            it('should reset the pagination properties', function() {
                collection.offset = {contacts: 2, leads: 2};
                collection.next_offset = {contacts: 2, leads: 2};

                collection.resetPagination();

                expect(collection.offset).toEqual({});
                expect(collection.next_offset).toEqual({});
            });
        });
    });
});
