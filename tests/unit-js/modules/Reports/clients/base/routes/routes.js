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
describe('Reports.Routes', function() {
    var app;
    var loadViewStub;
    var filterOptionsSpy;
    var redirectSpy;

    beforeEach(function() {
        app = SugarTest.app;
        loadViewStub = sinon.stub(app.controller, 'loadView');
        SugarTest.loadFile('../modules/Reports/clients/base/routes', 'routes', 'js', function(d) {
            eval(d);
            app.routing.start();
        });

        app.isSynced = true;
        sinon.stub(app.router, 'index');
        sinon.stub(app.router, 'hasAccessToModule').returns(true);
        sinon.stub(app.api, 'isAuthenticated').returns(true);
        sinon.stub(app, 'sync');
        filterOptionsSpy = sinon.spy(app.utils, 'FilterOptions');
        redirectSpy = sinon.spy(app.router, 'redirect');
        sinon.stub(app.lang, 'get').callsFake(function(key) {
            return 'Accounts';
        });
    });

    afterEach(function() {
        sinon.restore();
        app.router.stop();
    });


    it('should route to Reports listview without filterOptions when no URL params are passed', function() {
        app.router.navigate('Reports', {trigger: true});

        expect(filterOptionsSpy).not.toHaveBeenCalled();
        expect(loadViewStub).toHaveBeenCalledWith({
            filterOptions: null,
            layout: 'records',
            module: 'Reports'
        });
    });

    it('should set filterOptions with the filterModule passed in URL params', function() {
        var filterModule = 'Accounts';
        app.router.navigate('Reports?filterModule=' + filterModule, {trigger: true});

        expect(filterOptionsSpy).toHaveBeenCalled();
        expect(loadViewStub).toHaveBeenCalledWith({
            module: 'Reports',
            layout: 'records',
            filterOptions: {
                filter_populate: {module: {$in: [filterModule]}},
                initial_filter: '$relate',
                initial_filter_label: 'Accounts',
                stickiness: false
            }
        });
    });
});
