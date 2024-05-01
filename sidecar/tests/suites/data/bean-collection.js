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
const DataManager = require('../../../src/data/data-manager');

describe('Data/BeanCollection', function() {
    var moduleName = 'Contacts';
    var metadata, app,
        dm = SUGAR.App.data;

    beforeEach(function() {
        app = SugarTest.app;
        app.config.maxQueryResult = 2;
        metadata = SugarTest.loadFixture("metadata");
        dm.reset();
    });

    it('should be a collection of beans by default', function() {
        var a = new BeanCollection();
        a.add({1: 1});

        expect(a.models[0] instanceof Bean).toBe(true);
    });

    describe('Passing options on initialize', function() {
        it('should set them as persistent options', function() {
            let coll = new BeanCollection(moduleName, {prop1: 'test1', prop2: 'test2'});

            expect(coll.getOption('prop1')).toEqual('test1');
        });
    });

    using('different options', [
        {
            callLeanCount : true, // has link so should call relatedLeanCount
            options : {
                params: {},
                apiOptions: {},
                link: {
                    bean: {
                        id: '123',
                        module: 'Accounts'
                    },
                    name: 'linkName'
                }
            }
        },
        {
            callLeanCount : false, // no link so should not call relatedLeanCount
            options : {
                params: {},
                apiOptions: {}
            }
        }
    ], function(params) {
        it('should call relatedLeanCount once if link exists', function() {
            var coll = new BeanCollection(moduleName, params.options);
            var stub = sinon.stub(SUGAR.App.data, 'parseOptionsForSync').callsFake(function() {return params.options;});
            var func = params.callLeanCount ? 'relatedLeanCount' : 'records';
            sinon.spy(SUGAR.App.api, func);

            coll.hasAtLeast(5, params.options);
            if (params.callLeanCount) {
                expect(SUGAR.App.api.relatedLeanCount).toHaveBeenCalledOnce();
            } else {
                expect(SUGAR.App.api.records).toHaveBeenCalledOnce();
            }
            stub.restore();
        });
    });

    using('different values for `total`', [
        {
            total: 20,
            expectedTotal: 19
        },
        {
            total: 0,
            expectedTotal: 0
        }
    ], function(data) {
        it('should decrement the total when removing a model', function() {
            var beans, bean1, bean2;

            dm.declareModel(moduleName, metadata.modules[moduleName]);
            bean1 = dm.createBean(moduleName);
            bean2 = dm.createBean(moduleName);
            beans = dm.createBeanCollection(moduleName, [bean1, bean2]);

            beans.total = data.total;
            beans.remove(bean1);

            expect(beans.total).toEqual(data.expectedTotal);
        });
    });

    it("should get records for page +n from the current", function() {
        app.config.maxQueryResult = 1;

        var beans, contacts, syncSpy;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        beans = dm.createBeanCollection(moduleName);

        contacts = SugarTest.loadFixture("contacts");

        contacts.next_offset = 1;
        contacts.result_count = 1;
        contacts.records.pop();

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\?max_num=1/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);
        syncSpy = sinon.spy(beans, "fetch");

        beans.fetch();
        SugarTest.server.respond();

        beans.paginate();
        SugarTest.server.respond();
        expect(syncSpy).toHaveBeenCalledTwice();
        expect(syncSpy.getCall(1).args[0].offset).toEqual(1);
        syncSpy.restore();
    });
    it("should get records for page -n from the current", function() {
        app.config.maxQueryResult = 1;

        var beans, contacts, syncSpy, options;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        beans = dm.createBeanCollection(moduleName);

        contacts = SugarTest.loadFixture("contacts");

        contacts.next_offset = 1;
        contacts.result_count = 1;
        contacts.records.pop();

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\?max_num=1/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);
        syncSpy = sinon.spy(beans, "fetch");
        beans.fetch();
        SugarTest.server.respond();

        beans.paginate();
        expect(syncSpy).toHaveBeenCalledTwice();
        expect(syncSpy.getCall(1).args[0].offset).toEqual(1);
        options = {page: -1};
        beans.paginate(options);
        SugarTest.server.respond();
        expect(syncSpy.getCall(2).args[0].offset).toEqual(-1);

        syncSpy.restore();
    });

    describe("paginate", function(){
        var fetchStub, beans;

        beforeEach(function(){
            var moduleName = "Contacts";
            dm.declareModel(moduleName, metadata.modules[moduleName]);
            beans = dm.createBeanCollection(moduleName);
            fetchStub = sinon.stub(beans, 'fetch');
        });

        afterEach(function(){
            fetchStub.restore();
        });

        it("should pass options.limit as fetch limit when it is set", function(){
            var options = {limit: 7};
            beans.paginate(options)
            expect(fetchStub.calledOnce).toBe(true);
            expect(fetchStub.args[0][0].limit).toEqual(options.limit);
        });

        it("should adjust offset based on options.limit when it is set", function(){
            var options = {
                limit: 7,
                page: 3
            };
            beans.paginate(options)
            expect(fetchStub.calledOnce).toBe(true);
            var fetchOptions = fetchStub.args[0][0];
            expect(fetchOptions.offset).toEqual(options.limit * 2);  //offset 2 page lengths to get 3rd page
        });

    });

    describe("getPageNumber", function(){
        var beans;

        beforeEach(function(){
            var moduleName = "Contacts";
            dm.declareModel(moduleName, metadata.modules[moduleName]);
            beans = dm.createBeanCollection(moduleName);
            app.config.maxQueryResult = 2;
        });

        it("should use options.limit as page length when available", function(){
            var options = {limit: 7};
            beans.offset = 14;
            expect(beans.getPageNumber(options)).toEqual(2);
        });

        it("should use maxQueryResult as page length by default", function(){
            beans.offset = 14;
            expect(beans.getPageNumber({})).toEqual(7);
        });
    });

    it("should append records for page +n", function() {
        app.config.maxQueryResult = 1;

        var beans, contacts, syncSpy, subSetContacts, server;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        beans = dm.createBeanCollection(moduleName);

        contacts = SugarTest.loadFixture("contacts");
        subSetContacts = contacts;
        subSetContacts.next_offset = 1;
        subSetContacts.result_count = 1;
        subSetContacts.records.pop();

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\?max_num=1/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(subSetContacts)]);
        syncSpy = sinon.spy(beans, "fetch");
        beans.fetch();

        SugarTest.server.respond();
        SugarTest.server.restore();
        contacts = SugarTest.loadFixture("contacts");

        contacts.records.shift();
        server = sinon.fakeServer.create();

        server.respondWith("GET", /.*\/rest\/v10\/Contacts\?offset=1&max_num=1/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);

        beans.paginate({add: true});
        server.respond();

        expect(beans.models.length).toEqual(2);
    });

    it("should get records by order by", function() {
        app.config.maxQueryResult = 1;
        var ajaxSpy = sinon.spy($, 'ajax'),
            beans, contacts, subSetContacts;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        beans = dm.createBeanCollection(moduleName);

        contacts = SugarTest.loadFixture("contacts");
        subSetContacts = contacts;
        beans.orderBy = {
            field: "bob",
            direction: "asc"
        };

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\?max_num=1&orderBy=bob%3Aasc/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(subSetContacts)]);
        beans.fetch();
        SugarTest.server.respond();
        expect(ajaxSpy.getCall(1).args[0].url).toMatch(/.*\/rest\/v10\/Contacts\?max_num=1&order_by=bob%3Aasc/);
        ajaxSpy.restore();
    });

    it("should get records assigned to me", function() {
        app.config.maxQueryResult = 1;
        var ajaxSpy = sinon.spy($, 'ajax'),
            beans, contacts;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        beans = dm.createBeanCollection(moduleName);

        contacts = SugarTest.loadFixture("contacts");

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\?max_num=1&my_items=1/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);
        beans.fetch({
            myItems: true
        });
        SugarTest.server.respond();
        expect(ajaxSpy.getCall(1).args[0].url).toMatch(/.*\/rest\/v10\/Contacts\?max_num=1&my_items=1/);
        ajaxSpy.restore();
    });

    it("should get records marked as favorites", function() {
        app.config.maxQueryResult = 1;
        var ajaxSpy = sinon.spy($, 'ajax'),
            beans, contacts;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        beans = dm.createBeanCollection(moduleName);

        contacts = SugarTest.loadFixture("contacts");

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\?max_num=1&favorites=1/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);
        beans.fetch({
            favorites: true
        });
        SugarTest.server.respond();
        expect(ajaxSpy.getCall(1).args[0].url).toMatch(/.*\/rest\/v10\/Contacts\?max_num=1&favorites=1/);
        ajaxSpy.restore();
    });

    it("should get records by search query", function() {
        app.config.maxQueryResult = 1;
        var ajaxSpy = sinon.spy($, 'ajax'),
            beans, contacts;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        beans = dm.createBeanCollection(moduleName);

        contacts = SugarTest.loadFixture("contacts");

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/globalsearch\?max_num=1&q=Pupochkin&module_list=Contacts$/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);
        beans.fetch({
            query: "Pupochkin",
            apiOptions: {
                useNewApi: true
            }
        });
        SugarTest.server.respond();
        expect(ajaxSpy.getCall(1).args[0].url).toMatch(/.*\/rest\/v10\/globalsearch\?max_num=1&q=Pupochkin&module_list=Contacts$/);
        expect(beans.at(0).searchInfo).toBeDefined();
        expect(beans.at(0).searchInfo.highlighted).toBeDefined();
        expect(beans.at(0).searchInfo.score).toBeDefined();
        ajaxSpy.restore();
    });

    it("should get the current page number", function() {
        app.config.maxQueryResult = 1;

        var beans, p;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        beans = dm.createBeanCollection(moduleName);

        beans.offset = 3;
        app.config.maxQueryResult = 2;

        p = beans.getPageNumber();
        expect(p).toEqual(2);
    });
    it("should be able to reset pagination", function() {
        app.config.maxQueryResult = 1;

        var beans;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        beans = dm.createBeanCollection(moduleName);

        beans.offset = 3;
        app.config.maxQueryResult = 2;

        beans.resetPagination();
        expect(beans.offset).toEqual(0);
        expect(beans.getPageNumber()).toEqual(1);
        expect(beans.next_offset).toEqual(0);

        beans = dm.createBeanCollection(moduleName, [], { offset: 3, next_offset: 4, page: 3 });
        beans.page = 4;
        beans.offset = 4;
        beans.next_offset = 5;

        beans.resetPagination();

        expect(beans.offset).toEqual(3);
        expect(beans.next_offset).toEqual(0);
        expect(beans.page).toEqual(3);
    });

    it('should actually reset pagination', function() {
        var module = 'Contacts';
        var sspy;
        var options = {
            params: {
                order_by: 'last_name'
            },
            limit: 1
        };
        var cont1 = {
            next_offset: 1,
            result_count: 1,
            records: [
                {
                    id: '1',
                    last_name: '1'
                }
            ]
        };
        var cont2 = {
            next_offset: 2,
            result_count: 1,
            records: [
                {
                    id: '2',
                    last_name: '2'
                }
            ]
        };

        // Set up fake server.
        SugarTest.seedFakeServer();
        // Request without offset
        SugarTest.server.respondWith('GET', /.*\/rest\/v10\/Contacts((?!.*offset.*).*)/,
            [200, {'Content-Type': 'application/json'},
                JSON.stringify(cont1)]);

        // Request with offset
        SugarTest.server.respondWith('GET', /.*\/rest\/v10\/Contacts.*(offset)+.*/,
            [200, {'Content-Type': 'application/json'},
                JSON.stringify(cont2)]);

        // Create the collection
        dm.declareModel('Contacts', metadata.modules[module]);
        var collection = dm.createBeanCollection(module, [], options);

        expect(collection.getOption('params')).toEqual(options.params);

        // Do a simple fetch.
        sspy = sinon.spy();
        collection.fetch({
            success: sspy
        });
        SugarTest.server.respond();

        expect(sspy).toHaveBeenCalledWith(sinon.match.any, cont1.records, sinon.match.any);
        expect(collection.length).toBe(1);

        // Paginate.
        sspy = sinon.spy();
        collection.paginate({
            limit: 1,
            add: true,
            success: sspy
        });
        SugarTest.server.respond();

        expect(sspy).toHaveBeenCalledWith(sinon.match.any, cont2.records, sinon.match.any);
        expect(collection.length).toBe(2);

        // Reset pagination and do a simple fetch.
        sspy = sinon.spy();
        collection.resetPagination();
        collection.fetch({
            success: sspy
        });
        SugarTest.server.respond();

        expect(sspy).toHaveBeenCalledWith(sinon.match.any, cont1.records, sinon.match.any);
        expect(collection.length).toBe(1);
    });

    it("should keep track of fields when paginating", function() {
        var bean, beans, stub, firstCallArgs, secondCallArgs;
        dm.declareModel("Contacts", metadata.modules["Contacts"]);

        bean  = dm.createBean("Contacts");
        beans = dm.createBeanCollection("Contacts");
        stub   = sinon.stub(beans, 'fetch');
        beans.fetch({fields:['a','b','c']});

        beans.paginate();
        firstCallArgs = stub.getCall(0).args[0];
        expect(firstCallArgs.fields).toEqual(['a','b','c']);
        secondCallArgs = stub.getCall(1).args[0];
        expect(secondCallArgs.offset).toEqual(0);
        expect(secondCallArgs.page).toEqual(0);
        beans.fetch.restore();
    });

    it("should get records by filter", function() {
        app.config.maxQueryResult = 1;
        var ajaxSpy = sinon.spy($, 'ajax'),
            moduleName = "Contacts",
            collection, contacts, filterDef, filterUrl;

        dm.declareModel(moduleName, metadata.modules[moduleName]);

        collection = dm.createBeanCollection('Contacts');
        contacts = SugarTest.loadFixture('contacts');
        filterDef = {
            "filter": [
                {"name": {"$starts": "J"}}
            ],
            apiOptions: {
                useNewApi: true
            }
        };

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\?max_num=1&filter%5B0%5D%5Bname%5D%5B%24starts%5D=J/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);

        // pass in filter obj
        collection.fetch({
            filter: filterDef
        });
        SugarTest.server.respond();

        expect(ajaxSpy.getCall(1).args[0].url).toMatch(/.*\/rest\/v10\/Contacts\?max_num=1&filter%5B0%5D%5Bname%5D%5B%24starts%5D=J/);
        ajaxSpy.restore();
    });

    it("should generate a valid filter query", function() {
        app.config.maxQueryResult = 1;
        var ajaxSpy = sinon.spy($, 'ajax'),
            moduleName = "Contacts",
            collection, contacts, filterDef, filterUrl;

        dm.declareModel(moduleName, metadata.modules[moduleName]);

        collection = dm.createBeanCollection("Contacts"),
        contacts = SugarTest.loadFixture("contacts"),
        filterDef = {
            "$owner": ""
        };

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\?max_num=1&filter%5B0%5D%5B%24owner%5D\=/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);

        // pass in filter obj
        collection.fetch({
            filter: filterDef
        });
        SugarTest.server.respond();

        expect(ajaxSpy.getCall(1).args[0].url).toMatch(/.*\/rest\/v10\/Contacts\?max_num=1&filter%5B0%5D%5B%24owner%5D\=/);
        ajaxSpy.restore();
    });

    it("should should preserve orderBy when filtering", function() {
        app.config.maxQueryResult = 1;
        var ajaxSpy = sinon.spy($, 'ajax'),
            moduleName = "Contacts",
            collection, contacts, filterDef, filterUrl;

        dm.declareModel(moduleName, metadata.modules[moduleName]);

        collection = dm.createBeanCollection("Contacts");
        contacts = SugarTest.loadFixture("contacts");
        filterDef = {
            "filter": {
                "name": {"$starts": "J"}
            }
        };

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\?max_num=1&order_by=name%3Adesc&filter%5B0%5D%5Bname%5D%5B%24starts%5D=J/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);

        // first do a fetch, specifying orderBy
        collection.orderBy = {
            field: "name",
            direction: "desc"
        };
        collection.fetch();

        // then do a fetch, this time passing in the filter object
        collection.fetch({
            filter: filterDef
        });
        SugarTest.server.respond();

        expect(ajaxSpy.getCall(2).args[0].url).toMatch(/.*\/rest\/v10\/Contacts\?max_num=1&order_by=name%3Adesc&filter%5B0%5D%5Bname%5D%5B%24starts%5D=J/);

        ajaxSpy.restore();
    });

    describe('BeanCollection traversing', function() {
        var app, collection, fetchStub,
            dm = SUGAR.App.data,
            fakeCallback;

        beforeEach(function() {
            app = SugarTest.app;
            metadata = SugarTest.loadFixture('metadata');
            dm.reset();
            dm.declareModel('Contacts', metadata.modules.Contacts);
            collection = dm.createBeanCollection('Contacts');
            collection.add([
                dm.createBean('Contacts', {'first_name': 'John1', 'last_name': 'Dow', 'id': '1'}),
                dm.createBean('Contacts', {'first_name': 'John2', 'last_name': 'Dow', 'id': '2'}),
                dm.createBean('Contacts', {'first_name': 'John3', 'last_name': 'Dow', 'id': '3'})
            ]);
            fetchStub = sinon.stub(collection, 'fetch');
            fakeCallback = sinon.spy();
        });

        afterEach(function(){
            fakeCallback.resetHistory();
            fetchStub.restore();
        });

        it('Should return existing next record', function() {
            var currentModel = _.first(collection.where({'first_name': 'John2'}));
            collection.getNext(currentModel,fakeCallback);
            expect(fakeCallback.args[0][0].get('first_name')).toEqual('John3');
            expect(fetchStub).not.toHaveBeenCalled();
        });

        it('Should return existing previous record', function() {
            var currentModel = _.first(collection.where({'first_name': 'John2'}));
            collection.getPrev(currentModel, fakeCallback);
            expect(fakeCallback.args[0][0].get('first_name')).toEqual('John1');
            expect(fetchStub).not.toHaveBeenCalled();
        });

        using('possible variations of input data for paginate a record', [
            {
                'currentId': '3',
                'next_offset': '-1',
                'expectedPrev': true,
                'expectedNext': false
            }, {
                'currentId': '1',
                'next_offset': -1,
                'expectedPrev': false,
                'expectedNext': true
            }, {
                'currentId': '1',
                'next_offset': undefined,
                'expectedPrev': false,
                'expectedNext': true
            }, {
                'currentId': '4',
                'next_offset': -1,
                'expectedPrev': false,
                'expectedNext': false
            }, {
                'currentId': '2',
                'next_offset': -1,
                'expectedPrev': true,
                'expectedNext': true
            }, {
                'currentId': '2',
                'next_offset': 3,
                'expectedPrev': true,
                'expectedNext': true
            }],  function(testData)  {
            it('Checking previous records', function() {
                var currentModel = dm.createBean('Contacts', {'id': testData.currentId});
                expect(collection.hasPreviousModel(currentModel)).toBe(testData.expectedPrev);
            });
            it('Checking next records', function() {
                var currentModel = dm.createBean('Contacts', {'id': testData.currentId});
                expect(collection.hasNextModel(currentModel)).toBe(testData.expectedNext);
            });
        });

        it('Fetching next records from server', function() {
            var currentModel = _.first(collection.where({'first_name': 'John3'}));
            collection.next_offset = 3;
            expect(collection.hasNextModel(currentModel)).toBe(true);
            expect(fetchStub).not.toHaveBeenCalled();
            collection.getNext(currentModel, fakeCallback);
            expect(fetchStub).toHaveBeenCalled();
        });
    });

    describe('fetch', function() {
        var bean, beanStub, module = 'Cases';

        beforeEach(function() {
            dm.declareModel(module, metadata.modules[module]);
            bean = app.data.createBeanCollection(module);
            beanStub = sinon.stub(Backbone.Collection.prototype, 'fetch');
        });

        afterEach(function() {
            sinon.restore();
            beanStub = null;
            bean = null;
        });

        it('should execute with reset: true by default', function() {
            bean.fetch();
            expect(beanStub).toHaveBeenCalledWith({
                // TODO: ideally we should not pass these undefined options.
                fields: null,
                myItems: void 0,
                favorites: void 0,
                query: void 0,
                reset: true
            });
        });

        it('should execute with whatever reset value was passed in', function() {
            bean.fetch({reset: false});
            expect(beanStub).toHaveBeenCalledWith({
                fields: null,
                myItems: void 0,
                favorites: void 0,
                query: void 0,
                reset: false
            });
        });

        it('should allow one-time options', function() {
            bean.fetch({once: 'abc'});
            expect(beanStub).toHaveBeenCalledWith({
                once: 'abc',
                fields: null,
                myItems: void 0,
                favorites: void 0,
                query: void 0,
                reset: true
            });
            beanStub.resetHistory();

            bean.fetch();
            expect(beanStub).toHaveBeenCalledWith();
        });

        it('should allow persistent options', function() {
            bean.setOption('always', '123');

            bean.fetch();
            expect(beanStub).toHaveBeenCalledWith({
                always: '123',
                fields: null,
                myItems: void 0,
                favorites: void 0,
                query: void 0,
                reset: true
            });
            beanStub.resetHistory();

            // call with options
            bean.fetch({once: 'abc'});
            expect(beanStub).toHaveBeenCalledWith({
                always: '123',
                once: 'abc',
                fields: null,
                myItems: void 0,
                favorites: void 0,
                query: void 0,
                reset: true
            });
        });

        it('should allow to extend persistent options', function() {
            bean.setOption('always', '123');

            bean.fetch({always: 'another'});
            expect(beanStub).toHaveBeenCalledWith({
                always: 'another',
                fields: null,
                myItems: void 0,
                favorites: void 0,
                query: void 0,
                reset: true
            });
            beanStub.resetHistory();

            // verify the override did not change the persistent option
            bean.fetch();
            expect(beanStub).toHaveBeenCalledWith({
                always: '123',
                fields: null,
                myItems: void 0,
                favorites: void 0,
                query: void 0,
                reset: true
            });
        });
    });

    describe('Managing the deltas', function () {
        let bean, collection;
        beforeEach(function() {
            SugarTest.seedMetadata();
            dm.declareModel(moduleName, metadata.modules[moduleName]);
            dm.declareModel('Accounts', metadata.modules['Accounts']);
            bean = app.data.createBean('Accounts', {id: '1234'});
            collection = bean.getRelatedCollection('contacts');
        });

        it('should return the same a `Backbone.Collection#remove` does when calling remove with a null or empty argument', function() {
            collection.add({id: '5678'});

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

        it('should reset the delta on sync', function() {
            collection.add({id: '5678'});
            expect(collection.hasDelta()).toBe(true);

            SugarTest.seedFakeServer();
            var server = SugarTest.server;
            server.respondWith('GET', /.*\/rest\/v10\/Accounts\/1234\/link\/contacts.*/,
                [200, {'Content-Type': 'application/json'},
                    JSON.stringify({records: [{id: '5678'}]})]);
            collection.fetch();
            server.respond();

            expect(collection.hasDelta()).toBe(false);
        });

        it('should not handle the delta if the collection is not a related collection', function() {
            let collection = app.data.createBeanCollection(moduleName);
            collection.add({id: '5678'});
            expect(collection.hasDelta()).toBe(false);

            collection.remove(collection.at(0));
            expect(collection.hasDelta()).toBe(false);
        });

        describe('add', function() {
            it('should add a newly created model to the `_create` array', function() {
                expect(collection.hasDelta()).toBe(false);
                let bean = collection.add({name: 'foo', description: 'I am a foo'});

                expect(_.isEqual(collection._create, [bean])).toBe(true);
            });

            it('should add the existing models to the `_add` array', function() {
                let model1 = collection.add({id: '1234'});

                expect(_.isEqual(collection._add, [model1])).toBe(true);

                let model2 = collection.add({id: '5678'});

                expect(_.isEqual(collection._add, [model1, model2])).toBe(true);
            });

            it('should remove an existing model from the `_delete` array when it is removed and added back', function () {
                SugarTest.seedFakeServer();
                var server = SugarTest.server;
                server.respondWith('GET', /.*\/rest\/v10\/Accounts\/1234\/link\/contacts.*/,
                    [200, { 'Content-Type': 'application/json' },
                        JSON.stringify({ records: [{ id: '1234' }, { id: '5678' }] })]);
                collection.fetch();
                server.respond();

                let bean = collection.at(0);

                collection.remove(bean);

                expect(_.isEqual(collection._delete, [bean])).toBe(true);
                expect(_.isEmpty(collection._add)).toBe(true);

                collection.add(bean);

                expect(_.isEmpty(collection._add)).toBe(true);
                expect(_.isEmpty(collection._delete)).toBe(true);
            });
        });

        describe('delete', function() {
            it('should add the removed models to the `_delete` array', function () {
                let response = [
                    {
                        id: '1111',
                        first_name:'Leatha',
                        last_name:'Cooke',
                        _module: 'Contacts',
                    },
                    {
                        id: '2222',
                        first_name:'Erika',
                        last_name:'Niswander',
                        _module: 'Contacts',
                    },
                ];
                SugarTest.seedFakeServer();
                var server = SugarTest.server;
                server.respondWith('GET', /.*\/rest\/v10\/Accounts\/1234\/link\/contacts.*/,
                    [200, { 'Content-Type': 'application/json' },
                        JSON.stringify({ records: response })]);
                collection.fetch();
                server.respond();

                expect(_.isEmpty(collection._add)).toBe(true);
                expect(_.isEmpty(collection._delete)).toBe(true);

                let bean1 = collection.at(0);
                let bean2 = collection.at(1);

                collection.remove(bean1);
                collection.remove(bean2);

                expect(_.isEqual(collection._delete, [bean1, bean2])).toBe(true);

            });

            it('should remove an existing model from the `_add` array when it is added then removed', function () {
                collection.add({id: '1234', name: 'foo', description: 'I am a foo'});
                let newModel = collection.at(0);

                expect(_.isEqual(collection._add, [newModel])).toBe(true);

                collection.remove(newModel);

                expect(_.isEmpty(collection._add)).toBe(true);
                expect(_.isEmpty(collection._delete)).toBe(true);
            });

            it('should remove an new model from the `_create` array when it is added then removed', function () {
                collection.add({name: 'foo', description: 'I am a foo'});
                let newModel = collection.at(0);

                expect(_.isEqual(collection._create, [newModel])).toBe(true);

                collection.remove(newModel);

                expect(_.isEmpty(collection._create)).toBe(true);
                expect(_.isEmpty(collection._delete)).toBe(true);
            });
        });

        describe('getDelta', function() {
            it('should return an object containing the information to be sent to the server', function() {
                let response = [
                    {
                        id: '1111',
                        first_name:'Leatha',
                        last_name:'Cooke',
                        _module: 'Contacts',
                    },
                    {
                        id: '2222',
                        first_name:'Erika',
                        last_name:'Niswander',
                        _module: 'Contacts',
                    },
                ];
                SugarTest.seedFakeServer();
                var server = SugarTest.server;
                server.respondWith('GET', /.*\/rest\/v10\/Accounts\/1234\/link\/contacts.*/,
                    [200, { 'Content-Type': 'application/json' },
                        JSON.stringify({ records: response })]);
                collection.fetch();
                server.respond();

                let bean1 = collection.at(0);
                let bean2 = collection.at(1);

                collection.remove(bean1);
                collection.remove(bean2);
                collection.add({name: 'contact1', description: 'I am contact1'});
                collection.add({name: 'contact2', description: 'I am contact2'});
                collection.add({id: '1234', name: 'contact3', description: 'I am contact3'});

                let expectedDelta = {
                    add: [{id: '1234'}],
                    create: [
                        // `field_0` is a default field defined in the fixtures.
                        {name: 'contact1', description: 'I am contact1', field_0: 100},
                        {name: 'contact2', description: 'I am contact2', field_0: 100}
                    ],
                    delete: ['1111', '2222'],
                };

                expect(collection.getDelta()).toEqual(expectedDelta);
            });

            it('should parse the `_add` array to keep only the ids and the relationship fields', function() {
                collection.add({id: '1234', name: 'contact1', description: 'I am contact1', account_rel_field: 'value1'});
                collection.add({id: '3456', name: 'contact2', description: 'I am contact2', account_rel_field: 'value2', account_rel_field2: 'value3'});

                const delta = collection.getDelta();

                expect(_.isEqual(delta.add,
                    [
                        {id: '1234', account_rel_field: 'value1'},
                        {id: '3456', account_rel_field: 'value2', account_rel_field2: 'value3'}
                    ]
                ));
            });
        });

        describe('reset', function() {
            it('should reset the delta correctly on collection reset', function () {
                collection.add({ name: 'foo', description: 'I am a foo' });
                collection.add([{ name: 'bar', description: 'I am a bar' }]);

                collection.reset([{ name: 'baz', description: 'I am a baz' }]);
                let newModel = collection.at(0);
                let expectedDelta = {
                    create: [{ name: 'baz', description: 'I am a baz', field_0: 100 }],
                    add: [],
                    delete: [],
                };

                expect(collection.getDelta()).toEqual(expectedDelta);

                collection.reset();

                expect(collection.hasDelta()).toBe(false);
            });
        });
    });

    describe('unsetOption', function() {
        beforeEach(function() {
            this.beanCollection = new BeanCollection();
            for (let i = 1; i <= 3; i++) {
                this.beanCollection.setOption('fetchOption' + i, 'fetchValue' + i);
            }
        });

        it('should unset all options if no key is passed', function() {
            this.beanCollection.unsetOption();
            for (let i = 1; i <= 3; i++) {
                expect(this.beanCollection.getOption('fetchOption' + i)).toBeUndefined();
            }
        });

        it('should unset the given option', function() {
            this.beanCollection.unsetOption('fetchOption1');
            expect(this.beanCollection.getOption()).toEqual({
                'fetchOption2': 'fetchValue2',
                'fetchOption3': 'fetchValue3',
            });
        });
    });

    describe('clone', function() {
        it('should clone the collection, including the link', function() {
            let collection = new BeanCollection([new Bean({a: 'a'}), new Bean({b: 'b'})], {
                link: {
                    name: 'myLink',
                    bean: new Bean({c: 'c'}),
                }
            });
            let collectionClone = collection.clone();
            expect(collectionClone.models).toEqual(collection.models);
            expect(collectionClone.link).toEqual(collection.link);
        });
    });
});
