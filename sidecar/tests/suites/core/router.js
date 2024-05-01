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

describe('Core/Router', function() {
    var app, router, defaultModule, externalLogin, navigateStub, moduleExistsStub;

    beforeEach(function() {
        app = SugarTest.app;
        defaultModule = app.config.defaultModule;
        externalLogin = app.config.externalLogin;
        navigateStub = sinon.stub(app.Router.prototype, 'navigate');
        app.routing.start();
        router = app.router;
        moduleExistsStub = sinon.stub(app.router, '_moduleExists');
        moduleExistsStub.returns(true);
    });

    afterEach(function() {
        app.config.externalLogin = externalLogin;
        app.config.defaultModule = defaultModule;
        sinon.restore();
        navigateStub.restore();
        if (moduleExistsStub) moduleExistsStub.restore();
        app.router.stop();
    });

    it("should build a route given a model", function(){
        var route,
            model = new Backbone.Model(),
            action = "edit";

        model.set("id", "1234");
        model.module = "Contacts";

        route = router.buildRoute(model.module, model.id, action);

        expect(route).toEqual("Contacts/1234/edit");
    });

    describe("_moduleExists", function(){
        var modStub;
        beforeEach(function(){
            moduleExistsStub.restore();
            moduleExistsStub = undefined;
            modStub = sinon.stub(app.metadata, "getModule");

        });
        afterEach(function(){
            modStub.restore();
        });
        it("should load a 404 error view when module metadata does not exist", function(){
            var mock = sinon.mock(app.error);
            mock.expects("handleHttpError").once().withArgs({status: 404});
            modStub.returns(undefined);
            expect(app.router._moduleExists("DOESNOTEXIST")).toEqual(false);
            expect(mock.verify()).toBeTruthy();
        });
        it("should load a 404 error view when module metadata does not exist", function(){
            var mock = sinon.mock(app.error);
            mock.expects("handleHttpError").never();
            modStub.returns({});
            expect(app.router._moduleExists("MODULEEXISTS")).toEqual(true);
            expect(mock.verify()).toBeTruthy();
        });
    });


    it("should build a route given a context", function(){
        var route,
            context = { get: function() { return "Contacts"; }},
            action = "create";

        route = router.buildRoute(context, null, action);

        expect(route).toEqual("Contacts/create");
    });

    it("should handle index route with default module", function() {
        app.config.defaultModule = "Cases";

        router.index();
        expect(navigateStub.calledWith(app.config.defaultModule, {trigger:true})).toBeTruthy();
    });

    it("should handle index route with unspecified default module", function() {
        app.config.defaultModule = null;

        router.index();
        expect(navigateStub.calledWith('Home', {trigger:true})).toBeTruthy();
    });

    it('should handle index route on external auth', function() {
        app.config.defaultModule = null;

        app.config.externalLogin = true;
        app.cache.set('externalAuthLastPage', '');
        router.index();
        expect(navigateStub.calledWith('Home', {trigger: true})).toBeTruthy();
    });

    it('should handle index route with last visited page on external auth', function() {
        app.config.defaultModule = null;

        app.config.externalLogin = true;
        app.cache.set('externalAuthLastPage', 'someLastPage');
        router.index();
        expect(navigateStub.calledWith('someLastPage', {trigger: true})).toBeTruthy();
    });

    it('should handle adding routes and stop after the first matching route', function() {
        var routes = [
            {
                name: 'myRouteCreate',
                route: 'HelloWorld/create',
                callback: function() {
                    return('myRouteCreate');
                }
            },
            {
                name: 'myRoute',
                route: 'HelloWorld(/:my_custom_route)',
                callback: function() {
                    return('myRoute');
                }
            }
        ];
        router.addRoutes(routes);

        var route,
            context = { get: function() { return 'HelloWorld'; }},
            action = 'create';
        route = router.buildRoute(context, null, action);

        expect(route).toEqual('HelloWorld/create');
    });

    it("should handle arbitrary layout route", function() {
        var mock = sinon.mock(app.controller);
        mock.expects("loadView").once().withArgs({
            module:'Cases',
            layout:'list'
        });

        router.layout('Cases', 'list');
        expect(mock.verify()).toBeTruthy();
    });

    it("should handle create route", function() {
        var mock = sinon.mock(app.controller);
        mock.expects("loadView").once().withArgs({
            module: 'Cases',
            create: true,
            layout: 'edit'
        });

        router.create('Cases');
        expect(mock.verify()).toBeTruthy();
    });

    it("should handle record route", function() {
        var mock = sinon.mock(app.controller);
        mock.expects("loadView").once().withArgs({
            module: 'Cases',
            modelId: 123,
            action: 'edit',
            layout: 'record'
        });

        router.record('Cases', 123, 'edit');
        expect(mock.verify()).toBeTruthy();
    });

    it("should handle login route", function() {
        var mock = sinon.mock(app.controller);
        mock.expects("loadView").once().withArgs({
            module:'Login',
            layout:'login',
            create: true
        });

        router.login();
        expect(mock.verify()).toBeTruthy();
    });

    using('different auth values', [
        {
            isAuthenticated: true,
            numLogoutCalls: 1
        },
        {
            isAuthenticated: false,
            numLogoutCalls: 0
        }
    ], function(provider) {
        it('should handle the logout route', function() {
            var mock = sinon.mock(app.api);
            var stub = sinon.stub(app.api, 'isAuthenticated').returns(provider.isAuthenticated);

            mock.expects('logout').exactly(provider.numLogoutCalls);
            router.logout();

            mock.verify();
        });
    });

    it("should reject a secure route if the user is not authenticated", function() {
        var stub = sinon.stub(app.api, "isAuthenticated").callsFake(function() { return false; });
        var beforeRouting = app.routing.beforeRoute("index");
        expect(beforeRouting).toBeFalsy();
        stub.restore();
    });

    it("should reject a secure route if the app is not synced", function() {
        app.isSynced = false;
        var beforeRouting = app.routing.beforeRoute("index");
        expect(beforeRouting).toBeFalsy();
    });

    it("should always accept an unsecure route", function() {
        var beforeRouting = app.routing.beforeRoute("signup");
        expect(beforeRouting).toBeTruthy();
    });

    it("should call a route handler and routing.after if routing.before returns true", function() {
        sinon.stub(app.routing, "beforeRoute").callsFake(function() { return true; });
        var stub = sinon.stub(app.routing, "after");
        var stub2 = sinon.stub(app.router, "index");

        app.router._routeHandler(app.router.index);
        expect(stub).toHaveBeenCalled();
        expect(stub2).toHaveBeenCalled();
        app.routing.beforeRoute.restore();
        app.routing.after.restore();
        app.router.index.restore();
    });

    it("should not call a route handler and routing.after if routing.before returns false", function() {
        sinon.stub(app.routing, "beforeRoute").callsFake(function() { return false; });
        var spy = sinon.spy(app.routing, "after");
        var spy2 = sinon.spy(app.router, "index");

        app.router._routeHandler(app.router.index);
        expect(spy).not.toHaveBeenCalled();
        expect(spy2).not.toHaveBeenCalled();
        app.routing.beforeRoute.restore();
        app.routing.after.restore();
        app.router.index.restore();
    });

    // TODO: This test has been disabled, as the paramters don't work properly. Need to add supporting routes
    xit("should add params to a route if given in options ", function(){
        var route,
            context = {},
            options = {
                module: "Contacts",
                params: [
                    {name: "first", value: "Rick"},
                    {name: "last", value: "Astley"},
                    {name: "job", value: "Rock Star"}
                ]
            },
            action = "create";

        route = router.buildRoute(context, action, {}, options);

        expect(route).toEqual("Contacts/create?first=Rick&last=Astley&job=Rock+Star");
    });

    it("should should trigger before event before routing", function() {
        var callback = sinon.stub(),
            route = "Accounts",
            args = {id:"1234"};

        app.routing.before("route", callback);
        app.routing.beforeRoute(route, args);
        expect(callback).toHaveBeenCalledWith({route:route, args:args});
        app.routing.offBefore("route", callback);
    });

    it("should not navigate when callback return false", function() {
        var callback = sinon.stub().returns(false),
            route = app.config.unsecureRoutes[0],
            args = {id:"1234"};

        expect(app.routing.beforeRoute(route, args)).toBeTruthy();
        app.routing.before("route", callback);
        expect(app.routing.beforeRoute(route, args)).toBeFalsy();
        app.routing.offBefore("route", callback);
    });

    it("should trigger load view with the same arguments when refresh is called", function() {
        var mock = sinon.mock(Backbone.history);
        var frag = Backbone.history.fragment;
            Backbone.history.fragment = "Cases/layout/list";

        mock.expects("loadUrl").once().withArgs(Backbone.history.fragment);

        router.refresh();

        expect(mock.verify()).toBeTruthy();

        Backbone.history.fragment = frag;
        mock.restore();
    });

    describe('reset', function() {
        var init;
        var stop;
        var start;
        beforeEach(function() {
            router._previousFragment = 'bwc/index.php?module=Administration&action=index';
            stop = sinon.stub(router, 'stop');
            init = sinon.stub(router, 'init');
            start = sinon.stub(router, 'start');
        });
        afterEach(function() {
            router._previousFragment = '';
            init = null;
            start = null;
            stop = null;
        });
        it('should check if previous fragement remains the same', function() {
            router.reset();
            expect(stop).toHaveBeenCalled();
            expect(init).toHaveBeenCalled();
            expect(start).toHaveBeenCalled();
            expect(router._previousFragment).toEqual('bwc/index.php?module=Administration&action=index');
        });
    });
});
