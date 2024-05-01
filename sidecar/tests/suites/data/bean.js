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
const MixedBeanCollection = require('../../../src/data/mixed-bean-collection');

describe('Data/Bean', function() {

    var app, dm, metadata;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.seedMetadata();
        dm = SugarTest.dm;
        metadata = SugarTest.metadata;
    });

    afterEach(function() {
        sinon.restore();
    });

    describe('getDefault', function() {
        var bean;
        beforeEach(function() {
            dm.declareModel("Cases", metadata.modules["Cases"]);
            bean = app.data.createBean("Cases");
        });
        afterEach(function() {
            bean = null;
        });
        it("should return empty object if setDefault not yet called", function() {
            expect(bean.getDefault()).toEqual({});
        });
        it("should return null if no default attribute was set yet", function() {
            expect(bean.getDefault('foo')).toEqual(null);
        });
        it("should set default attributes", function() {
            var attributes = {
                id: '1234',
                name: 'test'
            };
            bean.setDefault(attributes);
            expect(bean.getDefault()).toEqual(attributes);
        });
        it("should set individual default attribute", function() {
            var key = 'foo';
            var value = 'bar';
            var attributes = {};
            attributes[key] = value;
            bean.setDefault(key, value);
            expect(bean.getDefault()).toEqual(attributes);
            expect(bean.getDefault(key)).toEqual(value);
        });
        it("should set individual default attribute appending to pre-existing attributes", function() {
            var attributes = {
                id: '1234',
                name: 'test'
            };
            bean.setDefault(attributes);
            var key = 'foo';
            var value = 'bar';
            attributes[key] = value;
            bean.setDefault(key, value);
            expect(bean.getDefault()).toEqual(attributes);
        });
    });

    using('different _acl attributes', [
        {
            // Both field-level changes, and model-level changes
            attributes: {
                _acl: {
                    fields: {
                        name: {
                            create: 'no',
                            read: 'no',
                            write: 'no'
                        }
                    },
                    edit: 'no',
                    massupdate: 'no'
                },
            },
            syncedAttributes: {
                _acl: {
                    fields: {}
                },
            },
            expected: {
                aclFieldTrigger: true,
                aclTrigger: true
            }
        },
        {
            // Only field-level changes (empty case)
            attributes: {
                _acl: {
                    fields: {
                        name: {
                            create: 'no',
                            read: 'no',
                            write: 'no'
                        }
                    }
                },
            },
            syncedAttributes: {
                _acl: {
                    fields: {}
                },
            },
            expected: {
                aclFieldTrigger: true,
                aclTrigger: true
            }
        },
        {
            // Only model-level changes
            attributes: {
                _acl: {
                    edit: 'no',
                    massupdate: 'no',
                    fields: {}
                },
            },
            syncedAttributes: {
                _acl: {
                    fields: {}
                },
            },
            expected: {
                aclFieldTrigger: false,
                aclTrigger: true
            }
        },
        {
            // No changes
            attributes: {
                _acl: {
                    fields: {}
                },
            },
            syncedAttributes: {
                _acl: {
                    fields: {}
                },
            },
            expected: {
                aclFieldTrigger: false,
                aclTrigger: false
            }
        },
        {
            // Different fields
            attributes: {
                _acl: {
                    fields: {
                        name: {
                            create: 'no',
                            read: 'no',
                            write: 'no'
                        }
                    }
                },
            },
            syncedAttributes: {
                _acl: {
                    fields: {
                        industry: {
                            create: 'no',
                            read: 'no',
                            write: 'no'
                        }
                    }
                },
            },
            expected: {
                aclFieldTrigger: true,
                aclTrigger: true
            }
        },
        {
            // Mix of same and different fields
            attributes: {
                _acl: {
                    fields: {
                        industry: {
                            create: 'no',
                            read: 'no',
                            write: 'no'
                        }
                    }
                },
            },
            syncedAttributes: {
                _acl: {
                    fields: {
                        name: {
                            create: 'no',
                            read: 'no',
                            write: 'no'
                        },
                        industry: {
                            create: 'no',
                            read: 'no',
                            write: 'no'
                        }
                    }
                },
            },
            expected: {
                aclFieldTrigger: true,
                aclTrigger: true
            }
        },
        {
            // Mix of same/different fields, and same/different model-level
            // attrs
            attributes: {
                _acl: {
                    edit: 'no',
                    fields: {
                        industry: {
                            create: 'no',
                            read: 'no',
                            write: 'no'
                        }
                    }
                },
            },
            syncedAttributes: {
                _acl: {
                    edit: 'no',
                    massupdate: 'no',
                    fields: {
                        name: {
                            create: 'no',
                            read: 'no',
                            write: 'no'
                        },
                        industry: {
                            create: 'no',
                            read: 'no',
                            write: 'no'
                        }
                    }
                },
            },
            expected: {
                aclFieldTrigger: true,
                aclTrigger: true
            }
        }
    ], function(provider) {
        it('should trigger acl events accordingly', function() {
            SugarTest.seedFakeServer();
            dm.declareModel('Cases', metadata.modules['Cases']);
            var bean = app.data.createBean('Cases');
            var triggerSpy = sinon.spy(bean, 'trigger');
            var attributes = provider.attributes;
            bean.set({id:'1234'});
            bean.setSyncedAttributes(provider.syncedAttributes);
            var server = SugarTest.server;
            server.respondWith('GET', /.*\/rest\/v10\/Cases.*/,
                [200, {'Content-Type': 'application/json'},
                    JSON.stringify(attributes)]);
            bean.fetch();
            server.respond();

            expect(triggerSpy.calledWith('acl:change')).toEqual(provider.expected.aclTrigger);
            expect(triggerSpy.calledWith('acl:change:name')).toEqual(provider.expected.aclFieldTrigger);
        });
    });

    it("should set previous attributes", function() {
        dm.declareModel("Cases", metadata.modules["Cases"]);
        var bean = app.data.createBean("Cases");
        var attributes = {
            id: '1234',
            name: 'test'
        };
        bean.setSyncedAttributes(attributes);

        expect(bean.getSynced()).toEqual(attributes);
    });

    it("should set previous attributes on sync", function() {
        SugarTest.seedFakeServer();
        dm.declareModel("Cases", metadata.modules["Cases"]);
        var bean = app.data.createBean("Cases");
        var attributes = {
            id: '1234',
            name: 'test'
        };
        bean.set({id:'1234'});
        var server = SugarTest.server;
        server.respondWith("GET", /.*\/rest\/v10\/Cases.*/,
            [200, {"Content-Type": "application/json"},
                JSON.stringify(attributes)]);
        bean.fetch();
        server.respond();

        expect(bean.getSynced()).toEqual(attributes);
    });

    it("should not set previous attributes on sync", function() {

        sinon.stub(console, 'error');

        SugarTest.seedFakeServer();
        dm.declareModel("Cases", metadata.modules["Cases"]);
        var bean = app.data.createBean("Cases");
        var attributes = {
            id: '1234',
            name: 'test'
        };
        bean.set({id:'1234'});
        var server = SugarTest.server;
        server.respondWith("GET", /.*\/rest\/v10\/Cases.*/,
            [500, {"Content-Type": "application/json"}, ""]);
        bean.fetch();
        server.respond();

        expect(bean.getSynced()).not.toEqual(attributes);
    });

    it("should be able to reset attributes to previous attributes", function() {
        dm.declareModel("Cases", metadata.modules["Cases"]);
        var bean = app.data.createBean("Cases");
        var attributes = {
            id: '1234',
            name: 'test'
        };

        var changedAttributes = {
            id: '5123',
            name: 'notTest'
        };

        var partialChange = {
            name: 'anotherChange'
        };

        var partiallyChangedAttributes = {
            id: '1234',
            name: 'anotherChange'
        };
        bean.setSyncedAttributes(attributes);
        expect(bean.getSynced()).toEqual(attributes);
        bean.set(changedAttributes);
        expect(bean.attributes).toEqual(changedAttributes);
        bean.revertAttributes();
        expect(bean.attributes).toEqual(attributes);

        bean.set(partialChange);
        expect(bean.attributes).toEqual(partiallyChangedAttributes);
        bean.revertAttributes();
        expect(bean.attributes).toEqual(attributes);

        bean.set(partialChange);
        expect(bean.attributes).toEqual(partiallyChangedAttributes);
        bean.set(changedAttributes);
        expect(bean.attributes).toEqual(changedAttributes);
        bean.revertAttributes();
        expect(bean.attributes).toEqual(attributes);

    });


    describe('fetch', function() {
        var bean, beanStub, module = 'Cases';

        beforeEach(function() {
            dm.declareModel(module, metadata.modules[module]);
            bean = app.data.createBean(module);
            beanStub = sinon.stub(Backbone.Model.prototype, 'fetch');
        });

        afterEach(function() {
            sinon.restore();
            beanStub = null;
            bean = null;
        });

        it('should execute with no options', function() {
            bean.fetch();
            expect(beanStub).toHaveBeenCalledWith({});
        });

        it('should allow one-time options', function() {
            bean.fetch({once: 'abc'});
            expect(beanStub).toHaveBeenCalledWith({once: 'abc'});
            beanStub.resetHistory();

            bean.fetch();
            expect(beanStub).toHaveBeenCalledWith();
        });

        it('should allow persistent options', function() {
            bean.setOption('always', '123');

            bean.fetch();
            expect(beanStub).toHaveBeenCalledWith({always: '123'});
            beanStub.resetHistory();

            // call with options
            bean.fetch({once: 'abc'});
            expect(beanStub).toHaveBeenCalledWith({always: '123', once: 'abc'});
        });

        it('should allow to extend persistent options', function() {
            bean.setOption('always', '123');

            bean.fetch({always: 'another'});
            expect(beanStub).toHaveBeenCalledWith({always: 'another'});
            beanStub.resetHistory();

            // verify the override did not change the persistent option
            bean.fetch();
            expect(beanStub).toHaveBeenCalledWith({always: '123'});
        });

        it('should let you set, get, and unset persistent fetch options', function() {
            bean.setOption({
                fetchOption1: 'a',
                fetchOption2: 'b',
                fetchOption3: 'c',
            });

            expect(bean.getOption('fetchOption1')).toEqual('a');

            bean.unsetOption('fetchOption1');

            expect(bean.getOption('fetchOption1')).toBeUndefined();

            bean.unsetOption();

            expect(bean.getOption()).toEqual({});
        });
    });

    it("should be able to copy all fields from another bean", function() {
        dm.declareModel("Cases", metadata.modules["Cases"]);
        var source = app.data.createBean("Cases", {
            id: "123",
            case_number: "555",
            account_id: "zxc",
            account_name: "Account X",
            email: {
                email1: "blah@example.com",
                email2: "blah-blah@example.com"
            }
        });

        var bean = app.data.createBean("Cases");
        bean.copy(source);

        expect(bean.id).toBeUndefined();
        expect(bean.has("case_number")).toBeFalsy();
        expect(bean.get("account_id")).toEqual("zxc");
        expect(bean.get("account_name")).toEqual("Account X");
        expect(bean.get("email")).toBeDefined();
        expect(bean.get("email").email1).toEqual("blah@example.com");
        expect(bean.get("email").email2).toEqual("blah-blah@example.com");

        // Modify the copy and make sure the source is not affected
        var email = bean.get("email");
        email.email1 = "x@example.com";
        email.email2 = "y@example.com";
        expect(source.get("email").email1).toEqual("blah@example.com");
        expect(source.get("email").email2).toEqual("blah-blah@example.com");
    });

    it("should be able to copy specified fields from another bean", function() {
        dm.declareModel("Cases", metadata.modules["Cases"]);
        var source = app.data.createBean("Cases", {
            id: "123",
            case_number: "555",
            account_id: "zxc",
            account_name: "Account X"
        });

        var bean = app.data.createBean("Cases");
        bean.copy(source, ["case_number", "account_name"]);

        expect(bean.id).toBeUndefined();
        expect(bean.has("case_number")).toBeFalsy();
        expect(bean.has("account_id")).toBeFalsy();
        expect(bean.get("account_name")).toEqual("Account X");
    });

    it("should be able to avoid copying fields that are forbidden by metadata", function() {
        dm.declareModel('Accounts', app.metadata['Accounts']);
        var source = app.data.createBean('Accounts', {
            id: '123', // this shouldn't be copied
            assigned_user_id: '34456', // this should be copied
            date_created: '2013-05-01T00:10:00+00:00' // this should not be copied
        });

        var bean = app.data.createBean('Accounts');
        bean.copy(source);

        expect(bean.id).toBeUndefined();
        expect(bean.get('assigned_user_id')).toEqual('34456');
        expect(bean.has('date_created')).toBeFalsy();
    });

    it("should be able to see that a bean was not copied", function() {
        dm.declareModel('Accounts', app.metadata['Accounts']);
        var bean = app.data.createBean('Accounts');
        expect(bean.isCopy()).toBe(false);
    });

    it("should be able to see that a bean was copied", function() {
        dm.declareModel('Accounts', app.metadata['Accounts']);
        var source = app.data.createBean('Accounts', {
            assigned_user_id: '34456'
        });

        var bean = app.data.createBean('Accounts');
        bean.copy(source);

        expect(bean.isCopy()).toBe(true);
    });

    it("should be able to validate, when value is '0' ", function() {
        var moduleName = "Cases", bean, error, errors, stub;

        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, { case_number: 0});
        bean.fields.case_number.min = 66;

        stub = sinon.stub();
        runs(function() {
            bean._doValidate(bean.fields, {}, stub);
        });
        waitsFor(function() {
            return stub.called;
        });
        runs(function() {
            errors = stub.lastCall.args[2];
            expect(errors).toBeDefined();

            error = errors["case_number"];
            expect(error).toBeDefined();
            expect(error.minValue).toEqual(66);
        });
    });

    it("should be able to validate itself", function() {
        var moduleName = "Opportunities", bean, error, errors, stub, spy;

        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, { account_name: "Super long account name"});

        stub = sinon.stub();
        runs(function() {
            bean._doValidate(bean.fields, {}, stub);
        });
        waitsFor(function() {
            return stub.called;
        });
        runs(function() {
            errors = stub.lastCall.args[2];
            expect(errors).toBeDefined();

            error = errors["account_name"];
            expect(error).toBeDefined();
            expect(error.maxLength).toEqual(20);

            error = errors["name"];
            expect(error).toBeDefined();
            expect(error.required).toBeTruthy();
        });
        runs(function() {
            stub.resetHistory();
            spy = sinon.spy();
            bean.on("error:validation:account_name", spy);
            bean.on("error:validation:name", spy);
            bean.doValidate(null, stub);
        });
        waitsFor(function() {
           return stub.called;
        });
        runs(function() {
            var isValid = stub.lastCall.args[0];
            expect(isValid).toBeFalsy();
            expect(spy).toHaveBeenCalledTwice();
        });

        // Check the optional fields param as object
        runs(function() {
            stub.resetHistory();
            bean._doValidate({
                account_name: bean.fields["account_name"]
            }, {}, stub);
        });
        waitsFor(function() {
           return stub.called;
        });
        runs(function() {
            errors = stub.lastCall.args[2];
            expect(errors).toBeDefined();
            expect(errors["account_name"]).toBeDefined();
            expect(errors["name"]).toBeUndefined();
        });
        // Check the optional fields param as array
        runs(function() {
            stub.resetHistory();
            bean._doValidate(["account_name"], {}, stub);
        });
        waitsFor(function() {
           return stub.called;
        });
        runs(function() {
            errors = stub.lastCall.args[2];
            expect(errors).toBeDefined();
            expect(errors["account_name"]).toBeDefined();
            expect(errors["name"]).toBeUndefined();
        });
    });

    it("should provide a true response to validation callback when bean is valid", function() {
        var moduleName = "Opportunities", bean, stub;

        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, {name: "Satisfy required field"});

        stub = sinon.stub();
        runs(function() {
            bean.isValidAsync(null, stub);
        });
        waitsFor(function() {
           return stub.called;
        });
        runs(function() {
            expect(stub).toHaveBeenCalledWith(true);
        });
    });

    it("should provide a false response and hash of errors to validation callback when bean is invalid", function() {
        var moduleName = "Opportunities", bean, stub,
            expectedErrors = {
                account_name: {maxLength: 20},
                name: {required: true}
            };

        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, {account_name: "Super long account name"});

        stub = sinon.stub();
        runs(function() {
            bean.isValidAsync(null, stub);
        });
        waitsFor(function() {
            return stub.called;
        });
        runs(function() {
            expect(stub).toHaveBeenCalledWith(false, expectedErrors);
        });
    });

    it("should be able to add validation tasks", function() {
        var moduleName = "Opportunities", bean, stub, task1, task2,
            test1 = false,
            test2 = true;

        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, { account_name: "Super long account name"});

        stub = sinon.stub();
        task1 = function(fields, errors, callback) {
            test1 = true;
            callback(null, fields, errors);
        };
        task2 = function(fields, errors, callback) {
            test2 = false;
            callback(null, fields, errors);
        };
        runs(function() {
            bean.addValidationTask('test1', task1);
            bean.addValidationTask('test2', task2);
            bean.doValidate(null, stub);
        });
        waitsFor(function() {
           return stub.called;
        });
        runs(function() {
            expect(test1).toBeTruthy();
            expect(test2).toBeFalsy();
        });
    });

    it('should be able to remove validation tasks', function() {
        var moduleName = 'Opportunities', bean, stub, task1, task2,
            test1 = false,
            test2 = false;

        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, { account_name: 'Super long account name'});

        stub = sinon.stub();
        task1 = function(fields, errors, callback) {
            test1 = true;
            callback(null, fields, errors);
        };
        task2 = function(fields, errors, callback) {
            test2 = true;
            callback(null, fields, errors);
        };

        runs(function() {
            bean.addValidationTask('task1', task1);
            bean.addValidationTask('task2', task2);
            bean.removeValidationTask('task1');
            bean.doValidate(null, stub);
        });
        waitsFor(function() {
            return stub.called;
        });
        runs(function() {
            expect(test1).toBe(false);
            expect(test2).toBe(true);
        });
    });

    it("should trigger a 'validation:success' before the 'validation:complete' event on a valid bean", function(){
        var moduleName = "Contacts", bean, stub, triggerStub;
        bean = dm.createBean(moduleName);
        stub = sinon.stub();
        triggerStub = sinon.stub(bean, "trigger").callsFake(function(event){
            if(triggerStub.calledOnce){
                expect(event).toEqual('validation:start');
            } else if(triggerStub.calledTwice) {
                expect(event).toEqual("validation:success");
            } else {
                expect(event).toEqual("validation:complete");
            }
        });
        runs(function(){
            bean.doValidate(null, stub);
        });
        waitsFor(function() { return stub.called; });
        runs(function(){
            expect(triggerStub.calledThrice).toBeTruthy();
            triggerStub.restore();
        });
    });

    it("should trigger a 'validation:complete' event even on invalid bean", function(){
        var moduleName = "Contacts", bean, stub, triggerStub;
        bean = dm.createBean(moduleName);
        bean.fields = {field: {required: true, name: 'field'}};
        bean.set("field", "");
        stub = sinon.stub();
        triggerStub = sinon.stub(bean, "trigger").callsFake(function(event){
            expect(
                event === 'error:validation:field' ||
                event === 'error:validation' ||
                event === 'validation:start' ||
                event === 'validation:complete'
            ).toBeTruthy();
        });
        expect(triggerStub).not.toHaveBeenCalled();
        runs(function(){
            bean.doValidate(null, stub);
        });
        waitsFor(function() { return stub.called; });
        runs(function(){
            expect(triggerStub.lastCall.args[0]).toEqual('validation:complete');
            triggerStub.restore();
            expect(0).toEqual(0);
        });
    });

    it("should be populated with defaults upon instantiation", function() {
        var moduleName = "Contacts", bean;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, { first_name: "John" });
        expect(bean.get("field_0")).toEqual(100);
        expect(bean.get("first_name")).toEqual("John");
    });

    it("should not be populated with defaults upon instantiation if the model exists", function() {
        var moduleName = "Contacts", bean;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, { id: "xyz ", first_name: "John" });
        expect(bean.has("field_0")).toBeFalsy();
        expect(bean.get("first_name")).toEqual("John");
    });

    it("should not be populated with defaults if value already exists in the attribute", function() {
        var moduleName = "Contacts", bean;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, { field_0: 101 });
        expect(bean.get("field_0")).toBe(101);
    });

    it("should be able to create a collection of related beans", function() {
        dm.declareModels(metadata.modules);
        var opportunity = dm.createBean("Opportunities");
        opportunity.id = "opp-1";

        var contacts = opportunity.getRelatedCollection("contacts");

        expect(contacts.module).toEqual("Contacts");
        expect(contacts.link).toBeDefined();
        expect(contacts.link.name).toEqual("contacts");
        expect(contacts.link.bean).toEqual(opportunity);
        expect(opportunity._relatedCollections["contacts"]).toEqual(contacts);

        // Make sure we get the same instance (cached)
        expect(opportunity.getRelatedCollection("contacts")).toEqual(contacts);
    });

    it("should skip validation upon save if fieldsToValidate param is not specified", function() {
        var moduleName = "Contacts", bean;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName);

        var stub = sinon.stub(Backbone.Model.prototype, 'save');
        var mock = sinon.mock(bean);
        mock.expects("doValidate").never();

        bean.save();
        mock.verify();
        stub.restore();
    });

    it("should not skip validation upon save if fieldsToValidate param is specified", function() {
        var moduleName = "Contacts", bean;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName);

        var stub = sinon.stub(Backbone.Model.prototype, 'save');
        var mock = sinon.mock(bean);
        mock.expects("doValidate").once();

        bean.save(null, { fieldsToValidate: bean.fields });
        mock.verify();
        stub.restore();
    });

    it("should be able to check if it can have attachments", function() {
        var moduleName = "Contacts", bean;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName);

        expect(bean.canHaveAttachments()).toBeFalsy();

        moduleName = "KBDocuments";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName);

        expect(bean.canHaveAttachments()).toBeTruthy();
    });

    it("should be able to fetch file list", function() {
        var moduleName = "KBDocuments", bean;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, {id: "123"});

        var mock = sinon.mock(app.api);

        mock.expects("file").once().withArgs("read", {
            module: "KBDocuments",
            id: "123"
        });
        bean.getFiles();

        mock.verify();
    });

    it("should be able to mark itself as favorite", function() {
        var moduleName = "Contacts", bean;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, {id: "123", my_favorite: false});
        expect(bean.isFavorite()).toBeFalsy();

        var mock = sinon.mock(app.api);

        mock.expects("favorite").once().withArgs("Contacts", "123", true);
        bean.favorite(true);
        mock.verify();
        expect(bean.isFavorite()).toBeTruthy();

        mock = sinon.mock(app.api);
        mock.expects("favorite").once().withArgs("Contacts", "123", false);
        bean.favorite(false);
        mock.verify();
        expect(bean.isFavorite()).toBeFalsy();
    });

    it('should be followable', function() {
        let moduleName = 'Contacts';
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        let bean = dm.createBean(moduleName, {id: '123'});
        let stub = sinon.stub(bean, 'save');
        bean.follow(true);
        expect(stub).toHaveBeenCalledWith({ following: true });
    });

    describe('fieldsOfType', function() {
        var bean,
            moduleName = 'Cases';

        beforeEach(function() {
            dm.declareModel(moduleName, metadata.modules[moduleName]);
            bean = app.data.createBean(moduleName);
        });

        it('should return the id field from cases', function() {
            var fields = bean.fieldsOfType('id');
            expect(fields).toEqual([bean.fields.id]);
        });

        it('should return the the two relate fields', function() {
            var fields = bean.fieldsOfType('relate');
            expect(fields).toEqual([bean.fields.account_name, bean.fields.account_id]);
        });

        it('should return an empty array when no fields are found', function() {
            var fields = bean.fieldsOfType('invalid_field_type');
            expect(fields).toEqual([]);
        });
    });

    describe('Handling collection fields', function() {
        describe('get', function() {
            it('should create mixed bean collection for `collection` fields if one does not exist yet', function() {
                var bean = app.data.createBean('Emails');

                expect(bean.attributes.to).toBeUndefined();

                sinon.spy(app.data, 'createMixedBeanCollection');
                sinon.spy(bean, 'listenTo');

                var to = bean.get('to');

                expect(app.data.createMixedBeanCollection).toHaveBeenCalledOnce();
                expect(to instanceof MixedBeanCollection).toBe(true);
                expect(bean.attributes.to).toBeDefined();
                expect(bean.listenTo).toHaveBeenCalledOnce();

                app.data.createMixedBeanCollection.resetHistory();
                bean.listenTo.resetHistory();

                to = bean.get('to');

                expect(app.data.createMixedBeanCollection).not.toHaveBeenCalled();
                expect(bean.listenTo).not.toHaveBeenCalled();

                to.add(app.data.createBean('Contacts', {id: '1234', name: 'Ryan Ulmer'}));
                to.add(app.data.createBean('EmailAddresses', {id: '5678', email_address: 'test@example.com'}));

                expect(to.length).toEqual(2);
            });
        });

        describe('set', function() {
            it('should populate collection fields', function() {
                var bean = app.data.createBean('Quotes');
                var quote = SugarTest.loadFixture('quote');
                sinon.spy(app.data, 'createMixedBeanCollection');
                sinon.spy(bean, 'listenTo');
                bean.set('bundles', quote.bundles);

                expect(app.data.createMixedBeanCollection).toHaveBeenCalled();
                expect(bean.listenTo).toHaveBeenCalledOnce();

                expect(bean.get('bundles').length).toEqual(quote.bundles.records.length);
                expect(bean.get('bundles') instanceof MixedBeanCollection).toBe(true);
                expect(bean.get('bundles').at(0).attributes).toEqual(quote.bundles.records[0]);

                app.data.createMixedBeanCollection.resetHistory();
                bean.listenTo.resetHistory();
                sinon.spy(bean, 'stopListening');
                bean.set('bundles', app.data.createMixedBeanCollection([{id: '1234', name: 'recordX'}]));

                expect(bean.stopListening).toHaveBeenCalledOnce();
                expect(bean.listenTo).toHaveBeenCalledOnce();
            });

            it('should set `offset` property the collection fields based on the `next_offset` returned by the server', function() {
                let bean = app.data.createBean('Quotes');
                let quote = SugarTest.loadFixture('quote');
                bean.set('bundles', quote.bundles);

                expect(bean.get('bundles').next_offset).toEqual({product_bundles: -1});
                expect(bean.get('bundles').offset).toEqual({product_bundles: -1});
            });
        });

        describe('When a change occur in a collection field', function() {
            it('should trigger `change` in the bean accordingly to the `silent` option', function() {
                var bean = app.data.createBean('Quotes');
                var quote = SugarTest.loadFixture('quote');
                sinon.stub(bean, 'trigger');
                sinon.spy(bean, 'listenTo');
                bean.set('bundles', quote.bundles);
                expect(bean.listenTo).toHaveBeenCalledOnce();
                expect(bean.trigger).toHaveBeenCalledWith('change:bundles');

                bean.trigger.resetHistory();
                bean.listenTo.resetHistory();

                bean.set('bundles', quote.bundles, {silent: true});
                expect(bean.listenTo).not.toHaveBeenCalled();
                expect(bean.trigger).not.toHaveBeenCalledWith('change:bundles');

                bean.trigger.resetHistory();
                bean.listenTo.resetHistory();

                bean.get('bundles').add([{id:'1234', name:'recordX'}, {id: '3456', name: 'recordY'}]);

                expect(bean.listenTo).not.toHaveBeenCalled();
                expect(bean.trigger).toHaveBeenCalledWith('change:bundles');
                expect(bean.trigger).toHaveBeenCalledOnce();

                bean.trigger.resetHistory();

                bean.get('bundles').remove({id:'1234'});

                expect(bean.trigger).toHaveBeenCalledWith('change:bundles');
                expect(bean.trigger).toHaveBeenCalledOnce();

                bean.trigger.resetHistory();
                bean.get('bundles').add({id: '986745', name: 'recordW', _link: 'product_bundles'}, {silent: true});
                bean.get('bundles').reset([{id: '2345', name: 'recordW', _link: 'product_bundles'}]);

                expect(bean.trigger).toHaveBeenCalledWith('change:bundles');
                expect(bean.trigger).toHaveBeenCalledOnce();
            });

            using('different kind of updates on the collection', ['add', 'reset'], function(method) {
                it('should be in the changed attributes', function() {
                    let meeting = SugarTest.loadFixture('meeting');
                    let bean = app.data.createBean('Meetings', {id: '4c71aa46-b03b-11e6-9bd6-20c9d048ef45'});
                    SugarTest.seedFakeServer();
                    var server = SugarTest.server;
                    server.respondWith('GET', /.*\/rest\/v10\/Meetings\/4c71aa46-b03b-11e6-9bd6-20c9d048ef45.*/,
                        [200, { 'Content-Type': 'application/json' },
                            JSON.stringify(meeting)]);

                    bean.fetch();
                    server.respond();
                    let origInvitees = bean.get('invitees').toJSON();

                    expect(bean.changedAttributes(bean.getSynced())).toEqual(false);

                    bean.get('invitees')[method]([{_link: 'users', name: 'jeanMich'}]);

                    expect(bean.changedAttributes(bean.getSynced())).toEqual({invitees: origInvitees});
                });
            });
        });

        describe('revertAttributes', function() {
            it('should properly reverts collection fields', function() {
                let meeting = SugarTest.loadFixture('meeting');
                let bean = app.data.createBean('Meetings', {id: '4c71aa46-b03b-11e6-9bd6-20c9d048ef45'});

                SugarTest.seedFakeServer();
                var server = SugarTest.server;
                server.respondWith('GET', /.*\/rest\/v10\/Meetings\/4c71aa46-b03b-11e6-9bd6-20c9d048ef45.*/,
                    [200, { 'Content-Type': 'application/json' },
                    JSON.stringify(meeting)]);

                bean.fetch();
                server.respond();

                let users = bean.getRelatedCollection('users');
                let contacts = bean.getRelatedCollection('contacts');
                let leads = bean.getRelatedCollection('leads');
                let invitees = bean.get('invitees');

                //FIXME: When SC-6189 is implemented, we should use `toObject`
                //here.
                let origUsers = users.toJSON();
                let origLeads = leads.toJSON();
                let origContacts = contacts.toJSON();
                let origInvitees = invitees.toJSON();

                expect(bean.changedAttributes(bean.getSynced())).toEqual(false);

                invitees.add({_link: 'users', name: 'jeanMich'});
                invitees.remove(invitees.models[0]);
                invitees.remove(invitees.models[1]);

                expect(_.isEmpty(bean.changedAttributes(bean.getSynced()))).toBe(false);

                bean.revertAttributes();

                expect(invitees.toJSON()).toEqual(origInvitees);
                expect(users.toJSON()).toEqual(origUsers);
                expect(leads.toJSON()).toEqual(origLeads);
                expect(contacts.toJSON()).toEqual(origContacts);
            });
        });

        it('should reset the delta of changes in linked collections on sync', function() {
            dm.declareModel('Meetings', metadata.modules['Meetings']);
            var response = SugarTest.loadFixture('meeting');
            var bean = app.data.createBean('Meetings');
            // In a Meeting bean, `invitees` is a collection of Contacts, Leads and Users.
            var invitees = bean.get('invitees');
            invitees.add({name: 'baz', _link: 'leads'});
            invitees.add({id: '1234', _link: 'users'});

            var expectedDelta = {
                leads: {
                    create: [{name: 'baz', _link: 'leads'}],
                    add: [],
                    delete: [],
                },
                users: {
                    create: [],
                    add: [{id:'1234'}],
                    delete: [],
                }
            };
            expect(invitees.getDelta()).toEqual(expectedDelta);
            SugarTest.seedFakeServer();
            var server = SugarTest.server;
            server.respondWith('GET', /.*\/rest\/v10\/Meetings.*/,
                [200, {'Content-Type': 'application/json'},
                    JSON.stringify(response)]);
            bean.fetch();
            server.respond();

            expect(invitees.getDelta()).toEqual({});
        });

        describe('hasChanged', function() {
            it('should return `true` if a collection attribute has changed', function() {
                dm.declareModel('Meetings', metadata.modules['Meetings']);
                var meeting = SugarTest.loadFixture('meeting');

                let bean = app.data.createBean('Meetings', {id: '4c71aa46-b03b-11e6-9bd6-20c9d048ef45'});

                SugarTest.seedFakeServer();
                var server = SugarTest.server;
                server.respondWith('GET', /.*\/rest\/v10\/Meetings\/4c71aa46-b03b-11e6-9bd6-20c9d048ef45.*/,
                    [200, { 'Content-Type': 'application/json' },
                    JSON.stringify(meeting)]);

                bean.fetch();
                server.respond();

                expect(bean.hasChanged('invitees')).toBe(false);
                let invitees = bean.get('invitees');

                invitees.add({name: 'newInvitee', _link: 'leads'});

                expect(bean.hasChanged('invitees')).toBe(true);

                let newInvitee = invitees.at(5);

                invitees.remove(newInvitee);

                expect(bean.hasChanged('invitees')).toBe(false);
            });
        });
    });

    describe('toJSON', function() {
        var bean,
            moduleName = 'Cases';

        beforeEach(function() {
            dm.declareModel(moduleName, metadata.modules[moduleName]);
            bean = app.data.createBean(moduleName);
        });

        it('should return object of attributes', function() {
            var attr = {
                id: 1,
                case_number: 987
            };

            bean.set(attr);

            expect(bean.toJSON()).toEqual(attr);
        });

        it('should call toJSON of its attributes if toJSON function exists', function() {
            bean.set({
                id: 1,
                collection: new Backbone.Collection([{id: 2}, {id: 3}])
            });

            expect(bean.toJSON()).toEqual({
                id: 1,
                collection: [{id:2},{id:3}]
            });
        });

        it('should set the links data from collection fields', function() {
            dm.declareModel('Meetings', metadata.modules['Meetings']);
            var bean = app.data.createBean('Meetings');
            // In a Meeting bean, `invitees` is a collection of Contacts, Leads and Users.
            var invitees = bean.get('invitees');
            invitees.add({name: 'foo', _link: 'contacts'});
            invitees.add({name: 'bar', _link: 'contacts'});
            invitees.add({name: 'baz', _link: 'leads'});

            var expectedContactsDelta = {
                create: [
                    {
                        "name": "foo",
                        "_link": "contacts"
                    },
                    {
                        "name": "bar",
                        "_link": "contacts"
                    },
                ],
                add: [],
                delete: [],
            };
            var expectedLeadsDelta = {
                create: [
                    {
                        "name": "baz",
                        "_link": "leads"
                    }
                ],
                add: [],
                delete: [],
            };
            var json = bean.toJSON();

            expect(json['contacts']).toEqual(expectedContactsDelta);
            expect(json['leads']).toEqual(expectedLeadsDelta);
        });

        it('should not include collection fields or links data when a collection has not changed', function() {
            dm.declareModel('Meetings', metadata.modules['Meetings']);
            var bean = app.data.createBean('Meetings');
            var invitees = bean.get('invitees');
            var json = bean.toJSON();

            expect(json.invitees).toBeUndefined();
            expect(json.contacts).toBeUndefined();
            expect(json.leads).toBeUndefined();
            expect(json.users).toBeUndefined();
        });

        it('should only return attributes that are specified if the options.fields is set', function() {
            bean.set({
                id: 1,
                collection: new Backbone.Collection([{id: 2}, {id: 3}]),
                foo: 123,
                bar: 321
            });

            expect(bean.toJSON({fields: ['collection','bar']})).toEqual({
                collection: [{id:2},{id:3}],
                bar: 321
            });
        });
    });

    describe('merge', function() {
        it('should merge attributes', function() {
            let initialAttributes = {
                a: 'value a',
                b: 'value b',
            };
            let changes = {
                a: 'value A',
            };
            expect(Bean.prototype.merge(initialAttributes, changes)).toEqual({
                a: 'value A',
                b: 'value b',
            });
        });
    });

    describe('abortFetchRequest', function() {
        it('should abort the fetch request if it is in progress', function() {
            let moduleName = 'Cases';
            dm.declareModel(moduleName, metadata.modules[moduleName]);
            let bean = app.data.createBean(moduleName);
            sinon.stub(bean, 'getFetchRequest').returns({ uid: 5 });
            let stub = sinon.stub(app.api, 'abortRequest');
            bean.abortFetchRequest();
            expect(stub).toHaveBeenCalled();
        });
    });
});
