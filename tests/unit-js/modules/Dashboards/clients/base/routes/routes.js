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
describe('Dashboards.Routes', function() {
    var app;
    var oldSync;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();

        SugarTest.loadFile('../modules/Dashboards/clients/base/routes', 'routes', 'js', function(d) {
            eval(d);
            app.routing.start();
        });

        oldSync = app.isSynced;
        app.isSynced = true;

        sandbox.stub(app.api, 'isAuthenticated').returns(true);
    });

    afterEach(function() {
        sandbox.restore();
        app.isSynced = oldSync;
        app.router.stop();
    });

    describe('Routes', function() {
        describe('DashboardCreate', function() {
            it('should throw a 404', function() {
                var errorStub = sandbox.stub(app.error, 'handleHttpError');
                app.router.navigate('Dashboards/create', {trigger: true});

                expect(errorStub).toHaveBeenCalledWith({status: 404});
            });
        });

        describe('DashboardManage', function() {
            var appLangGetStub;
            var loadViewStub;

            beforeEach(function() {
                sandbox.stub(app.metadata, 'getModule').returns(true);
                appLangGetStub = sandbox.stub(app.lang, 'get');
                loadViewStub = sandbox.stub(app.controller, 'loadView');
            });

            it('should not have pre-filled filter if no params are in url', function() {
                var testUrl = 'Dashboards';
                var expected = {
                    module: 'Dashboards',
                    layout: 'records'
                };
                app.router.navigate(testUrl, {trigger: true});
                expect(loadViewStub).toHaveBeenCalledWith(expected);
            });

            it('should set the filter options for RHS dashboards', function() {
                var testModule = 'Tests';
                var testView = 'TestView';

                sandbox.stub(app.lang, 'getModuleName').withArgs(testModule, {plural: true})
                    .returns(testModule);
                appLangGetStub.withArgs('LBL_FILTER_BY_MODULE_AND_VIEW', 'Dashboards', {
                    module: testModule,
                    view: testView
                }).returns('All Dashboards for ' + testModule + ' module ' + testView + ' view');

                var testUrl = 'Dashboards?moduleName=' + testModule + '&viewName=' + testView;
                var expected = {
                    module: 'Dashboards',
                    layout: 'records',
                    filterOptions: {
                        initial_filter: 'module_and_layout',
                        initial_filter_label: 'All Dashboards for ' + testModule + ' module ' + testView + ' view',
                        filter_populate: {
                            dashboard_module: [testModule],
                            view_name: testView
                        },
                        'stickiness': false
                    }
                };

                app.router.navigate(testUrl, {trigger: true});

                expect(loadViewStub).toHaveBeenCalledWith(expected);
            });

            it('should set the filter options for Home dashboards', function() {
                var testModule = 'Home';

                sandbox.stub(app.lang, 'getModuleName').withArgs(testModule, {plural: true})
                    .returns(testModule);
                sandbox.stub(app.lang, 'getAppListStrings').returns({});
                appLangGetStub.withArgs('LBL_FILTER_BY_MODULE', 'Dashboards', {
                    module: testModule
                }).returns('All Dashboards for ' + testModule + ' module');

                var testUrl = 'Dashboards?moduleName=' + testModule;
                var expected = {
                    module: 'Dashboards',
                    layout: 'records',
                    filterOptions: {
                        initial_filter: 'module',
                        initial_filter_label: 'All Dashboards for ' + testModule + ' module',
                        filter_populate: {
                            dashboard_module: [testModule]
                        },
                        'stickiness': false
                    }
                };

                app.router.navigate(testUrl, {trigger: true});

                expect(loadViewStub).toHaveBeenCalledWith(expected);
            });
        });
    });
});
