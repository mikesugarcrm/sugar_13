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

describe('View.Layouts.Base.DashboardGridLayout', function() {
    var app;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Home', 'dashboard-grid');
    });

    afterEach(function() {
        sinon.restore();
        app.cache.cutAll();
        app.view.reset();
        layout.dispose();
        app = null;
        layout.context = null;
        layout.model = null;
        layout = null;
    });

    describe('_render', function() {
        var loadDashletsStub;
        beforeEach(function() {
            loadDashletsStub = sinon.stub(layout, 'loadDashlets');
            sinon.stub(layout, '_super');
        });
        using('different values for whether dashlets have been loaded', [
            [true], [false]
        ], function(data) {
            it('should load dashlets when dashlets are not already loaded', function() {
                layout.dashletsLoaded = data;
                layout._render();
                if (!data) {
                    expect(loadDashletsStub).toHaveBeenCalled();
                } else {
                    expect(loadDashletsStub).not.toHaveBeenCalled();
                }
            });
        });
    });

    describe('addNewDashlet', function() {
        it('should set autoposition: true on new dashlets, then set to false before saving', function() {
            var dashletDef = {
                test: 'test_dashlet_def'
            };
            sinon.stub(layout, 'addDashlet').returns(dashletDef);
            sinon.stub(layout, 'saveDashboard');
            layout.addNewDashlet(dashletDef);
            dashletDef.autoPosition = true;
            expect(layout.addDashlet).toHaveBeenCalledWith(dashletDef);
            dashletDef.auotPosition = false;
            expect(layout.dashlets).toEqual([dashletDef]);
            expect(layout.saveDashboard).toHaveBeenCalled();
        });
    });

    describe('removeDashlet', function() {
        var dashletContainer;
        beforeEach(function() {
            layout.dashlets = [{
                id: '1'
            }];
            dashletContainer = {
                el: {
                    getAttribute: function() {
                        return '1';
                    }
                },
                model: {
                    unset: sinon.stub()
                }
            };
            layout.handleSave = sinon.stub();
            layout.grid.removeWidget = sinon.stub();
        });
        afterEach(function() {
            sinon.restore();
        });
        it('should remove element from grid, and remove dashlet from metadata', function() {
            layout.removeDashlet(dashletContainer);
            expect(layout.grid.removeWidget).toHaveBeenCalledWith(dashletContainer.el);
            expect(layout.dashlets).toEqual([]);
            expect(dashletContainer.model.unset).toHaveBeenCalledWith('updated');
        });
    });

    describe('editDashlet', function() {
        var dashletContainer;
        beforeEach(function() {
            layout.dashlets = [{
                id: '1'
            }];
            dashletContainer = {
                el: {
                    getAttribute: function() {
                        return '1';
                    }
                }
            };
            layout.handleSave = sinon.stub();
        });
        afterEach(function() {
            sinon.restore();
        });
        it('should combine old and new dashlet metadata', function() {
            var newDashletDef = {
                newAttribute: 'new value'
            };
            var expected = [{id: '1', newAttribute: 'new value'}];
            layout.editDashlet(dashletContainer, newDashletDef);
            expect(layout.dashlets).toEqual(expected);
        });
    });

    describe('handleSave', function() {
        using('different acl access values', [[true], [false]], function(hasAccess) {
            beforeEach(function() {
                _.extend(layout.model, {
                    set: sinon.stub(),
                    save: sinon.stub(),
                    unset: sinon.stub()
                });
                layout.dashlets = [{
                    id: '1'
                }];
                layout._updateModelMeta = sinon.stub();
            });
            afterEach(function() {
                sinon.restore();
            });
            it('should not save if user doesn\'t have access', function() {
                var stubHasAccessToModel = sinon.stub(app.acl, 'hasAccessToModel').returns(hasAccess);
                var expectedCount = hasAccess ? 1 : 0;
                layout.handleSave();
                if (!hasAccess) {
                    expect(layout.model.unset).toHaveBeenCalledWith('updated');
                }
                expect(layout._updateModelMeta.callCount).toBe(expectedCount);
                expect(layout.model.set.callCount).toBe(expectedCount);
                expect(layout.model.save.callCount).toBe(expectedCount);
                stubHasAccessToModel.restore();
            });
        });
    });

    describe('_initializeDashlet', function() {
        var addDashletStub;
        var removeClassStub;
        var findStub;
        var wrapperLayoutStub;
        var wrapperLayout;
        beforeEach(function() {
            addDashletStub = sinon.stub();
            removeClassStub = sinon.stub();
            findStub = sinon.stub().returns({removeClass: removeClassStub});
            wrapperLayout = app.view.createLayout({
                name: 'dashlet-grid-wrapper',
                layout: layout,
            });
            wrapperLayout.addDashlet = addDashletStub;
            wrapperLayout.$el.find = findStub;

            wrapperLayoutStub = sinon.stub(app.view, 'createLayout').callsFake(function() {
                return wrapperLayout;
            });
        });
        afterEach(function() {
            sinon.restore();
        });
        it('should create a new dashlet-grid-wrapper layout', function() {
            let dashletMetaId = '123';
            let dashletDef = {test: 'test metadata'};
            let actual = layout._initializeDashlet(dashletDef, dashletMetaId);
            expect(actual).toEqual(wrapperLayout);
            expect(layout._components).toEqual([wrapperLayout]);
            expect(addDashletStub).toHaveBeenCalled(dashletDef);
            expect(findStub).toHaveBeenCalledWith('.dashlet');
            expect(removeClassStub).toHaveBeenCalledWith('ui-draggable');
            expect(wrapperLayoutStub).toHaveBeenCalledWith({
                name: 'dashlet-grid-wrapper',
                layout: layout,
                meta: {name: _.size(layout._components) - 1},
                context: layout.context,
                dashletMetaId: dashletMetaId,
            });
        });
    });

    describe('_handleGridChange', function() {
        using('different combinations of grid items and dashlets', [
            [
                [
                    {x: 0, y: 12, width: 4, height: 4, id: '3'},
                    {x: 12, y: 12, width: 4, height: 4, id: '2'},
                    {x: 8, y: 8, width: 8, height: 8, id: '1'},
                ],
                [
                    {x: 0, y: 12, width: 4, height: 4, id: '1'},
                    {x: 0, y: 0, width: 1, height: 1, id: '2'},
                    {x: 8, y: 8, width: 3, height: 3, id: '3'},
                ],
                [
                    {x: 8, y: 8, width: 8, height: 8, id: '1'},
                    {x: 12, y: 12, width: 4, height: 4, id: '2'},
                    {x: 0, y: 12, width: 4, height: 4, id: '3'},
                ],
            ],
        ], function(items, dashlets, expected) {
            beforeEach(function() {
                layout.handleSave = sinon.stub();
                layout.dashlets = dashlets;
            });
            afterEach(function() {
                sinon.restore();
            });
            it('should update dashlet meta based on grid items, by ID', function() {
                layout._handleGridChange({}, items);
                expect(layout.dashlets).toEqual(expected);
            });
        });
    });

    describe('convertLegacyComponents', function() {
        using('different legacy metadata values', [
            {
                // One Column Dashboard
                legacy: [{
                    width: 12,
                    rows: [
                        [{width: 12, view: {}}],
                        [{width: 6, view: {}}, {width: 6, view: {}}]
                    ]
                }],
                expected: [
                    {x: 0, y: 0, width: 12, height: 6, view: {}},
                    {x: 0, y: 6, width: 6, height: 6, view: {}},
                    {x: 6, y: 6, width: 6, height: 6, view: {}}
                ]
            }, {
                // Two Column Dashboard
                legacy: [{
                    width: 4,
                    rows: [
                        [{width: 12, view: {}}]
                    ]
                }, {
                    width: 8,
                    rows: [
                        [{width: 6, view: {}}, {width: 6, view: {}}],
                        [{width: 12, view: {}}]
                    ]
                }],
                expected: [
                    {x: 0, y: 0, width: 4, height: 6, view: {}},
                    {x: 4, y: 0, width: 4, height: 6, view: {}},
                    {x: 8, y: 0, width: 4, height: 6, view: {}},
                    {x: 4, y: 6, width: 8, height: 6, view: {}}
                ]
            }, {
                // Three Column Dashboard
                legacy: [{
                    width: 4,
                    rows: [
                        [{width: 12, view: {}}]
                    ]
                }, {
                    width: 4,
                    rows: [
                        [{width: 12, view: {}}],
                        [{width: 12, view: {}}]
                    ]
                }, {
                    width: 4,
                    rows: [
                        [{width: 12, view: {}}],
                        [{width: 12, view: {}}],
                        [{width: 12, view: {}}]
                    ]
                }],
                expected: [
                    {x: 0, y: 0, width: 4, height: 6, view: {}},
                    {x: 4, y: 0, width: 4, height: 6, view: {}},
                    {x: 4, y: 6, width: 4, height: 6, view: {}},
                    {x: 8, y: 0, width: 4, height: 6, view: {}},
                    {x: 8, y: 6, width: 4, height: 6, view: {}},
                    {x: 8, y: 12, width: 4, height: 6, view: {}},
                ]
            }, {
                // Dashboard without view metadata
                legacy: [{
                    width: 12,
                    rows: [
                        [{width: 12, view: {}}],
                        [{width: 12}]
                    ]
                }],
                expected: [
                    {x: 0, y: 0, width: 12, height: 6, view: {}}
                ]
            }
        ], function(values) {
            it('should properly convert legacy to updated metadata', function() {
                var actual = layout._convertLegacyComponents(values.legacy);
                expect(actual).toEqual(values.expected);
            });
        });
    });

    describe('_setInitialDashlets', function() {
        using('different metadata values', [
            {
                // Legacy Components
                metadata: {
                    legacyComponents: [{
                        width: 12,
                        rows: [
                            [{width: 12}],
                        ]
                    }]
                },
                expected: [{x: 0, y: 0, width: 12, height: 12}]
            }, {
                // Tabbed Dashboard
                metadata: {
                    tabs: [
                        {dashlets: [{x: 0, y: 0, width: 12, height: 12}]}
                    ]
                },
                expected: [{x: 0, y: 0, width: 12, height: 12}]
            }, {
                // Non-Tabbed Dashboard
                metadata: {
                    dashlets: [{x: 0, y: 0, width: 12, height: 12}]
                },
                expected: [{x: 0, y: 0, width: 12, height: 12}]
            }
        ], function(values) {
            beforeEach(function() {
                layout._convertLegacyComponents = sinon.stub().returns(values.expected);
                layout.model.get = sinon.stub().returns(values.metadata);
                layout.tabIndex = 0;
            });
            afterEach(function() {
                sinon.restore();
            });
            it('should set this.dashlets approprieately', function() {
                layout._setInitialDashlets();
                expect(layout.dashlets).toEqual(values.expected);
            });
        });
    });

    describe('_updateModelMeta', function() {
        using('tabbed vs non-tabbed dashboards', [
            {
                // tabbed metadata
                metadata: {
                    tabs: [
                        {dashlets: [], components: 'set'}
                    ],
                    legacyComponents: 'set'
                },
                dashlets: [{x: 0, y: 0, width: 4, height: 4}]
            }, {
                // Non tabbed dashboard
                metadata: {dashlets: [], components: 'set', legacyComponents: 'set'},
                dashlets: [{x: 0, y: 0, width: 12, height: 12}]
            }
        ], function(values) {
            it('should set the appropriate metadata based on tabbed dashboards', function() {
                layout.model.get = sinon.stub().returns(values.metadata);
                layout.tabIndex = 0;
                layout.dashlets = values.dashlets;
                var actual = layout._updateModelMeta();
                if (values.metadata.tabs) {
                    expect(actual.tabs[0].dashlets).toEqual(values.dashlets);
                    expect(actual.tabs[0].components).toBeUndefined();
                } else {
                    expect(actual.dashlets).toEqual(values.dashlets);
                    expect(actual.components).toBeUndefined();
                }
                expect(actual.legacyComponents).toBeUndefined();
            });
        });
    });

    describe('_setDefaultGridOptions', function() {
        using('different ACL edit values', [false, true], function(hasAccess) {
            it('should disable dragging and resizing if user lacks edit acces', function() {
                sinon.stub(app.acl, 'hasAccessToModel').callsFake(function() {
                    return hasAccess;
                });
                layout._setDefaultGridOptions();
                expect(layout.defaultGridOptions.disableDrag).toEqual(!hasAccess);
                expect(layout.defaultGridOptions.disableResize).toEqual(!hasAccess);
            });
        });
    });

    describe('_setDefaultElementOptions', function() {
        using('whether we are in a side-drawer or not',
            [{sideDrawer: true, expectedWidth: 6}, {sideDrawer: undefined, expectedWidth: 2}],
            function(values) {
                it('should set the minWidth appropriately', function() {
                    sinon.stub(layout, 'closestComponent').returns(values.sideDrawer);
                    layout._setDefaultElementOptions();
                    expect(layout.defaultElementOptions.minWidth).toEqual(values.expectedWidth);
                });
            });
    });

    describe('toggleCollapseDashlet', function() {
        let dashlet;

        beforeEach(function() {
            SugarTest.loadComponent('base', 'layout', 'dashlet-grid-wrapper');
            layout.addDashlet({});
            dashlet = layout._components[0];

            sinon.spy(layout.grid, 'resizable');
            sinon.spy(layout.grid, 'minHeight');
            sinon.spy(layout.grid, 'resize');
        });

        it('should adjust the grid properly when collapsing', function() {
            layout.toggleCollapseDashlet(dashlet, true);
            expect(layout.grid.resizable).toHaveBeenCalledWith(dashlet.$el, false);
            expect(layout.grid.minHeight).toHaveBeenCalledWith(dashlet.$el, null);
            expect(layout.grid.resize).toHaveBeenCalledWith(dashlet.$el, null, 0);
        });

        it('should adjust the grid properly when expanding', function() {
            dashlet.$el.data('expand-min-height', 1234);
            dashlet.$el.data('expand-height', 5678);
            layout.toggleCollapseDashlet(dashlet, false);
            expect(layout.grid.minHeight).toHaveBeenCalledWith(dashlet.$el, 1234);
            expect(layout.grid.resize).toHaveBeenCalledWith(dashlet.$el, null, 5678);
        });
    });
});
