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

describe('Dashboards.Base.View.DashboardFab', function() {
    var app;
    var view;
    var clock;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'dashboard-fab');
        SugarTest.loadComponent('base', 'view', 'dashboard-fab', 'Dashboards');
        view = SugarTest.createView('base', 'Dashboards', 'dashboard-fab');
        clock = sinon.useFakeTimers();
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        clock.restore();
        sinon.restore();
        view.dispose();
        view = null;
    });

    describe('openSugarLiveConfig', function() {
        beforeEach(function() {
            app.drawer = {
                _getDrawers: $.noop,
                open: sinon.stub()
            };
        });

        it('should call app.drawer.open', function() {
            sinon.stub(app.drawer, '_getDrawers').callsFake(function() {
                return {
                    $top: undefined
                };
            });
            sinon.stub(view, 'closestComponent').callsFake(function() {
                return undefined;
            });

            view.openSugarLiveConfig();
            expect(app.drawer.open).toHaveBeenCalled();
        });

        it('should not close Sugar Live config if a drawer is not open in background', function() {
            sinon.stub(app.drawer, '_getDrawers').callsFake(function() {
                return {
                    $top: undefined
                };
            });
            return {
                boundCloseImmediately: sinon.stub()
            };

            view.openSugarLiveConfig();
            expect(view.closestComponent.boundCloseImmediately).not.toHaveBeenCalled();
        });

        it('should close Sugar Live config if a drawer is open in background', function() {
            sinon.stub(app.drawer, '_getDrawers').callsFake(function() {
                return {
                    $top: {test: 'test'}
                };
            });
            sinon.stub(view, 'closestComponent').withArgs('omnichannel-console-config')
                .returns({
                    boundCloseImmediately: sinon.stub()
                });

            view.openSugarLiveConfig();
            expect(view.closestComponent('omnichannel-console-config').boundCloseImmediately).toHaveBeenCalled();
        });
    });

    describe('updateButtonVisibilities', function() {
        using('different tab values', [
            {tab: 0, expected: false, inConfig: false},
            {tab: 1, expected: false, inConfig: false},
            {tab: 0, expected: false, inConfig: true},
            {tab: 1, expected: true, inConfig: true},
            ], function(values) {
            it('should call toggleFabButton with true for all but searhch tab', function() {
                sinon.stub(view, 'toggleFabButton');
                sinon.stub(view, '_getActiveDashboardTab').returns(values.tab);
                sinon.stub(view, 'openFABs');
                sinon.stub(view, '_inConfigLayout').returns(values.inConfig);
                sinon.stub(view, '_super');

                view.updateButtonVisibilities();

                if (this.inConfig) {
                    expect(view._getActiveDashboardTab).toHaveBeenCalled();
                    expect(view.toggleFabButton).toHaveBeenCalledWith(values.expected);
                    clock.tick(210);
                    expect(view.openFABs).toHaveBeenCalled();
                } else {
                    expect(view._super).toHaveBeenCalled();
                }
            });
        });
    });

    describe('handleRestoreDashletsClick', function() {
        it('should trigger an alert to restore dashlets', function() {
            sinon.stub(app.alert, 'show');

            view.handleRestoreDashletsClick();
            expect(app.alert.show).toHaveBeenCalledWith('restore_dashlet_confirmation');
        });
    });

    describe('restoreDashlets', function() {
        it('should trigger dashboard:restore_dashlets_button:click event', function() {
            sinon.stub(view, 'closestComponent').withArgs('dashboard')
                .returns({
                    layout: {
                        trigger: sinon.stub()
                    },
                    context: {
                        test: 'test'
                    }
                });

            view.restoreDashlets();
            expect(view.closestComponent('dashboard').layout.trigger).toHaveBeenCalledWith(
                'dashboard:restore_dashlets_button:click', {test: 'test'}
            );
        });
    });
});
