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

describe('Base.View.SidebarNavItemView', function() {
    let view;
    let app;

    beforeEach(function() {
        app = SugarTest.app;
        app.routing.start();
        view = SugarTest.createView('base', null, 'sidebar-nav-item');
    });

    afterEach(function() {
        sinon.restore();
        app.router.stop();
        view.dispose();
        view = null;
    });

    describe('primaryActionOnClick', function() {
        let event;
        let currentRoute = 'currentRoute';

        beforeEach(function() {
            event = {
                preventDefault: sinon.stub(),
                stopPropagation: sinon.stub()
            };

            sinon.stub(app.router, 'getFragment').returns(currentRoute);
            sinon.stub(app.router, 'refresh');
            sinon.stub(app.router, 'navigate');
            sinon.stub(window, 'open');
        });

        it('should trigger a refresh if the route is the same as the current one', function() {
            view.route = `#${currentRoute}`;
            view.primaryActionOnClick(event);
            expect(event.preventDefault).toHaveBeenCalled();
            expect(app.router.refresh).toHaveBeenCalled();
            expect(app.router.navigate).not.toHaveBeenCalled();
        });

        it('should trigger navigation if the route differs from the current one', function() {
            view.route = '#testing';
            view.primaryActionOnClick(event);
            expect(event.preventDefault).toHaveBeenCalled();
            expect(app.router.refresh).not.toHaveBeenCalled();
            expect(app.router.navigate).toHaveBeenCalledWith(view.route);
        });

        it('should not trigger any route if one is not specified', function() {
            view.route = '';
            view.primaryActionOnClick(event);
            expect(event.preventDefault).toHaveBeenCalled();
            expect(app.router.refresh).not.toHaveBeenCalled();
            expect(app.router.navigate).not.toHaveBeenCalled();
        });

        it('should open a new tab when ctrl key clicked', function() {
            view.route = `#${currentRoute}`;
            event.ctrlKey = true;
            view.primaryActionOnClick(event);
            expect(event.preventDefault).toHaveBeenCalled();
            expect(event.stopPropagation).toHaveBeenCalled();
            expect(window.open).toHaveBeenCalled();
        });

        it('should open a new tab when meta key clicked', function() {
            view.route = `#${currentRoute}`;
            event.metaKey = true;
            view.primaryActionOnClick(event);
            expect(event.preventDefault).toHaveBeenCalled();
            expect(event.stopPropagation).toHaveBeenCalled();
            expect(window.open).toHaveBeenCalled();
        });
    });

    describe('secondaryActionOnClick', function() {
        let event;
        let mockFlyout;

        beforeEach(function() {
            event = {
                currentTarget: '#target'
            };
            mockFlyout = {
                toggle: sinon.stub()
            };

            sinon.stub(view, 'initPopover').callsFake(function() {
                view.flyout = mockFlyout;
            });
            sinon.stub(view, '_getFlyoutComponents');
        });

        it('should initialize the popover component if it does not exist yet', function() {
            view.secondaryActionOnClick(event);
            expect(view.initPopover).toHaveBeenCalled();
            expect(mockFlyout.toggle).toHaveBeenCalled();
        });

        it('should not re-initialize the popover component if it already exists', function() {
            view.flyout = mockFlyout;
            view.secondaryActionOnClick(event);
            expect(view.initPopover).not.toHaveBeenCalled();
            expect(mockFlyout.toggle).toHaveBeenCalled();
        });
    });
});
