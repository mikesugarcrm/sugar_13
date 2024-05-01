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

describe('Base.Layout.SidebarNav', function() {
    let layout;
    let app;
    let clock;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Accounts', 'sidebar-nav');
        clock = sinon.useFakeTimers();
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        layout = null;
        clock.restore();
    });

    describe('_toggleExpand', function() {
        let sidebarEl;
        let overlayEl;

        beforeEach(function() {
            sidebarEl = {
                toggleClass: sinon.stub(),
                addClass: sinon.stub(),
                removeClass: sinon.stub()
            };
            overlayEl = {
                toggleClass: sinon.stub()
            };

            sinon.stub(layout.$el, 'find')
                .withArgs('.sidebar-nav').returns(sidebarEl)
                .withArgs('.sidebar-nav-overlay').returns(overlayEl);
            sinon.stub(app.events, 'trigger');
        });

        using('different starting states of expansion', [false, true], function(startsExpanded) {
            it('should handle toggling the sidebar classes appropriately', function() {
                layout.expanded = startsExpanded;
                layout._toggleExpand();

                let animationClass = startsExpanded ? 'collapsing' : 'expanding';
                expect(sidebarEl.addClass).toHaveBeenCalledWith(animationClass);
                clock.tick(300);
                expect(sidebarEl.toggleClass).toHaveBeenCalledWith('expanded', !startsExpanded);
                expect(sidebarEl.removeClass).toHaveBeenCalledWith(animationClass);
            });

            it('should hide/show the overlay properly', function() {
                layout.expanded = startsExpanded;
                layout._toggleExpand();
                expect(overlayEl.toggleClass).toHaveBeenCalledWith('hide', startsExpanded);
            });

            it('should trigger an event to let the app know the sidebar was toggled', function() {
                layout.expanded = startsExpanded;
                layout._toggleExpand();
                expect(app.events.trigger).toHaveBeenCalledWith('sidebar-nav:expand:toggled', !startsExpanded);
            });
        });
    });

    describe('_isAvailable', () => {
        using('different combination of auth, setup complete and impersonation states', [
            {
                authenticated: true,
                isSetupCompleted: true,
                isImpersonationFor: true,
                expected: true
            }, {
                authenticated: true,
                isSetupCompleted: false,
                isImpersonationFor: true,
                expected: true
            }, {
                authenticated: true,
                isSetupCompleted: true,
                isImpersonationFor: false,
                expected: true
            }, {
                authenticated: false,
                isSetupCompleted: false,
                isImpersonationFor: false,
                expected: false
            }, {
                authenticated: false,
                isSetupCompleted: true,
                isImpersonationFor: true,
                expected: false
            },
        ], (values) => {
            it('should return true when authenticated and setup complete or in impersonation mode', () => {
                sinon.stub(app.api, 'isAuthenticated').returns(values.authenticated);
                sinon.stub(app.user, 'isSetupCompleted').returns(values.isSetupCompleted);
                sinon.stub(app.cache, 'has')
                    .withArgs('ImpersonationFor')
                    .returns(values.isImpersonationFor);
                let actual = layout._isAvailable();
                expect(actual).toEqual(values.expected);
            });
        });
    });
});
