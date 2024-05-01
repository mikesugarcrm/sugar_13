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
describe('View.Layouts.Base.HomeSidebarNavFlyoutModuleMenuLayout', () => {
    let app;
    let layout;
    const moduleName = 'Home';
    const layoutName = 'sidebar-nav-flyout-module-menu';

    beforeEach(() => {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', moduleName, layoutName, null, null, true);
    });

    afterEach(() => {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        layout.dispose();
        layout = null;
    });

    describe('Activity Stream Access Checks', () => {
        it('should remove Activity Stream link from the menu if it\'s not enabled', () => {
            const expected = [
                {
                    route: '#test-create',
                },
                {
                    type: 'test-type',
                },
            ];

            const menuMeta = [...expected, {
                route: '#activities',
            }];

            const layoutStub = sinon.stub(layout, '_super');
            layoutStub.withArgs('_getMenuActions').returns(menuMeta);

            const result = layout._getMenuActions();
            expect(result).toEqual(expected);
        });
    });
});
