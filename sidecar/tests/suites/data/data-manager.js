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

const Api = require('@sugarcrm/ventana');
const Bean = require('../../../src/data/bean');
const BeanCollection = require('../../../src/data/bean-collection');
const User = require('../../../src/core/user');

describe('Data/DataManager', function() {

    var metadata, app, dm, copiedUser, sinonSandbox,
        instanceOfError = sinon.match.instanceOf(Api.HttpError),
        instanceOfRequest = sinon.match.instanceOf(Api.HttpRequest);


    beforeEach(function() {
        sinonSandbox = sinon.createSandbox();
        app = SugarTest.app;
        dm = app.data;
        SugarTest.seedMetadata();
        app.config.maxQueryResult = 2;
        dm.reset();
        metadata = SugarTest.metadata;
        copiedUser = User.toJSON(); // save original to reset on teardown
        User.set(fixtures.user);
        User.id = 'seed_sally_id';

        this.api = Api.createInstance({
            serverUrl: '/rest/v10',
        });
    });


    afterEach(function() {
        sinonSandbox.restore();
        User.set(copiedUser);
        app.events.off("data:sync:start data:sync:complete data:sync:success data:sync:error");
    });

    it("should be able to create an empty instance of bean and collection", function() {
        dm.declareModels(metadata);

        _.each(_.keys(metadata.modules), function(moduleName) {
            expect(dm.createBean(moduleName)).toBeDefined();
            expect(dm.createBeanCollection(moduleName)).toBeDefined();
        });

    });

    it("should be able to create an instance of bean and collection", function() {
        var moduleName = "Contacts", bean, collection;

        dm.declareModel(moduleName, metadata.modules[moduleName]);

        bean = dm.createBean(moduleName, { someAttr: "Some attr value"});
        expect(bean.module).toEqual(moduleName);
        expect(bean.fields).toEqual(metadata.modules[moduleName].fields);
        expect(bean.get("someAttr")).toEqual("Some attr value");

        collection = dm.createBeanCollection(moduleName);
        expect(collection.module).toEqual(moduleName);
        expect(collection.model).toBeDefined();

    });

    it("should be able to create a base model and base collection for a platform", function() {
        var platform = 'base', bean, collection;

        dm.declareModelClass(null, null, platform, null);
        dm.declareCollectionClass(null, platform, null);

        bean = dm.createBean("BaseModel", { someAttr: "Some attr value"});
        expect(bean.module).toEqual("BaseModel");
        expect(bean.fields).toEqual({});
        expect(bean.get("someAttr")).toEqual("Some attr value");

        collection = dm.createBeanCollection("BaseCollection");
        expect(collection.module).toEqual("BaseCollection");
        expect(collection.model).toBeDefined();
    });

    it("should be able to override a base model and base collection for a platform", function() {
        var baseBean, baseCollection, platformBean, platformCollection, moduleBean, moduleCollection;

        var baseModelController = {
            someBaseAttr: true
        };
        var baseCollectionController = {
            someBaseAttr: true
        };

        dm.declareModelClass(null, null, 'base', baseModelController);
        dm.declareCollectionClass(null, 'base', baseCollectionController);

        var platformModelController = {
            somePlatformAttr: true
        };
        var platformCollectionController = {
            somePlatformAttr: true
        };

        dm.declareModelClass(null, null, 'platform', platformModelController);
        dm.declareCollectionClass(null, 'platform', platformCollectionController);

        var moduleModelController = {
            someModuleAttr: true
        };
        var moduleCollectionController = {
            someModuleAttr: true
        };

        dm.declareModelClass("MyModule", null, 'platform', moduleModelController);
        dm.declareCollectionClass("MyModule", 'platform', moduleCollectionController);

        baseBean = dm.createBean("BaseModel", {});
        expect(baseBean.someBaseAttr).toBe(true);
        expect(baseBean.somePlatformAttr).toBeUndefined();
        expect(baseBean.someModuleAttr).toBeUndefined();

        baseCollection = dm.createBeanCollection("BaseCollection");
        expect(baseCollection.someBaseAttr).toBe(true);
        expect(baseCollection.somePlatformAttr).toBeUndefined();
        expect(baseCollection.someModuleAttr).toBeUndefined();

        platformBean = dm.createBean("PlatformModel", {});
        expect(platformBean.someBaseAttr).toBe(true);
        expect(platformBean.somePlatformAttr).toBe(true);
        expect(platformBean.someModuleAttr).toBeUndefined();

        platformCollection = dm.createBeanCollection("PlatformCollection");
        expect(platformCollection.someBaseAttr).toBe(true);
        expect(platformCollection.somePlatformAttr).toBe(true);
        expect(platformCollection.someModuleAttr).toBeUndefined();

        moduleBean = dm.createBean("MyModule", {});
        expect(moduleBean.someBaseAttr).toBe(true);
        expect(moduleBean.somePlatformAttr).toBe(true);
        expect(moduleBean.someModuleAttr).toBe(true);

        moduleCollection = dm.createBeanCollection("MyModule");
        expect(moduleCollection.someBaseAttr).toBe(true);
        expect(moduleCollection.somePlatformAttr).toBe(true);
        expect(moduleCollection.someModuleAttr).toBe(true);

    });

    it("should be able to fetch a bean by ID", function() {
        var moduleName = "Teams", mock, bean;

        dm.declareModel(moduleName, metadata.modules[moduleName]);

        mock = sinon.mock(Bean.prototype);
        mock.expects("sync").once().withArgs("read");

        bean = dm.createBean(moduleName, {id: "xyz"});
        bean.fetch();

        expect(bean.id).toEqual("xyz");
        expect(bean.module).toEqual(moduleName);
        mock.verify();
    });

    it("should be able to fetch beans", function() {
        var moduleName = "Teams", mock, collection;
        dm.declareModel(moduleName, metadata.modules[moduleName]);

        mock = sinon.mock(BeanCollection.prototype);
        mock.expects("sync").once().withArgs("read");

        collection = dm.createBeanCollection(moduleName, null);
        collection.fetch();

        expect(collection.module).toEqual(moduleName);
        expect(collection.model).toBeDefined();
        mock.verify();
    });

    it("should be able to sync (read) a bean", function() {
        var moduleName = "Contacts", bean, contact;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, { id: "1234" });

        contact = SugarTest.loadFixture("contact");

        var cb1 = sinon.spy(), cb2 = sinon.spy();
        var sspy = sinon.spy(), cspy = sinon.spy();
        bean.on("data:sync:start", cb1);
        bean.on("data:sync:complete", cb2);

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\/1234.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contact)]);

        expect(bean.dataFetched).toBeFalsy();
        bean.fetch({
            success: sspy,
            complete: cspy
        });

        expect(cb1).toHaveBeenCalledWith("read", sinon.match.object);
        SugarTest.server.respond();

        expect(cb2).toHaveBeenCalledWith("read", sinon.match.object, instanceOfRequest);
        expect(bean.get("primary_address_city")).toEqual("Cupertino");
        expect(bean.get('_acl')).toBeDefined();
        expect(bean.dataFetched).toBeTruthy();
        expect(sspy).toHaveBeenCalledWith(bean, contact, sinon.match.object);
        expect(cspy).toHaveBeenCalledWith(instanceOfRequest);
    });

    it("should fire GLOBAL data:sync:start and data:sync:complete/success events", function() {
        var moduleName = "Contacts", bean, contact;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, { id: "1234" });

        contact = SugarTest.loadFixture("contact");

        var cb1 = sinon.spy(), cb2 = sinon.spy(), cb3 = sinon.spy();

        app.events.on("data:sync:start", cb1);
        app.events.on("data:sync:complete", cb2);
        app.events.on("data:sync:success", cb3);

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\/1234.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contact)]);

        bean.fetch();

        expect(cb1).toHaveBeenCalledWith("read", bean);
        expect(cb2.called).toBe(false);
        cb1.resetHistory();
        SugarTest.server.respond();
        expect(cb1.called).toBe(false);
        expect(cb2).toHaveBeenCalledWith("read", bean, sinon.match.object, instanceOfRequest);
        expect(cb3).toHaveBeenCalledWith("read", bean, sinon.match.object, instanceOfRequest);
    });

    it("should fire MODEL data:sync:start and data:sync:complete events", function() {
        var moduleName = "Contacts", bean, contact;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, { id: "1234" });

        contact = SugarTest.loadFixture("contact");

        var cb1 = sinon.spy(), cb2 = sinon.spy();

        bean.on("data:sync:start", cb1);
        bean.on("data:sync:complete", cb2);

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\/1234.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contact)]);

        expect(bean.inSync).toBeFalsy();
        bean.fetch({
            success: function(){
                expect(bean.inSync).toBeTruthy();
            },
        });

        expect(cb1).toHaveBeenCalledWith("read", sinon.match.object);
        expect(cb2.called).toBe(false);
        cb1.resetHistory();
        SugarTest.server.respond();
        expect(bean.inSync).toBeFalsy();
        expect(cb1.called).toBe(false);
        expect(cb2).toHaveBeenCalledWith("read", sinon.match.object, instanceOfRequest);
    });

    it("should be able to sync (create) a bean", function() {
        var moduleName = "Contacts", contact;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        contact = dm.createBean(moduleName, { first_name: "Clara", last_name: "Tsetkin" });

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("POST", /.*\/rest\/v10\/Contacts.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify({ id: "xyz" })]);

        contact.save();
        SugarTest.server.respond();

        expect(contact.id).toEqual("xyz");
    });

    it("should be able to sync (update) a bean", function() {
        var moduleName = "Contacts", contact;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        contact = dm.createBean(moduleName, { id: "xyz", first_name: "Clara", last_name: "Tsetkin", dateModified: "1" });

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("PUT", /.*\/rest\/v10\/Contacts\/xyz.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify({ dateModified: "2" })]);

        contact.save();
        SugarTest.server.respond();

        expect(contact.get("dateModified")).toEqual("2");
    });

    it("should be able to sync (delete) a bean", function() {
        var moduleName = "Contacts", contact;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        contact = dm.createBean(moduleName, { id: "xyz" });

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("DELETE", /.*\/rest\/v10\/Contacts\/xyz.*/,
            [200, {  "Content-Type": "application/json"}, ""]);

        contact.destroy();
        SugarTest.server.respond();
    });

    it("should be able to sync (read) beans", function() {
        var moduleName = "Contacts", beans, contacts;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        beans = dm.createBeanCollection(moduleName);

        contacts = SugarTest.loadFixture("contacts");

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts[?]{1}max_num=2.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);

        beans.fetch();
        SugarTest.server.respond();

        expect(beans.length).toEqual(2);
        expect(beans.at(0).get("name")).toEqual("Vladimir Vladimirov");
        expect(beans.at(1).get("name")).toEqual("Petr Petrov");
        expect(beans.at(1).module).toEqual("Contacts");
        expect(beans.at(1).fields).toBeDefined();
        expect(beans.at(0).get('_acl')).toBeDefined();
        expect(beans.at(0).get('_acl')["edit"]).toEqual("yes");
        expect(beans.at(0).get('_acl').fields).toBeDefined();
        expect(beans.at(0).get('_acl').fields["name"]).toBeDefined();
        expect(beans.at(0).get('_acl').fields["name"]["edit"]).toBeDefined();
        expect(beans.at(0).get('_acl').fields["name"]["edit"]).toEqual("no");
        expect(beans.at(1).get('_acl')).toBeUndefined();
    });

    it('should fetch a collection field', function() {
        let module = 'Meetings';
        dm.declareModels(metadata.modules);
        let bean = dm.createBean(module, {id: '12345'});
        let collection = bean.get('invitees');

        sinonSandbox.spy(SUGAR.App.api, 'collection');

        // Get fixture for server response. It contains 2 contacts records.
        let contacts = SugarTest.loadFixture('2_contacts_collection_API');

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith('GET', /.*\/rest\/v10\/Meetings\/12345\/collection\/invitees[?]*/,
            [200, {'Content-Type': 'application/json'},
                JSON.stringify(contacts)]);

        collection.fetch({collectionField: 'invitees', beanId: 12345, module: 'Meetings'});

        SugarTest.server.respond();

        expect(SUGAR.App.api.collection).toHaveBeenCalledOnce();
        // The length of the collection matches the number of records provided
        // in the fixture.
        expect(collection.length).toEqual(2);
        // Making sure the `next_offset` returned by the server
        // (here the fixture) has been set to the collection.
        expect(collection.offset).toEqual({contacts: -1, leads: -1, users: -1});
        expect(collection.next_offset).toEqual({contacts: -1, leads: -1, users: -1});
    });

    it("should be able to handle empty response", function() {
        var moduleName = "Contacts", beans, contacts;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        beans = dm.createBeanCollection(moduleName);

        contacts = SugarTest.loadFixture("contacts");

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts[?]{1}max_num=2.*/,
            [200, {  "Content-Type": "application/json"}, ""]);

        beans.fetch();
        SugarTest.server.respond();

        expect(beans.length).toEqual(0);
    });

    it("should be able to handle sync errors", function() {
        var moduleName = "Contacts", bean, syncError, syncComplete, flag = false,
            globalSyncError, globalSyncComplete, modelSyncError;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName);

        syncError = sinon.spy(function() {
            // Error callback must be called before data:sync:complete gets triggered
            expect(flag).toBeFalsy();
        });
        syncComplete = sinon.spy(function() {
            flag = true;
        });
        modelSyncError = sinon.spy();
        globalSyncError = sinon.spy();
        globalSyncComplete = sinon.spy();

        bean.on("data:sync:complete", syncComplete);
        bean.on("data:sync:error", modelSyncError);
        app.events.on("data:sync:complete", globalSyncComplete);
        app.events.on("data:sync:error", globalSyncError);
        SugarTest.seedFakeServer();
        SugarTest.server.respondWith([422, {}, ""]);
        bean.save(null, {error: syncError});
        SugarTest.server.respond();

        expect(syncError).toHaveBeenCalledWith(bean, instanceOfError, sinon.match.object);
        expect(modelSyncError).toHaveBeenCalledWith("create", sinon.match.object, instanceOfError);
        expect(syncComplete).toHaveBeenCalledWith("create", sinon.match.object, instanceOfRequest);
        expect(globalSyncComplete).toHaveBeenCalledWith("create", bean, sinon.match.object, instanceOfRequest);
        expect(globalSyncError).toHaveBeenCalledWith("create", bean, sinon.match.object, instanceOfError);
    });

    it("should add result count and next offset to a collection if in server response", function(){
        var moduleName = "Contacts", beans, contacts;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        beans = dm.createBeanCollection(moduleName);

        contacts = SugarTest.loadFixture("contacts");

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts[?]{1}max_num=2.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);

        beans.fetch();
        SugarTest.server.respond();

        expect(beans.offset).toEqual(2);
    });

    describe('getEditableFields', function() {
        it("should be able to prune fields user doesn't have access to by bean", function() {
            var moduleName = "Contacts", bean;

            dm.declareModel(moduleName, metadata.modules[moduleName]);
            bean = dm.createBean(moduleName, {first_name: "First", last_name:"Last", id:"123"});

            var acls = {};
            bean.set('_acl', acls);

            acls.fields = {"last_name":{"write":"no"}, "first_name":{"write":"yes"}, "id":{"write":"yes"}};

            var fields = dm.getEditableFields(bean);

            expect(fields["last_name"]).toBeFalsy();
            expect(fields["first_name"]).toBeTruthy();
            expect(fields["id"]).toBeTruthy();
        });

        it("should be able to prune fields user doesn't have access to", function() {
            var moduleName = "Contacts", bean;

            dm.declareModel(moduleName, metadata.modules[moduleName]);
            bean = dm.createBean(moduleName, {first_name: "First", last_name:"Last", id:"123"});
            var fields = dm.getEditableFields(bean);

            expect(fields["last_name"]).toBeFalsy();
            expect(fields["first_name"]).toBeTruthy();
            expect(fields["id"]).toBeTruthy();
        });

        it('should not return fields that are not in the vardef', function() {
            var moduleName = 'Contacts',
                bean,
                fields;

            dm.declareModel(moduleName, metadata.modules[moduleName]);
            bean = dm.createBean(moduleName, {
                id: '1',
                foo: 'bar'
            });

            fields = dm.getEditableFields(bean);

            expect(fields.foo).toBeUndefined();
        });

        // FIXME: This test is deprecated, since it relies on the format
        // introduced by the Virtual Collection plugin (SC-6145 will remove it).
        it('should return fields that are specified via collection field links definition', function() {
            var moduleName = 'Quotes';
            var bean;
            var fields;
            var bundles = dm.createMixedBeanCollection();
            // This format below is created by the Virtual collection plugin and
            // has nothing to do with sidecar.
            bundles.links = [{
                link: {
                    name: 'foo'
                }
            }];

            dm.declareModel(moduleName, metadata.modules[moduleName]);
            bean = dm.createBean(moduleName, {
                id: '1',
                bundles: bundles,
                foo: 'bar'
            });

            fields = dm.getEditableFields(bean);

            expect(fields).toEqual({
                id: '1',
                foo: 'bar'
            });
        });
    });

    it("should be able to fetch a collection with a custom endpoint", function() {
        var ajaxSpy = sinon.spy($, 'ajax'),
            moduleName = 'Contacts',
            records, endpoint;

        dm.declareModel(moduleName, metadata.modules[moduleName]);
        records = dm.createBeanCollection(moduleName);

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("POST",  /.*\/rest\/v10\/Contacts\/duplicateCheck/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(fixtures.api["rest/v10/search"].GET.response)]);

        endpoint = function (method, module, options, callbacks){
            var url = this.api.buildURL('Contacts', 'duplicateCheck', null, {});
            return this.api.call('POST', url, null, callbacks);
        }.bind(this);
        records.fetch({
            module_list: ["Contacts"],
            endpoint: endpoint
        });

        SugarTest.server.respond();
        expect(ajaxSpy.getCall(0).args[0].url).toMatch(/.*\/rest\/v10\/Contacts\/duplicateCheck/);
        ajaxSpy.restore();
    });

    it('should return true for a many to many relationship', function() {
        var getModuleStub = sinonSandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                fields: {
                    test_link_one: { name: 'test_link_one', type: 'link', relationship: 'foo', link_type: 'many' },
                    test_link_two: { name: 'test_link_two', type: 'link', relationship: 'foo' }
                }
            };
        });
        var getRelationshipStub = sinonSandbox.stub(app.metadata, 'getRelationship').callsFake(function() {
            return {
                relationship_type: 'many-to-many',
                lhs_module: 'test1',
                rhs_module: 'test2'
            };
        });
        expect(dm.canHaveMany('test', 'test_link_one')).toBeTruthy();
        expect(dm.canHaveMany('test', 'test_link_two')).toBeTruthy();
    });

    it('should return false for a one to one relationship', function() {
        var getModuleStub = sinonSandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                fields: {
                    test_link_one: { name: 'test_link_one', type: 'link', relationship: 'foo', link_type: 'one' },
                    test_link_two: { name: 'test_link_two', type: 'link', relationship: 'foo' }
                }
            };
        });
        var getRelationshipStub = sinonSandbox.stub(app.metadata, 'getRelationship').callsFake(function() {
            return {
                relationship_type: 'one-to-one',
                lhs_module: 'test1',
                rhs_module: 'test2'
            };
        });
        expect(dm.canHaveMany('test', 'test_link_one')).toBeFalsy();
        expect(dm.canHaveMany('test', 'test_link_two')).toBeFalsy();
    });

    it('should return false for a one to one relationship with equal module names', function() {
        var getModuleStub = sinonSandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                fields: {
                    test_link: { name: 'test_link', type: 'link', relationship: 'foo' }
                }
            };
        });
        var getRelationshipStub = sinonSandbox.stub(app.metadata, 'getRelationship').callsFake(function() {
            return {
                relationship_type: 'one-to-one',
                lhs_module: 'test1',
                rhs_module: 'test1'
            };
        });
        expect(dm.canHaveMany('test', 'test_link')).toBeFalsy();
    });

    it('should return true for a one to many relationship across the same module with ' +
       'no link side or type specified', function() {
        var getModuleStub = sinonSandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                fields: {
                    test_link: { name: 'test_link', type: 'link', relationship: 'foo' }
                }
            };
        });
        var getRelationshipStub = sinonSandbox.stub(app.metadata, 'getRelationship').callsFake(function() {
            return {
                relationship_type: 'one-to-many',
                lhs_module: 'test1',
                rhs_module: 'test1'
            };
        });
        expect(dm.canHaveMany('test1', 'test_link')).toBeTruthy();
    });

    it('should return false for a one to many relationship across the same module ' +
       'if the module given is on the one side or type is one', function() {
        var getModuleStub = sinonSandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                fields: {
                    test_link_one: { name: 'test_link_one', type: 'link', relationship: 'foo', link_type: 'one' },
                    test_link_two: { name: 'test_link_two', type: 'link', relationship: 'foo', side: 'left' }
                }
            };
        });
        var getRelationshipStub = sinonSandbox.stub(app.metadata, 'getRelationship').callsFake(function() {
            return {
                relationship_type: 'one-to-many',
                lhs_module: 'test1',
                rhs_module: 'test1'
            };
        });
        expect(dm.canHaveMany('test1', 'test_link_one')).toBeFalsy();
        expect(dm.canHaveMany('test1', 'test_link_two')).toBeFalsy();
    });

    using('invalid metadata', [
        undefined,
        {},
        {
            fields: {
                test_link_two: { name: 'test_link_two', type: 'link', relationship: 'foo', side: 'left' }
            }
        }
    ], function(meta) {
        it('should return false', function() {
            sinonSandbox.stub(app.metadata, 'getModule').returns(meta);
            sinonSandbox.stub(app.metadata, 'getRelationship').callsFake(function() {
                return {
                    relationship_type: 'one-to-many',
                    lhs_module: 'test1',
                    rhs_module: 'test1'
                };
            });
            expect(dm.canHaveMany('test1', 'test_link_one')).toBeFalsy();
        });
    });

    it('should respect true_relationship_type', function() {
        var getModuleStub = sinonSandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                fields: {
                    test_link: { name: 'test_link', type: 'link', relationship: 'foo' }
                }
            };
        });
        var getRelationshipStub = sinonSandbox.stub(app.metadata, 'getRelationship').callsFake(function() {
            return {
                relationship_type: 'one-to-many',
                true_relationship_type: 'one-to-one',
                lhs_module: 'test1',
                rhs_module: 'test1'
            };
        });
        expect(dm.canHaveMany('test1', 'test_link')).toBeFalsy();
    });

    it('should respect link-type', function() {
        var getModuleStub = sinonSandbox.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                fields: {
                    test_link: { name: 'test_link', type: 'link', relationship: 'foo', 'link-type': 'many' },
                }
            };
        });
        expect(dm.canHaveMany('test1', 'test_link')).toBeTruthy();
    });

    describe("parseOptionsForSync", function(){
        it("should add viewed=1 URL parameter only when viewed option is true", function(){
            var moduleName = "Contacts", bean;
            dm.declareModel(moduleName, metadata.modules[moduleName]);
            bean = dm.createBean(moduleName, {first_name: "First", last_name:"Last", id:"123"});
            var options = dm.parseOptionsForSync("read", bean, {viewed:true});
            expect(options.params.viewed).toEqual("1");
            options = dm.parseOptionsForSync("create", bean, {viewed:true});
            expect(options.params.viewed).toEqual("1");
            options = dm.parseOptionsForSync("read", bean, {viewed:false});
            expect(options.params.viewed).toBeUndefined();
            options = dm.parseOptionsForSync("read", bean, {});
            expect(options.params.viewed).toBeUndefined();
        });

        it("should set the modified date in the header when the lastModified attribute has been passed during an update", function(){
            var bean, actual,
                moduleName = 'Contacts',
                dateModified = '1234';

            dm.declareModel(moduleName, metadata.modules[moduleName]);
            bean = dm.createBean('Contacts', {first_name: "First", last_name:"Last", id:"123"});

            actual = dm.parseOptionsForSync('update', bean, {lastModified: dateModified});
            expect(actual.apiOptions.headers['X-TIMESTAMP']).toBe(dateModified);
        });

        it("should not set the modified date in the header when the lastModified attribute is empty during an update", function(){
            var bean, actual,
                moduleName = 'Contacts';

            dm.declareModel(moduleName, metadata.modules[moduleName]);
            bean = dm.createBean('Contacts', {first_name: "First", last_name:"Last", id:"123"});

            actual = dm.parseOptionsForSync('update', bean, {});
            expect(actual.apiOptions).toBeUndefined();
        });

        it("should not set the modified date in the header if it is not an update", function(){
            var bean, actual,
                moduleName = 'Contacts',
                dateModified = '1234';

            dm.declareModel(moduleName, metadata.modules[moduleName]);
            bean = dm.createBean('Contacts', {first_name: "First", last_name:"Last", id:"123"});

            actual = dm.parseOptionsForSync('create', bean, {lastModified: dateModified});
            expect(actual.apiOptions).toBeUndefined();

            actual = dm.parseOptionsForSync('read', bean, {lastModified: dateModified});
            expect(actual.apiOptions).toBeUndefined();

            actual = dm.parseOptionsForSync('delete', bean, {lastModified: dateModified});
            expect(actual.apiOptions).toBeUndefined();
        });

        it('should pass the view paramter through to the API', function() {
            dm.declareModel("Bar", {});
            var bean = dm.createBean("Bar", {id:"123"}),
                actual = dm.parseOptionsForSync('read', bean, {view: "foo"});

            expect(actual.params.view).toBe("foo");
        });

    });

    describe('getRelatedModule', function() {
        using('invalid metadata', [
            undefined,
            {},
            {
                fields: {
                    test_link_two: { name: 'test_link_two', type: 'link', relationship: 'foo', side: 'left' }
                }
            }
        ], function(meta) {
            it('should return false', function() {
                sinonSandbox.stub(app.metadata, 'getModule').returns(meta);
                expect(dm.getRelatedModule('test1', 'test_link_one')).toBeFalsy();
            });
        });
    });

    describe('getRelateFields', function() {
        it('should log an error if called on a module with a nonexistent link field', function() {
            let errorStub = sinonSandbox.stub(app.logger, 'error');
            sinonSandbox.stub(app.metadata, 'getModule').returns({fields: {}});
            let result = dm.getRelateFields('BuggyModule', 'mylink');
            let msg = `Calling 'getRelateFields' on 'BuggyModule' with link 'mylink' but no fields have been found. ` +
                `Please fix your metadata.`;
            expect(errorStub).toHaveBeenCalledWith(msg);
            expect(result).toEqual([]);
        });
    });

    describe('getOppositeLink', function() {
        it('should get the link field name of the other module of a relationship', function() {
            let oppositeLink = dm.getOppositeLink('Contacts', 'opportunities');

            expect(oppositeLink).toEqual('contacts');
        });
    });
});
