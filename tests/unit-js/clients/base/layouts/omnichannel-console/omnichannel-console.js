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
describe('Base.Layout.OmnichannelConsole', function() {
    var layout;
    var app;

    beforeEach(function() {
        app = SugarTest.app;
        app.routing.start();
        app.lang.direction = 'ltr';
        SugarTest.loadComponent('base', 'layout', 'omnichannel-console');
        layout = SugarTest.createLayout('base', 'layout', 'omnichannel-console', {
            last_state: {
                id: 'omnichannel-console'
            }
        });
        layout.sidebarNavWidth = '2.5rem';
    });

    afterEach(function() {
        sinon.restore();
        layout.dispose();
        app.router.stop();
    });

    describe('open', function() {
        describe('when the console is closed', function() {
            beforeEach(function() {
                layout.currentState = '';
                sinon.stub(layout, '_initMode');
                sinon.stub(layout.$el, 'css');
                sinon.stub(app.events, 'on');
                sinon.stub(jQuery.fn, 'addClass');
            });

            it('should show the console', function() {
                layout.open();

                expect(app.events.on).toHaveBeenCalled();
                expect(jQuery.fn.addClass).toHaveBeenCalledWith('omniconsole-visible');
                expect(layout._initMode).toHaveBeenCalled();
                expect(layout.$el.css).toHaveBeenCalledWith({
                    left: '2.5rem',
                    display: ''
                });
                expect(layout.currentState).toEqual('idle');
            });
        });

        describe('when the console is already open', function() {
            beforeEach(function() {
                layout.currentState = 'idle';
                sinon.stub(layout, '_initMode');
                sinon.stub(layout.$el, 'css');
            });

            it('should not try to open the console again', function() {
                layout.open();
                expect(layout._initMode).not.toHaveBeenCalled();
                expect(layout.$el.css).not.toHaveBeenCalled();
                expect(layout.currentState).toEqual('idle');
            });
        });
    });

    describe('isOpen', function() {
        it('should return false if not yet open', function() {
            layout.currentState = '';
            expect(layout.isOpen()).toBeFalsy();
        });

        it('should return true if already open', function() {
            layout.currentState = 'idle';
            expect(layout.isOpen()).toBeTruthy();
        });
    });

    describe('closeImmediately', function() {
        it('should close console when mode is full', function() {
            sinon.stub(layout, 'handleResetOnClose');
            sinon.stub(layout, 'getMode').callsFake(function() {
                return 'full';
            });
            layout.closeImmediately();

            expect(layout.handleResetOnClose).toHaveBeenCalled();
        });

        it('should not close console when mode is compact', function() {
            sinon.stub(layout, 'handleResetOnClose');
            sinon.stub(layout, 'getMode').callsFake(function() {
                return 'compact';
            });
            layout.closeImmediately();

            expect(layout.handleResetOnClose).not.toHaveBeenCalled();
        });
    });

    describe('handleResetOnClose', function() {
        beforeEach(function() {
            sinon.stub(layout, '_resetPageElementsCssOnClose');
            sinon.stub(layout, '_offEvents');
        });

        it('should call the necessary functions', function() {
            layout.handleResetOnClose();

            expect(layout._resetPageElementsCssOnClose).toHaveBeenCalled();
            expect(layout._offEvents).toHaveBeenCalled();
        });
    });

    describe('closeOnLoginRedirect', function() {
        beforeEach(function() {
            layout.closedAfterRouteChange = false;
            sinon.stub(layout, 'handleResetOnClose');

            layout.closeOnLoginRedirect();
        });

        it('should set the closedAfterRouteChange property to be true', function() {
            expect(layout.closedAfterRouteChange).toBeTruthy();
        });

        it('should call handleResetOnClose method', function() {
            expect(layout.handleResetOnClose).toHaveBeenCalled();
        });
    });

    describe('_resetPageElementsCssOnClose', function() {
        var jQueryCssStub;

        beforeEach(function() {
            jQueryCssStub = sinon.stub(jQuery.fn, 'css');
            sinon.stub(jQuery.fn, 'removeClass').callsFake(function() {
                return {
                    css: jQueryCssStub
                };
            });
            sinon.stub(jQuery.fn, 'width').returns(1000);
        });

        afterEach(function() {
            jQueryCssStub = null;
        });

        it('should call jquery css three times to reset elements', function() {
            var callArgs = {
                marginLeft: '',
                width: ''
            };
            layout._resetPageElementsCssOnClose();

            expect(jQueryCssStub.getCall(0).args[0]).toEqual(callArgs);
            expect(jQueryCssStub.getCall(1).args[0]).toEqual(callArgs);
            expect(jQueryCssStub.getCall(2).args[0]).toEqual(callArgs);
        });

        it('should call removeClass', function() {
            layout._resetPageElementsCssOnClose();

            expect(jQuery.fn.removeClass).toHaveBeenCalledWith('omniconsole-visible');
        });

        it('should set this $el left position in compact mode', function() {
            layout.currentMode = 'compact';
            layout._resetPageElementsCssOnClose();

            expect(layout.$el.css).toHaveBeenCalledWith('left', '-320px');
        });

        it('should set this $el left position in full mode', function() {
            layout.currentMode = 'full';
            layout._resetPageElementsCssOnClose();

            expect(layout.$el.css).toHaveBeenCalledWith('left', '-1000px');
        });

        it('should set state values', function() {
            layout._resetPageElementsCssOnClose();

            expect(layout.currentState).toBe('');
            expect(layout.isMinimized).toBeTruthy();
        });
    });

    describe('close', function() {
        beforeEach(function() {
            sinon.stub(layout, '_resetPageElementsCssOnClose');
            sinon.stub(app.events, 'off');
        });

        it('should close the console', function() {
            sinon.stub(layout, 'isOpen').callsFake(function() {
                return true;
            });

            layout.close();

            expect(layout._resetPageElementsCssOnClose).toHaveBeenCalled();
        });

        it('should close and call app.events.off', function() {
            sinon.stub(layout, 'isOpen').callsFake(function() {
                return true;
            });

            layout.close();

            expect(app.events.off).toHaveBeenCalled();
        });

        it('should not close if not open', function() {
            sinon.stub(layout, 'isOpen').callsFake(function() {
                return false;
            });

            layout.close();

            expect(layout._resetPageElementsCssOnClose).not.toHaveBeenCalled();
        });
    });

    describe('getModelPrepopulateData', function() {
        var mockOmniSwitch;

        beforeEach(function() {
            sinon.stub(layout, '_getCCPComponent').returns({
                activeContact: {},
                getContactInfo: sinon.stub().returns({
                    property1: 'prop1',
                    property2: 'prop2'
                }),
                getActiveContactId: sinon.stub()
            });
            layout.prepopulateAwsContactDenyList = [
                'property1'
            ];

            mockOmniSwitch = {
                getModelPrepopulateData: sinon.stub().returns({
                    primary_contact_id: 123,
                    primary_contact_name: 'Fake Contact'
                })
            };
            sinon.stub(layout, '_getOmnichannelDashboardSwitch').returns(mockOmniSwitch);

            layout.prepopulateAttributes = {
                extra_property_1: 'Extra Property'
            };
        });

        it('should get the list of values to pre-populate a model with from the various sources', function() {
            expect(layout.getModelPrepopulateData('Cases')).toEqual({
                property2: 'prop2',
                primary_contact_id: 123,
                primary_contact_name: 'Fake Contact',
                extra_property_1: 'Extra Property'
            });
        });
    });

    describe('_handleClosedQuickcreateDrawer', function() {
        var createdBean;
        var dashboardSwitchMock;

        beforeEach(function() {
            createdBean = app.data.createBean('Cases');
            sinon.stub(layout, '_getCCPComponent').returns({
                getActiveContactId: function() {
                    return '123';
                }
            });
            dashboardSwitchMock = {
                setModel: sinon.stub(),
                postQuickCreate: sinon.stub()
            };
            sinon.stub(layout, '_getOmnichannelDashboardSwitch').returns(dashboardSwitchMock);
            sinon.stub(layout, 'open');
        });

        it('should set the created model on the omnichannel-dashboard-switch layout', function() {
            layout._handleClosedQuickcreateDrawer(createdBean);
            expect(dashboardSwitchMock.setModel).toHaveBeenCalledWith('123', createdBean, false);
        });

        it('should signal to the omnichannel-dashboard-switch layout that a model was quick-created', function() {
            layout._handleClosedQuickcreateDrawer(createdBean);
            expect(dashboardSwitchMock.postQuickCreate).toHaveBeenCalledWith('123', createdBean);
        });
    });

    describe('getContactModelDataForQuickcreate', function() {
        it('should get model data from the Contact for the quickcreate drawer', function() {
            var model = app.data.createBean('Contacts');

            model.set({
                id: 123,
                name: 'Customer',
                account_id: 456,
                account_name: 'Account 1',
            });

            var moduleTabIndex = {
                Contacts: 1,
                Cases: 2,
            };

            sinon.stub(layout, '_getOmnichannelDashboard').returns({
                moduleTabIndex: moduleTabIndex,
                tabModels: [
                    {},
                    model,
                ]
            });

            var actual = layout.getContactModelDataForQuickcreate();

            expect(actual).toEqual({
                primary_contact_id: 123,
                primary_contact_name: 'Customer',
                account_id: 456,
                account_name: 'Account 1',
            });
        });
    });

    describe('setMode', function() {
        beforeEach(function() {
            sinon.stub(layout, 'trigger');
            sinon.stub(layout.$el, 'animate');
            sinon.stub(layout.$el, 'css');
            app.drawer = {
                count: function() {
                    return 0;
                }
            };
        });

        afterEach(function() {
            delete app.drawer;
        });
        using('different modes', [
            'compact',
            'full'
        ], function(testMode) {
            it('should set the current mode of the console', function() {
                layout.setMode(testMode);
                expect(layout.currentMode).toEqual(testMode);
            });

            it('should trigger an event on the layout to notify other components', function() {
                layout.setMode(testMode);
                expect(layout.trigger).toHaveBeenCalledWith('omniconsole:mode:set', testMode);
            });
        });

        describe('during an inbound conversation', function() {
            beforeEach(function() {
                sinon.stub(layout, '_getActiveContactDirection').returns('inbound');
            });

            it('should cache the inbound mode', function() {
                sinon.stub(layout, 'getActiveContact').callsFake(function() {
                    return {
                        contactId: 'abc-123',
                        isInbound: () => {
                            return true;
                        }
                    };
                });
                sinon.stub(layout, 'getMode').callsFake(function() {
                    return 'full';
                });

                layout.setMode('compact');

                var key = app.user.lastState.key('inbound-mode', layout);
                expect(app.user.lastState.get(key)).toEqual('compact');
            });
        });

        describe('during an outbound conversation', function() {
            beforeEach(function() {
                sinon.stub(layout, '_getActiveContactDirection').returns('outbound');
            });

            it('should cache the outbound mode', function() {
                layout.setMode('full');

                var key = app.user.lastState.key('outbound-mode', layout);
                expect(app.user.lastState.get(key)).toEqual('full');
            });
        });

        describe('when the console is open', function() {
            beforeEach(function() {
                sinon.stub(layout, 'isOpen').returns(true);
            });

            it('should animate any console size changes', function() {
                layout.setMode('compact');

                expect(layout.$el.css).toHaveBeenCalledWith({
                    left: '2.5rem',
                    width: '320px'
                });
            });
        });

        describe('when the console is closed', function() {
            beforeEach(function() {
                sinon.stub(layout, 'isOpen').returns(true);
            });

            it('should animate any console size changes', function() {
                layout.setMode('full');

                expect(layout.$el.css).toHaveBeenCalledWith({
                    left: '2.5rem',
                    width: 'calc(100% - 2.5rem)'
                });
            });
        });
    });

    describe('_toggleModeClicked', function() {
        beforeEach(function() {
            sinon.stub(layout, 'setMode');
        });

        using('different modes', [
            {currentMode: 'full', expectedMode: 'compact'},
            {currentMode: 'compact', expectedMode: 'full'}
        ], function(testValues) {
            it('should correctly toggle the console mode', function() {
                layout.currentMode = testValues.currentMode;
                layout._toggleModeClicked();
                expect(layout.setMode).toHaveBeenCalledWith(testValues.expectedMode);
            });
        });
    });

    describe('_initMode', function() {
        beforeEach(function() {
            sinon.stub(layout, 'setMode');
            sinon.stub(layout, 'trigger');
        });

        describe('when there is no active contact', function() {
            beforeEach(function() {
                sinon.stub(layout, '_getActiveContactDirection').returns(null);
            });

            it('should initialize the mode to "compact"', function() {
                layout._initMode();

                expect(layout.setMode).toHaveBeenCalledWith('compact');
                expect(layout.trigger).toHaveBeenCalledWith('omniconsole:activeCall', false);
            });
        });

        describe('when there is an active inbound contact', function() {
            beforeEach(function() {
                sinon.stub(layout, '_getActiveContactDirection').returns('inbound');
                sinon.stub(layout, 'getActiveContact').returns(true);
            });

            describe('and the inbound mode is cached', function() {
                beforeEach(function() {
                    // First set the cache key to an expected value
                    var key = app.user.lastState.key('inbound-mode', layout);
                    app.user.lastState.set(key, 'compact');
                });

                it('should use the cached inbound mode', function() {
                    layout._initMode();

                    expect(layout.setMode).toHaveBeenCalledWith('compact');
                    expect(layout.trigger).toHaveBeenCalledWith('omniconsole:activeCall', true);
                });
            });

            describe('and the inbound mode is not cached', function() {
                it('should use the default inbound mode', function() {
                    layout._initMode();

                    expect(layout.setMode).toHaveBeenCalledWith('full');
                    expect(layout.trigger).toHaveBeenCalledWith('omniconsole:activeCall', true);
                });
            });
        });

        describe('when there is an active outbound contact', function() {
            beforeEach(function() {
                sinon.stub(layout, '_getActiveContactDirection').returns('outbound');
            });

            describe('and the outbound mode is cached', function() {
                beforeEach(function() {
                    // First set the cache key to an expected value
                    var key = app.user.lastState.key('outbound-mode', layout);
                    app.user.lastState.set(key, 'full');
                });

                it('should use the cached outbound mode', function() {
                    layout._initMode();
                    expect(layout.setMode).toHaveBeenCalledWith('full');
                });
            });

            describe('and the outbound mode is not cached', function() {
                it('should use the default outbound mode', function() {
                    layout._initMode();
                    expect(layout.setMode).toHaveBeenCalledWith('compact');
                });
            });
        });
    });

    describe('getActiveContact', function() {
        it('should return false', function() {
            sinon.stub(layout, '_getCCPComponent').returns(false);
            expect(layout.getActiveContact()).toBeFalsy();

        });

        it('should return the active contact', function() {
            var contact = {
                contactId: 'abc-123'
            };
            sinon.stub(layout, '_getCCPComponent').returns({
                getActiveContact: function() {
                    return contact;
                }
            });

            expect(layout.getActiveContact()).toBe(contact);
        });
    });

    describe('_setSize', function() {
        var hasClassStub;
        var drawerSidebarHasClassStub;
        var jQueryStub;
        var contentCssStub;
        var mainPaneCssStub;
        var headerpaneCssStub;
        var activeDrawerMainPaneCssStub;
        var activeDrawerHeaderpaneCssStub;
        var inactiveDrawerHeaderpaneCssStub;
        var sidebarNavCssStub;
        const sideNavWidth = '2.5rem';

        beforeEach(function() {
            contentCssStub = sinon.stub();
            mainPaneCssStub = sinon.stub();
            headerpaneCssStub = sinon.stub();
            hasClassStub = sinon.stub();
            drawerSidebarHasClassStub = sinon.stub();
            activeDrawerMainPaneCssStub = sinon.stub();
            activeDrawerHeaderpaneCssStub = sinon.stub();
            inactiveDrawerHeaderpaneCssStub = sinon.stub();
            sidebarNavCssStub =  sinon.stub();
            app.drawer = {};
        });

        afterEach(function() {
            contentCssStub = null;
            mainPaneCssStub = null;
            headerpaneCssStub = null;
            hasClassStub = null;
            drawerSidebarHasClassStub = null;
            activeDrawerMainPaneCssStub = null;
            activeDrawerHeaderpaneCssStub = null;
            inactiveDrawerHeaderpaneCssStub = null;
            sidebarNavCssStub = null;
            delete app.drawer;
        });

        describe('when Sidebar is visible', function() {
            beforeEach(function() {
                hasClassStub.withArgs('side-collapsed').returns(false);
                app.drawer.count = function() {
                    return 0;
                };
                jQueryStub = sinon.stub(window, '$').callsFake(function(selector) {
                    var retObj;

                    switch (selector) {
                        case '.side.sidebar-content':
                            retObj = {
                                length: 1,
                                hasClass: hasClassStub
                            };
                            break;
                        case '#content':
                            retObj = {
                                css: contentCssStub
                            };
                            break;
                        case '#content .main-pane':
                            retObj = {
                                css: mainPaneCssStub,
                                find: function() {
                                    // return the headerpane stub
                                    return {
                                        css: headerpaneCssStub
                                    };
                                }
                            };
                            break;
                    }
                    return retObj;
                });

                layout._setSize(false);
            });

            afterEach(function() {
                hasClassStub = null;
            });

            it('should call #content css', function() {
                expect(contentCssStub.lastCall.args[0]).toEqual({
                    marginLeft: 320,
                    width: `calc(100% - 320px - ${sideNavWidth})`
                });
            });

            it('should call .main-pane css', function() {
                expect(mainPaneCssStub.lastCall.args[0]).toEqual({
                    marginLeft: '',
                    width: 'calc(100% - 34vw)'
                });
            });

            it('should call .headerpane css', function() {
                expect(headerpaneCssStub.lastCall.args[0]).toEqual({
                    marginLeft: 320,
                    width: `calc(100% - 320px - 34vw - ${sideNavWidth})`
                });
            });
        });

        describe('when Sidebar is collapsed', function() {
            beforeEach(function() {
                hasClassStub.withArgs('side-collapsed').returns(true);
                app.drawer.count = function() {
                    return 0;
                };
                jQueryStub = sinon.stub(window, '$').callsFake(function(selector) {
                    var retObj;

                    switch (selector) {
                        case '.side.sidebar-content':
                            retObj = {
                                length: 1,
                                hasClass: hasClassStub
                            };
                            break;
                        case '#content':
                            retObj = {
                                css: contentCssStub
                            };
                            break;
                        case '#content .main-pane':
                            retObj = {
                                css: mainPaneCssStub,
                                find: function() {
                                    // return the headerpane stub
                                    return {
                                        css: headerpaneCssStub
                                    };
                                }
                            };
                            break;
                    }
                    return retObj;
                });

                layout._setSize(false);
            });

            afterEach(function() {
                hasClassStub = null;
            });

            it('should call #content css', function() {
                expect(contentCssStub.lastCall.args[0]).toEqual({
                    marginLeft: 320,
                    width: '100%'
                });
            });

            it('should call .main-pane css', function() {
                expect(mainPaneCssStub.lastCall.args[0]).toEqual({
                    marginLeft: '',
                    width: `calc(100% - 320px - ${sideNavWidth})`
                });
            });

            it('should call .headerpane css', function() {
                expect(headerpaneCssStub.lastCall.args[0]).toEqual({
                    marginLeft: 0,
                    width: `calc(100% - 320px - ${sideNavWidth})`
                });
            });
        });

        describe('when no Sidebar exists - Home', function() {
            beforeEach(function() {
                hasClassStub.withArgs('side-collapsed').returns(false);
                app.drawer.count = function() {
                    return 0;
                };
                jQueryStub = sinon.stub(window, '$').callsFake(function(selector) {
                    var retObj;

                    switch (selector) {
                        case '.side.sidebar-content':
                            retObj = {
                                length: 0,
                                hasClass: hasClassStub
                            };
                            break;
                        case '#content':
                            retObj = {
                                css: contentCssStub
                            };
                            break;
                        case '#content .main-pane':
                            retObj = {
                                css: mainPaneCssStub,
                                find: function() {
                                    // return the headerpane stub
                                    return {
                                        css: headerpaneCssStub
                                    };
                                }
                            };
                            break;
                    }
                    return retObj;
                });

                layout._setSize(false);
            });

            afterEach(function() {
                hasClassStub = null;
            });

            it('should call #content css', function() {
                expect(contentCssStub.lastCall.args[0]).toEqual({
                    marginLeft: 320,
                    width: `calc(100% - 320px - ${sideNavWidth})`
                });
            });

            it('should call .main-pane css', function() {
                expect(mainPaneCssStub.lastCall.args[0]).toEqual({
                    marginLeft: '',
                    width: '100%'
                });
            });

            it('should call .headerpane css', function() {
                expect(headerpaneCssStub.lastCall.args[0]).toEqual({
                    marginLeft: 0,
                    width: `calc(100% - 320px - ${sideNavWidth})`
                });
            });
        });

        describe('when drawers are active', function() {
            beforeEach(function() {
                hasClassStub.withArgs('side-collapsed').returns(false);
                drawerSidebarHasClassStub.withArgs('side-collapsed').returns(false);
                app.drawer.count = function() {
                    return 1;
                };
                jQueryStub = sinon.stub(window, '$').callsFake(function(selector) {
                    var retObj;

                    switch (selector) {
                        case '.side.sidebar-content':
                            retObj = {
                                length: 1,
                                hasClass: hasClassStub
                            };
                            break;
                        case '#content':
                            retObj = {
                                css: contentCssStub
                            };
                            break;
                        case '#content .main-pane':
                            retObj = {
                                css: mainPaneCssStub,
                                find: function() {
                                    return {
                                        css: headerpaneCssStub
                                    };
                                }
                            };
                            break;
                        case '.drawer.active .side.sidebar-content':
                            retObj = {
                                length: 1,
                                hasClass: drawerSidebarHasClassStub
                            };
                            break;
                        case '#drawers .drawer.active .main-pane':
                            retObj = {
                                css: activeDrawerMainPaneCssStub,
                                find: function() {
                                    return {
                                        css: activeDrawerHeaderpaneCssStub
                                    };
                                }
                            };
                            break;
                        case '#drawers .drawer.inactive .main-pane .headerpane':
                            retObj = {
                                css: inactiveDrawerHeaderpaneCssStub
                            };
                            break;
                    }
                    return retObj;
                });

                layout.currentState = 'opening';
                layout._setSize(false);
            });

            afterEach(function() {
                hasClassStub = null;
            });

            it('should call #content css', function() {
                expect(contentCssStub.lastCall.args[0]).toEqual({
                    marginLeft: 320,
                    width: `calc(100% - 320px - ${sideNavWidth})`
                });
            });

            it('should call .main-pane css', function() {
                expect(mainPaneCssStub.lastCall.args[0]).toEqual({
                    marginLeft: '',
                    width: 'calc(100% - 34vw)'
                });
            });

            it('should call .headerpane css', function() {
                expect(headerpaneCssStub.lastCall.args[0]).toEqual({
                    marginLeft: 0,
                    width: '100%'
                });
            });

            it('should call active drawer main-pane css', function() {
                expect(activeDrawerMainPaneCssStub.lastCall.args[0]).toEqual({
                    marginLeft: 320,
                    width: `calc(100% - 320px - 34vw)`
                });
            });

            it('should call active drawer headerpane css', function() {
                expect(activeDrawerHeaderpaneCssStub.lastCall.args[0]).toEqual({
                    marginLeft: 320,
                    width: `calc(100% - 320px - 34vw - ${sideNavWidth})`
                });
            });

            it('should call inactive drawer headerpane css', function() {
                expect(inactiveDrawerHeaderpaneCssStub.lastCall.args[0]).toEqual({
                    marginLeft: '320px',
                    width: `calc(100% - 320px - ${sideNavWidth})`
                });
            });
        });

        describe('with animate', function() {
            beforeEach(function() {
                sinon.stub(layout.$el, 'css');
                app.drawer.count = function() {
                    return 0;
                };
            });

            it('should set width when animate is true and mode is compact', function() {
                layout.currentMode = 'compact';
                layout._setSize(true);

                expect(layout.$el.css).toHaveBeenCalledWith({
                    left: '2.5rem',
                    width: '320px'
                });
            });

            it('should set width when animate is true and mode is full', function() {
                layout.currentMode = 'full';
                layout._setSize(true);

                expect(layout.$el.css).toHaveBeenCalledWith({
                    left: '2.5rem',
                    width: 'calc(100% - 2.5rem)'
                });
            });

            it('should set left when animate is false', function() {
                layout._setSize(false);

                expect(layout.$el.css).toHaveBeenCalledWith('left', '2.5rem');
            });
        });
    });
});
