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
describe('Plugins.DashboardFiltersVisibility', function() {
    var app;
    var plugin;
    var moduleName = 'Dashboards';
    var userId = 'user1';
    var dashboardId = 'dashboard1';
    var dashboardFiltersActiveKey = 'dashboard-filter-active';

    beforeEach(function() {
        app = SugarTest.app;

        app.user.id = userId;

        SugarTest.loadPlugin('DashboardFiltersVisibility');
        plugin = app.plugins.plugins.layout.DashboardFiltersVisibility;

        plugin.model = SugarTest.app.data.createBean(moduleName, {
            id: dashboardId
        });
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app = null;
    });

    describe('getDashboardFilterActiveKey', function() {
        it('should construct the key', function() {
            plugin._dashboardFilterActiveKey = dashboardFiltersActiveKey;

            const key = plugin.getDashboardFilterActiveKey();

            expect(key).toEqual(`${dashboardId}:${userId}:${dashboardFiltersActiveKey}`);
        });
    });

    describe('initFiltersVisibilityProperties', function() {
        it('should set default values', function() {
            sinon.stub(plugin, 'isDashboardFiltersPanelActive').returns(true);

            plugin.initFiltersVisibilityProperties();

            expect(plugin._dashboardFilterActiveKey).toEqual(dashboardFiltersActiveKey);
            expect(plugin._filtersOnScreen).toEqual(true);
        });
    });

    describe('isDashboardFiltersPanelActive', function() {
        it('should return panel active or not', function() {
            const tempKey = 'testKey';
            sinon.stub(plugin, 'getDashboardFilterActiveKey').returns(tempKey);
            sinon.stub(app.user.lastState, 'get').withArgs(tempKey).returns(true);

            const testRes = plugin.isDashboardFiltersPanelActive();

            expect(testRes).toEqual(true);
        });
    });

    describe('storeFilterPanelState', function() {
        it('should store filter panel state', function() {
            const lastStateStub = sinon.stub(app.user.lastState, 'set');

            const tempKey = 'testKey';
            sinon.stub(plugin, 'getDashboardFilterActiveKey').returns(tempKey);

            plugin.storeFilterPanelState(true);

            expect(lastStateStub).toHaveBeenCalledWith(tempKey, true);
        });
    });
});
