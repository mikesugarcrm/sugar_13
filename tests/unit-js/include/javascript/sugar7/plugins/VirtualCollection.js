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
describe('Plugins.VirtualCollection', function() {
    var app;
    var attribute;
    var collection;
    var contacts;
    var newContacts;
    var context;
    var model;
    var module;
    var sandbox;

    module = 'Meetings';

    contacts = [
        {_module: 'Contacts', id: '1', name: 'Sam Stewart'},
        {_module: 'Contacts', id: '2', name: 'Ralph Davis'},
        {_module: 'Contacts', id: '3', name: 'Joe Reynolds'},
        {_module: 'Contacts', id: '4', name: 'Katie Ross'},
        {_module: 'Contacts', id: '5', name: 'Brad Harris'},
        {_module: 'Contacts', id: '6', name: 'Thomas Wallace'}
    ];

    newContacts = [
        {_module: 'Contacts', name: 'Nelly NewContact'},
        {_module: 'Contacts', name: 'Ned NewContact'}
    ];

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.declareData('base', module, true, false);
        SugarTest.loadPlugin('VirtualCollection');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        context = app.context.getContext({module: module});
        context.prepare(true);
        model = context.get('model');
        model.set('id', 1);
        model.fields = {
            invitees: {
                name: 'invitees',
                source: 'non-db',
                type: 'collection',
                vname: 'LBL_INVITEES',
                links: ['contacts', 'accounts'],
                order_by: 'name:asc',
                fields: [
                    {
                        name: 'name',
                        type: 'name',
                        label: 'LBL_SUBJECT'
                    },
                    'accept_status_meetings',
                    'picture'
                ]
            },
            contacts: {
                name: 'contacts',
                type: 'link',
                source: 'non-db'
            },
            accounts: {
                name: 'accounts',
                type: 'link',
                source: 'non-db'
            },
            name: {
                name: 'name',
                type: 'varchar'
            }
        };
        attribute = model.fields.invitees.name;

        sandbox = sinon.createSandbox();
        sandbox.stub(app.data, 'getRelatedModule');
        app.data.getRelatedModule.withArgs('Meetings', 'contacts').returns('Contacts');
        app.data.getRelatedModule.withArgs('Meetings', 'accounts').returns('Accounts');
        app.data.getRelatedModule.withArgs('Meetings', 'cases').returns('Cases');
    });

    afterEach(function() {
        sandbox.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('creating a collection attribute', function() {
        it('should initialize the collection model', function() {
            model.set(attribute, contacts);
            collection = model.get(attribute);

            expect(collection.parent).toBe(model);
            expect(_.size(collection.links)).toBe(_.size(model.fields[attribute].links));
        });

        it('should identify the initial models as having come from the server', function() {
            model.set(attribute, _.first(contacts, 2));
            collection = model.get(attribute);
            collection.add(_.rest(contacts, 2));

            expect(collection.length).toBe(6);
            expect(collection.links.contacts.length).toBe(4);
            expect(collection.links.contacts.synced.length).toBe(2);
        });
    });

    describe('when the parent model is synchronized', function() {
        beforeEach(function() {
            model.set(attribute, _.first(contacts, 4));
            collection = model.get(attribute);

            collection.add(_.rest(contacts, 4));
            collection.remove([2]);
        });

        it('should not report any new changes', function() {
            expect(collection.hasChanged()).toBe(true);

            model.trigger('sync');

            expect(collection.hasChanged()).toBe(false);
        });

        it('should reset the links to empty', function() {
            model.trigger('sync');

            _.each(collection.links, function(link) {
                expect(link.length).toBe(0);
            });
        });

        it('should add synchronized models to their respective synced list', function() {
            model.trigger('sync');

            expect(collection.links.contacts.synced.length).toBe(5);
        });
    });

    describe('change events on the parent model', function() {
        var fieldSpy, modelSpy;

        beforeEach(function() {
            model.set(attribute, _.first(contacts, 4));
            collection = model.get(attribute);

            fieldSpy = sandbox.spy();
            model.on('change:' + attribute, function() {
                fieldSpy();
            });

            modelSpy = sandbox.spy();
            model.on('change', function() {
                modelSpy();
            });
        });

        it('should trigger when data set is different from what is currently in the model', function() {
            model.set(attribute, _.first(contacts, 3));

            expect(fieldSpy.calledOnce).toBe(true);
            expect(modelSpy.calledOnce).toBe(true);
        });

        it('should not trigger when data set is the same as what is currently in the model', function() {
            model.set(attribute, _.first(contacts, 4));

            expect(fieldSpy).not.toHaveBeenCalled();
            expect(modelSpy).not.toHaveBeenCalled();
        });

        it('should trigger when both collection and non-collection fields update', function() {
            var data = {};
            data[attribute] = _.first(contacts, 3);
            data.name = 'foo';
            model.set(data);

            expect(fieldSpy.calledOnce).toBe(true);
            expect(modelSpy.calledOnce).toBe(true);
        });

        it('should still trigger when only non-collection fields update', function() {
            model.set({
                name: 'foo'
            });

            expect(fieldSpy).not.toHaveBeenCalled();
            expect(modelSpy.calledOnce).toBe(true);
        });

        it('should trigger when the virtual collection changes', function() {
            collection.add(_.rest(contacts, 4));

            expect(fieldSpy.calledOnce).toBe(true);
            expect(modelSpy.calledOnce).toBe(true);
        });

        it('should not trigger when the virtual collection changes with silent option set to true', function() {
            collection.add(_.rest(contacts, 4), {silent: true});

            expect(fieldSpy).not.toHaveBeenCalled();
            expect(modelSpy).not.toHaveBeenCalled();
        });
    });

    describe('adding models to the collection', function() {
        beforeEach(function() {
            model.set(attribute, _.first(contacts, 2));
            collection = model.get(attribute);
            sandbox.spy(collection, '_triggerChange');
        });

        it('should not do anything if there are no models to add', function() {
            collection.add([]);

            expect(collection._triggerChange).not.toHaveBeenCalled();
            expect(collection.length).toBe(2);
        });

        it('should add a model that does not already exist in the collection', function() {
            collection.add(_.last(contacts));

            expect(collection._triggerChange).toHaveBeenCalled();
            expect(collection.length).toBe(3);
            expect(collection.links.contacts.length).toBe(1);
            expect(collection.links.contacts.first().get('_action')).toEqual('add');
        });

        it('should add a model that was marked for removal', function() {
            collection.remove([2]);

            expect(collection.length).toBe(1);
            expect(collection.links.contacts.length).toBe(1);
            expect(collection.links.contacts.first().get('_action')).toEqual('delete');

            collection.add(contacts[1]);

            expect(collection.length).toBe(2);
            expect(collection.links.contacts.length).toBe(0);
        });

        it('should add models that have no id as "create"', function() {
            collection.add(newContacts);

            expect(collection.links.contacts.length).toBe(2);
            expect(collection.links.contacts.first().get('_action')).toEqual('create');
        });

        it('should not add a model that already exists in the collection', function() {
            collection.add(contacts[1]);

            expect(collection.length).toBe(2);
            expect(collection.links.contacts.length).toBe(0);
        });

        it('should merge a model that already exists in the collection', function() {
            var contact = {_module: 'Contacts', id: '1', name: 'Sammy Stewart'};

            collection.add([contact], {merge: true});

            expect(collection.get(contact.id).get('name')).toEqual(contact.name);
            expect(collection.links.contacts.length).toBe(1);
            expect(collection.links.contacts.first().get('_action')).toEqual('add');
        });

        it('should not merge a model that already exists in the collection', function() {
            var contact = {_module: 'Contacts', id: '1', name: 'Sammy Stewart'};

            collection.add([contact]);

            expect(collection.get(contact.id).get('name')).toEqual('Sam Stewart');
            expect(collection.links.contacts.length).toBe(0);
        });
    });

    describe('removing models from the collection', function() {
        beforeEach(function() {
            model.set(attribute, _.first(contacts, 2));
            collection = model.get(attribute);
            sandbox.spy(collection, '_triggerChange');
        });

        it('should not do anything if there are no models to remove', function() {
            collection.remove([]);

            expect(collection._triggerChange).not.toHaveBeenCalled();
            expect(collection.length).toBe(2);
        });

        it('should not remove a model that does not exist in the collection', function() {
            collection.remove([5]);

            expect(collection._triggerChange).not.toHaveBeenCalled();
            expect(collection.length).toBe(2);
        });

        it('should remove a model that exists in the collection', function() {
            collection.remove([1]);

            expect(collection._triggerChange).toHaveBeenCalled();
            expect(collection.length).toBe(1);
            expect(collection.links.contacts.length).toBe(1);
            expect(collection.links.contacts.first().get('_action')).toEqual('delete');
        });

        it('should add and then remove a model', function() {
            var contact = _.last(contacts);

            collection.add(contact);

            expect(collection._triggerChange).toHaveBeenCalled();
            expect(collection.length).toBe(3);
            expect(collection.links.contacts.length).toBe(1);

            collection.remove(contact);

            expect(collection.length).toBe(2);
            expect(collection.links.contacts.length).toBe(0);
        });
    });

    describe('resetting the collection', function() {
        beforeEach(function() {
            model.set(attribute, _.first(contacts, 4));
            collection = model.get(attribute);
            sandbox.spy(collection, '_triggerChange');
        });

        it('should remove all of the models', function() {
            collection.reset([]);

            expect(collection._triggerChange).toHaveBeenCalled();
            expect(collection.length).toBe(0);
            expect(collection.links.contacts.length).toBe(4);

            collection.links.contacts.each(function(contact) {
                expect(contact.get('_action')).toEqual('delete');
            });
        });

        it('should replace all of the models', function() {
            collection.reset(_.rest(contacts, 4));

            expect(collection._triggerChange).toHaveBeenCalled();
            expect(collection.length).toBe(2);
            expect(collection.pluck('id').sort().join(',')).toEqual('5,6');
            expect(collection.links.contacts.length).toBe(6);
            expect(collection.links.contacts.get('1').get('_action')).toEqual('delete');
            expect(collection.links.contacts.get('2').get('_action')).toEqual('delete');
            expect(collection.links.contacts.get('3').get('_action')).toEqual('delete');
            expect(collection.links.contacts.get('4').get('_action')).toEqual('delete');
            expect(collection.links.contacts.get('5').get('_action')).toEqual('add');
            expect(collection.links.contacts.get('6').get('_action')).toEqual('add');
        });

        it('should replace some of the models', function() {
            collection.reset(_.rest(contacts, 3));

            expect(collection._triggerChange).toHaveBeenCalled();
            expect(collection.length).toBe(3);
            expect(collection.pluck('id').sort().join(',')).toEqual('4,5,6');
            expect(collection.links.contacts.length).toBe(5);
            expect(collection.links.contacts.get('1').get('_action')).toEqual('delete');
            expect(collection.links.contacts.get('2').get('_action')).toEqual('delete');
            expect(collection.links.contacts.get('3').get('_action')).toEqual('delete');
            expect(collection.links.contacts.get('5').get('_action')).toEqual('add');
            expect(collection.links.contacts.get('6').get('_action')).toEqual('add');
        });

        it('should revert the collection to its original state', function() {
            sandbox.spy(collection, 'trigger');

            collection.add(_.rest(contacts, 4));
            collection.remove([2]);

            expect(collection.length).toBe(5);
            expect(collection.links.contacts.length).toBe(3);

            collection.revert();

            expect(collection._triggerChange).toHaveBeenCalled();
            expect(collection.trigger.lastCall.args[0]).toEqual('reset');
            expect(collection.length).toBe(4);
            expect(collection.pluck('id').sort().join(',')).toEqual('1,2,3,4');
            expect(collection.links.contacts.length).toBe(0);
        });
    });

    describe('detecting changes to the collection', function() {
        beforeEach(function() {
            model.set(attribute, _.first(contacts, 4));
            collection = model.get(attribute);
        });

        it('should return true', function() {
            collection.add(_.rest(contacts, 4));

            expect(collection.hasChanged()).toBe(true);
        });

        it('should return false', function() {
            expect(collection.hasChanged()).toBe(false);
        });

        it('should return false when only defaults exist on collection', function() {
            collection.add(_.rest(contacts, 4), {default: true});

            expect(collection.hasChanged()).toBe(false);
        });

        it('should return true when there are defaults, but also additional changes', function() {
            collection.add(contacts[4], {default: true});
            collection.add(contacts[5]);

            expect(collection.hasChanged()).toBe(true);
        });

        it('should return true when model is added as default, but then removed and added as non-default', function() {
            collection.add(contacts[4], {default: true});
            collection.remove(contacts[4]);
            collection.add(contacts[4]);

            expect(collection.hasChanged()).toBe(true);
        });
    });

    describe('supporting pagination', function() {
        beforeEach(function() {
            model.set(attribute, _.first(contacts, 4));
            collection = model.get(attribute);
        });

        describe('are there more records to fetch?', function() {
            it('should return true', function() {
                expect(collection.hasMore()).toBe(true);
            });

            it('should return false', function() {
                _.each(collection.offsets, function(offset, link) {
                    collection.offsets[link] = -1;
                });

                expect(collection.hasMore()).toBe(false);
            });

            it('should return true even if some offsets are -1', function() {
                var first = true;

                _.each(collection.offsets, function(offset, link) {
                    if (first) {
                        collection.offsets[link] = 4;
                    } else {
                        collection.offsets[link] = -1;
                    }
                });

                expect(collection.hasMore()).toBe(true);
            });
        });

        describe('fetching more records', function() {
            beforeEach(function() {
                sandbox.stub(app.api, 'call');
            });

            it('should make a request to the Collections API', function() {
                collection.fetch();

                expect(app.api.call.lastCall.args[1]).toMatch(/.*\/rest\/v10\/Meetings\/1\/collection\/invitees.*/);
            });

            it('should use the related modules for the links from the field definition', function() {
                collection.fetch();

                expect(app.api.call.lastCall.args[1]).toMatch(/.*&module_list=Contacts%2CAccounts.*/);
            });

            it('should default to `name` for the fields', function() {
                collection.fetch();

                expect(app.api.call.lastCall.args[1]).toMatch(/.*fields=name&.*/);
            });

            it('should use the specified fields', function() {
                var options;

                options = {
                    fields: ['foo', 'bar'],
                    // set order_by to one of the above fields to avoid "name"
                    // from being included
                    order_by: 'foo:asc'
                };
                collection.fetch(options);

                expect(app.api.call.lastCall.args[1]).toMatch(/.*fields=foo%2Cbar&.*/);
            });

            it('should default to the order_by from the field definition', function() {
                collection.fetch();

                expect(app.api.call.lastCall.args[1]).toMatch(/.*&order_by=name%3Aasc&.*/);
            });

            it('should use the specified order_by', function() {
                var options;

                options = {
                    fields: ['foo', 'bar'],
                    order_by: ['foo:asc', 'bar:desc']
                };
                collection.fetch(options);

                expect(app.api.call.lastCall.args[1]).toMatch(/.*&order_by=foo%3Aasc%2Cbar%3Adesc&.*/);
            });

            it('should add any fields to the request that are to be used for sorting', function() {
                var options;

                options = {
                    fields: ['foo', 'bar'],
                    // name and biz should be added; foo is already in fields
                    order_by: ['name:asc', 'biz:asc', 'foo:asc']
                };
                collection.fetch(options);

                expect(app.api.call.lastCall.args[1]).toMatch(/.*&fields=foo%2Cbar%2Cname%2Cbiz&.*/);
            });

            it('should include the offset for the links', function() {
                collection.fetch({offset: collection.offsets});

                expect(app.api.call.lastCall.args[1]).toMatch(/.*&offset%5Bcontacts%5D=4&offset%5Baccounts%5D=0.*/);
            });

            it('should not include the offset for the links', function() {
                collection.fetch();

                expect(app.api.call.lastCall.args[1]).not.toMatch(/.*&offset%5B.*/);
            });

            it('should trigger `sync:fieldname` on the parent model when successful', function() {
                app.api.call.restore();
                sandbox.stub(app.api, 'call').callsFake(function(method, url, data, callbacks, options) {
                    callbacks.success(data);
                });

                sandbox.spy(collection.parent, 'trigger');
                collection.fetch();

                expect(collection.parent.trigger).toHaveBeenCalledWith('sync:' + attribute);
            });

            describe('fetching all records', function() {
                var hasMore;

                beforeEach(function() {
                    app.api.call.restore();
                    sandbox.stub(app.api, 'call').callsFake(function(method, url, data, callbacks, options) {
                        callbacks.success(data);
                    });

                    hasMore = 3;
                });

                it('should recursively paginate until all records have been fetched', function() {
                    sandbox.stub(collection, 'hasMore').callsFake(function() {
                        return --hasMore;
                    });

                    sandbox.stub(collection, 'paginate').callsFake(function(options) {
                        options.success({});
                    });

                    runs(function() {
                        collection.fetchAll();
                    });

                    waitsFor(function() {
                        return hasMore === 0;
                    }, 'it took too long make requests', 1000);

                    runs(function() {
                        expect(collection.paginate.callCount).toBe(2);
                    });
                });

                it('should call the success callback once all records have been fetched', function() {
                    var spy = sandbox.spy();

                    sandbox.stub(collection, 'hasMore').callsFake(function() {
                        return --hasMore;
                    });

                    sandbox.stub(collection, 'paginate').callsFake(function(options) {
                        options.success({});
                    });

                    runs(function() {
                        collection.fetchAll({success: spy});
                    });

                    waitsFor(function() {
                        if (hasMore > 0) {
                            // make sure the spy hasn't been called yet
                            expect(spy).not.toHaveBeenCalled();
                        }

                        return hasMore === 0;
                    }, 'it took too long make requests', 1000);

                    runs(function() {
                        expect(spy).toHaveBeenCalled();
                    });
                });

                using('maximum limits', [[5, 10, 20], [10, 20, 5], [20, 5, 10]], function(limits) {
                    it('should use the maximum limit possible', function() {
                        var options, max;

                        options = {limit: limits[0]};
                        app.config.maxSubpanelResult = limits[1];
                        app.config.maxQueryResult = limits[2];
                        max = _.max([options.limit, app.config.maxSubpanelResult, app.config.maxQueryResult]);

                        sandbox.stub(collection, 'hasMore').callsFake(function() {
                            return --hasMore;
                        });

                        sandbox.stub(collection, 'paginate').callsFake(function(options) {
                            // every call to paginate should use this limit
                            expect(options.limit).toBe(max);
                            options.success({});
                        });

                        runs(function() {
                            collection.fetchAll();
                        });

                        waitsFor(function() {
                            return hasMore === 0;
                        }, 'it took too long make requests', 1000);
                    });
                });
            });
        });

        it('should specify the offsets', function() {
            sandbox.stub(collection, 'fetch');
            collection.paginate();

            expect(collection.fetch.lastCall.args[0].offset).toEqual(collection.offsets);
        });

        describe('handling a successful pagination request', function() {
            var records;

            beforeEach(function() {
                sandbox.stub(collection, 'fetch').callsFake(function(options) {
                    var response;

                    response = {
                        records: records,
                        next_offset: {
                            contacts: -1,
                            accounts: 7
                        }
                    };
                    options.success(response);
                });
                sandbox.spy(collection, '_triggerChange');
            });

            using('fetched records', [0, 3], function(num) {
                it('should merge the fetched records', function() {
                    records = _.last(contacts, num);
                    sandbox.spy(collection, 'add');
                    collection.paginate();

                    expect(collection.add.lastCall.args[0].length).toBe(num);
                    expect(collection.add.lastCall.args[1].merge).toBe(true);
                });
            });

            it('should add the fetched records as synced for their links', function() {
                expect(collection.links.contacts.synced.length).toBe(4);

                records = _.last(contacts, 2);
                collection.paginate();

                expect(collection.links.contacts.synced.length).toBe(4 + records.length);
            });

            it('should update the offsets', function() {
                expect(collection.offsets.contacts).toBe(4);
                expect(collection.offsets.accounts).toBe(0);

                collection.paginate();

                expect(collection.offsets.contacts).toBe(-1);
                expect(collection.offsets.accounts).toBe(7);
            });
        });
    });

    describe('Bean overrides for supporting collection fields', function() {
        beforeEach(function() {
            model.set(attribute, _.first(contacts, 4));
            model.setSyncedAttributes(model.attributes);
            collection = model.get(attribute);
            sandbox.spy(collection, '_triggerChange');
        });

        describe('calling toJSON() on the model', function() {
            it('should return an object with links when only the link fields are specified', function() {
                collection.add(_.rest(contacts, 4));
                collection.add(newContacts[0]);
                collection.remove([3]);
                collection.add({_module: 'Accounts', id: '10', name: 'Foo Bar'});

                expect(model.toJSON({
                    fields: ['contacts', 'accounts']
                })).toEqual({
                    contacts: {
                        create: [
                            {_module: 'Contacts', name: 'Nelly NewContact'}
                        ],
                        add: [
                            {_module: 'Contacts', id: '5', name: 'Brad Harris'},
                            {_module: 'Contacts', id: '6', name: 'Thomas Wallace'}
                        ],
                        delete: ['3']
                    },
                    accounts: {
                        add: [{_module: 'Accounts', id: '10', name: 'Foo Bar'}]
                    }
                });
            });

            it('should not return links that have not been specified', function() {
                collection.add(_.rest(contacts, 4));
                collection.remove([3]);
                collection.add({_module: 'Accounts', id: '10', name: 'Foo Bar'});

                expect(model.toJSON({
                    fields: ['accounts']
                })).toEqual({
                    accounts: {
                        add: [{_module: 'Accounts', id: '10', name: 'Foo Bar'}]
                    }
                });
            });

            it('should only return the collection if only the collection name is specified', function() {
                var result;

                collection.add(_.rest(contacts, 4));
                collection.remove([3]);
                collection.add({_module: 'Accounts', id: '10', name: 'Foo Bar'});

                result = model.toJSON({
                    fields: ['invitees']
                });

                expect(_.size(result)).toBe(1);
                expect(_.size(result.invitees)).toBe(6);
            });

            it('should return invitees (and id) by default', function() {
                var result;

                result = model.toJSON();

                expect(_.size(result)).toBe(2);
                expect(result.id).toBeTruthy();
                expect(_.size(result.invitees)).toBe(4);
            });

            it('should return no collections and links if specifically not included in options.fields', function() {
                var result;

                model.set('id', '123');

                result = model.toJSON({fields:['id']});

                expect(result).toEqual({id: '123'});
            });
        });

        describe('copying a model with a collection field', function() {
            var fields, target;

            beforeEach(function() {
                fields = app.utils.deepCopy(model.fields);
                sandbox.stub(app.metadata, 'getModule').withArgs('Meetings').returns({fields: fields});

                target = app.data.createBean('Meetings');
                target.fields = fields;
            });

            it('should still copy any non-collection fields to the new bean', function() {
                model.set('foo', 'bar');

                expect(target.get(attribute)).toBeUndefined();

                target.fields.foo = {name: 'foo', type: 'varchar'};
                target.copy(model);

                expect(target.get('foo')).toEqual('bar');
            });

            it('should copy any collection fields to the new bean', function() {
                expect(target.get(attribute)).toBeUndefined();

                target.copy(model);
                collection = target.get(attribute);

                expect(collection.length).toBe(4);
            });

            it('should copy the exact state of the collection field to the new bean', function() {
                model.get(attribute).remove([2]);
                target.copy(model);

                expect(target.get(attribute).length).toBe(3);
            });

            it('should not have any synced set on the link', function() {
                target.copy(model);
                collection = target.get(attribute);

                _.each(collection.links, function(link) {
                    expect(link.synced.length).toBe(0);
                });
            });

            it('should have the correct number of links added', function() {
                target.copy(model);
                collection = target.get(attribute);

                expect(collection.links.contacts.length).toBe(4);
            });
        });

        describe('setting a collection field', function() {
            it('should set the collection as the default when created', function() {
                expect(model.getDefault(attribute)).toBe(collection);
            });

            it('should still set any non-collection fields on the bean', function() {
                var attributes = {};

                attributes[attribute] = _.last(contacts);
                attributes.foo = 'bar';
                model.set(attributes);

                expect(model.get(attribute).length).toBe(1);
                expect(model.get('foo')).toEqual('bar');
            });

            describe('setting to undefined', function() {
                beforeEach(function() {
                    model.set(attribute, _.last(contacts));
                });

                it('should set the attribute to an empty collection when setting with undefined', function() {
                    model.set(attribute, undefined);

                    expect(model.get(attribute).length).toBe(0);
                });

                it('should set the attribute to an empty collection when unsetting', function() {
                    model.unset(attribute);

                    expect(model.get(attribute).length).toBe(0);
                });
            });

            it('should accept the models from the server response', function() {
                var response = {
                    records: contacts,
                    next_offset: {}
                };

                model.set(attribute, response);

                expect(model.get(attribute).length).toBe(contacts.length);
            });

            it('should accept the models from another collection', function() {
                var collection = app.data.createBeanCollection('Contacts', contacts);

                model.set(attribute, collection);

                expect(model.get(attribute) === collection).toBe(false);
                expect(model.get(attribute).length).toBe(collection.length);
            });

            describe('setting the next offsets', function() {
                it('should set the next offsets from the server response', function() {
                    var response = {
                        records: contacts,
                        next_offset: {
                            contacts: -1,
                            accounts: 0
                        }
                    };

                    model.set(attribute, response);

                    expect(model.get(attribute).offsets.contacts).toBe(-1);
                    expect(model.get(attribute).offsets.accounts).toBe(0);
                });

                it('should set the next offsets from the options', function() {
                    var options = {
                        offsets: {
                            contacts: -1,
                            accounts: 0
                        }
                    };

                    model.set(attribute, contacts, options);

                    expect(model.get(attribute).offsets.contacts).toBe(-1);
                    expect(model.get(attribute).offsets.accounts).toBe(0);
                });

                it('should infer the next offsets when not provided', function() {
                    model.set(attribute, contacts);

                    expect(model.get(attribute).offsets.contacts).toBe(6);
                    expect(model.get(attribute).offsets.accounts).toBe(0);
                });
            });
        });

        describe('has the bean changed?', function() {
            using('attribute names', [undefined, attribute], function(attr) {
                it('should return true when the collection has changed but no other attributes have', function() {
                    model.get(attribute).remove([2]);

                    expect(model.hasChanged(attr)).toBe(true);
                });
            });

            using('attribute names', [undefined, 'foo'], function(attr) {
                it('should return true when the collection has not changed but another attribute has', function() {
                    model.set('foo', 'bar');

                    expect(model.hasChanged(attr)).toBe(true);
                });
            });

            using('attribute names', [undefined, attribute, 'foo'], function(attr) {
                it('should return false', function() {
                    model.set('foo', 'bar');
                    delete model.changed.foo;

                    expect(model.hasChanged(attr)).toBe(false);
                });
            });
        });

        it('should include any collection fields in the return value for synchronized attributes', function() {
            expect(model.getSynced()[attribute]).not.toBeUndefined();
        });

        it('should revert changes to any collection fields', function() {
            collection.remove([2, 3]);

            expect(collection.length).toBe(2);

            model.revertAttributes();

            expect(collection.length).toBe(4);
        });

        describe('getting changed attributes', function() {
            it('should not include `invitees` in the return value', function() {
                var synced = model.getSynced();
                var changed = model.changedAttributes(synced);

                expect(changed[attribute]).toBeUndefined();
            });

            it('should include `invitees` in the return value', function() {
                var changed;
                var synced = model.getSynced();

                model.get(attribute).remove([2]);
                changed = model.changedAttributes(synced);

                expect(changed[attribute]).not.toBeUndefined();
            });

            it('should not include `invitees` in the return value when diff is a deep copy', function() {
                var synced = app.utils.deepCopy(model.getSynced());
                var changed = model.changedAttributes(synced);

                expect(changed[attribute]).toBeUndefined();
            });

            it('should not include `invitees` in the return value when not changed and diff is undefined', function() {
                model.revertAttributes();
                var changed = model.changedAttributes();

                expect(changed[attribute]).toBeUndefined();
            });

            it('should include `invitees` in the return value when changed and diff is undefined', function() {
                var changed;

                model.get(attribute).remove([2]);
                changed = model.changedAttributes();

                expect(changed[attribute]).not.toBeUndefined();
            });

            it('should not include `invitees` in the return value when changed and not in diff', function() {
                var changeAttrs = {
                    id: '123'
                };
                model.get(attribute).remove([2]);
                var changed = model.changedAttributes(changeAttrs);

                expect(changed[attribute]).toBeUndefined();
            });

            it('should not include `invitees` in the return value when not changed and not in diff', function() {
                var changeAttrs = {
                    id: '123'
                };
                var changed = model.changedAttributes(changeAttrs);

                expect(changed[attribute]).toBeUndefined();
            });
        });

        describe('getting the names of the collection fields', function() {
            it('should return an empty array', function() {
                delete model.fields[attribute];

                expect(model.getCollectionFieldNames().length).toBe(0);
            });

            it('should return an array with the collection field names', function() {
                var fields = model.getCollectionFieldNames();

                // Virtual collection now explicitly only handles invitees
                expect(fields.length).toBe(1);
                expect(fields).toEqual(['invitees']);
            });
        });
    });
});
