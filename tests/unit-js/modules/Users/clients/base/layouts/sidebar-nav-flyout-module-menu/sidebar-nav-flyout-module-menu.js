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
describe('Base.Users.SidebarNavFlyoutModuleMenuLayout', function() {
    let layout;
    let app;
    let oldConfig;
    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout(
            'base',
            'Users',
            'sidebar-nav-flyout-module-menu',
            null,
            null,
            true
        );
        oldConfig = app.config;
        app.config = {
            idmModeEnabled: true
        };
    });
    afterEach(function() {
        sinon.restore();
        layout.dispose();
        layout = null;
        app.config = oldConfig;
    });

    describe('_getMenuActions', function() {
        let event;
        let getAttributeStub;
        beforeEach(function() {
            sinon.stub(layout, '_getCloudConsoleLink')
                .returns('https://[INSTANCE]/users/new?tenant_hint=[Tenant_SRN]');
            sinon.stub(app.utils, 'createUserSrn').returns('[Admin_SRN]');
            let mockActions = [
                {
                    label: 'LNK_NEW_USER',
                    route: '#bwc/action=edit'
                }
            ];
            sinon.stub(layout, '_super')
                .withArgs('_getMenuActions').returns(mockActions);
        });
        afterEach(function() {
            event = null;
            getAttributeStub = null;
        });
        it('should change route for IDM instance on new user action click', function() {
            let result = layout._getMenuActions();
            let expected = 'https://[INSTANCE]/users/new?tenant_hint=[Tenant_SRN]&user_hint=[Admin_SRN]';
            expect(layout._super).toHaveBeenCalledWith('_getMenuActions');
            expect(layout._getCloudConsoleLink).toHaveBeenCalled();
            expect(_.first(result).route).toEqual(expected);

        });
        it('should not change route for non-IDM instance on new user action click', function() {
            app.config.idmModeEnabled = false;
            let result = layout._getMenuActions();
            expect(layout._super).toHaveBeenCalledWith('_getMenuActions');
            expect(layout._getCloudConsoleLink).toHaveBeenCalled();
            expect(_.first(result).route).toEqual('#bwc/action=edit');
        });
    });

    describe('_getCloudConsoleLink', function() {
        let oldMeta;
        beforeEach(function() {
            oldMeta = layout.meta;
            layout.meta = {
                cloudConsoleLink: 'https://[INSTANCE]/users/new?tenant_hint=[Tenant_SRN]'
            };
        });
        afterEach(function() {
            layout.meta = oldMeta;
        });
        it('should build cloud console link', function() {
            expect(layout._getCloudConsoleLink())
                .toEqual('https://[INSTANCE]/users/new?tenant_hint=[Tenant_SRN]');
        });
    });
});
