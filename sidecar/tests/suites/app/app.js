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

const User = require('../../../src/core/user');
const Utils = require('../../../src/utils/utils');
const Context = require('../../../src/core/context');
const Language = require('../../../src/core/language');

describe("App", function() {
    var app, server;
    // TODO: Refactor this test suite. It has lots of code duplication

    beforeEach(function() {
        SugarTest.seedFakeServer();
        app = SugarTest.app;
        server = SugarTest.server;
    });

    afterEach(function() {
        sinon.restore();
    });

    it("should return an existing instance", function() {
        // The way app.init is designed is incorrect in my opinion
        // So we shouldn't be calling app.init multiple times
        var app2 = SUGAR.App.init({el: "body"});
        expect(app2).toEqual(app);
    });

    it("should be able to register a module", function() {
        var mock,
            module = {
                init: function() {
                }
            };
        mock = sinon.mock(module);

        mock.expects("init").once();
        app.augment("test", module, true);

        expect(mock.verify()).toBeTruthy();
    });

    describe('sync', function() {
        var _previousRouter;

        beforeEach(function() {
            _previousRouter = app.router;
            app.router = {
                reset: sinon.stub()
            };
        });

        afterEach(function() {
            app.router = _previousRouter;
        });

        using('success or error', [
            {me: true, metadata: true, success: true},
            {me: false, metadata: true, success: false},
            {me: true, metadata: false, success: false}
        ], function(options) {

            it('should fire error or complete events when all of the sync jobs have finished', function() {
                var completeSpy = sinon.spy();
                var errorSpy = sinon.spy();
                var complete = function() {
                    SugarTest.setWaitFlag();
                };

                sinon.stub(User, 'load').callsFake(function(callback) {
                    var error = !options.me ? 'Error!' : void 0;
                    callback(error);
                });
                sinon.stub(app.metadata, 'sync').callsFake(function(callback) {
                    var error = !options.metadata ? 'Error!' : void 0;
                    callback(error);
                });

                app.on('app:sync:error', errorSpy);
                app.on('app:sync:complete', completeSpy);

                app.sync({callback: complete});

                SugarTest.wait();

                runs(function() {
                    expect(User.load).toHaveBeenCalled();
                    expect(app.metadata.sync).toHaveBeenCalled();

                    if (options.success) {
                        expect(errorSpy).not.toHaveBeenCalled();
                        expect(completeSpy).toHaveBeenCalled();
                        expect(app.router.reset).toHaveBeenCalled();
                    } else {
                        expect(errorSpy).toHaveBeenCalled();
                        expect(completeSpy).not.toHaveBeenCalled();
                        expect(app.router.reset).not.toHaveBeenCalled();
                    }

                    app.off('app:sync:error', errorSpy);
                    app.off('app:sync:complete', completeSpy);
                });
            });
        });

        it('should not trigger multiple sync when called multiple times', function() {
            var completeSpy = sinon.spy();
            var complete1 = sinon.spy();
            var complete2 = sinon.spy();
            var complete3 = function() {
                SugarTest.setWaitFlag();
            };
            var complete3Spy = sinon.spy(complete3);

            app.on('app:sync:complete', completeSpy);

            sinon.stub(User, 'load').callsFake(function(callback) {
                callback();
            });
            sinon.stub(app.metadata, 'sync').callsFake(function(callback) {
                callback();
            });

            app.sync({callback: complete1});
            app.sync({callback: complete2});
            app.sync({callback: complete3Spy});

            SugarTest.wait();

            runs(function() {
                expect(User.load).toHaveBeenCalledOnce();
                expect(app.metadata.sync).toHaveBeenCalledOnce();

                expect(completeSpy).toHaveBeenCalled();

                expect(complete1).toHaveBeenCalled();
                expect(complete2).toHaveBeenCalled();
                expect(complete3Spy).toHaveBeenCalled();

                app.off('app:sync:complete', completeSpy);
            });
        });
    });

    it('should navigate given context, model and action', function() {
        var model = new Backbone.Model(),
            mock,
            action = "edit",
            options = {},
            context = new Context();

        app.router = {
            buildRoute: function() {},
            navigate: function() {}
        };

        mock = sinon.mock(app.router);
        model.set("id", "1234");
        model.module = "Contacts";
        context.set("model", model);

        mock.expects("buildRoute").withArgs("Contacts", "1234", "edit");
        mock.expects("navigate").once();

        app.navigate(context, model, action, options);

        mock.verify();
        delete app.router;
    });

    it("should login", function() {

        var loginResponse = fixtures.api["/rest/v10/oauth2/token"].POST.response;

        var server = SugarTest.server;
        server.respondWith("POST", /.*\/rest\/v10\/oauth2\/token.*/,
            [200, {"Content-Type": "application/json"},
                JSON.stringify(loginResponse)]);

        var loginSpy = sinon.spy(app.api, "login");
        var appSpy = sinon.spy(app, "trigger");
        var syncStub = sinon.stub(app, "sync");
        var callbackSpy = sinon.spy(function(loginData) {});
        var completeSpy = sinon.spy();
        app.login({user:'dauser',pass:'dapass'}, null, {
            success: callbackSpy,
            complete: completeSpy
        });
        server.respond();

        expect(loginSpy).toHaveBeenCalled();
        expect(appSpy).toHaveBeenCalledWith("app:login:success", loginResponse);
        expect(callbackSpy).toHaveBeenCalledWith(loginResponse);
        expect(completeSpy).toHaveBeenCalled();
        expect(syncStub).toHaveBeenCalled();

        loginSpy.restore();
        appSpy.restore();
        syncStub.restore();
    });

    it("should process error if login fails", function() {
        server.respondWith("POST", /.*\/rest\/v10\/oauth2\/token.*/,
            [401, {"Content-Type": "application/json"},
                JSON.stringify({})]);

        var appSpy = sinon.spy(app, "trigger");
        var callbackSpy = sinon.spy(function(error) {});

        app.login({}, null, {
            error: callbackSpy
        });
        server.respond();

        expect(appSpy).not.toHaveBeenCalled();
        expect(callbackSpy).toHaveBeenCalled();

        appSpy.restore();
    });

    it("should logout", function() {
        var mock        = sinon.mock(app.api),
            successFn   = function() {},
            callbacks   = {success: successFn};

        mock.expects("logout").once().withArgs(callbacks);
        app.logout( callbacks );
        expect(mock.verify()).toBeTruthy();
    });

    it("should run compatibility check simply returning true if no config.minServerVersion is set", function() {
        var serverInfoFixture, compatible;
        app.config.minServerVersion = undefined;
        serverInfoFixture = {}; // meaningless for this test
        compatible = app.isServerCompatible(serverInfoFixture);
        expect(compatible).toEqual(true);
    });

    it("should run compatibility check returning true if server version < config.minServerVersion", function() {
        var serverInfoFixture, compatible;
        app.config.minServerVersion = 6.5; // our fixture is 6.6
        serverInfoFixture = fixtures.metadata.server_info;
        compatible = app.isServerCompatible(serverInfoFixture);
        expect(compatible).toEqual(true);
    });

    it("should run compatibility check returning error object if server version > config.minServerVersion", function() {
        var expectedErrorObject, serverInfoFixture, compatible;
        serverInfoFixture = fixtures.metadata.server_info;
        expectedErrorObject = {
            code: "server_version_incompatible",
            label: "ERR_SERVER_VERSION_INCOMPATIBLE",
            server_info: serverInfoFixture
        };
        app.config.minServerVersion = "6.7";
        compatible = app.isServerCompatible(serverInfoFixture);
        expect(compatible).toEqual(expectedErrorObject);
    });

    it("should run compatibility check returning error object if no data supplied and min version is specified", function() {
        var expectedErrorObject, compatible;
        expectedErrorObject = {
            code: "server_version_incompatible",
            label: "ERR_SERVER_VERSION_INCOMPATIBLE",
            server_info: null
        };
        app.config.minServerVersion = "1.1";
        // Even though minServerVersion < server version ... we sill supply no data thus getting an error object
        compatible = app.isServerCompatible(null);
        expect(compatible).toEqual(expectedErrorObject);
    });

    it("should run compatibility check returning OK if flavor is ENT", function() {
        var serverInfoFixture, compatible;
        serverInfoFixture = fixtures.metadata.server_info;
        app.config.supportedServerFlavors = ["PRO", "ENT", "ULT"];
        app.config.minServerVersion = "6.7";
        compatible = app.isServerCompatible(serverInfoFixture);
        expect(compatible).toBeTruthy();
    });

    it("should run compatibility check returning error object if no data supplied and min version and flavors are specified", function() {
        var expectedErrorObject, compatible;
        expectedErrorObject = {
            code: "server_version_incompatible",
            label: "ERR_SERVER_VERSION_INCOMPATIBLE",
            server_info: null
        };
        app.config.minServerVersion = "6.7";
        app.config.supportedServerFlavors = ["PRO", "ENT", "ULT"];
        compatible = app.isServerCompatible(null);
        expect(compatible).toEqual(expectedErrorObject);
    });

    it("should run compatibility check returning error object if no data supplied and just flavors are specified", function() {
        var expectedErrorObject, compatible;
        expectedErrorObject = {
            code: "server_flavor_incompatible",
            label: "ERR_SERVER_FLAVOR_INCOMPATIBLE",
            server_info: null
        };
        app.config.minServerVersion = null;
        app.config.supportedServerFlavors = ["PRO", "ENT", "ULT"];
        compatible = app.isServerCompatible(null);
        expect(compatible).toEqual(expectedErrorObject);
    });

    /**
     * @see core/user.js
     */
    describe("language process", function() {

        var cbSpy, complete, dmstub, sstub, suser, mstub, stub, sauth;

        beforeEach(function() {
            cbSpy = sinon.spy();
            complete = function() {
                SugarTest.setWaitFlag();
            };

            dmstub = sinon.stub(app.data, "declareModels").callsFake(function() { /* nop */ });
            sstub = sinon.stub(app, "isServerCompatible").callsFake(function(callback) { callback(); });
            suser = sinon.stub(User, 'updateLanguage').callsFake(function(language, callback) {
                callback();
            });
            mstub = sinon.stub(User, 'load').callsFake(function(callback) { callback(); });
            stub = sinon.stub(app.metadata, "sync").callsFake(function(callback) { callback(); });

            app.router = {
                reset: sinon.stub()
            };

            app.events.on('app:locale:change', cbSpy);
        });

        afterEach(function() {
            dmstub.restore();
            sauth.restore();
            sstub.restore();
            stub.restore();
            mstub.restore();
            suser.restore();
            User.clear();
            delete app.router;
        });

        describe("an authenticated user changes the language", function() {

            beforeEach(function() {
                sauth = sinon.stub(app.api, "isAuthenticated").callsFake(function() {
                    return true;
                });
            });

            it("should update language on server", function() {
                User.setPreference('language', 'en_us');
                Language.setLanguage("fr_FR", complete);

                SugarTest.wait();

                runs(function() {
                    expect(app.cache.get("lang")).toEqual("fr_FR");
                    expect(User.getPreference('language')).toEqual('fr_FR');
                    expect(suser).toHaveBeenCalled();
                    expect(app.router.reset).toHaveBeenCalled();
                    expect(cbSpy).toHaveBeenCalled();
                });
            });

            it("should not update language on server because of noUserUpdate:true", function() {
                User.setPreference('language', 'en_us');
                Language.setLanguage("fr_FR", complete, {noUserUpdate:true});

                SugarTest.wait();

                runs(function() {
                    expect(app.cache.get("lang")).toEqual("fr_FR");
                    expect(User.getPreference('language')).toEqual('fr_FR');
                    expect(suser).not.toHaveBeenCalled();
                    expect(app.cache.get("langHasChanged")).toBeFalsy();
                    expect(app.router.reset).toHaveBeenCalled();
                    expect(cbSpy).toHaveBeenCalled();
                });
            });
        });

        describe("a non-authenticated user changes the language", function() {

            var syncPublicStub;

            beforeEach(function() {
                sauth = sinon.stub(app.api, "isAuthenticated").callsFake(function() {
                    return false;
                });
                syncPublicStub = sinon.stub(app, "syncPublic").callsFake(function(options) { options.callback(); });
            });
            afterEach(function() {
                syncPublicStub.restore();
            });

            it("should set langHasChanged to true", function() {
                User.setPreference('language', 'en_us');
                Language.setLanguage("fr_fr", complete);

                SugarTest.wait();

                runs(function() {
                    expect(app.cache.get("lang")).toEqual("fr_fr");
                    expect(User.getPreference('language')).toEqual('fr_fr');
                    expect(syncPublicStub).toHaveBeenCalled();
                    expect(app.cache.get('langHasChanged')).toBeTruthy();
                    expect(cbSpy).toHaveBeenCalled();
                });
            });

            it("should update language when the user login", function() {
                app.cache.set('langHasChanged', true);
                Language.setCurrentLanguage('fr_fr');

                app.sync({callback:complete});

                SugarTest.wait();

                runs(function() {
                    expect(suser).toHaveBeenCalled();
                    expect(app.cache.get('langHasChanged')).toBeFalsy();
                    expect(app.router.reset).toHaveBeenCalled();
                    app.cache.cut('langHasChanged');
                });
            });
        });
    });

    describe("having config.loadCss set", function() {

        let cssResponse = {
            url: ['tests/fixtures/test.css'],
            text: ['body { background-color: #fff; }']
        };

        it("should call the CSS Api when the app initializes and call metadata.sync after", function() {

            var server = SugarTest.server;
            server.respondWith("GET", /.*\/rest\/v10\/css.*/,
                [200, {"Content-Type": "application/json"},
                    JSON.stringify(cssResponse)]);

            var loadCssStub = sinon.stub(app, "loadCss").callsFake(function(callback) { callback(); });
            var syncStub = sinon.stub(app.metadata, "sync");

            app.config.loadCss = "url";
            app.config.syncConfig = true;
            app._init({el: "body"});
            server.respond();

            expect(loadCssStub).toHaveBeenCalled();
            expect(syncStub).toHaveBeenCalled();

            app.config.loadCss = false;
            app.config.syncConfig = false;
            loadCssStub.restore();
            syncStub.restore();
        });

        it("should call the CSS Api and load the link in the header", function() {

            var server = SugarTest.server;
            server.respondWith("GET", /.*\/rest\/v10\/css.*/,
                [200, {"Content-Type": "application/json"},
                    JSON.stringify(cssResponse)]);

            var cssSpy = sinon.spy(app.api, "css");
            var syncStub = sinon.stub(app.metadata, "sync");

            app.config.loadCss = "url";
            app.config.syncConfig = false;
            app._init({el: "body"});
            server.respond();

            expect(cssSpy).toHaveBeenCalled();
            expect(syncStub).not.toHaveBeenCalled();

            var linkCss = $('head').children('link:last-child').attr('href').split('?');
            expect(linkCss[0]).toEqual(Utils.buildUrl(cssResponse.url[0]));
            $('head').children('link:last-child').remove();

            app.config.loadCss = false;
            cssSpy.restore();
            syncStub.restore();
        });

        it("should call the CSS Api and load the css in the header", function() {

            var server = SugarTest.server;
            server.respondWith("GET", /.*\/rest\/v10\/css.*/,
                [200, {"Content-Type": "application/json"},
                    JSON.stringify(cssResponse)]);

            var cssSpy = sinon.spy(app.api, "css");
            var syncStub = sinon.stub(app.metadata, "sync");

            app.config.loadCss = "text";
            app.config.syncConfig = false;
            app._init({el: "body"});
            server.respond();

            expect(cssSpy).toHaveBeenCalled();
            expect(syncStub).not.toHaveBeenCalled();

            var linkCss = $('head').children('style:last-child').html();
            expect(linkCss).toEqual(cssResponse.text[0]);
            $('head').children('style:last-child').remove();

            app.config.loadCss = false;
            cssSpy.restore();
            syncStub.restore();
        });

    });

    describe('setConfig', function() {
        it('should set the given configuration on the app object, extending the existing or overriding the existing values', function() {
            let oldConfig = app.config;
            app.config = {prop1: 'val1'};

            app.setConfig({prop2: 'val2'});

            expect(app.config).toEqual({prop1: 'val1', prop2: 'val2'});

            app.setConfig({prop1: 'other_val', prop3: 'val3'});

            expect(app.config).toEqual({prop1: 'other_val', prop2: 'val2', prop3: 'val3'});

            app.config = oldConfig;
        });
    });
});
