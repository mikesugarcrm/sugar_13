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

describe('Base.Layout.SidebarNavItemOmnichannel', function() {
    let app;
    let view;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'view', 'sidebar-nav-item');
        SugarTest.loadComponent('base', 'view', 'sidebar-nav-item-omnichannel');

        app = SugarTest.app;
        view = SugarTest.createView('base', null, 'sidebar-nav-item-omnichannel');
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('_render', function() {
        using('different SugarLive availability flags', [
            true,
            false
        ], function(hasSugarLiveAccess) {
            it('should hide the nav item if SugarLive is not available', function() {
                sinon.stub(view, '_isAvailable').returns(hasSugarLiveAccess);
                sinon.stub(view, '_configAvailable').returns(hasSugarLiveAccess);
                view._render();
                expect(view.$el.hasClass('hidden')).toBe(!hasSugarLiveAccess);
            });
        });
    });
});
