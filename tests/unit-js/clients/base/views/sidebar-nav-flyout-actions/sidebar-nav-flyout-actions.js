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

describe('Base.Layout.SidebarNavFlyoutActions', function() {
    let view;
    let app;

    beforeEach(function() {
        app = SugarTest.app;
        app.routing.start();
        view = SugarTest.createView('base', null, 'sidebar-nav-flyout-actions', {
            actions: []
        });
    });

    afterEach(function() {
        sinon.restore();
        app.router.stop();
        view.dispose();
        view = null;
    });

    describe('updateActions', function() {
        let mockCreateAction;
        let mockListAction;

        beforeEach(function() {
            mockCreateAction = {
                label: 'LNK_NEW_ACCOUNT',
                acl_action: 'create',
                acl_module: 'Accounts',
                icon: 'sicon-plus',
                route: '#Accounts/create'
            };
            mockListAction = {
                label: 'LNK_ACCOUNT_LIST',
                acl_action: 'list',
                acl_module: 'Accounts',
                icon: 'sicon-list-view',
                route: '#Accounts'
            };

            sinon.stub(app.acl, 'hasAccess')
                .withArgs('create', 'Accounts').returns(false)
                .withArgs('list', 'Accounts').returns(true);

            sinon.stub(view, 'render');
        });

        it('should filter actions based on user ACLs', function() {
            view.updateActions([mockCreateAction, mockListAction]);
            expect(view.actions).toEqual([mockListAction]);
            expect(view.render).toHaveBeenCalled();
        });
    });
});
