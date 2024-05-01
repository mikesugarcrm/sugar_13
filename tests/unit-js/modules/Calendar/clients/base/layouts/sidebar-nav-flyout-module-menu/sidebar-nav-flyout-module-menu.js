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

describe('Base.Layout.Calendar.SidebarNavFlyoutModuleMenu', function() {
    let layout;
    let app;

    beforeEach(function() {
        app = SugarTest.app;
        app.routing.start();
        layout = SugarTest.createLayout(
            'base',
            'Calendar',
            'sidebar-nav-flyout-module-menu'
            ,null
            ,null,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        app.router.stop();
        layout.dispose();
        layout = null;
    });

    describe('_getMenuComponents', function() {
        beforeEach(function() {
            let mockActions = ['action1', 'action2'];
            let mockMenuComponenets = [
                {
                    view: 'sidebar-nav-flyout-header',
                    title: 'Calendar'
                },
                {
                    view: {
                        type: 'sidebar-nav-flyout-actions',
                        actions: mockActions
                    }
                },
                {
                    view: {
                        type: 'sidebar-nav-flyout-actions',
                        name: 'recently_viewed',
                        actions: mockActions
                    }
                },
                {
                    view: {
                        type: 'sidebar-nav-flyout-actions',
                        name: 'favorites',
                        actions: mockActions
                    }
                }
            ];

            sinon.stub(app.lang, 'get').returns('Accounts');
            sinon.stub(layout, '_super').withArgs('_getMenuComponents').returns(mockMenuComponenets);
        });

        it('should set the second component to contain the Calendar Modules', function() {
            let components = layout._getMenuComponents();
            expect(components[1].view.type).toEqual('sidebar-nav-flyout-actions');
            expect(components[1].view.name).toEqual('calendar-modules');
            expect(components[3].view.type).toEqual('sidebar-nav-flyout-actions');
            expect(components[3].view.name).toEqual('recently_viewed');
            expect(components[4].view.type).toEqual('sidebar-nav-flyout-actions');
            expect(components[4].view.name).toEqual('favorites');
        });
    });

    describe('_populateCalendarModules', function() {
        beforeEach(function() {
            sinon.stub(app.api, 'call');
        });

        it('should call the Calendar Modules Api Should have been called', function() {
            layout._populateCalendarModules();
            expect(app.api.call).toHaveBeenCalled();

        });
    });

    describe('_populateCalendarModulesSucceess', function() {
        let mockData;
        let mockCalendarModulesComp;
        let mockActions;

        beforeEach(function() {
            mockData = {
                'modules': {
                    'Calls': {
                        'objName': 'Call'
                    }
                }
            };

            mockActions = [
                {
                    'acl_action': 'create',
                    'acl_module': 'Calls',
                    'icon': 'sicon-phone-lg',
                    'label': 'Log Call',
                    'route': '#Calls/create'
                },
                {
                    'type': 'divider'
                }
            ];

            mockCalendarModulesComp = {
                updateActions: sinon.stub()
            };
            sinon.stub(layout, 'getComponent').returns(mockCalendarModulesComp);
            sinon.stub(app.lang, 'get').returns('Log Call');
            sinon.stub(app.metadata, 'getModule').returns({icon: 'sicon-phone-lg'});
        });

        it('should call updateActions with mockActions', function() {
            layout._populateCalendarModulesSucceess(mockData);
            expect(mockCalendarModulesComp.updateActions).toHaveBeenCalledWith(mockActions);
        });
    });
});
