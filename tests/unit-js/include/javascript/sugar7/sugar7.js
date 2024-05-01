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
describe('Sugar7.Routes', function() {
    var app, loadViewStub, buildKeyStub, getStub, setStub;

    beforeEach(function() {
        app = SugarTest.app;
        loadViewStub = sinon.stub(app.controller, 'loadView');
        buildKeyStub = sinon.stub(app.user.lastState, 'buildKey');
        getStub = sinon.stub(app.user.lastState, 'get');
        setStub = sinon.stub(app.user.lastState, 'set');

        SugarTest.loadFile('../include/javascript', 'sugar7', 'js', function(d) {
            eval(d);
            app.routing.start();
        });
    });

    afterEach(function() {
        sinon.restore();
        app.router.stop();
    });

    describe('Tile View Route', function() {
        let mockKey = 'foo:key';
        let oldIsSynced;
        let sinonSandbox;
        let routerStub;

        beforeEach(function() {
            oldIsSynced = app.isSynced;
            app.isSynced = true;

            sinon.stub(app.router, 'hasAccessToModule').returns(true);
            sinon.stub(app.api, 'isAuthenticated').returns(true);
            sinon.stub(app, 'sync');
            buildKeyStub.returns(mockKey);
            sinonSandbox = sinon.createSandbox();
            routerStub = sinonSandbox.stub(app.router, 'redirect');
        });

        afterEach(function() {
            app.isSynced = oldIsSynced;
        });

        it('should call the redirect method', function() {
            app.router.navigate('Opportunities/pipeline', {trigger: true});
            expect(routerStub).toHaveBeenCalled();
        });
    });

    describe('Routes', function() {
        var mockKey = 'foo:key',
            oldIsSynced;

        beforeEach(function () {
            oldIsSynced = app.isSynced;
            app.isSynced = true;
            sinon.stub(app.router, 'index');
            sinon.stub(app.router, 'hasAccessToModule').returns(true);
            sinon.stub(app.api, 'isAuthenticated').returns(true);
            sinon.stub(app, 'sync');
            buildKeyStub.returns(mockKey);
        });

        afterEach(function() {
            app.isSynced = oldIsSynced;
        });

        describe('404', function() {
            var errorStub, appMetaStub;

            beforeEach(function() {
                appMetaStub = sinon.stub(app.metadata, 'getModule');
                errorStub = sinon.stub(app.error, 'handleHttpError');
            });

            // FIXME: We should ensure that current routes work as expected
            // with valid modules as well, aka, testing route callbacks; will
            // be completed in SC-2761.
            using('module routes', [
                'notexists',
                'notexists/test_ID',
                'notexists/create',
                'notexists/vcard-import',
                'notexists/config',
                'notexists/layout/test_view',
                'notexists/test_ID/edit',
                'notexists/test_ID/layout/test_view',
                'notexists/test_ID/layout/test_view/edit'
            ], function(route) {
                it('should redirect to 404 if module does not exist', function() {
                    app.router.navigate(route, {trigger: true});
                    expect(errorStub).toHaveBeenCalledWith({status: 404});
                });
            });
        });

        describe('Forecast Routes', function(){

            beforeEach(function(){
                sinon.stub(app.metadata, 'getModule').callsFake(function() {
                    return {forecast_by: 'RevenueLineItems'};
                });
                sinon.stub(app.alert, 'show');
            });

            it('should not restrict access', function(){
                sinon.stub(app.user, 'getAcls').callsFake(function() {
                    return {Forecasts: {}, RevenueLineItems: {}};
                });
                sinon.stub(app.utils, 'checkForecastConfig').callsFake(function() {
                    return true;
                });

                app.router.navigate('Forecasts', {trigger: true});

                expect(app.controller.loadView).toHaveBeenCalled();
            });

            it('should pop up an alert because of no access to forecasts', function() {
                sinon.stub(app.user, 'getAcls').callsFake(function() {
                    return {Forecasts: {access:'no'}, RevenueLineItems: {}};
                });
                sinon.stub(app.utils, 'checkForecastConfig').callsFake(function() {
                    return true;
                });

                app.router.navigate('Forecasts', {trigger: true});

                expect(app.alert.show).toHaveBeenCalled();
            });

            it('should pop up an alert because of no access to forecast by module', function() {
                sinon.stub(app.user, 'getAcls').callsFake(function() {
                    return {Forecasts: {}, RevenueLineItems: {access:'no'}};
                });
                sinon.stub(app.utils, 'checkForecastConfig').callsFake(function() {
                    return true;
                });

                app.router.navigate('Forecasts', {trigger: true});

                expect(app.alert.show).toHaveBeenCalled();
            });

            it('should pop up an alert because of forecasts not being set up', function() {
                sinon.stub(app.user, 'getAcls').callsFake(function() {
                    return {Forecasts: {}, RevenueLineItems: {}};
                });
                sinon.stub(app.utils, 'checkForecastConfig').callsFake(function() {
                    return false;
                });

                app.router.navigate('Forecasts', {trigger: true});

                expect(app.alert.show).toHaveBeenCalled();
            });
        });
    });

    describe('Before Route BWC Redirect Check', function() {
        var hasAccessStub;
        var getModuleStub;

        beforeEach(function() {
            hasAccessStub = sinon.stub(app.acl, 'hasAccess');
            hasAccessStub.returns(true);

            getModuleStub = sinon.stub(app.metadata, 'getModule');
            getModuleStub.returns({isBwcEnabled: true});

        });

        afterEach(function() {
            hasAccessStub.restore();
            getModuleStub.restore();
        });

        it('should not redirect to bwc for search route', function() {
            var route = 'search';
            var response = app.routing.triggerBefore('route', {route: route, args: ['test']});
            expect(response).toBe(true);
        });

        it('should redirect to bwc for list route', function() {
            var route = 'list';
            var response = app.routing.triggerBefore('route', {route: route, args: ['test']});
            expect(response).toBe(false);
        });
    });

    describe("Before Route Show Wizard Check", function() {
        var hasAccessStub;

        beforeEach(function() {
            app.controller.$el.append('<div id="header-nav"></div>');
            app.controller.loadAdditionalComponents(app.config.additionalComponents);
            hasAccessStub = sinon.stub(app.acl, 'hasAccess');
            hasAccessStub.returns(true);
        });

        afterEach(function() {
            app.controller.$el.empty();
            hasAccessStub.restore();
            app.user.unset('show_wizard', {silent: true});
        });

        it("should return false if user's show_wizard true", function() {
            var route = 'record';
            app.user.set('show_wizard', true);
            var response = app.routing.triggerBefore('route', {route:route});
            expect(response).toBe(false);
        });
    });

    describe("Before Route Access Check", function() {
        var hasAccessStub;

        beforeEach(function() {
            hasAccessStub = sinon.stub(app.acl, 'hasAccess');
            hasAccessStub.withArgs('view', 'Foo').returns(true);
            hasAccessStub.withArgs('view', 'Bar').returns(false);
        });

        afterEach(function() {
            hasAccessStub.restore();
        });

        it("should continue to route if routing to the record view and user has access", function() {
            var route = 'record',
                args = ['Foo'];
            var response = app.routing.triggerBefore("route", {route:route, args:args})

            expect(response).toBe(true);
        });

        it("should continue to route if routing to a view that is not on the check access list", function() {
            var route = 'baz',
                args = ['Foo'];
            var response = app.routing.triggerBefore("route", {route:route, args:args})

            expect(response).toBe(true);
        });

        it("should stop route if routing to the record view and user is missing access", function() {
            var route = 'record',
                args = ['Bar'];
            var response = app.routing.triggerBefore("route", {route:route, args:args})

            expect(response).toBe(false);
        });
    });

    describe('Logout event', function() {

        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.testMetadata.set();
            SugarTest.app.data.declareModels();
            SugarTest.declareData('base', 'Filters');
        });

        it('should clear the filters from cache', function() {
            var filters = app.data.getCollectionClasses().Filters;
            sinon.spy(filters.prototype, 'resetFiltersCacheAndRequests');
            app.trigger('app:logout');
            expect(filters.prototype.resetFiltersCacheAndRequests).toHaveBeenCalled();
        });

        describe('View change event', function() {
            let title = 'bar';

            beforeEach(function() {
                sinon.stub(Handlebars, 'compile').callsFake(function() {
                    return function() {return title;};
                });
                sinon.stub(app.controller.context, 'get').callsFake(function() {});
                sinon.stub(app.metadata, 'getModule').callsFake(function() {});
            });

            it('should update "document.title" on "app:view:change"', function() {
                document.title = 'foo';
                app.trigger('app:view:change');
                expect(document.title).toEqual(title);
            });
        });
    });
});
