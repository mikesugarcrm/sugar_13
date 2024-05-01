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
describe('Home.Routes', function() {
    var app, loadViewStub, buildKeyStub, getStub, setStub;

    beforeEach(function() {
        app = SugarTest.app;
        app.controller.loadAdditionalComponents(app.config.additionalComponents);
        // FIXME: SC-4677, load additionalComponents in tests
        // "Before Route Show Wizard Check" dependency
        loadViewStub = sinon.stub(app.controller, 'loadView');
        buildKeyStub = sinon.stub(app.user.lastState, 'buildKey');
        getStub = sinon.stub(app.user.lastState, 'get');
        setStub = sinon.stub(app.user.lastState, 'set');

        SugarTest.loadFile('../modules/Home/clients/base/routes', 'routes', 'js', function(d) {
            eval(d);
            app.routing.start();
        });
    });

    afterEach(function() {
        sinon.restore();
        app.router.stop();
    });

    describe('Routes', function() {
        var mockKey = 'foo:key';
        var oldIsSynced;

        beforeEach(function() {
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

        describe('Activities', function() {
            it('should set last visited Home to activity stream when routing to activity stream', function() {
                app.router.navigate('activities', {trigger: true});

                expect(setStub).toHaveBeenCalledWith(mockKey, 'activities');
                expect(loadViewStub).toHaveBeenCalledWith({
                    layout: 'activities',
                    module: 'Activities'
                });
            });
        });

        describe('homeRecord', function() {
            var recordStub;

            beforeEach(function() {
                recordStub = sinon.stub(app.router, 'record');
            });

            it('should set last visited Home to dashboard when routing to a dashboard', function() {
                app.router.navigate('Home/test_ID', {trigger: true});

                expect(setStub).toHaveBeenCalledWith(mockKey, 'dashboard');
                expect(loadViewStub).toHaveBeenCalledWith({
                    module: 'Home',
                    layout: 'record',
                    action: 'detail',
                    modelId: 'test_ID'
                });
            });
        });

        describe('Home', function() {
            var redirectStub;

            beforeEach(function() {
                redirectStub = sinon.stub(app.router, 'redirect');
            });

            using('homeOptions', [
                {
                    value: 'dashboard',
                    redirectCalled: false
                },
                {
                    value: 'activities',
                    redirectCalled: true
                }
            ], function(option) {
                it('should navigate to the appropriate route according to the lastState', function() {
                    getStub.returns(option.value);
                    app.router.navigate('Home', {trigger: true});

                    expect(redirectStub.calledWith('#activities')).toBe(option.redirectCalled);
                });
            });
        });
    });
});
