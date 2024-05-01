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

describe('Base.Layout.SidebarNavItemModule', function() {
    let app;
    let view;
    let module = 'Accounts';

    beforeEach(function() {
        SugarTest.loadComponent('base', 'view', 'sidebar-nav-item');
        SugarTest.loadComponent('base', 'view', 'sidebar-nav-item-module');

        app = SugarTest.app;
        app.drawer = {
            getActive: sinon.stub()
        };
        view = SugarTest.createView('base', module, 'sidebar-nav-item-module');
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('_determineActiveStatus', function() {
        describe('when the module item\'s module matches the app context module', function() {
            beforeEach(function() {
                sinon.stub(app.controller.context, 'get').returns(module);
            });

            it('should set the module item to active', function() {
                view._determineActiveStatus();
                expect(view.active).toBe(true);
            });
        });

        describe('when the module item\'s module does not match the app context module', function() {
            beforeEach(function() {
                sinon.stub(app.controller.context, 'get').returns('OtherModule');
            });

            it('should set the module item to active', function() {
                view._determineActiveStatus();
                expect(view.active).toBe(false);
            });
        });

        describe('when the module item\'s module matches the active drawer context module ', function() {
            beforeEach(function() {
                sinon.stub(app.controller.context, 'get').returns('OtherModule');
                app.drawer.getActive.returns({
                    context: {
                        get: sinon.stub().returns(module)
                    }
                });
            });

            it('should set the module item to active',function() {
                view._determineActiveStatus();
                expect(view.active).toBe(true);
            });
        });
    });
});
