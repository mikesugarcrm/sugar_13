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
describe('Base.Layout.OmnichannelConsoleConfigLayout', function() {
    var console;
    var app;

    beforeEach(function() {
        app = SugarTest.app;
        app.routing.start();
        SugarTest.loadComponent('base', 'layout', 'omnichannel-console');
        SugarTest.loadComponent('base', 'layout', 'omnichannel-console-config');
        console = SugarTest.createLayout('base', 'layout',
            'omnichannel-console-config', {});
    });

    afterEach(function() {
        sinon.restore();
        console.dispose();
        app.router.stop();
    });

    describe('open', function() {
        beforeEach(function() {
            sinon.stub(console, 'setMode');
            sinon.stub(console, 'removeToolbarActionListener');
            sinon.stub(console.$el, 'show');
        });

        describe('when the console config is currently closed', function() {
            beforeEach(function() {
                sinon.stub(console, 'isOpen').returns(false);
            });

            it('should open the console config', function() {
                console.open();
                expect(console.setMode).toHaveBeenCalledWith('full');
                expect(console.$el.show).toHaveBeenCalled();
                expect(console.removeToolbarActionListener).toHaveBeenCalled();
            });
        });

        describe('when the console config is currently open', function() {
            beforeEach(function() {
                sinon.stub(console, 'isOpen').returns(true);
            });

            it('should not try to re-open the console config', function() {
                console.open();
                expect(console.setMode).not.toHaveBeenCalled();
                expect(console.$el.show).not.toHaveBeenCalled();
                expect(console.removeToolbarActionListener).not.toHaveBeenCalled();
            });
        });
    });

    describe('_handleClosedQuickcreateDrawer', function() {
        using('different values for isOpen and CCPOnly', [
            {
                drawerCount: 0,
                expected: 1
            },
            {
                drawerCount: 1,
                expected: 0
            }
        ], function(values) {
            it('should open when there are no drawers', function() {
                app.drawer = {
                    count: function() {
                        return values.drawerCount;
                    }
                };
                var openStub = sinon.stub(console, 'open');

                console._handleClosedQuickcreateDrawer();

                if (values.expected) {
                    expect(openStub).toHaveBeenCalled();
                } else {
                    expect(openStub).not.toHaveBeenCalled();
                }
            });
        });
    });

    describe('method call logic', function() {
        it('should resize the ccp', function() {
            var ccp = {
                resize: $.noop
            };
            sinon.stub(console, 'getComponent').withArgs('omnichannel-ccp').returns(ccp);
            var ccpresize = sinon.stub(ccp, 'resize');
            console.resizeCCP();

            expect(ccpresize).toHaveBeenCalled();
        });

        it('should close the config', function() {
            var closeStub = sinon.stub(console, 'close');
            console.closeAndDispose();
            expect(closeStub).toHaveBeenCalled();
        });
    });

    describe('disposeOnRoute', function() {
        beforeEach(function() {
            sinon.stub(console, 'closeImmediately');
            sinon.stub(console, 'dispose');
            sinon.stub(console, 'open');
            sinon.stub(console, 'setKebabState');
        });

        afterEach(function() {
            console.isSyncing = false;
            sinon.restore();
        });

        using('different routing history', [{
            currentFragment: 'Current',
            previousFragment: 'Previous',
            isSyncing: false,
            disposeExpected: true,
            setKebabExpected: false
        }, {
            currentFragment: 'Current',
            previousFragment: 'Current',
            isSyncing: true,
            disposeExpected: false,
            setKebabExpected: true
        }, {
            currentFragment: 'Current',
            previousFragment: 'Current',
            isSyncing: false,
            disposeExpected: true,
            setKebabExpected: false
        }], function(values) {
            it('should dispose if current fragment does not match previous', function() {
                app.router._currentFragment = values.currentFragment;
                app.router._previousFragment = values.previousFragment;
                console.isSyncing = values.isSyncing;
                console.disposeOnRoute();
                expect(console.closeImmediately).toHaveBeenCalled();
                expect(console.dispose.called).toEqual(values.disposeExpected);
                expect(console.open.called).toEqual(!values.disposeExpected);
                expect(console.setKebabState.called).toEqual(values.setKebabExpected);
            });
        });
    });

    describe('setKebabState', function() {
        it('should set the main button active', function() {
            var footer = $('<div id="footer"></div>');
            var configMenu = $('<div class="config-menu"></div>');
            sinon.stub(console.$el, 'siblings').withArgs('#footer').returns(footer);
            sinon.stub(footer, 'find').returns(configMenu);
            var attrStub = sinon.stub(configMenu, 'attr');
            console.setKebabState('init');
            expect(attrStub).toHaveBeenCalled();
        });
    });
});
