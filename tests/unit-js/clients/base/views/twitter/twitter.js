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
describe("Twitter View", function() {

    var app, view, moduleName = 'Home', layout;

    beforeEach(function() {
        if (!$.fn.tooltip) {
            $.fn.tooltip = sinon.stub();
        }
        SugarTest.loadPlugin('Connector');
        app = SugarTest.app;
        app.drawer = {
            close: $.noop,
            reset: $.noop,
            open: $.noop
        };
        var context = app.context.getContext({module: moduleName});
        context.set('model', app.data.createBean(moduleName));
        context.parent = app.context.getContext({
            module: moduleName
        });
        layout = SugarTest.createLayout('base', moduleName, 'list', null, context.parent);
        view = SugarTest.createView('base', moduleName, 'twitter', {}, context, true, layout);
        view.model = new Backbone.Model();
        view.settings = new Backbone.Model();
        view.settings.set('twitter','test');
        view.moduleType = 'Home';
        view.context.set('module', moduleName);
        SugarTest.clock.restore();
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        delete app.drawer;
        Handlebars.templates = {};
        view.model = null;
        view = null;
        delete app.plugins.plugins['view']['Connector'];
    });

    it("should set date flag", function() {
        // workaround since dashlet config not testable atm
        view.meta.config = false;

        var rightNow = new Date();
        var twoDaysFromToday = new Date(rightNow.getTime()-(1000*60*60*24*3));
        var tweets = [{
            created_at: rightNow.toString(),
            text:'test1',
            user:{
                name:'test1'
            }
        },{
            created_at: twoDaysFromToday.toString(),
            text:'test1',
            user:{
                name:'test1'
            }
        }];
        SugarTest.seedFakeServer();
        SugarTest.server.respondImmediately = true;

        SugarTest.server.respondWith("GET", /.*rest\/v10\/connector\/twitter\/currentUser.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify({})]);
        SugarTest.server.respondWith("GET", /.*rest\/v10\/connector\/twitter\/test.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify(tweets)]);
        SugarTest.server.respondWith("GET", /.*rest\/v10\/connectors.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify({
                _hash : 'lolhi2u',
                connectors : {
                    ext_rest_twitter : {
                        eapm_bean: true,
                        testing_enabled : true,
                        test_passed : true,
                        field_mapping : {}
                    }
                }
            })]);

        view.loadData();

        expect(view.tweets[0].useAbsTime).toBeFalsy();
        expect(view.tweets[1].useAbsTime).toBeTruthy();
    });

    it("should set current user info", function() {
        // workaround since dashlet config not testable atm
        view.meta.config = false;

        SugarTest.seedFakeServer();
        SugarTest.server.respondImmediately = true;
        SugarTest.server.respondWith("GET", /.*rest\/v10\/connector\/twitter\/currentUser.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify({
                screen_name : 'testName',
                profile_image_url: 'testURL'
            })]);
        SugarTest.server.respondWith("GET", /.*rest\/v10\/connector\/twitter\/test.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify([])]);
        SugarTest.server.respondWith("GET", /.*rest\/v10\/connectors.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify({
                _hash : 'lolhi2u',
                connectors : {
                    ext_rest_twitter : {
                        eapm_bean: true,
                        testing_enabled : true,
                        test_passed : true,
                        field_mapping : {}
                    }
                }
            })]);

        view.loadData();

        expect(view.current_twitter_user_name).toEqual('testName');
        expect(view.current_twitter_user_pic).toEqual('testURL');
    });

    it("should pull twitter field from config when context is not Home", function() {
        // workaround since dashlet config not testable atm
        view.meta.config = false;
        let apiSpy = sinon.spy(SugarTest.app.api, 'call');
        sinon.stub(view.settings, 'get').returns('bob');

        SugarTest.seedFakeServer();
        SugarTest.server.respondImmediately = true;
        SugarTest.server.respondWith("GET", /.*rest\/v10\/connectors.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify({
                _hash : 'lolhi2u',
                connectors : {
                    ext_rest_twitter : {
                        eapm_bean: true,
                        testing_enabled : true,
                        test_passed : true,
                        field_mapping : {}
                    }
                }
            })]);

        view.loadData();

        expect(apiSpy.getCall(2).args[1].indexOf('bob')).toBeGreaterThan(-1);

        view.model.set('name','test');
        view.context.set('module', 'test');
        view.loadData();
        expect(apiSpy.getCall(4).args[1].indexOf('bob')).toEqual(-1);
    });

    it("should loop only once with bad connector", function() {
        view.meta.config = false;
        var apiStub = sinon.spy(SugarTest.app.api, 'call');

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*rest\/v10\/connectors.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify({
                _hash : 'lolhi2u',
                connectors : {
                    ext_rest_twitter : {
                        eapm_bean: false,
                        testing_enabled : true,
                        test_passed : false,
                        field_mapping : {}
                    }
                }
            })]);

        view.loadData();
        SugarTest.server.respond();

        expect(apiStub.callCount).toEqual(1);
    });

    it("should loop only once when 412 error", function() {
        view.meta.config = false;
        var apiStub = sinon.spy(SugarTest.app.api, 'call');

        SugarTest.seedFakeServer();
        SugarTest.server.respondImmediately = true;
        SugarTest.server.respondWith("GET", /.*rest\/v10\/connectors.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify({
                _hash : 'lolhi2u',
                connectors : {
                    ext_rest_twitter : {
                        eapm_bean: true,
                        testing_enabled : true,
                        test_passed : true,
                        field_mapping : {}
                    }
                }
            })]);
        SugarTest.server.respondWith("GET", /.*rest\/v10\/connector\/twitter\/currentUser.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify({})]);
        SugarTest.server.respondWith("GET", /.*rest\/v10\/connector\/twitter\/test.*/,
            [412, { "Content-Type": "application/json"}, JSON.stringify([])]);

        view.loadData();

        expect(apiStub.callCount).toEqual(6);
    });

    it("handleLoadError should set needOAuth if test fails", function() {
        view.meta.config = false;
        sinon.spy(SugarTest.app.api, 'call');

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*rest\/v10\/connectors.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify({
                _hash : 'lolhi2u',
                connectors : {
                    ext_rest_twitter : {
                        eapm_bean: true,
                        testing_enabled : true,
                        test_passed : false,
                        field_mapping : {}
                    }
                }
            })]);

        view.loadData();
        SugarTest.server.respond();

        expect(view.needOAuth).toEqual(true);
        expect(view.needConnect).toEqual(false);
    });

    it("handleLoadError should set needConnect if EAPM field returned false", function() {
        view.meta.config = false;
        sinon.spy(SugarTest.app.api, 'call');

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*rest\/v10\/connectors.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify({
                _hash : 'lolhi2u',
                connectors : {
                    ext_rest_twitter : {
                        eapm_bean: false,
                        testing_enabled : false,
                        test_passed : false,
                        field_mapping : {}
                    }
                }
            })]);

        view.loadData();
        SugarTest.server.respond();

        expect(view.needOAuth).toEqual(false);
        expect(view.needConnect).toEqual(true);
    });
});
