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

describe('Base.View.TabbedDashboardView', function() {
    var view;
    var app;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.createView('base', 'Home', 'tabbed-dashboard');
    });

    afterEach(function() {
        sinon.restore();
        view = null;
    });

    describe('initialize', function() {
        it('should have tabs based on the given metadata', function() {
            sinon.stub(view, 'getLastStateKey');
            sinon.stub(app.user.lastState, 'set');

            var tabs = [
                {name: 'tab 0'},
                {
                    name: 'tab 1',
                    badges: [{cssClass: 'label-important', text: '5'}],
                },
            ];
            view.initialize({
                meta: {
                    activeTab: 1,
                    tabs: tabs,
                },
            });

            expect(view.activeTab).toEqual(1);
            expect(view.tabs).toEqual(tabs);
        });
    });

    describe('tabClicked', function() {
        var triggerStub;

        beforeEach(function() {
            triggerStub = sinon.stub(view.context, 'trigger');
            sinon.stub(view, '$').withArgs('tab 1').returns({
                data: sinon.stub().withArgs('index').returns(1)
            });
            sinon.stub(view, 'canSwitchTab').returns(true);
        });

        it('should trigger tabbed-dashboard:switch-tab on the context if the active tab changed', function() {
            view.activeTab = 0;

            view.tabClicked({currentTarget: 'tab 1'});

            expect(triggerStub).toHaveBeenCalledWith('tabbed-dashboard:switch-tab', 1);
        });

        it('should not do anything if the active tab did not change', function() {
            view.activeTab = 1;

            view.tabClicked({currentTarget: 'tab 1'});

            expect(triggerStub).not.toHaveBeenCalled();
        });

        it('should not do anything if tab is disabled', function() {
            view.activeTab = 0;
            var evt = {
                currentTarget: 'tab 1',
                stopPropagation: $.noop
            };
            sinon.stub(view, 'isTabEnabled').withArgs(1).returns(false);
            view.tabClicked(evt);
            expect(triggerStub).not.toHaveBeenCalled();
        });
    });

    describe('events', function() {
        it('should re-render on tabbed-dashboard:update', function() {
            var renderStub = sinon.stub(view, 'render');
            view.context.trigger('tabbed-dashboard:update');
            expect(renderStub).toHaveBeenCalled();
        });
    });

    describe('_initTabs', function() {
        it('should set last visited tab as active ', function() {
            sinon.stub(view, 'getLastStateKey').returns('key');
            sinon.stub(app.user.lastState, 'get').returns(2);
            view.activeTab = 1;
            view._initTabs();
            expect(view.activeTab).toEqual(2);
        });
    });

    describe('_initTabBadges', function() {
        it('should set badge filter', function() {
            var expectedFilter = [
                {
                    follow_up_datetime: 'followupFilter'
                },
                {
                    name: 'nameFilter'
                }
            ];
            view.tabs = [
                {
                    badges: [
                        {
                            type: 'record-count',
                            module: 'Cases',
                            filter: [
                                {
                                    follow_up_datetime: 'followupFilter'
                                }
                            ]
                        }
                    ]
                }
            ];
            view.context = {
                get: function() {return 'model_id';},
                off: $.noop
            };
            sinon.stub(app.metadata, 'getModule').returns({
                config: {
                    filter_def: {
                        model_id: {
                            Cases: [
                                {
                                    name: 'nameFilter'
                                }
                            ]
                        }
                    }
                }
            });
            view._initTabBadges();
            expect(view.tabs[0].badges[0].filter).toEqual(expectedFilter);
        });
    });

    describe('getLastStatekey', function() {
        it('should return a key', function() {
            view.model.set('id', 'my_dashboard_id');
            expect(view.getLastStateKey()).toEqual('my_dashboard_id.last_tab');
        });
    });

    describe('_isDashboardTab', function() {
        var tab;

        afterEach(function() {
            tab = null;
        });

        using('different dashboard tab settings',
            [
                {name: 'tab0', components: {rows: ['row 1, tab 0', 'row 2, tab 0'], width: 22}},
                {name: 'tab0', dashlets: [{id: 'test1'}, {id: 'test2'}]}
            ],
            function(dashValue) {
                it('should return true for dashboard tab', function() {
                    var tab = dashValue;
                    view.tabs = [tab];
                    expect(view._isDashboardTab(0)).toBeTruthy();
                });
            }
        );

        using('different non-dashboard settings',
            [
                {name: 'tab1', components: {view: 'multi-line-list'}},
                {name: 'tab1'}
            ],
            function(dashValue) {
                it('should return true for dashboard tab', function() {
                    var tab = dashValue;
                    view.tabs = [tab];
                    expect(view._isDashboardTab(0)).toBeFalsy();
                });
            }
        );

        it('should return true for empty dashboard tab', function() {
            view.tabs = [];
            expect(view._isDashboardTab(0)).toBeTruthy();
        });
    });

    describe('_setMode', function() {
        var $tab;

        beforeEach(function() {
            var tab0 = {name: 'tab0', components: {rows: ['row 1, tab 0', 'row 2, tab 0'], width: 22}};
            var tab1 = {name: 'tab1', components: {view: 'multi-line-list'}};
            view.tabs = [tab0, tab1];
            view.activeTab = 0;
            $tab = {
                addClass: sinon.stub(),
                removeClass: sinon.stub()
            };
            sinon.stub(view, '$').returns($tab);
        });

        it('should disable tab', function() {
            view._setMode('edit');
            expect($tab.addClass).toHaveBeenCalledWith('disabled');
        });

        it('should enable tab', function() {
            view._setMode('view');
            expect($tab.removeClass).toHaveBeenCalledWith('disabled');
        });
    });

    describe('canSwitchTab', function() {
        beforeEach(function() {
            app.sideDrawer = {
                isOpen: () => false
            };
        });

        afterEach(() => {
            app.sideDrawer = null;
        });

        it('should return true with no components', function() {
            sinon.stub(view, '_getSideDrawer').returns(null);
            sinon.stub(view, '_getOmnichannelDashboard').returns(null);

            var actual = view.canSwitchTab(0);

            expect(actual).toEqual(true);
        });

        it('should return false with a blocking component', function() {
            sinon.stub(view, '_getSideDrawer').returns(null);
            sinon.stub(view, '_getOmnichannelDashboard').returns({
                triggerBefore: function() {
                    return false;
                }
            });

            var actual = view.canSwitchTab(0);

            expect(actual).toEqual(false);
        });
    });
});
