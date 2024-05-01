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
describe('Base.View.OmnichannelDashboardView', function() {
    var view;
    var layout;
    var omniDashboardLayout;

    beforeEach(function() {
        omniDashboardLayout = SugarTest.createLayout('base', 'Contacts', 'omnichannel-dashboard');
        layout = SugarTest.createLayout('base', 'Dashboards', 'dashboard', null, null, null, {
            layout: omniDashboardLayout
        });

        SugarTest.loadComponent('base', 'view', 'tabbed-dashboard');
        view = SugarTest.createView('base', 'Contacts', 'omnichannel-dashboard', null, null, false, layout);
    });

    afterEach(function() {
        sinon.restore();
        omniDashboardLayout.dispose();
        layout.dispose();
        view.dispose();
    });

    describe('_setTabs', function() {
        it('should check for an active tab index in the layout', function() {
            omniDashboardLayout.initActiveTab = 3;
            view._setTabs({});
            expect(view.activeTab).toEqual(3);
        });
    });
});
