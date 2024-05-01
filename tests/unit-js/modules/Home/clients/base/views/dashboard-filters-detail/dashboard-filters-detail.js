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
describe('HomeDashboardFiltersDetail', function() {
    let filtersView;
    let app;

    beforeEach(function() {
        app = SugarTest.app;
        let context = new app.Context();
        filtersView = SugarTest.createView('base', 'Home', 'dashboard-filters-detail', null, context, true);
        sinon.stub(filtersView, 'listenTo');

        app.user.set({'id': 'test_userid', full_name: 'Selected User'});
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        context = null;
        filtersView.dispose();
        filtersView = null;
        app.user.unset('id');
    });

    describe('initialize', function() {
        it('should call _registerEvents', function() {
            spyOn(filtersView, '_registerEvents');

            filtersView.initialize({});

            expect(filtersView._registerEvents).toHaveBeenCalled();
        });
    });

    describe('_registerEvents', function() {
        it('should listen for dashboard-filters-metadata-loaded events', function() {
            filtersView._registerEvents();
            expect(filtersView.listenTo).toHaveBeenCalled();
        });
    });

    describe('_buildDashboardStateKey', function() {
        it('should return the correct state key', function() {
            filtersView.module = 'Home';
            var result = filtersView._buildDashboardStateKey('test');

            expect(result).toEqual('Home:test:test_userid:dashboard-filters');
        });
    });

    describe('_getDashboardLastState', function() {
        it('should return the last state if it exists', function() {
            sinon.stub(app.user.lastState, 'get').returns('{"test": "data"}');
            var result = filtersView._getDashboardLastState('test');

            expect(result).toEqual({test: 'data'});
        });

        it('should return the default state if the last state does not exist', function() {
            sinon.stub(app.user.lastState, 'get').returns(null);
            var result = filtersView._getDashboardLastState('test');

            expect(result).toEqual({});
        });
    });

    describe('_getDashboardDefaultState', function() {
        it('should return an empty object', function() {
            var result = filtersView._getDashboardDefaultState();

            expect(result).toEqual({});
        });
    });
});
