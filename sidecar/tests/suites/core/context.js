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
const MixedBeanCollection = require('../../../src/data/mixed-bean-collection');
const Context = require('../../../src/core/context');

describe('Core/Context', function() {
    var app;
    var module = 'Cases';

    beforeEach(function() {
        SugarTest.seedMetadata();
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
    });

    it("should return a new context object", function() {
        var context = new Context({});
        expect(context.attributes).toEqual({});
    });

    it("should return properties", function() {
        var context = new Context({
            prop1: "Prop1",
            prop2: "Prop2",
            prop3: "Prop3"
        });
        expect(context.get("prop1")).toEqual("Prop1");
        expect(context.attributes).toEqual({prop1: "Prop1", prop2: "Prop2", prop3: "Prop3"});
    });

    it("should prepare data for a module path", function() {
        var context = new Context({module:'Contacts'});
        expect(context.attributes.model).toBeUndefined();
        expect(context.attributes.collection).toBeUndefined();

        context.prepare();

        expect((context.attributes.model instanceof Backbone.Model)).toBeTruthy();
        expect((context.attributes.collection instanceof Backbone.Collection)).toBeTruthy();

        expect(context.attributes.model.module).toEqual("Contacts");
        expect(context.attributes.collection.module).toEqual("Contacts");
    });

    it('should prepare data for a global search', function() {
        var context = new Context({
            mixed: true,
            module_list: ['Accounts', 'Contacts']
        });
        var collection = context.get('collection');
        expect(collection).toBeUndefined();

        context.prepare();

        collection = context.get('collection');
        expect((collection instanceof MixedBeanCollection)).toBeTruthy();

        var mock = sinon.mock(collection).expects('fetch').once().withArgs({
            context: context,
            someOption: 'xxx'
        });

        context.loadData({someOption: "xxx"});
        mock.verify();
    });

    it("should not load data if we set skipFetch", function() {
        var context = new Context({
            module: module,
            skipFetch: true
        });

        context.prepare();

        var collection = context.attributes.collection;
        var mock = sinon.mock(collection).expects("fetch").never();

        context.loadData({ someOption: "xxx"});
        mock.verify();
    });

    it("should prepare data for a record path", function() {
        var context = new Context({
            modelId: '123',
            module: module
        });

        context.prepare();

        expect(context.attributes.model).toBeDefined();
        expect(context.attributes.model.id).toEqual("123");
        expect(context.isCreate()).toBeFalsy();
    });

    it("should prepare data for a create path", function() {
        var context = new Context({
            create: true,
            module: module
        });

        context.prepare();

        expect(context.get("module")).toEqual(module);
        expect(context.get("model") instanceof app.Bean).toBeTruthy();
        expect(context.get("model").isNew()).toBeTruthy();
        expect(context.isCreate()).toBeTruthy();
    });

    it("should load data for a module path", function() {
        var collection = app.data.createBeanCollection(module);
        var context = new Context({
            collection: collection,
            module: module
        });

        var mock = sinon.mock(collection).expects("fetch").once();
        context.loadData();

        mock.verify();
    });

    it("should load data for a record path", function() {
        var model = app.data.createBean(module, { id: "xyz" });
        var context = new Context({
            model: model,
            module: module,
            modelId: 'xyz'
        });

        var mock = sinon.mock(model).expects("fetch").once();
        context.loadData();

        mock.verify();
    });

    it("should set the order by on collection if defined in config", function() {
        var collection = app.data.createBeanCollection(module);

        var context = new Context({
            module: module,
            collection: collection
        });
        app.config.orderByDefaults = {
            'Cases': {
                field: 'case_number',
                direction: 'asc'
            }
        };

        // Prevent outgoing http request
        var stub = sinon.stub($, 'ajax');
        context.loadData();
        stub.restore();

        expect(collection.orderBy).toBeDefined();
        expect(collection.orderBy.field).toEqual('case_number');
        expect(collection.orderBy.direction).toEqual('asc');
    });

    it("should maintain order by if already set on collection event if defined in config", function() {
        var collection = app.data.createBeanCollection(module);
        collection.orderBy = {
                field: 'fooby',
                direction: 'updownallaround'
            };

        var context = new Context({
            module: module,
            collection: collection
        });

        app.config.orderByDefaults = {
            'Cases': {
                field: 'case_number',
                direction: 'asc'
            }
        };

        // Prevent outgoing http request
        var stub = sinon.stub($, 'ajax');
        context.loadData();
        stub.restore();

        expect(context.get('collection').orderBy).toBeDefined();
        expect(context.get('collection').orderBy.field).toEqual('fooby');
        expect(context.get('collection').orderBy.direction).toEqual('updownallaround');
    });

    it("should prepare data for a link path", function() {
        var context = new Context({
            link: "contacts",
            parentModelId: 'xyz',
            parentModule: 'Opportunities',
            module: 'Contacts'
        });
        context.prepare();

        expect(context.get("parentModel")).toBeDefined();
        expect(context.get("parentModel").module).toEqual("Opportunities");
        expect(context.get("parentModel").id).toEqual("xyz");

        expect(context.get("collection")).toBeDefined();
        expect(context.get("collection").module).toEqual("Contacts");
        expect(context.get("collection").link).toBeDefined();
        expect(context.get("collection").link.name).toEqual("contacts");
        expect(context.get("collection").link.bean).toEqual(context.get("parentModel"));
    });

    it("should prepare data for a link path with pre-filled parent model", function() {
        var context = new Context({
            link: "contacts",
            parentModel: app.data.createBean("Opportunities", { id: "xyz "})
        });

        context.prepare();

        expect(context.get("collection")).toBeDefined();
        expect(context.get("collection").module).toEqual("Contacts");
        expect(context.get("collection").link).toBeDefined();
        expect(context.get("collection").link.name).toEqual("contacts");
        expect(context.get("collection").link.bean).toEqual(context.get("parentModel"));
    });

    it("should prepare data for a related record path", function() {
        var context = new Context({
            link: "contacts",
            parentModelId: 'xyz',
            parentModule: "Opportunities",
            modelId: 'asd'
        });

        context.prepare();

        expect(context.get("parentModel")).toBeDefined();
        expect(context.get("parentModel").module).toEqual("Opportunities");
        expect(context.get("parentModel").id).toEqual("xyz");

        expect(context.get("model")).toBeDefined();
        expect(context.get("model").module).toEqual("Contacts");
        expect(context.get("model").id).toEqual("asd");
        expect(context.get("model").link).toBeDefined();
        expect(context.get("model").link.name).toEqual("contacts");
        expect(context.get("model").link.bean).toEqual(context.get("parentModel"));
        expect(context.get("model").link.isNew).toBeTruthy();
    });

    it("should prepare data for a create related record path", function() {
        var context = new Context({
            link: "contacts",
            parentModelId: 'xyz',
            parentModule: "Opportunities",
            create: true
        });

        context.prepare();

        expect(context.get("parentModel")).toBeDefined();
        expect(context.get("parentModel").module).toEqual("Opportunities");
        expect(context.get("parentModel").id).toEqual("xyz");

        expect(context.get("model")).toBeDefined();
        expect(context.get("model").module).toEqual("Contacts");
        expect(context.get("model").isNew()).toBeTruthy();
        expect(context.get("model").link).toBeDefined();
        expect(context.get("model").link.name).toEqual("contacts");
        expect(context.get("model").link.bean).toEqual(context.get("parentModel"));
        expect(context.get("model").link.isNew).toBeTruthy();
    });

    it('should create child contexts for each one of its links in `collection` fields', function() {
        var context = new Context({
            modelId: '1234',
            module: 'Quotes'
        });

        context.prepare();

        // Get the links from collection fields.
        var links = [];
        var bean = context.get('model');
        _.each(bean.fieldsOfType('collection'), function(collection) {
            links = _.union(links, collection.links);
        });

        expect(context.children.length).toEqual(links.length);
    });

    it('should build a mixed bean collection for each one of its `collection` fields', function() {
        var context = new Context({
            modelId: '1234',
            module: 'Quotes'
        });

        context.prepare();
        var bean = context.get('model');
        var collectionFieldNames = _.pluck(bean.fieldsOfType('collection'), 'name');

        _.each(collectionFieldNames, function(fieldName) {
            expect(bean.get(fieldName) instanceof MixedBeanCollection).toBe(true);
        });
    });

    it("should not reset child contexts during resetLoadFlag when recursive is false", function() {
        var parent = new Context({module: "Opportunities"}),
            mock = {resetLoadFlag: sinon.stub()};
        parent.children.push(mock);
        parent.resetLoadFlag({ recursive: false });
        expect(mock.resetLoadFlag).not.toHaveBeenCalled();
        parent.resetLoadFlag();
        expect(mock.resetLoadFlag).toHaveBeenCalled();
    });

    it("should remove all events when clearing a context", function() {
        var context = new Context({
            modelId: '123',
            module: module
        });
        context.prepare();

        var childContext = new Context({
            modelId: '456',
            module: 'Contacts'
        });
        childContext.prepare();

        context.children.push(childContext);
        childContext.parent = context;

        var contextCollection = context.get('collection'),
            contextModel = context.get('model'),
            childContextCollection = childContext.get('collection'),
            childContextModel = childContext.get('model');

        context.clear();

        expect(_.size(context._events)).toBe(0);
        expect(_.size(contextCollection._events)).toBe(0);
        expect(_.size(contextModel._events)).toBe(0);
        expect(_.size(childContext._events)).toBe(0);
        expect(_.size(childContextCollection._events)).toBe(0);
        expect(_.size(childContextModel._events)).toBe(0);
    });

    describe("Child context", function() {

        it("should create and prepare child contexts from a parent model", function() {
            var model = app.data.createBean("Opportunities", { id: "xyz"});
            var context = new Context({
                module: "Opportunities",
                model: model
            });

            var subcontext = context.getChildContext({ module: "Contacts" });

            expect(context.children.length).toEqual(1);
            expect(subcontext.parent).toEqual(context);
            expect(subcontext.get("module")).toEqual("Contacts");

            var subcontext2 = context.getChildContext({ module: "Contacts" });
            expect(subcontext).toEqual(subcontext2);

            expect(context.children.length).toEqual(1);

            subcontext.prepare();
            expect(subcontext.get("model")).toBeDefined();
            expect(subcontext.get("module")).toEqual("Contacts");

            context.clear();
            expect(context.children.length).toEqual(0);
            expect(context.parent).toBeNull();
        });

        it("should create and prepare child contexts from a link name", function() {
            var model = app.data.createBean("Opportunities", { id: "xyz"});
            var context = new Context({
                module: "Opportunities",
                model: model
            });

            var subrelatedContext = context.getChildContext({ link: "contacts" });

            expect(context.children.length).toEqual(1);

            expect(subrelatedContext.parent).toEqual(context);
            expect(subrelatedContext.get("link")).toEqual("contacts");
            expect(subrelatedContext.get("parentModel")).toEqual(model);

            var subrelatedContext2 = context.getChildContext({ link: "contacts" });
            expect(subrelatedContext).toEqual(subrelatedContext2);

            expect(context.children.length).toEqual(1);

            subrelatedContext.prepare();

            expect(subrelatedContext.get("model")).toBeDefined();
            expect(subrelatedContext.get("model").module).toEqual("Contacts");
            expect(subrelatedContext.get("parentModule")).toEqual("Opportunities");
            expect(subrelatedContext.get("module")).toEqual("Contacts");

            context.clear();
            expect(context.children.length).toEqual(0);

            subrelatedContext.clear();
            expect(context.parent).toBeNull();
        });

        it("should create and prepare a new child context when forceNew attribute is set to true and child context with same module name exists", function() {
            var model = app.data.createBean("Opportunities", { id: "xyz"}),
                context = new Context({
                    module: "Opportunities",
                    model: model
                }),
                childContext = context.getChildContext({
                    module: 'Opportunities',
                    prop1: "Prop1",
                    prop2: "Prop2",
                    prop3: "Prop3"
                });

            expect(childContext.get("prop1")).toEqual("Prop1");
            expect(childContext.attributes).toEqual({module: 'Opportunities', prop1: "Prop1", prop2: "Prop2", prop3: "Prop3"});

            expect(context.children.length).toEqual(1);

            var childContext2 = context.getChildContext({
                module: 'Opportunities',
                forceNew: true
            });

            expect(childContext2.attributes).toEqual({module : 'Opportunities'});

            expect(context.children.length).toEqual(2);
        });

        it("should return existing child context when context with module name exists", function() {
            var model = app.data.createBean("Opportunities", { id: "xyz"}),
                context = new Context({
                    module: "Opportunities",
                    model: model
                }),
                childContext = context.getChildContext({
                    module: 'Opportunities',
                    prop1: "Prop1",
                    prop2: "Prop2",
                    prop3: "Prop3"
                });

            expect(childContext.get("prop1")).toEqual("Prop1");
            expect(childContext.attributes).toEqual({module: 'Opportunities', prop1: "Prop1", prop2: "Prop2", prop3: "Prop3"});

            expect(context.children.length).toEqual(1);

            var childContext2 = context.getChildContext({
                module: 'Opportunities'
            });

            expect(childContext2.get("prop1")).toEqual("Prop1");
            expect(childContext2.attributes).toEqual({module: 'Opportunities', prop1: "Prop1", prop2: "Prop2", prop3: "Prop3"});

            expect(context.children.length).toEqual(1);
        });

        it("should return existing child context when context with matching cid exists", function() {
            var model = app.data.createBean("Opportunities", { id: "xyz"}),
                context = new Context({
                    module: "Opportunities",
                    model: model
                }),
                childContext = context.getChildContext({
                    module: 'Opportunities',
                    prop1: "Prop1",
                    prop2: "Prop2",
                    prop3: "Prop3"
                });

            expect(childContext.get("prop1")).toEqual("Prop1");
            expect(childContext.attributes).toEqual({module: 'Opportunities', prop1: "Prop1", prop2: "Prop2", prop3: "Prop3"});

            expect(context.children.length).toEqual(1);

            var childContext2 = context.getChildContext({
                cid: childContext.cid
            });

            expect(childContext2.get("prop1")).toEqual("Prop1");
            expect(childContext2.attributes).toEqual({module: 'Opportunities', prop1: "Prop1", prop2: "Prop2", prop3: "Prop3"});

            expect(context.children.length).toEqual(1);
        });

        it("should fire an event when a child context is added", function() {
            var model = app.data.createBean("Opportunities", { id: "xyz"}),
                context = new Context({
                    module: "Opportunities",
                    model: model
                }),
                childContext,
                mock = sinon.mock(context);

            mock.expects("trigger").once().calledWith("context:child:add");

            childContext = context.getChildContext({
                module: 'Opportunities',
                prop1: "Prop1",
                prop2: "Prop2",
                prop3: "Prop3"
            });

            mock.verify();
        });
    });

    it("should be able to indicate is data has been loaded", function() {
        var context = new Context({module:'Contacts'});
        context.prepare();

        expect((context.attributes.model instanceof Backbone.Model)).toBeTruthy();
        expect((context.attributes.collection instanceof Backbone.Collection)).toBeTruthy();

        expect(context.isDataFetched()).toBeFalsy();
        context.attributes.collection.dataFetched = true;
        expect(context.isDataFetched()).toBeTruthy();
        context.resetLoadFlag();
        expect(context.isDataFetched()).toBeFalsy();

        context._fetchCalled = true;
        expect(context.isDataFetched()).toBeTruthy();
        context.resetLoadFlag();
        expect(context.isDataFetched()).toBeFalsy();
    });

    it('should throw a warning if model is not a Bean', function() {
        var model = {module: 'Contacts', id: 'xyz'};
        var warn = sinon.stub(app.logger, 'warn');
        var fetchStub = sinon.stub(Backbone.Model.prototype, 'fetch');
        var context = new Context({
            model: model,
            module: module,
            modelId: 'xyz'
        });

        context.loadData();

        expect(fetchStub).not.toHaveBeenCalled();
        expect(warn).toHaveBeenCalled();
    });

    it("should pass the 'dataView' property as 'view' to the API", function() {
        var model = app.data.createBean(module, {id: 'xyz'});

        var context = new Context({
            modelId: 'xyz',
            module: module
        });

        var fetchStub = sinon.stub(Backbone.Model.prototype, 'fetch');
        context.set('dataView', 'foo');
        context.set('limit', 'foo');
        context.set('viewed', 'foo');
        context.prepare();
        context.loadData();

        expect(fetchStub).toHaveBeenCalled();
        expect(fetchStub.lastCall.args[0].view).toEqual('foo');
    });

    describe('addFields', function () {
        using('different data', [
            {
                fields: ['test'],
                fieldsArray: null,
                expectedFields: ['test'],
            },
            {
                fields: ['test'],
                fieldsArray: ['test2'],
                expectedFields: ['test2', 'test'],
            },
        ], function (provider) {
            it('addFields should union the fields', function () {
                var context = new Context({
                    fields: provider.fields
                });

                context.addFields(provider.fieldsArray);

                expect(context.get('fields')).toEqual(provider.expectedFields);
            });
        });
    });

    describe('reloadData', function () {
        it('should call fetch by default', function () {
            var fetchStub = sinon.stub(Backbone.Model.prototype, 'fetch');
            var model = app.data.createBean(module, {name: 'xyz'});
            var context = new Context({
                modelId: 'xyz',
                model: model
            });

            context.reloadData();

            expect(fetchStub).toHaveBeenCalled();
        });

        it('should not call fetch if skipFetch is true', function () {
            var fetchStub = sinon.stub(Backbone.Model.prototype, 'fetch');
            var model = app.data.createBean(module, {name: 'xyz'});
            var context = new Context({
                modelId: 'xyz',
                model: model,
                skipFetch: true
            });

            context.reloadData();

            expect(fetchStub).not.toHaveBeenCalled();
        });
    });

    describe('resetLoadFlag', function () {
        using('different data', [
            {
                options: null,
                expectedCollectionDataFetched: false,
                expectedModelDataFetched: false,
                expectedRecursiveCall: true
            },
            {
                options: {},
                expectedCollectionDataFetched: false,
                expectedModelDataFetched: false,
                expectedRecursiveCall: true
            },
            {
                options: {
                    recursive: false,
                },
                expectedCollectionDataFetched: false,
                expectedModelDataFetched: false,
                expectedRecursiveCall: false
            },
            {
                options: {
                    resetModel: false
                },
                expectedCollectionDataFetched: false,
                expectedModelDataFetched: true,
                expectedRecursiveCall: true
            },
            {
                options: {
                    resetCollection: false
                },
                expectedCollectionDataFetched: true,
                expectedModelDataFetched: false,
                expectedRecursiveCall: true
            },
            {
                options: {
                    resetCollection: false,
                    resetModel: false,
                },
                expectedCollectionDataFetched: true,
                expectedModelDataFetched: true,
                expectedRecursiveCall: true
            },

        ], function (data) {
            it('should reset the correct load flags based on the given options', function () {
                var model = app.data.createBean(module, {name: 'xyz'});
                var collection = app.data.createBeanCollection(module);

                var context = new Context({
                    model: model,
                    collection: collection,
                    module: module,
                });
                var childContext = context.getChildContext({});

                context.get('collection').dataFetched = true;
                context.get('model').dataFetched = true;
                context._fetchCalled = true;

                sinon.stub(context.children[0], 'resetLoadFlag');

                context.resetLoadFlag(data.options);

                expect(context._fetchCalled).toBe(false);
                expect(context.get('collection').dataFetched).toBe(data.expectedCollectionDataFetched);
                expect(context.get('model').dataFetched).toBe(data.expectedModelDataFetched);
                expect(childContext.resetLoadFlag.called).toBe(data.expectedRecursiveCall);
            });
        });
    });

    describe('when fetch flag is used', function() {

        it('should fetch collection respecting fetch', function() {

            let context = new Context({
                collection: new BeanCollection(),
                fetch: false,
            });
            let fetchStub = sinon.stub(context.get('collection'), 'fetch');

            context.loadData();
            expect(fetchStub).not.toHaveBeenCalled();

            context.set('fetch', true);
            context.loadData();
            expect(fetchStub).toHaveBeenCalled();

            context.unset('fetch');
            context.loadData();
            expect(fetchStub).not.toHaveBeenCalledTwice();

            context.resetLoadFlag();
            context.loadData();
            expect(fetchStub).toHaveBeenCalledTwice();
        });

        it('should fetch model respecting fetch', function() {

            let context = new Context({
                model: new Bean(),
                modelId: 'test-model-id',
                fetch: false,
            });
            let fetchStub = sinon.stub(context.get('model'), 'fetch');

            context.loadData();
            expect(fetchStub).not.toHaveBeenCalled();

            context.set('fetch', true);
            context.loadData();
            expect(fetchStub).toHaveBeenCalled();

            context.unset('fetch');
            context.loadData();
            expect(fetchStub).not.toHaveBeenCalledTwice();

            context.resetLoadFlag();
            context.loadData();
            expect(fetchStub).toHaveBeenCalledTwice();
        });

        it('should accept forceFetch option and bypass internal flag', function() {
            let context = new Context({
                collection: new BeanCollection(),
                fetch: false,
            });
            let fetchStub = sinon.stub(context.get('collection'), 'fetch');

            context.loadData();
            expect(fetchStub).not.toHaveBeenCalled();

            context.loadData({forceFetch: true});
            expect(fetchStub).toHaveBeenCalled();

            context = new Context({
                model: new Bean(),
                modelId: 'test-model-id',
                fetch: false,
            });
            fetchStub = sinon.stub(context.get('model'), 'fetch');

            context.loadData();
            expect(fetchStub).not.toHaveBeenCalled();

            context.loadData({forceFetch: true});
            expect(fetchStub).toHaveBeenCalled();
        });

        describe('with child contexts', function() {

            it('should update fetch flag recursively', function() {
                let context = new Context({
                    collection: new BeanCollection(),
                    fetch: false,
                });
                let c1 = context.getChildContext();
                let c2 = context.getChildContext();

                expect(c1.get('fetch')).toBeFalsy();
                expect(c2.get('fetch')).toBeFalsy();

                context.setFetch(true, {recursive: false});
                expect(context.get('fetch')).toBeTruthy();
                expect(c1.get('fetch')).toBeFalsy();
                expect(c2.get('fetch')).toBeFalsy();

                context.setFetch(true, {recursive: true});
                expect(context.get('fetch')).toBeTruthy();
                expect(c1.get('fetch')).toBeTruthy();
                expect(c2.get('fetch')).toBeTruthy();

                context.setFetch(false);
                expect(context.get('fetch')).toBeFalsy();
                expect(c1.get('fetch')).toBeFalsy();
                expect(c2.get('fetch')).toBeFalsy();

                context.setFetch(true);
                expect(context.get('fetch')).toBeTruthy();
                expect(c1.get('fetch')).toBeTruthy();
                expect(c2.get('fetch')).toBeTruthy();
            });
        });
    });
});
