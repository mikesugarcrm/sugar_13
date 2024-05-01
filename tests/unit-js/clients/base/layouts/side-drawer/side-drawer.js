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
describe('Base.Layout.SideDrawer', function() {
    var drawer;
    var app;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'layout', 'side-drawer');
        drawer = SugarTest.createLayout('base', 'layout', 'side-drawer', {});
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
        drawer.dispose();
    });

    describe('_open', function() {
        beforeEach(function() {
            sinon.stub(drawer, 'resetTabs');
        });

        afterEach(function() {
            sinon.restore();
        });

        it('should show the drawer if not yet open', function() {
            var elShowStub = sinon.stub(drawer.$el, 'show');
            drawer._open();
            expect(elShowStub).toHaveBeenCalled();
            expect(drawer.currentState).toEqual('idle');
        });

        it('should not try to show the drawer again if already open', function() {
            var elShowStub = sinon.stub(drawer.$el, 'show');
            var showCompStub = sinon.stub(drawer, 'showComponent');
            drawer.currentState = 'idle';
            drawer._open();
            expect(elShowStub).not.toHaveBeenCalled();
            expect(drawer.currentState).toEqual('idle');
            expect(showCompStub).toHaveBeenCalled();
        });
    });

    describe('switchTab', function() {
        var showStub;
        var resetStub;

        beforeEach(function() {
            showStub = sinon.stub(drawer, 'showComponent');
            resetStub = sinon.stub(drawer, 'resetTabs');
        });

        afterEach(function() {
            sinon.restore();
            drawer._tabs = [];
            drawer.activeTabIndex = 0;
            drawer.currentContextDef = null;
        });

        it('should switch if index is valid', function() {
            drawer._tabs = [1, 2, 3];
            drawer.switchTab(2);
            expect(showStub).toHaveBeenCalled();
            expect(resetStub).toHaveBeenCalled();
            expect(drawer.activeTabIndex).toEqual(2);
        });

        it('should not switch if index is invalid', function() {
            drawer._tabs = [1, 2, 3];
            drawer.activeTabIndex = 1;
            drawer.switchTab(3);
            expect(showStub).not.toHaveBeenCalled();
            expect(resetStub).not.toHaveBeenCalled();
            expect(drawer.activeTabIndex).toEqual(1);
        });
    });

    describe('closeTab', function() {
        var showStub;
        var resetStub;

        beforeEach(function() {
            showStub = sinon.stub(drawer, 'showComponent');
            resetStub = sinon.stub(drawer, 'resetTabs');
        });

        afterEach(function() {
            sinon.restore();
            drawer._tabs = [];
            drawer.activeTabIndex = 0;
            drawer.currentContextDef = null;
        });

        it('should not close if only one tab', function() {
            drawer._tabs = [1];
            drawer.closeTab(0);
            expect(showStub).not.toHaveBeenCalled();
            expect(resetStub).not.toHaveBeenCalled();
        });

        it('should close if there are more than one tab and index is valid', function() {
            drawer._tabs = [1, 2, 3];
            drawer.activeTabIndex = 2;
            drawer.closeTab(2);
            expect(showStub).toHaveBeenCalled();
            expect(resetStub).toHaveBeenCalled();
            expect(drawer.activeTabIndex).toEqual(1);
        });
    });

    describe('getTabIndex', function() {
        it('should retrun index if found', function() {
            drawer._tabs = [
                {
                    isFocusDashboard: true,
                    context: {
                        module: 'Cases',
                        modelId: 'caseId'
                    }
                }
            ];
            let index = drawer.getTabIndex({
                isFocusDashboard: true,
                context: {
                    module: 'Cases',
                    modelId: 'caseId'
                }
            });
            expect(index).toBe(0);
        });

        it('should return null if not found', function() {
            drawer._tabs = [
                {
                    isFocusDashboard: true,
                    context: {
                        module: 'Cases',
                        modelId: 'caseId'
                    }
                }
            ];
            let index = drawer.getTabIndex({
                isFocusDashboard: true,
                context: {
                    module: 'Acounts',
                    modelId: 'accountId'
                }
            });
            expect(index).toBe(null);
        });
    });

    describe('showComponent', function() {
        it('should remove old component', function() {
            var comp = {
                dispose: sinon.stub()
            };
            var initCompStub = sinon.stub(drawer, '_initializeComponentsFromDefinition');

            drawer._components = [comp];
            drawer.showComponent();
            expect(comp.dispose).toHaveBeenCalled();
            expect(drawer._components.length).toBe(0);
        });

        it('should add new component', function() {
            var comp = {
                loadData: sinon.stub(),
                render: sinon.stub(),
                dispose: sinon.stub()
            };
            var initCompStub = sinon.stub(drawer, '_initializeComponentsFromDefinition').callsFake(function() {
                drawer._components = [comp];
            });

            drawer.showComponent();
            expect(comp.loadData).toHaveBeenCalled();
            expect(comp.render).toHaveBeenCalled();
        });
    });

    describe('toggle', function() {
        var elToggleStub;

        beforeEach(function() {
            elToggleStub = sinon.stub(drawer.$el, 'toggle');
        });

        it('should toggle if drawer is open', function() {
            drawer.currentState = 'idle';
            drawer.toggle();
            expect(elToggleStub).toHaveBeenCalled();
        });

        it('should not toggle if drawer is not open', function() {
            drawer.currentState = '';
            drawer.toggle();
            expect(elToggleStub).not.toHaveBeenCalled();
        });
    });

    describe('close', function() {
        var comp;
        var elHideStub;
        var onCloseCallback;

        beforeEach(function() {
            comp = {
                dispose: sinon.stub()
            };
            onCloseCallback = {
                apply: sinon.stub()
            };
            elHideStub = sinon.stub(drawer.$el, 'hide');
            drawer._components = [comp];
            drawer.onCloseCallback = onCloseCallback;
            drawer.context = {
                module: 'Cases',
                modelId: 'caseId',
                off: sinon.stub(),
                trigger: sinon.stub(),
            };
        });

        it('should close the drawer, remove component and callback', function() {
            drawer.close();
            expect(elHideStub).toHaveBeenCalled();
            expect(comp.dispose).toHaveBeenCalled();
            expect(drawer._components.length).toBe(0);
            expect(onCloseCallback.apply).toHaveBeenCalled();
            expect(drawer.currentState).toEqual('');
        });

        it('should add shortcuts to close drawer', function() {
            expect(drawer.shortcuts).toContain('SideDrawer:Close');
        });

        it('should not close if before event prevents it', function() {
            sinon.stub(drawer, 'triggerBefore').withArgs('side-drawer:close').returns(false);
            drawer.close();
            expect(elHideStub).not.toHaveBeenCalled();
            expect(comp.dispose).not.toHaveBeenCalled();
            expect(onCloseCallback.apply).not.toHaveBeenCalled();
        });

        it('should call trigger function when closing focus drawer with close button', function() {
            sinon.stub(drawer, '$'). returns({
                closest: function() {
                    return {
                        length: 1,
                    };
                },
            });
            drawer.close('el');
            expect(drawer.context.trigger).toHaveBeenCalledWith('side-drawer:start:close');

        });

        it('should not call trigger function when close called from dispose chain', function() {
            sinon.stub(drawer, '$'). returns({
                closest: function() {
                    return {
                        length: 0,
                    };
                },
            });
            drawer.close('el');
            expect(drawer.context.trigger).not.toHaveBeenCalledWith('side-drawer:start:close');

        });
    });

    describe('registerShortcuts', function() {
        it('should register and  match the shortcuts from this.shortcuts', function() {
            var registerStub = sinon.stub(app.shortcuts, 'register');
            drawer.registerShortcuts();
            expect(registerStub.callCount).toBe(drawer.shortcuts.length);
        });
    });

    describe('button actions', function() {
        it('should have the actions enabled by default', function() {
            expect(drawer.areActionsEnabled).toEqual(true);
        });
    });
});
