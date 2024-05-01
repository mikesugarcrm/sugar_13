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
describe('Base.Users.SidebarNavFlyoutMenuView', function() {
    let app;
    let module = 'Users';
    let view;
    let oldConfig;
    beforeEach(function() {
        SugarTest.loadComponent('base', 'view', 'sidebar-nav-flyout-actions', module);
        app = SugarTest.app;
        view = SugarTest.createView('base', module, 'sidebar-nav-flyout-actions');
        oldConfig = app.config;
        app.config = {
            idmModeEnabled: true
        };
        sinon.stub(view, '_super');
    });
    afterEach(function() {
        sinon.restore();
        view.dispose();
        app.config = oldConfig;
    });

    describe('_handleRouteItemClick', function() {
        let event;
        let getAttributeStub;
        beforeEach(function() {
            getAttributeStub = sinon.stub();
            getAttributeStub.withArgs('data-navbar-menu-item').returns('LNK_NEW_USER');
            getAttributeStub.withArgs('data-route');
            event = {
                target: {
                    closest: function() {
                        return {
                            getAttribute: getAttributeStub
                        };
                    },
                }
            };
            sinon.stub(app.alert, 'show');
        });
        afterEach(function() {
            event = null;
            getAttributeStub = null;
        });
        it('should show idm_create_user alert for IDM instance on new user action click', function() {
            view._handleRouteItemClick(event);
            expect(app.alert.show).toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('_handleRouteItemClick');

        });
        it('should not show idm_create_user alert for non-IDM instance on new user action click', function() {
            app.config.idmModeEnabled = false;
            view._handleRouteItemClick(event);
            expect(app.alert.show).not.toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('_handleRouteItemClick');
        });
    });
});
