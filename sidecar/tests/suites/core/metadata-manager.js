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

const Template = require('../../../src/view/template');
const User = require('../../../src/core/user');
const Utils = require('../../../src/utils/utils');
const Language = require('../../../src/core/language');

describe('Core/MetadataManager', function() {
    var app, meta = fixtures.metadata;

    beforeEach(function () {
        app = SugarTest.app;
        SugarTest.seedMetadata(true);
        User.set('module_list', fixtures.metadata.module_list);
    });

    afterEach(function () {
        app.metadata.reset();
        Handlebars.templates = {};
        sinon.restore();
    });

    it('should get metadata hash', function() {
        expect(app.metadata.getHash()).toEqual("2q34aasdfwrasdfse");
    });

    it('should get all modules', function() {
        expect(app.metadata.getModules()['Cases']).toBeDefined();
        expect(app.metadata.getModules()['BOGUS']).not.toBeDefined();
    });
    it('should set the config on the global app object', function() {
        sinon.stub(app, 'setConfig');

        var origConfig = app.config;
        var newConfig = fixtures.metadata.config;
        app.metadata.set({config: newConfig});

        expect(app.setConfig).toHaveBeenCalledWith(newConfig);

        // return config to its orignal state
        app.metadata.set({config: origConfig});
        expect(app.setConfig).toHaveBeenCalledWith(origConfig);

        app.setConfig.resetHistory();

        app.metadata.set({prop1: {}});

        expect(app.setConfig).not.toHaveBeenCalled();
    });

    it('should merge metadata values except when _override_values is set', function() {
        app.metadata.set({foo:{a:"test1"}});
        app.metadata.set({foo:{b:"test2"}});
        expect(app.metadata._dev_data.foo.a).toBe("test1");
        expect(app.metadata._dev_data.foo.b).toBe("test2");
        app.metadata.set({bar:{a:"one"}});
        app.metadata.set({
            _override_values:["foo"],
            foo:{c:"test3"},
            bar:{b:"two"}
        });
        expect(app.metadata._dev_data.foo.a).toBeUndefined();
        expect(app.metadata._dev_data.foo.b).toBeUndefined();
        expect(app.metadata._dev_data.foo.c).toBe("test3");
        expect(app.metadata._dev_data.bar.a).toBe("one");
        expect(app.metadata._dev_data.bar.b).toBe("two");
    });

    it('should get config vars', function() {
        expect(app.metadata.getConfig()).toEqual(meta.config);
    });

    it('should get base currency id', function() {
        expect(app.metadata.getBaseCurrencyId()).toEqual('-99');
    });

    it('should get currencies', function() {
        expect(app.metadata.getCurrencies()).toEqual(meta.currencies);
    });

    it('should get currency', function() {
        expect(app.metadata.getCurrency('-99')).toEqual(meta.currencies['-99']);
    });

    it('should get company logo url', function() {
        expect(app.metadata.getLogoUrl()).toEqual('company_logo.jpg');
    });

    it('should get company logo dark mode url', function() {
        expect(app.metadata.getLogoUrl(true)).toEqual('company_logo_dark.jpg');
    });

    it('should get full module list', function() {
        expect(app.metadata.getFullModuleList()).toEqual(meta.full_module_list);
    });
    it('should get default layout defs', function() {
        expect(app.metadata.getLayout("Test", "list")).toEqual(meta.layouts.list.meta);
    });
    it('should get a specific layout', function() {
        expect(JSON.stringify(app.metadata.getLayout("Contacts", "detail")))
            .toBe(JSON.stringify(meta.modules.Contacts.layouts.detail.meta));
    });

    it('should return filter operators', function() {
        expect(app.metadata.getFilterOperators()).toBe(meta.filters.operators.meta);
    });

    it('should return filter operators for module', function() {
        var module = 'Accounts';
        var expectedOperators = _.extend(
            {},
            meta.filters.operators.meta,
            meta.modules[module].filters.operators.meta
        );

        expect(app.metadata.getFilterOperators(module)).toEqual(expectedOperators);
    });

    describe('getModuleNames', function() {
        var backupAllDisplayableModules, backupUserDisplayModules;
        beforeEach(function() {
            //Save previous data
            backupAllDisplayableModules = fixtures.metadata.modules_info;
            backupUserDisplayModules = User.get('module_list');

            //Test data
            fixtures.metadata.modules_info = {
                'Accounts': {
                    'display_tab': true,
                    'enabled': true,
                    'quick_create': false,
                    'show_subpanels': true,
                    'visible': false
                },
                'Bugs': {
                    'display_tab': true,
                    'enabled': true,
                    'quick_create': true,
                    'show_subpanels': false,
                    'visible': true
                },
                'Cases': {
                    'display_tab': true,
                    'enabled': true,
                    'quick_create': true,
                    'show_subpanels': true,
                    'visible': true
                },
                'Contacts': {
                    'display_tab': true,
                    'enabled': true,
                    'quick_create': false,
                    'show_subpanels': true,
                    'visible': false
                },
                'KBDocuments': {
                    'display_tab': true,
                    'enabled': true,
                    'quick_create': false,
                    'show_subpanels': false,
                    'visible': true
                },
                'Test': {
                    'display_tab': false,
                    'enabled': true,
                    'quick_create': false,
                    'show_subpanels': false,
                    'visible': false
                }
            };
            User.set('module_list', ['Contacts', 'Accounts', 'KBDocuments']); //Change order, remove Bugs and Cases
            // Remove the permission to create an Account.
            User.set('acl', {Accounts: {create: 'no'}});
        });
        afterEach(function() {
            //Restore previous data
            fixtures.metadata.modules_info = backupAllDisplayableModules;
            User.set('module_list', backupUserDisplayModules);
        });

        it('should return all modules order by user preferences', function() {
            var expectedList = ['Contacts', 'Accounts', 'KBDocuments', 'Bugs', 'Cases', 'Test'];
            expect(app.metadata.getModuleNames()).toEqual(expectedList);
            expect(app.metadata.getModuleNames({filter: 'bad'})).toEqual(expectedList);
        });
        it('should return tabs modules only', function() {
            var expectedList = ['Contacts', 'Accounts', 'KBDocuments'];
            var oppositeList = _.difference(_.keys(fixtures.metadata.modules_info), expectedList);
            oppositeList = _.difference(oppositeList, User.get('module_list'));
            //Note that KBDocuments has been removed because not in the app level visible module list
            expect(app.metadata.getModuleNames({filter: 'display_tab'})).toEqual(expectedList);
            expect(app.metadata.getModuleNames({filter: '!display_tab'})).toEqual(oppositeList);
        });
        it('should return visible modules only', function() {
            var expectedList = ['Bugs', 'Cases', 'KBDocuments'];
            var oppositeList = _.difference(_.keys(fixtures.metadata.modules_info), expectedList);
            //Note that KBDocuments has been removed because not in the app level visible module list
            expect(app.metadata.getModuleNames({filter: 'visible'})).toEqual(expectedList);
            expect(app.metadata.getModuleNames({filter: '!visible'})).toEqual(oppositeList);
        });
        it('should return quick create modules only', function() {
            var expectedList = ['Bugs', 'Cases'];
            var oppositeList = _.difference(_.keys(fixtures.metadata.modules_info), expectedList);
            //Note that KBDocuments has been removed because not in the app level visible module list
            expect(app.metadata.getModuleNames({filter: 'quick_create'})).toEqual(expectedList);
            expect(app.metadata.getModuleNames({filter: '!quick_create'})).toEqual(oppositeList);
        });
        it('should return subpanels modules only', function() {
            var expectedList = ['Accounts', 'Cases', 'Contacts'];
            var oppositeList = _.difference(_.keys(fixtures.metadata.modules_info), expectedList);
            //Note that KBDocuments has been removed because not in the app level visible module list
            expect(app.metadata.getModuleNames({filter: 'show_subpanels'})).toEqual(expectedList);
            expect(app.metadata.getModuleNames({filter: '!show_subpanels'})).toEqual(oppositeList);
        });
        it('should return tabs modules only that user has `create` access', function() {
            var expectedList = ['Contacts', 'KBDocuments'];
            var oppositeList = _.difference(_.keys(fixtures.metadata.modules_info), expectedList);
            oppositeList = _.intersection(oppositeList, app.metadata.getModuleNames({access: 'create'}));
            expect(app.metadata.getModuleNames({filter: 'display_tab', access: 'create'})).toEqual(expectedList);
            expect(app.metadata.getModuleNames({filter: '!display_tab', access: 'create'})).toEqual(oppositeList);
        });
        it('should return only visible quick create modules', function() {
            // turn bugs visibility off
            fixtures.metadata.modules_info['Bugs'].visible = false;
            // cases is the only one with both quick_create and visible
            expect(app.metadata.getModuleNames({filter: ['quick_create', 'visible']})).toEqual(['Cases']);
            // bugs is the only one with quick create and !visible
            expect(app.metadata.getModuleNames({filter: ['quick_create', '!visible']})).toEqual(['Bugs']);
            // find all with out quick create and not visible
            expect(app.metadata.getModuleNames({filter: ['!quick_create', '!visible']}))
                .toEqual(['Accounts', 'Contacts', 'Test']);
            fixtures.metadata.modules_info['Bugs'].visible = true;
        });
        it('should support the old APIs that do not return modules_info and get the tabs list', function() {
            fixtures.metadata.modules_info = undefined;
            //Same expectations as previous unit test
            var expectedList = ['Contacts', 'KBDocuments'];
            expect(app.metadata.getModuleNames({filter: 'display_tab', access: 'create'})).toEqual(expectedList);
        });
        it('should return all modules that user has `create` access', function() {
            var expectedList = ['Contacts', 'KBDocuments', 'Bugs', 'Cases', 'Test'];
            expect(app.metadata.getModuleNames({access: 'create'})).toEqual(expectedList);
        });
        it('should return all modules that user has `dummy` access', function() {
            var expectedList = ['Contacts', 'Accounts', 'KBDocuments', 'Bugs', 'Cases', 'Test'];
            expect(app.metadata.getModuleNames({access: 'dummy'})).toEqual(expectedList);
        });
    });

    it('should get strings', function() {
        var labels = SugarTest.labelsFixture;
        expect(app.metadata.getStrings("mod_strings")).toBe(labels.mod_strings);
        expect(app.metadata.getStrings("app_strings")).toBe(labels.app_strings);
        expect(app.metadata.getStrings("app_list_strings")).toBe(labels.app_list_strings);
    });

    it('should get currencies', function() {
        expect(app.metadata.getCurrency("abc123")).toBeDefined();
        expect(app.metadata.getCurrency("abc123").iso4217).toBe("EUR");
    });

    it('should patch field displayParams metadata', function() {
        var field = app.metadata.getView("Contacts", "edit").panels[0].fields[2],
            field2 = app.metadata.getView("Contacts", "edit").panels[0].fields[4];
        expect(_.isObject(field)).toBeTruthy();
        expect(field.name).toEqual("phone_home");
        expect(field.type).toEqual("text");
        expect(field.label).toEqual("LBL_PHONE_HOME");
        expect(field.required).toBeTruthy();

        expect(_.isObject(field2)).toBeTruthy();
        expect(field2.fields).toBeTruthy();
        expect(field2.fields[0].name).toEqual("subfield 1");
    });

    it('should patch field type param', function() {
        var moduleMeta = {};
        moduleMeta.fields = {};
        moduleMeta.fields['field_test'] = { 'name': 'field_test' };

        // Fallback to `base`
        var fields = app.metadata._patchFields('Contacts', moduleMeta, ['field_test']);
        expect(_.isObject(fields[0])).toBeTruthy();
        expect(fields[0].type).toEqual('base');

        // Use vardef `type`
        moduleMeta.fields['field_test']['type'] = 'float';
        var fields = app.metadata._patchFields('Contacts', moduleMeta, ['field_test']);
        expect(fields[0].type).toEqual('float');

        // Use `fieldTypeMap[type]`
        moduleMeta.fields['field_test']['type'] = 'text';
        var fields = app.metadata._patchFields('Contacts', moduleMeta, ['field_test']);
        expect(fields[0].type).toEqual('textarea');

        // Use vardef `custom_type`
        moduleMeta.fields['field_test']['custom_type'] = 'currency';
        var fields = app.metadata._patchFields('Contacts', moduleMeta, ['field_test']);
        expect(fields[0].type).toEqual('currency');

        // Use viewdef `type`
        var fields = app.metadata._patchFields('Contacts', moduleMeta, [
            { 'name': 'field_test', 'type': 'enum'}
        ]);
        expect(fields[0].type).toEqual('enum');
    });

    it('should patch view metadata', function() {
        var field = app.metadata.getView("Contacts", "detail").panels[0].fields[3];
        expect(_.isObject(field)).toBeTruthy();
        expect(field.name).toEqual("phone_home");
        expect(field.type).toEqual("text");
    });

    describe('getHiddenSubpanels', function(){
        it('should return list of names for modules that are hidden in subpanels', function(){
            var hiddenList = app.metadata.getHiddenSubpanels();
            expect(_.size(hiddenList)).toBe(2);
            expect(hiddenList[0]).toEqual("contacts");
            expect(hiddenList[1]).toEqual("bugs");
        });
    });

    it("should delegate to view-manager if has a custom view controller", function() {
        sinon.spy(app.view, 'declareComponent');
        // Hack - metadata.set with a different object results in mutation of fixtures.metadata
        var originalMeta = Utils.deepCopy(fixtures.metadata);
        app.metadata.set({modules: { Home: fixtures.jssource.modules.Home }});//has base and portal platforms
        expect(app.view.declareComponent.getCall(0).args[0]).toEqual("view");
        expect(app.view.declareComponent.getCall(0).args[1]).toEqual("login");
        expect(app.view.declareComponent.getCall(0).args[2]).toEqual("Home");
        expect(app.view.declareComponent.getCall(0).args[3].customCallback()).toEqual("base called");
        expect(typeof(app.view.declareComponent.getCall(0).args[3].customCallback)).toBe("function");
        expect(app.view.declareComponent.getCall(1).args[0]).toEqual("view");
        expect(app.view.declareComponent.getCall(1).args[1]).toEqual("login");
        expect(app.view.declareComponent.getCall(1).args[2]).toEqual("Home");
        expect(app.view.declareComponent.getCall(1).args[3].customCallback()).toEqual("overriden portal");
        expect(app.view.views.BaseHomeLoginView).toBeDefined();
        expect(app.view.views.BaseHomeLoginView).toBeDefined();
        expect(app.view.views.PortalHomeLoginView.prototype.customCallback).toBeDefined();
        expect(app.view.views.PortalHomeLoginView.prototype.customCallback).toBeDefined();
        fixtures.metadata = originalMeta;
    });

    it("should delegate to view-manager if has custom layout controller", function() {
        sinon.spy(app.view, 'declareComponent');
        // Hack - metadata.set with a different object results in mutation of fixtures.metadata
        var originalMeta = Utils.deepCopy(fixtures.metadata);
        app.metadata.set({modules: { Contacts: fixtures.jssource.modules.Contacts}});
        expect(app.view.declareComponent.getCall(0).args[0]).toEqual("layout");
        expect(app.view.declareComponent.getCall(0).args[1]).toEqual("detailplus");
        expect(app.view.declareComponent.getCall(0).args[2]).toEqual("Contacts");
        expect(typeof(app.view.declareComponent.getCall(0).args[3].customLayoutCallback)).toBe("function");
        expect(app.view.layouts.BaseContactsDetailplusLayout).toBeDefined();
        expect(app.view.layouts.BaseContactsDetailplusLayout.prototype.customLayoutCallback).toBeDefined();
        fixtures.metadata = originalMeta;
    });

    it("should delegate to template.compile if meta set with custom view template", function() {
        sinon.spy(Template, 'setView');
        sinon.spy(Template, 'setLayout');
        app.metadata.set({
            modules: {
                Taxonomy: {
                    views: {
                        tree: {
                            templates: {
                                'tree': "My Lil Template",
                                'view2': "My Lil Template2" // can now have multiple templates per single view
                            }
                        }
                    },
                    layouts: {
                        oak: {
                            templates: {
                                'oak': "My happy Template",
                                'view2': "My happy Template2" // can now have multiple templates per single layout
                            }
                        }
                    }
                }
            }
        });
        expect(Template.setView.getCall(0).args[0]).toEqual('tree');
        expect(Template.setView.getCall(0).args[1]).toEqual('Taxonomy');
        expect(Template.setView.getCall(0).args[2]).toEqual('My Lil Template');
        expect(Template.setView.getCall(1).args[0]).toEqual('tree.view2');
        expect(Template.setView.getCall(1).args[1]).toEqual('Taxonomy');
        expect(Template.setView.getCall(1).args[2]).toEqual('My Lil Template2');

        expect(Template.setLayout.getCall(0).args[0]).toEqual('oak');
        expect(Template.setLayout.getCall(0).args[1]).toEqual('Taxonomy');
        expect(Template.setLayout.getCall(0).args[2]).toEqual('My happy Template');
        expect(Template.setLayout.getCall(1).args[0]).toEqual('oak.view2');
        expect(Template.setLayout.getCall(1).args[1]).toEqual('Taxonomy');
        expect(Template.setLayout.getCall(1).args[2]).toEqual('My happy Template2');
        //setters on views, layouts, etc., will now _add them without yet compiling
        expect(Handlebars.templates["tree.Taxonomy"]).not.toBeDefined();
    });

    it("should delegate to template.compile if meta set with custom field templates", function() {
        sinon.spy(Template, 'setField');
        app.metadata.set({
            modules: {
                Taxonomy: {
                    fieldTemplates: {
                        tree: {
                            templates: {
                                "default": "My Lil Template"
                            }
                        }
                    }
                }
            }
        });
        expect(Template.setField.getCall(0).args[0]).toEqual('tree');
        expect(Template.setField.getCall(0).args[1]).toEqual('default');
        expect(Template.setField.getCall(0).args[2]).toEqual('Taxonomy');
        expect(Template.setField.getCall(0).args[3]).toEqual('My Lil Template');
        //setters on fields, views, layouts, etc., will now _add them without yet compiling
        expect(Handlebars.templates["f.tree.Taxonomy.default"]).not.toBeDefined();
    });

    it("should register view controllers without metadata", function () {
        sinon.spy(app.view, 'declareComponent');
        app.metadata.set({
            modules:{
                Taxonomy:{
                    views:{
                        base: {
                            tree:{
                                controller:"({})"
                            }
                        }
                    }
                }
            }
        });
        expect(app.view.declareComponent.getCall(0).args[1]).toEqual("tree");
        expect(app.view.views.BaseTaxonomyTreeView).toBeDefined();
    });

    it("should call setLayout when a layout has a template", function () {
        sinon.spy(Template, 'setLayout');
        // for meta.modules, we don't have a <platform> like 'base'
        app.metadata.set({
            modules:{
                Taxonomy:{
                    layouts:{
                        tree:{
                            controller:"({})",
                            templates: {
                                "tree": "Custom Layout Template"
                            }
                        }
                    }
                }
            }
        });
        expect(Template.setLayout.getCall(0).args[0]).toEqual('tree');
        expect(Template.setLayout.getCall(0).args[1]).toEqual('Taxonomy');
        expect(Template.setLayout.getCall(0).args[2]).toEqual('Custom Layout Template');
    });

    describe('when syncing metadata', function() {
        beforeEach(function() {
            SugarTest.seedFakeServer();
            SugarTest.server.respondImmediately = true;
        });

        afterEach(function() {
            app.cache.cutAll(true);
        });

        it('should sync metadata and extend whatever is in memory cache', function() {
            let done = false;
            const mdTypes = ["modules", "relationships", "fields", "views", "layouts", "module_list"];
            runs(() => {
                app.metadata.set(
                    _.extend({_hash: 'xyz'}, _.reduce(mdTypes, (o, type) => {
                        o[type] = {ENTRY: {}};
                        return o;
                    }, {})), true, true);
                SugarTest.server.respondImmediately = true;
                SugarTest.server.respondWith("GET", /.*\/rest\/v10\/metadata.*/,
                    [200, {"Content-Type": "application/json"}, JSON.stringify(meta)]);

                // Fake response for _fetchLabels's ajax call, since meta.labels is just a URL to a JSON file
                SugarTest.server.respondWith("GET", /.*\/fixtures\/labels.json.*/,
                    [200, {"Content-Type": "application/json"}, JSON.stringify(SugarTest.labelsFixture)]);
                app.metadata.sync(() => {
                    done = true;
                });
            });
            waitsFor(() => done, "Sync should complete", 500);
            runs(() => {
                var newMeta = app.metadata._dev_data;
                _.each(mdTypes, prop => {
                    // Check the new meta has been updated
                    checkMeta(newMeta, meta, prop);
                    // Check that old meta is still there
                    expect(newMeta[prop].ENTRY).toBeDefined();
                });

                expect(app.config.configfoo).toEqual("configBar");
            });
        });

        function checkMeta(newMeta, oldMeta, prop) {
            _.each(_.keys(oldMeta[prop]), function(key) {
                expect(newMeta[prop][key]).toEqual(oldMeta[prop][key]);
            });
        }

        it('should cache metadata if requested', function() {
            let done = false;
            runs(() => {
                // Verify hash doesn't exist
                expect(app.cache.get('meta:hash')).toBeUndefined();
                app.config.cacheMeta = true;
                meta.config.cacheMeta = true;
                app.metadata.reset();

                SugarTest.server.respondImmediately = true;
                SugarTest.server.respondWith("GET", /.*\/rest\/v10\/metadata.*/,
                    [200, {"Content-Type": "application/json"}, JSON.stringify(meta)]);

                // Fake response for _fetchLabels's ajax call, since meta.labels is just a URL to a JSON file
                SugarTest.server.respondWith("GET", /.*\/fixtures\/labels.json.*/,
                    [200, {"Content-Type": "application/json"}, JSON.stringify(SugarTest.labelsFixture)]);
                app.metadata.sync(() => {
                    done = true;
                });
            });
            waitsFor(() => done);
            runs(()=> {
                _.each(meta, function(bucket, name) {
                    if (name !== "_hash" && name !=="labels") {
                        expect(app.cache.get('meta:data')[name]).toEqual(bucket);
                    }
                });
                expect(app.cache.get('meta:hash')).toEqual("2q34aasdfwrasdfse");
                expect(app.cache.get('meta:data')['_hash']).toEqual("2q34aasdfwrasdfse");

                app.metadata.clearCache();
                expect(app.cache.get('meta:data')).toBeUndefined();
                expect(app.cache.get('meta:hash')).toBeUndefined();
            });
        });

        it('should handle mime settings that are misconfigured thus returning text/plain for .json file', function() {
            let done = false;
            runs(() => {
                app.config.cacheMeta = true;
                meta.config.cacheMeta = true;
                app.metadata.reset();
                SugarTest.server.respondImmediately = true;
                SugarTest.server.respondWith("GET", /.*\/rest\/v10\/metadata.*/,
                    [200, {"Content-Type": "application/json"}, JSON.stringify(meta)]);
                // Fake response for _fetchLabels's ajax call, since meta.labels is just a URL to a JSON file
                SugarTest.server.respondWith("GET", /.*\/fixtures\/labels.json.*/,
                    [200, {"Content-Type": "text/plain"}, '{"omg": "it works!"}']);
                app.metadata.sync(() => {done=true;});
            });
            waitsFor(() => done);
            runs(() => {
                expect(app.metadata.getStrings('omg')).toEqual("it works!");
                app.metadata.clearCache();
            });
        });

        it('should handle invalid JSON after fetching labels data', function() {
            let done = false;
            let error;
            runs(() => {
                app.config.cacheMeta = true;
                meta.config.cacheMeta = true;
                app.metadata.reset();
                sinon.stub(console, 'error');

                SugarTest.server.respondWith("GET", /.*\/rest\/v10\/metadata.*/,
                    [200, {"Content-Type": "application/json"}, JSON.stringify(meta)]);
                // Fake response for _fetchLabels's ajax call, since meta.labels is just a URL to a JSON file
                SugarTest.server.respondWith("GET", /.*\/fixtures\/labels.json.*/,
                    [200, {"Content-Type": "text/plain"}, 'invalid json']);

                app.metadata.sync((e) => {
                    done = true;
                    error = e;
                });
            });
            waitsFor(() => done);
            runs(() => {
                expect(error.code).toEqual('sync_failed');
                expect(error.label).toEqual('ERR_SYNC_FAILED');
                app.metadata.clearCache();
            });
         });

        it('should not take any action when server returns 304', function() {
            let done = false;
            let spy;
            runs(() => {
                spy = sinon.spy(app.metadata, 'set');
                SugarTest.server.respondWith("GET", /.*\/rest\/v10\/metadata.*/,
                    [304, {"Content-Type": "application/json"}, ""]);

                // Fake response for _fetchLabels's ajax call, since meta.labels is just a URL to a JSON file
                SugarTest.server.respondWith("GET", /.*\/fixtures\/labels.json.*/,
                    [200, {"Content-Type": "application/json"}, JSON.stringify(SugarTest.labelsFixture)]);

                app.metadata.sync(() => {
                    done = true;
                });
            });
            waitsFor(() => done);
            runs(() => {
                expect(spy).not.toHaveBeenCalled();
            })
        });

        it("should fetch only requested types", function() {
            app.metadata.reset();
            const getMetadataStub = sinon.stub(app.api, 'getMetadata');
            app.metadata.sync(null, {metadataTypes: ['labels']});
            expect(getMetadataStub).toHaveBeenCalledWithMatch({types: ['labels']});
        });

        it("should use the default language if `Language` not yet set", function() {
            var metaJson, expectedUrl, ajaxStub;
            ajaxStub = sinon.stub($, 'ajax'); // prevent proxying to real ajax!
            app.metadata.reset();

            expectedUrl = Utils.buildUrl(meta.labels.en_us);
            metaJson = {
                jssource: null,
                labels: meta.labels,
                'server_info': {
                    'version': '6.6.0',
                },
            };
            sinon.stub(app.api, 'getMetadata').callsFake(function(options) {
                // Force production code's success hook to fire passing our fake meta
                options.callbacks.success(metaJson);
            });
            sinon.stub(User, 'getLanguage').callsFake(function() {
                return undefined; // simulate user lang not yet set
            });

            Language.setCurrentLanguage(undefined);
            app.metadata.sync();

            // Expectation: SUT will use 'default' property when `Language` doesn't yet have language set
            expect(ajaxStub).toHaveBeenCalled();
            expect(ajaxStub.args[0][0].url).toEqual(expectedUrl);

            SugarTest.server.restore();
            expect(Language.getLanguage()).toEqual('en_us');
        });

        it("should use the default language if app.user.getLanguage returns a language that is not present in metadata", function() {
            var metaJson, expectedUrl, ajaxStub;
            ajaxStub = sinon.stub($, 'ajax'); // prevent proxying to real ajax!
            app.metadata.reset();

            expectedUrl = Utils.buildUrl(meta.labels.en_us);
            metaJson = {
                jssource: null,
                labels: meta.labels,
                "server_info": {
                    "version":"6.6.0"
                }
            };
            sinon.stub(app.api, 'getMetadata').callsFake(function(options) {
                // Force production code's success hook to fire passing our fake meta
                options.callbacks.success(metaJson);
            });
            sinon.stub(User, 'getLanguage').callsFake(function() {
                return "esperanto_SomewhereInUniverse";
            });

            Language.setCurrentLanguage(undefined);
            app.metadata.sync();

            // Expectation: use 'default' property when getLanguage returns unknown language
            expect(ajaxStub).toHaveBeenCalled();
            expect(ajaxStub.args[0][0].url).toEqual(expectedUrl);

            SugarTest.server.restore();
            expect(Language.getLanguage()).toEqual('en_us');
        });

        it("should write language strings to metadata and cache", function() {
            let done = false;
            runs(() => {
                app.metadata.reset();

                SugarTest.server.respondImmediately = true;
                SugarTest.server.respondWith("GET", /.*\/rest\/v10\/metadata\?.*/,
                    [200, {"Content-Type": "application/json"}, JSON.stringify(meta)]);

                // Fake response for _fetchLabels's ajax call, since meta.labels is just a URL to a JSON file
                SugarTest.server.respondWith("GET", /.*\/fixtures\/labels.json.*/,
                    [200, {"Content-Type": "application/json"}, JSON.stringify(SugarTest.labelsFixture)]);

                let callback = () => {
                    done = true;
                };
                app.metadata.sync(callback);
            });
            waitsFor(() => done);
            runs(() => {
                expect(app.metadata.getStrings('app_strings')._hash).toEqual('x5');
                expect(app.metadata.getStrings('app_list_strings')._hash).toEqual('x4');
                SugarTest.server.restore();
            });
        });

        it("should include a jssource file if one is returned", function() {
            var scripts, lastScript, compFixtureSrc;
            let done = false;
            runs(() => {
                compFixtureSrc = SugarTest.componentsFixtureSrc;
                sinon.spy(app.metadata, '_declareClasses');
                app.config.cacheMeta = true;
                meta.config.cacheMeta = true;
                app.metadata.reset();

                SugarTest.server.respondWith("GET", /.*\/rest\/v10\/metadata.*/,
                    [200, {"Content-Type":"application/json"}, JSON.stringify({
                        "jssource" : compFixtureSrc,
                        labels: meta.labels,
                        "server_info": {
                            "version":"6.6.0"
                        }
                })]);

                // Fake response for _fetchLabels's ajax call, since meta.labels is just a URL to a JSON file
                SugarTest.server.respondWith("GET", /.*\/fixtures\/labels.json.*/,
                    [200, {"Content-Type": "application/json"}, JSON.stringify(SugarTest.labelsFixture)]);

                let callback = () => {
                    done = true;
                };
                app.metadata.sync(callback);
            });
            waitsFor(() => done);
            runs(() => {
                //Verify the element was added to the head.
                scripts    = $("head script");
                lastScript = scripts[scripts.length-1];
                expect($(lastScript).attr('src')).toEqual(Utils.buildUrl(compFixtureSrc));
                SugarTest.server.restore();
            });
        });

        it("should declare classes by platform", function() {
            require('../../fixtures/components.js');
            var compFixture = SUGAR.jssource,
                spy = sinon.spy(app.metadata, '_sortAndDeclareComponents'),
                stub = sinon.stub(app.view, 'declareComponent');

            // _declareClasses should loop fields, views, layouts, each by platform
            app.metadata._declareClasses(compFixture);
            _(6).times(function(n) {
                // Base platform
                if (n % 2 === 0) {
                    expect(spy.getCall(n).args[3]).toEqual('base');
                    expect(spy.getCall(n).args[0].a.controller.baseA).toEqual(true);
                    expect(stub.getCall(n).args[5]).toEqual('base');
                    expect(stub.getCall(n).args[3].baseA).toEqual(true);
                } else {
                    // Portal platform
                    expect(spy.getCall(1).args[3]).toEqual('portal');
                    expect(spy.getCall(1).args[0].a.controller.portalA).toEqual(true);
                    expect(stub.getCall(1).args[5]).toEqual('portal');
                    expect(stub.getCall(1).args[3].portalA).toEqual(true);
                }
            });
        });

        it('sorts controllers by inheritance', function() {
            var views = {
                leaf : {
                    controller : {
                        extendsFrom : "MiddleView",
                        test : "leaf"
                    }
                },
                middle : {
                    controller : {
                        extendsFrom : "Zz_baseView",
                        test : "middle"
                    }
                },
                middle2 : {
                    controller : {
                        extendsFrom : "Zz_baseView",
                        test : "middle2"
                    }
                },
                zz_base : {
                    meta : {},
                    controller : {
                        test : "zz_base"
                    }
                }
            };
            var sortedViews = app.metadata._sortControllers("view", views);
            expect(sortedViews[0].type).toBe("zz_base");
            expect(sortedViews[0].weight).toBe(-3);
            expect(sortedViews[1].type).toBe("middle");
            expect(sortedViews[1].weight).toBe(-1);
        });

        it("recognizes custom component as base class", function () {
            var components = {
                "child": {
                    controller: {
                        extendsFrom: "ParentComponent"
                    }
                },
                "custom-parent": {
                    controller: {}
                }
            };

            var sorted = app.metadata._sortControllers("component", components);
            expect(sorted[0].type).toBe("custom-parent");
            expect(sorted[1].type).toBe("child");
        });

        it("properly aligns base class, custom class and extending class", function () {
            var components = {
                "aa": {
                    controller: {
                        extendsFrom: "ZzComponent"
                    }
                },
                "zz": {
                    controller: {}
                },
                "custom-zz": {
                    controller: {
                        extendsFrom: "ZzComponent"
                    }
                }
            };

            var sorted = app.metadata._sortControllers("component", components);
            expect(sorted[0].type).toBe("zz");
            expect(sorted[1].type).toBe("custom-zz");
            expect(sorted[2].type).toBe("aa");
        });

        it("sorts module controllers by inheretence", function () {
            var layouts = {
                middle2:{
                    controller:{
                        extendsFrom:"FooZz_baseLayout",
                        test:"middle2"
                    }
                },
                middle:{
                    controller:{
                        extendsFrom:"FooZz_baseLayout",
                        test:"middle"
                    }
                },
                zz_base:{
                    meta:{},
                    controller:{
                        test:"zz_base"
                    }
                },
                leaf:{
                    controller:{
                        extendsFrom:"FooMiddleLayout",
                        test:"leaf"
                    }
                }
            };
            var sortedLayouts = app.metadata._sortControllers("layout", layouts, "Foo");
            expect(sortedLayouts[0].type).toBe("zz_base");
            expect(sortedLayouts[0].weight).toBe(-3);
            expect(sortedLayouts[1].type).toBe("middle");
            expect(sortedLayouts[1].weight).toBe(-1);
        });

        describe('local storage metadata hash changes', function() {
            var syncMock, hash, cacheStub;
            beforeEach(function() {
                syncMock = sinon.stub(app, 'sync');
                //Set the metadata Api to just call its callback argument (index 3) and set the hash
                sinon.stub(app.api, 'getMetadata').callsFake(function(options) {
                    options.callbacks.success({_hash: 'bar'});
                });
                sinon.stub(app, 'isServerCompatible').returns(true);
                hash = app.metadata.getHash();
                cacheStub = sinon.stub(app.cache, 'get');
            });

            afterEach(function() {
                //Clear an existing listener on the real window object
                window.removeEventListener('storage', app.metadata.storageHandler);
            });

            /**
             * This test is validating that when the application has already started, we will trigger another sync
             * when the localstorage indicated the in memory metadata is out of date.
             */
            it('Starts app sync private', function() {
                let done = false;
                //Start by syncing metadata
                runs(() => {
                    sinon.stub(app.api, 'isAuthenticated').callsFake(function() {
                        return true;
                    });
                    SugarTest.server.respondWith("GET", /.*\/rest\/v10\/metadata.*/,
                        [200, {"Content-Type": "application/json"}, JSON.stringify(meta)]);

                    // Fake response for _fetchLabels's ajax call, since meta.labels is just a URL to a JSON file
                    SugarTest.server.respondWith("GET", /.*\/fixtures\/labels.json.*/,
                        [200, {"Content-Type": "application/json"}, JSON.stringify(SugarTest.labelsFixture)]);
                    app.metadata.sync(() => {done = true;});
                });
                waitsFor(() => done, "Sync should complete", 500);
                runs(() => {
                    expect(app.metadata.isSyncing()).toBeFalsy();
                    //Call to init should create the interval we are looking for
                    app.metadata.init();
                    expect(hash).toBeDefined();
                    expect(hash).not.toBe('');
                    //Set the cache to return a different hash
                    cacheStub.withArgs('meta:hash').returns('foo');
                    app.metadata.storageHandler();
                    expect(app.metadata.isSyncing()).toBeTruthy();
                    expect(syncMock).toHaveBeenCalled();
                    expect(syncMock.lastCall.args[0]).toBeDefined();
                    expect(syncMock.lastCall.args[0].getPublic).not.toBeTruthy();
                });
            });

            it('Starts app sync public', function() {
                let done = false;
                //Start by syncing metadata
                runs(() => {
                    sinon.stub(app.api, 'isAuthenticated').callsFake(function() {
                        return false;
                    });
                    SugarTest.server.respondWith("GET", /.*\/rest\/v10\/metadata.*/,
                        [200, {"Content-Type": "application/json"}, JSON.stringify(meta)]);

                    // Fake response for _fetchLabels's ajax call, since meta.labels is just a URL to a JSON file
                    SugarTest.server.respondWith("GET", /.*\/fixtures\/labels.json.*/,
                        [200, {"Content-Type": "application/json"}, JSON.stringify(SugarTest.labelsFixture)]);
                    app.metadata.sync(() => {done = true;});
                });
                waitsFor(() => done, "Sync should complete", 500);
                runs(() => {
                    //Call to init should create the interval we are looking for
                    app.metadata.init();

                    expect(hash).toBeDefined();
                    expect(hash).not.toBe('');

                    //Set the cache to return a different hash
                    cacheStub.withArgs('meta:hash').returns('foo');

                    app.metadata.storageHandler();

                    expect(app.metadata.isSyncing()).toBeTruthy();
                    expect(syncMock).toHaveBeenCalled();
                    expect(syncMock.lastCall.args[0]).toBeDefined();
                    expect(syncMock.lastCall.args[0].getPublic).toBeTruthy();
                });
            });
        });

        it("should determine if metadata types are separated by platform", function() {
            var actual,
                metaIsSeparatedByPlatform = {
                    fields: {
                        base: {
                            foo: {controller: {}}
                        },
                        portal: {
                            bar: { controller: {}}
                        }
                    }
                },
                //most closely replicates SP-757 bug where modules/Contacts/clients/portal/views/record.js not loaded
                metaIsSeparatedByPlatformNoBasePlatform = {
                    views: {
                        portal: {
                            bar: { controller: {}}
                        }
                    }
                },
                metaNotSeparatedByPlatform = {
                    fields: {
                        // Note that 'base' here is actually the name of field e.g. app.view.fields.base
                        base: {
                            controller: {}
                        }
                    }
                };
            actual = app.metadata._metaTypeIsSeparatedByPlatform(metaIsSeparatedByPlatform, 'field', ['base','portal']);
            expect(actual).toBeTruthy();
            actual = app.metadata._metaTypeIsSeparatedByPlatform(metaNotSeparatedByPlatform, 'field', ['base','portal']);
            expect(actual).toBeFalsy();
            actual = app.metadata._metaTypeIsSeparatedByPlatform(metaIsSeparatedByPlatformNoBasePlatform, 'view', ['base','portal']);
            expect(actual).toBeTruthy();
        });

        it("should declare module field components when field component is specified in the module metadata", function() {
            var oldPlatform = app.config.platform,
                spy = sinon.spy(app.metadata, '_sortAndDeclareComponents'),
                moduleFieldsMetadata = {
                    modules: {
                        Contacts: {
                            fieldTemplates: {
                                base: {
                                    myfield: {
                                        controller: {},
                                        templates: {}
                                    }
                                }
                            }
                        }
                    }
                };
            sinon.stub(app.view, 'declareComponent');

            app.config.platform = 'base';
            app.metadata._declareClasses(moduleFieldsMetadata);

            expect(spy.calledOnce).toBe(true);
            expect(spy.withArgs(moduleFieldsMetadata.modules.Contacts.fieldTemplates.base, 'field', 'Contacts', 'base').calledOnce).toBe(true);

            app.config.platform = oldPlatform;
        });

        it('should deep copy metadata by default', function() {
            var obj = { a: '1', b: true },
                copy = app.metadata.copy(obj);

            // References should not be equal because we deep copy
            expect(copy).not.toBe(obj);
            expect(copy).toEqual(obj);
        });

    });

    describe('getField', function () {
        beforeEach(function () {
            this.meta = {
                modules: {
                    Module1: {
                        fields: {
                            field1: { prop1: 'value1' },
                            field2: { prop1: 'value1' },
                        },
                    },
                    Module2: {
                        fields: {},
                    },
                    Module3: {},
                },
            };

            app.metadata.set(this.meta, true, true);
        });

        it('should throw error if no module is supplied', function () {

            // TODO test without params - currently due to BC we can't test it
            expect(function () {
                app.metadata.getField({});
            }).toThrow('Cannot get vardefs without a module');

            expect(function () {
                app.metadata.getField({ name: 'foo' });
            }).toThrow('Cannot get vardefs without a module');
        });

        it('should return all field defs from module', function () {
            var result = this.meta.modules.Module1.fields;
            expect(app.metadata.getField({ module: 'Module1' })).toEqual(result);
            expect(app.metadata.getField({ module: 'Module2' })).toEqual({});
            expect(app.metadata.getField({ module: 'Module3' })).toBeUndefined();
        });

        it('should return field def from module', function () {
            var result = this.meta.modules.Module1.fields.field1;
            expect(app.metadata.getField({ module: 'Module1', name: 'field1' })).toEqual(result);

            expect(app.metadata.getField({ module: 'Module1', name: 'not-defined' })).toBeUndefined();
            expect(app.metadata.getField({ module: 'Module2', name: 'field1' })).toBeUndefined();
            expect(app.metadata.getField({ module: 'Module3', name: 'field1' })).toBeUndefined();
            expect(app.metadata.getField({ module: 'Unknown', name: 'field1' })).toBeUndefined();
        });
    });

    describe('getLayout', function () {

        beforeEach(function () {

            this.meta = {
                // clients/base/layout/*
                layouts: {
                    layout1: { meta: { corelayout1prop1: 'corelayout1value1' } },
                    layout2: { meta: { corelayout2prop1: 'corelayout2value2' } },
                    layout3: { meta: { corelayout3prop1: 'corelayout1value1' } },
                },

                modules: {
                    // modules/GlobalModule
                    GlobalModule: {
                        // modules/GlobalModule/clients/base/layouts/*
                        layouts: {
                            layout1: { meta: { layout1prop1: 'layout1value1' } },
                            layout2: { meta: { layout2prop1: 'layout2value2' } },
                        },
                    },

                    // modules/Module1
                    Module1: {
                        // modules/Module1/clients/base/layouts/*
                        layouts: {
                            layout1: { meta: { module1layout1Prop1: 'Module1layout1value1' } },
                            specificL1: { meta: { module1specific1Prop1: 'Module1specificL1value1' } },
                            specificL2: { meta: { module1specific2Prop1: 'Module1specificL2value1' } },
                        },
                    },

                    // modules/Module2
                    Module2: {
                        // modules/Module2/clients/base/layouts/*
                        layouts: {
                            layout1: {},
                        },
                    },
                },
            };
            app.metadata.set(this.meta, true, true);
        });

        it('should get all layout definitions when not passing layout param', function () {
            var result = this.meta.layouts;
            expect(app.metadata.getLayout()).toEqual(result);
            expect(app.metadata.getLayout(undefined)).toEqual(result);
            expect(app.metadata.getLayout(null)).toEqual(result);

            result = this.meta.modules.Module1.layouts;
            expect(app.metadata.getLayout('Module1')).toEqual(result);

            expect(app.metadata.getLayout('UnknownModule')).toBeUndefined();
        });

        it('should get all layout definitions from core', function() {
            var result = this.meta.layouts.layout1.meta;
            expect(app.metadata.getLayout(undefined, 'layout1')).toEqual(result);
            expect(app.metadata.getLayout(null, 'layout1')).toEqual(result);

            result = this.meta.layouts.layout2.meta;
            expect(app.metadata.getLayout(undefined, 'layout2')).toEqual(result);
            expect(app.metadata.getLayout(null, 'layout2')).toEqual(result);

            result = this.meta.layouts.layout3.meta;
            expect(app.metadata.getLayout(undefined, 'layout3')).toEqual(result);
            expect(app.metadata.getLayout(null, 'layout3')).toEqual(result);
        });

        it('should get layout definitions with fallback to core', function () {
            var result = this.meta.modules.Module1.layouts.layout1.meta;
            expect(app.metadata.getLayout('Module1', 'layout1')).toEqual(result);

            result = this.meta.modules.Module1.layouts.specificL1.meta;
            expect(app.metadata.getLayout('Module1', 'specificL1')).toEqual(result);

            result = this.meta.modules.Module1.layouts.specificL2.meta;
            expect(app.metadata.getLayout('Module1', 'specificL2')).toEqual(result);

            expect(app.metadata.getLayout('Module1', 'not-defined')).toBeNull();

            result = this.meta.layouts;
            expect(app.metadata.getLayout()).toEqual(result);
        });

        it('should get layout definitions respecting loadModule fallbacks', function () {

            var result = this.meta.modules.GlobalModule.layouts.layout2.meta;
            expect(app.metadata.getLayout(undefined, 'layout2', 'GlobalModule')).toEqual(result);
            expect(app.metadata.getLayout(null, 'layout2', 'GlobalModule')).toEqual(result);

            expect(app.metadata.getLayout(undefined, 'layout3', 'GlobalModule')).toBeNull();
            expect(app.metadata.getLayout(null, 'layout3', 'GlobalModule')).toBeNull();

            expect(app.metadata.getLayout(undefined, 'not-defined', 'GlobalModule')).toBeNull();
            expect(app.metadata.getLayout(null, 'not-defined', 'GlobalModule')).toBeNull();

            result = this.meta.modules.Module1.layouts.layout1.meta;
            expect(app.metadata.getLayout('Module1', 'layout1', 'GlobalModule')).toEqual(result);

            result = this.meta.modules.GlobalModule.layouts.layout2.meta;
            expect(app.metadata.getLayout('Module1', 'layout2', 'GlobalModule')).toEqual(result);

            expect(app.metadata.getLayout('Module1', 'layout3', 'GlobalModule')).toBeNull();

            expect(app.metadata.getLayout('Module1', 'not-defined', 'GlobalModule')).toBeNull();

            result = this.meta.modules.GlobalModule.layouts.layout1.meta;
            expect(app.metadata.getLayout('Module2', 'layout1', 'GlobalModule')).toEqual(result);
        });
    });

    describe('getView', function () {

        beforeEach(function () {

            this.meta = {
                // clients/base/views/*
                views: {
                    view1: { meta: { coreview1prop1: 'coreview1value1' } },
                    view2: { meta: { coreview2prop1: 'coreview2value1' } },
                    view3: { meta: { coreview3prop1: 'core1view3value1' } },
                },
                modules: {
                    // modules/GlobalModule
                    GlobalModule: {
                        // modules/GlobalModule/clients/base/views/*
                        views: {
                            view1: { meta: { view1prop1: 'view1value1' } },
                            view2: { meta: { view2prop1: 'view2value1' } },
                        },
                    },

                    // modules/Module1
                    Module1: {
                        // modules/Module1/clients/base/views/*
                        views: {
                            view1: { meta: { module1view1Prop1: 'Module1view1value1' } },
                            specificV1: { meta: { module1specificV1Prop1: 'Module1specificV1value1' } },
                            specificV2: { meta: { module1specificV2Prop1: 'Module1specificV2value1' } },
                        },
                    },

                    // modules/Module2
                    Module2: {
                        // modules/Module2/clients/base/views/*
                        views: {
                            view1: {},
                        },
                    },
                },
            };
            app.metadata.set(this.meta, true, true);
        });

        it('should get all view definitions when not passing view param', function () {
            var result = this.meta.views;
            expect(app.metadata.getView()).toEqual(result);
            expect(app.metadata.getView(undefined)).toEqual(result);
            expect(app.metadata.getView(null)).toEqual(result);

            result = this.meta.modules.Module1.views;
            expect(app.metadata.getView('Module1')).toEqual(result);

            expect(app.metadata.getView('UnknownModule')).toBeUndefined();
        });

        it('should get all view definitions from core', function() {
            var result = this.meta.views.view1.meta;
            expect(app.metadata.getView(undefined, 'view1')).toEqual(result);
            expect(app.metadata.getView(null, 'view1')).toEqual(result);

            result = this.meta.views.view2.meta;
            expect(app.metadata.getView(undefined, 'view2')).toEqual(result);
            expect(app.metadata.getView(null, 'view2')).toEqual(result);

            result = this.meta.views.view3.meta;
            expect(app.metadata.getView(undefined, 'view3')).toEqual(result);
            expect(app.metadata.getView(null, 'view3')).toEqual(result);
        });

        it('should get view definitions with fallback to core', function () {
            var result = this.meta.modules.Module1.views.view1.meta;
            expect(app.metadata.getView('Module1', 'view1')).toEqual(result);

            result = this.meta.modules.Module1.views.specificV1.meta;
            expect(app.metadata.getView('Module1', 'specificV1')).toEqual(result);

            result = this.meta.modules.Module1.views.specificV2.meta;
            expect(app.metadata.getView('Module1', 'specificV2')).toEqual(result);

            expect(app.metadata.getView('Module1', 'not-defined')).toBeNull();

            result = this.meta.views;
            expect(app.metadata.getView()).toEqual(result);
        });

        it('should get view definitions respecting loadModule fallbacks', function () {

            var result = this.meta.modules.GlobalModule.views.view2.meta;
            expect(app.metadata.getView(undefined, 'view2', 'GlobalModule')).toEqual(result);

            expect(app.metadata.getView(undefined, 'view3', 'GlobalModule')).toBeNull();

            expect(app.metadata.getView(undefined, 'not-defined', 'GlobalModule')).toBeNull();

            result = this.meta.modules.Module1.views.view1.meta;
            expect(app.metadata.getView('Module1', 'view1', 'GlobalModule')).toEqual(result);

            result = this.meta.modules.GlobalModule.views.view2.meta;
            expect(app.metadata.getView('Module1', 'view2', 'GlobalModule')).toEqual(result);

            expect(app.metadata.getView('Module1', 'view3', 'GlobalModule')).toBeNull();

            expect(app.metadata.getView('Module1', 'not-defined', 'GlobalModule')).toBeNull();

            result = this.meta.modules.GlobalModule.views.view1.meta;
            expect(app.metadata.getView('Module2', 'view1', 'GlobalModule')).toEqual(result);
        });

    });

    describe('hasLicense', () => {
        using('different licenses', [
            {
                serverLicenses: ['SUGAR_SELL'],
                desiredLicenses: ['SUGAR_SELL', 'SUGAR_SERVE'],
                hasAll: false,
                expected: true,
            },
            {
                serverLicenses: ['SUGAR_SELL'],
                desiredLicenses: ['SUGAR_SELL', 'SUGAR_SERVE'],
                hasAll: true,
                expected: false,
            },
            {
                serverLicenses: ['SUGAR_SERVE', 'SUGAR_SELL'],
                desiredLicenses: ['SUGAR_SELL', 'SUGAR_SERVE'],
                hasAll: true,
                expected: true,
            },
            {
                serverLicenses: ['SUGAR_SELL', 'SUGAR_SERVE'],
                desiredLicenses: ['SUGAR_SELL'],
                hasAll: true,
                expected: true,
            },
            {
                serverLicenses: ['SUGAR_SELL', 'SUGAR_SERVE'],
                desiredLicenses: 'SUGAR_SERVE',
                hasAll: true,
                expected: true,
            },
            {
                serverLicenses: ['CURRENT'],
                desiredLicenses: ['SUGAR_SERVE'],
                hasAll: false,
                expected: false,
            },
            {
                serverLicenses: null,
                desiredLicenses: ['SUGAR_SERVE'],
                hasAll: true,
                expected: false,
            }
        ], details => {
            it('should properly check server licenses', () => {
                sinon.stub(app.metadata, 'getServerInfo').callsFake(() => ({licenses: details.serverLicenses}));
                let hasLicense = app.metadata.hasLicense(details.desiredLicenses, details.hasAll);
                expect(hasLicense).toBe(details.expected);
            });
        });
    });
});
