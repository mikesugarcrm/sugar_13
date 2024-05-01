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
describe('Base.View.MultiLineListView', function() {
    var view;
    var app;
    var panels;
    let query;
    let context;
    let layout;
    const layoutName = 'dashlet-main';
    const moduleName = 'Cases';

    beforeEach(function() {
        view = SugarTest.createView('base', 'Cases', 'multi-line-list');
        app = SUGAR.App;
        panels = [
            {
                'label': 'LBL_PANEL_1',
                'fields': [
                    {
                        'name': 'case_number',
                        'label': 'LBL_LIST_NUMBER',
                        'subfields': [
                            {'name': 'name_1', 'label': 'label_1'},
                            {'name': 'name_2', 'label': 'label_2'},
                        ],
                    },
                    {
                        'name': 'status',
                        'label': 'LBL_STATUS',
                        'subfields': [
                            {'name': 'name_3', 'label': 'label_3'},
                            {'name': 'name_4', 'label': 'label_4', 'related_fields': ['name_5']},
                        ],
                    }
                ]
            }
        ];
        view.metric = {
            viewdefs: {
                base: {
                    view: {
                        'multi-line-list': {
                            panels: panels
                        }
                    }
                }
            }
        };

        context = app.context.getContext();
        context.set({
            module: moduleName,
            layout: layoutName
        });

        layout = app.view.createLayout({
            name: layoutName,
            context: context,
        });

        view.layout = layout;
        view.layout.getComponent = function() {
            return {
                getComponent: function() {
                    return {
                        getComponent: function() {
                            return {
                                currentSearch: query
                            };
                        },

                        buildFilterDef: function() {
                            return [
                                {
                                    '$and': [
                                        'test',
                                        'test2',
                                        query
                                    ]
                                }
                            ];
                        }
                    };
                }
            };
        };
        view.layout.off = $.noop;

        sinon.stub(view, '_unbindResizableColumns');
        sinon.stub(view, '_makeColumnResizable');
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        app.cache.cutAll();
        app.view.reset();
        query = null;
        view = null;
        layout = null;
    });

    describe('initialize', function() {
        let options;
        beforeEach(function() {
            sinon.stub(view, '_setConfig');
            sinon.stub(view, 'addContextListeners');
            sinon.stub(view.$el, 'on').callsFake(function() {});
            sinon.stub(view, 'toggleFrozenColumnBorder');
            sinon.stub(view, 'setSubfieldType');
        });

        it('should setup resizable columns', function() {
            sinon.stub(view, '_setCollectionOption');
            let setStub = sinon.stub(view, '_setResizableColumns');
            view.initialize({module: 'Cases'});
            expect(setStub).toHaveBeenCalled();
        });

        it('should add listeners on the context', function() {
            sinon.stub(view, '_setCollectionOption');
            view.initialize({module: 'Cases'});
            expect(view.addContextListeners).toHaveBeenCalled();
        });

        it('should initialize with module-specified view metadata', function() {
            var initializedStub = sinon.stub(view, '_super');
            var getStub = sinon.stub().returns(true);
            sinon.stub(view, '_extractFieldNames').returns(['test']);

            var rowactions = {
                'actions': [
                    {
                        'type': 'rowaction',
                        'label': 'LBL_EDIT_IN_NEW_TAB',
                        'tooltip': 'LBL_EDIT_IN_NEW_TAB',
                        'event': 'list:editrow:fire',
                        'icon': 'sicon-pencil',
                        'acl_action': 'edit',
                    }
                ]
            };

            var getViewStub = sinon.stub(app.metadata, 'getView');
            getViewStub.withArgs(null, 'multi-line-list')
                .returns({rowactions: rowactions});
            getViewStub.withArgs('Cases', 'multi-line-list')
                .returns({panels: panels});

            view.initialize({
                module: 'Cases',
                context: {get: getStub},
            });

            expect(initializedStub).toHaveBeenCalledWith('initialize', [{
                module: 'Cases',
                meta: {rowactions: rowactions, panels: panels},
                context: {get: getStub},
                fields: ['test']
            }]);
        });

        it('should set skipFetch to false if its true', function() {
            sinon.stub(view, '_super');
            sinon.stub(app.metadata, 'getView');
            sinon.stub(view, '_setCollectionOption');
            sinon.stub(view, '_extractFieldNames');
            view.context = app.context.getContext();
            view.context.set('skipFetch', 'true');
            view.initialize({module: 'Cases'});
            expect(view.context.get('skipFetch')).toBeFalsy();
        });

        it('should set scroll listener on view if hasFrozenColumn is true', function() {
            view.hasFrozenColumn = true;
            sinon.stub(view, '_setCollectionOption');
            view.initialize({});

            expect(view.$el.on).toHaveBeenCalledWith('scroll');
        });

        it('should not set listener on view if hasFrozenColumn is false', function() {
            view.hasFrozenColumn = false;
            sinon.stub(view, '_setCollectionOption');
            view.initialize({});

            expect(view.$el.on).not.toHaveBeenCalledWith('scroll');
        });
    });

    describe('_handleConfirm', function() {
        it('should call callback function if there is no unsaved changes', function() {
            const onConfirmCallback = sinon.stub();
            view._handleConfirm(onConfirmCallback);
            expect(onConfirmCallback).toHaveBeenCalled();
        });
    });

    describe('_handlePaginate', function() {
        it('should call callback function on pagination event', function() {
            const onConfirmCallback = sinon.stub();
            view._handlePaginate(onConfirmCallback);
            expect(onConfirmCallback).toHaveBeenCalled();
        });

        it('should check if it needs to display confirmation message', function() {
            const handleConfirm = sinon.stub(view, '_handleConfirm');
            view._handlePaginate(() => {});
            expect(handleConfirm).toHaveBeenCalled();
        });
    });

    describe('toggleFrozenColumnBorder', function() {
        let toggleClassStub;
        let hasClassStub;
        let el;
        beforeEach(function() {
            toggleClassStub = sinon.stub(jQuery.fn, 'toggleClass');
            el = _.clone(view.$el);
            sinon.stub(view, '$').returns({
                toggleClass: toggleClassStub,
                find: {
                    css: $.noop,
                    length: 1
                }
            });
        });

        afterEach(function() {
            view.$el = el;
        });

        it('should not do anything if hasFrozenColumn is false', function() {
            view.hasFrozenColumn = false;

            view.toggleFrozenColumnBorder();
            expect(view.$).not.toHaveBeenCalled();
            expect(jQuery.fn.toggleClass).not.toHaveBeenCalled();
        });

        it('should get the elements to add border class', function() {
            view.hasFrozenColumn = true;
            view.$el = {
                0: {
                    scrollLeft: 1
                },
                off: sinon.stub(),
                closest: sinon.stub().returns({css: $.noop}),
                parent: sinon.stub().returns({
                    addClass: $.noop,
                    removeClass: $.noop
                }),
                remove: sinon.stub()
            };

            view.toggleFrozenColumnBorder();
            expect(view.$).toHaveBeenCalledWith('.table tbody tr td:nth-child(2), .table thead tr th:nth-child(2)');
        });

        it('should add column-border class if scrollLeft is greater than 0', function() {
            view.hasFrozenColumn = true;
            view.$el = {
                0: {
                    scrollLeft: 1
                },
                off: sinon.stub(),
                closest: sinon.stub().returns({css: $.noop}),
                parent: sinon.stub().returns({
                    addClass: $.noop,
                    removeClass: $.noop
                }),
                remove: sinon.stub()
            };

            view.toggleFrozenColumnBorder();
            expect(jQuery.fn.toggleClass).toHaveBeenCalledWith('column-border', true);
        });

        it('should remove column-border class if scrollLeft is not greater than 0', function() {
            view.hasFrozenColumn = true;
            view.$el = {
                0: {
                    scrollLeft: 0
                },
                off: sinon.stub(),
                closest: sinon.stub().returns({css: $.noop}),
                parent: sinon.stub().returns({
                    addClass: $.noop,
                    removeClass: $.noop
                }),
                remove: sinon.stub()
            };

            view.toggleFrozenColumnBorder();
            expect(jQuery.fn.toggleClass).toHaveBeenCalledWith('column-border', false);
        });
    });

    describe('_extractFieldNames', function() {
        it('should return an array of fields', function() {
            var meta = {panels: panels};
            var actual = view._extractFieldNames(meta);
            var expected = ['name_1', 'name_2', 'name_3', 'name_4', 'name_5'];
            expect(actual).toEqual(expected);
        });
    });

    describe('_setResizableColumns', function() {
        it('should setup resizable columns', function() {
            view.meta = {
                panels: [{
                    fields: [
                        {name: 'field1'}
                    ]
                }]
            };
            sinon.stub(app.user.lastState, 'key').returns('key1');
            sinon.stub(view, 'on');
            view._setResizableColumns();
            expect(view._fieldSizesKey).toEqual('key1');
            expect(view._fieldSizes).toEqual(null);
            expect(view._fields.visible).toEqual([{name: 'field1'}]);
        });
    });

    describe('getCacheWidths', function() {
        it('should return current widths', function() {
            view._fieldSizes = {
                visible: ['field1', 'field2', 'field3'],
                widths: [12, 23, 34]
            };
            view._fields = {
                visible: [
                    {name: 'field1'},
                    {name: 'field2'},
                    {name: 'field3'}
                ]
            };
            expect(view.getCacheWidths()).toEqual([12, 23, 34]);
        });

        it('should return cached widths', function() {
            view._fieldSizes = null;
            view._fieldSizesKey = 'key1';
            view._fields = {
                visible: [
                    {name: 'field1'},
                    {name: 'field2'},
                    {name: 'field3'}
                ]
            };
            sinon.stub(app.user.lastState, 'get').returns({
                visible: ['field1', 'field2', 'field3'],
                widths: [12, 23, 34]
            });
            expect(view.getCacheWidths()).toEqual([12, 23, 34]);
        });
    });

    describe('saveCurrentWidths', function() {
        it('should save widths', function() {
            let setStub = sinon.stub(app.user.lastState, 'set');
            view._fieldSizesKey = 'key1';
            view._fieldSizes = null;
            view._fields = {
                visible: [
                    {name: 'field1'},
                    {name: 'field2'},
                    {name: 'field3'}
                ]
            };
            view.saveCurrentWidths([12, 23, 34]);
            expect(setStub).toHaveBeenCalledWith(
                'key1',
                {
                    visible: ['field1', 'field2', 'field3'],
                    widths: [12, 23, 34]
                }
            );
        });
    });

    describe('rowactions', function() {
        var model;

        beforeEach(function() {
            model = app.data.createBean('Cases', {id: 'my_case_id'});
            app.routing.start();
        });

        afterEach(function() {
            app.router.stop();
            model = null;
        });

        it('should open record view in edit mode', function() {
            var buildRouteStub = sinon.stub(app.router, 'buildRoute').returns('Cases/my_case_id/edit');
            var openStub = sinon.stub(window, 'open');
            view.editClicked(model);
            expect(openStub).toHaveBeenCalledWith('#Cases/my_case_id/edit', '_blank');
        });

        it('should open record view in view mode', function() {
            var buildRouteStub = sinon.stub(app.router, 'buildRoute').returns('Cases/my_case_id');
            var openStub = sinon.stub(window, 'open');
            view.openClicked(model);
            expect(openStub).toHaveBeenCalledWith('#Cases/my_case_id', '_blank');
        });
    });

    describe('setFilterDef', function() {
        var context;
        var options;
        beforeEach(function() {
            context = app.context.getContext();
            context.set('collection', new Backbone.Collection());
            context.prepare();

            options = {
                context: context
            };
        });

        afterEach(function() {
            context = null;
        });

        it('should not do anything if options meta is empty', function() {
            view.setFilterDef(options);

            expect(options.context.get('collection').defaultFilterDef).not.toBeDefined();
        });

        it('should set defaultFilterDef and filterDef in collection', function() {
            options.meta = {
                filterDef: [
                    'test'
                ]
            };
            options.context.get('collection').origFilterDef = ['test2'];
            view.setFilterDef(options);

            expect(options.context.get('collection').defaultFilterDef).toEqual(['test']);
            expect(options.context.get('collection').filterDef).toEqual(['test', 'test2']);
        });

        it('should set update filterDef if search filter is applied', function() {
            options.meta = {
                filterDef: [
                    'test'
                ]
            };
            options.context.get('collection').origFilterDef = ['test2'];
            query = 'query';
            view.setFilterDef(options);

            expect(options.context.get('collection').filterDef).toEqual([
                {
                    $and: ['test', 'test2', 'query']
                }]);
        });
    });

    describe('highlightRow', function() {
        var $el;
        var $row1;
        var $row2;
        beforeEach(function() {
            $el = view.$el;
            $row1 = $('<tr class="multi-line-row"></tr>');
            $row2 = $('<tr class="multi-line-row"></tr>');
            var $mainTable = $('<table><tbody></tbody></table>');
            $row1.appendTo($mainTable);
            $row2.appendTo($mainTable);
            view.$el = $mainTable;
        });

        afterEach(function() {
            view.$el = $el;
        });

        it('should highlight the clicked row', function() {
            view.highlightRow($row1);
            expect($row1.hasClass('current highlighted')).toBeTruthy();
            expect($row2.hasClass('current highlighted')).toBeFalsy();

            view.highlightRow($row2);
            expect($row2.hasClass('current highlighted')).toBeTruthy();
            expect($row1.hasClass('current highlighted')).toBeFalsy();
        });
    });

    describe('handleRowClick', function() {
        var $el;
        var target = 'targetValue';
        var event = {target: target};

        beforeEach(function() {
            $el = {
                closest: $.noop
            };
            sinon.stub(view, '$').withArgs(target).returns($el);
            sinon.stub(view, 'highlightRow');
        });

        afterEach(function() {
            $el = null;
        });

        it('should not take any action when event trigger by dropdown toggle', function() {
            var closestStub = sinon.stub($el, 'closest');
            var getDrawerStub = sinon.stub(view, '_getSideDrawer');
            sinon.stub(view, 'isDropdownToggle').withArgs($el).returns(true);
            view.handleRowClick(event);

            // Method not try to get closest row model id to proceed further action
            expect(closestStub).not.toHaveBeenCalled();
            expect(getDrawerStub).not.toHaveBeenCalled();
        });

        it('should not take any action when any action dropdowns are open', function() {
            var closestStub = sinon.stub($el, 'closest');
            var getDrawerStub = sinon.stub(view, '_getSideDrawer');

            sinon.stub(view, 'isDropdownToggle').withArgs($el).returns(false);
            sinon.stub(view, 'isActionsDropdownOpen').returns(true);
            view.handleRowClick(event);

            // Method not try to get closest row model id to proceed further action
            expect(closestStub).not.toHaveBeenCalled();
            expect(getDrawerStub).not.toHaveBeenCalled();
        });

        describe('open drawer', function() {
            var model1;
            var model2;
            var layout;

            beforeEach(function() {
                model1 = app.data.createBean('Cases', {id: '1234'});
                model2 = app.data.createBean('Cases', {id: '9999'});
                view.collection = app.data.createBeanCollection('Cases', [model1, model2]);
                sinon.stub(view, 'isDropdownToggle').withArgs($el).returns(false);
                sinon.stub(view, 'isActionsDropdownOpen').returns(false);
                layout = {
                    setRowModel: sinon.stub().returns(true)
                };

                sinon.stub(view, '_getNameFieldDefs').returns({});
            });

            afterEach(() => {
                app.sideDrawer = null;
            });

            it('should open drawer when no existing drawer open', function() {
                sinon.stub($el, 'closest').withArgs('.multi-line-row').returns({
                    data: sinon.stub().withArgs('id').returns('1234')
                });

                app.sideDrawer = {
                    isOpen: () => false,
                    getDataTitle: () => {},
                    open: sinon.stub()
                };

                view.handleRowClick(event);

                expect(app.sideDrawer.open.lastCall.args[0].layout).toEqual('row-model-data');
                expect(app.sideDrawer.open.lastCall.args[0].context.layout).toEqual('focus');
                expect(view.drawerModelId).toEqual('1234');
            });


            describe('clicking different row', function() {
                beforeEach(function() {
                    view.drawerModelId = '9999';
                    sinon.stub($el, 'closest').withArgs('.multi-line-row').returns({
                        data: sinon.stub().withArgs('id').returns('1234')
                    });

                    app.sideDrawer = {
                        open: sinon.stub(),
                        isOpen: () => true,
                        getDataTitle: () => {},
                        getComponent: () => layout,
                        triggerBefore: () => true,
                        getParentContextDef: () => ({
                            context: {
                                modelId: null
                            }
                        }),
                        setParentContextDef: _.noop,
                        showPreviousNextBtnGroup: _.noop
                    };
                });

                afterEach(() => {
                    app.sideDrawer = null;
                });

                it('should change model in context if different row is clicked', function() {
                    view.handleRowClick(event);

                    expect(app.sideDrawer.open).toHaveBeenCalled();
                    expect(view.drawerModelId).toEqual('1234');
                });

                it('should not change model in context if unsaved changes warning appears', function() {
                    sinon.stub(app.sideDrawer, 'triggerBefore').returns(false);

                    view.handleRowClick(event);

                    expect(layout.setRowModel).not.toHaveBeenCalled();
                    expect(view.drawerModelId).toEqual('9999');
                });
            });

            it('should not close existing drawer if same row is clicked', function() {
                view.drawerModelId = '1234';
                sinon.stub($el, 'closest').withArgs('.multi-line-row').returns({
                    data: sinon.stub().withArgs('id').returns('1234')
                });
                var drawer = {
                    isOpen: function() {
                        return true;
                    },
                    getDataTitle: () => {},
                    open: sinon.stub(),
                    getComponent: function() {
                        return layout;
                    }
                };
                sinon.stub(view, '_getSideDrawer').returns(drawer);

                view.handleRowClick(event);

                expect(drawer.open).not.toHaveBeenCalled();
                expect(layout.setRowModel).not.toHaveBeenCalled();
            });
        });
    });

    describe('addActions', function() {
        beforeEach(function() {
            view.leftColumns = [];
        });

        it('should not add field to leftColunms when meta is empty', function() {
            view.addActions(undefined);
            expect(view.leftColumns.length).toBe(0);
        });

        it('should not add field to leftColunms when rowactions is empty', function() {
            view.addActions({
                rowactions: undefined
            });
            expect(view.leftColumns.length).toBe(0);
        });

        it('should add field to leftColunms when meta', function() {
            var actions = ['action1', 'action2'];
            var cssClass = 'dummy_class';
            var label = 'LBL_DUMMY_LABLE';

            var expectedFieldMeta = {
                'type': 'fieldset',
                'css_class': 'overflow-visible',
                'fields': [
                    {
                        'type': 'rowactions',
                        'no_default_action': true,
                        'label': label,
                        'css_class': cssClass,
                        'buttons': actions
                    }
                ]
            };

            view.addActions({
                rowactions: {
                    actions: actions,
                    css_class: cssClass,
                    label: label
                }
            });

            expect(view.leftColumns.length).toBe(1);
            expect(view.leftColumns[0]).toEqual(expectedFieldMeta);
        });
    });

    describe('isActionDropdownOpen', function() {
        it('should return true when any elements match with selector', function() {
            var selector = '.fieldset.actions.list.btn-group.open';
            sinon.stub(view, '$').withArgs(selector).returns({length: 1});

            expect(view.isActionsDropdownOpen()).toBe(true);
        });

        it('should return false when no element matchs with selector', function() {
            var selector = '.fieldset.actions.list.btn-group.open';
            sinon.stub(view, '$').withArgs(selector).returns({length: 0});

            expect(view.isActionsDropdownOpen()).toBe(false);
        });
    });

    describe('isDropdownToggle', function() {
        it('should return true when element has the dropdown-toggle class', function() {
            var $el = {
                hasClass: sinon.stub().withArgs('dropdown-menu').returns(true)
            };

            expect(view.isDropdownToggle($el)).toBe(true);
        });

        it('should return true when any parents of element has the dropdown-menu class', function() {
            var $el = {
                hasClass: sinon.stub().withArgs('dropdown-menu').returns(false),
                closest: sinon.stub().returns([
                    {},
                ]),
            };

            expect(view.isDropdownToggle($el)).toBe(true);
        });

        it('should return false when neither the element nor its parents has the dropdown-menu class', function() {
            var $el = {
                hasClass: sinon.stub().withArgs('dropdown-menu').returns(false),
                parent: sinon.stub().returns({
                    hasClass: sinon.stub().withArgs('dropdown-menu').returns(false)
                })
            };

            expect(view.isDropdownToggle($el)).toBe(false);
        });
    });

    describe('updateDropdownDirection', function() {
        var $buttonGroup;
        var jQueryMock;
        var target = 'targetValue';
        var event = {currentTarget: target};

        beforeEach(function() {
            $buttonGroup = {
                height: sinon.stub().returns(100),
                children: sinon.stub().withArgs('ul').returns({
                    first: sinon.stub().returns({
                        height: sinon.stub().returns(100)
                    })
                }), // height of button group + children = 200
                offset: sinon.stub(), // offset position determine dropup class
                toggleClass: sinon.stub()
            };
            jQueryMock = sinon.stub(view, '$');
            jQueryMock.withArgs('targetValue').returns({
                first: sinon.stub().returns($buttonGroup)
            });
            sinon.stub(window, '$').withArgs(window).returns({
                // windowHeight(865) - padding(65) = 800, making offset 600 as break point
                height: sinon.stub().returns(865)
            });
        });

        afterEach(function() {
            $buttonGroup = null;
            jQueryMock = null;
        });

        it('should not update $buttonGroup with dropup class when dropdown menu not out of window', function() {
            $buttonGroup.offset.returns({top: 600});
            view.updateDropdownDirection(event);
            expect($buttonGroup.toggleClass).not.toHaveBeenCalled();
        });

        it('should update $buttonGroup with dropup class when dropdown menu would be out of window', function() {
            $buttonGroup.offset.returns({top: 601});
            view.updateDropdownDirection(event);
            expect($buttonGroup.toggleClass).toHaveBeenCalledWith('dropup');
        });
    });

    describe('_setCollectionOption', function() {
        var options;

        beforeEach(function() {
            options = {
                module: 'Cases',
                context: {
                    get: sinon.stub(),
                    set: sinon.stub(),
                },
            };
        });

        afterEach(function() {
            options = null;
        });

        it('should create and set collection on context', function() {
            var mockCollection = {whateverProp: 'whateverValue'};
            var createCollectionStub = sinon.stub(app.data, 'createBeanCollection');
            options.context.get.withArgs('collection').returns(undefined);

            view._setCollectionOption(options);
            expect(createCollectionStub).toHaveBeenCalledWith('Cases');
            expect(options.context.set).toHaveBeenCalled();
        });

        it('should not set collection option and filterDef when not available', function() {
            var mockCollection = {
                whateverProp: 'whateverValue',
                setOption: sinon.stub(),
            };
            options.context.get.withArgs('collection').returns(mockCollection);

            view._setCollectionOption(options);
            expect(mockCollection.setOption).not.toHaveBeenCalled();
            expect(mockCollection.filterDef).toBeUndefined();
        });

        it('should set collection option and filterDef when available', function() {
            var mockCollection = {
                whateverProp: 'whateverValue',
                setOption: sinon.stub(),
            };
            options.context.get.withArgs('collection').returns(mockCollection);
            options.meta = {
                collectionOptions: {sampleProps: 'sampleValue'},
                filterDef: ['fakeFilterValue'],
            };

            view._setCollectionOption(options);
            expect(mockCollection.setOption).toHaveBeenCalledWith({sampleProps: 'sampleValue'});
            expect(mockCollection.filterDef).toEqual(['fakeFilterValue']);
        });
    });

    describe('_setConfig', function() {
        var filterDef = 'fake_filterDef';
        var orderByPrimary = 'fake_orderByPrimary';
        var orderByPrimaryDirection = 'asc';
        var orderBySecondary = 'fake_orderBySecondary';
        var orderBySecondaryDirection = 'desc';

        beforeEach(function() {
            options = {
                context: {
                    get: sinon.stub().returns('Cases'),
                    parent: {
                        get: sinon.stub().returns('my_console_id')
                    }
                },
            };
        });

        afterEach(function() {
            options = null;
        });

        it('should set order_by and filterDef when available', function() {
            sinon.stub(view, '_getCachedOrderBy').returns('');
            view.metric.filter_def = filterDef;
            view.metric.order_by_primary = orderByPrimary;
            view.metric.order_by_primary_direction = orderByPrimaryDirection;
            view.metric.order_by_secondary = orderBySecondary;
            view.metric.order_by_secondary_direction = orderBySecondaryDirection;
            var expectedOrderBy = orderByPrimary + ':' + orderByPrimaryDirection + ',' +
                orderBySecondary + ':' + orderBySecondaryDirection;

            view._setConfig(options);

            expect(options.meta.collectionOptions.params.order_by).toEqual(expectedOrderBy);
            expect(options.meta.filterDef).toEqual(filterDef);
        });
    });

    describe('setSubfieldType', function() {
        it('should set focus drawer types from vardefs.php to mulit-line-list fields', function() {
            let fields = [
                {
                    name: 'field_name',
                    type: 'name',
                },
                {
                    name: 'field_parent',
                    type: 'parent',
                },
                {
                    name: 'field_relate',
                    type: 'relate',
                },
                {
                    name: 'field_foo',
                    type: 'foo',
                },
                {
                    name: 'field_bar',
                    type: 'relate',
                },
            ];
            view.meta = {
                panels: [{
                    fields: [
                        {subfields: [{name: 'field_name'}]},
                        {subfields: [{name: 'field_parent'}]},
                        {subfields: [{name: 'field_relate'}]},
                        {subfields: [{name: 'field_foo'}]},
                        {subfields: [{name: 'field_bar', type: 'bar'}]},
                    ]
                }]
            };
            let expectedMeta = {
                panels: [{
                    fields: [
                        {
                            subfields: [{
                                name: 'field_name',
                                type: 'name',
                                link: true,
                            }]
                        },
                        {
                            subfields: [{
                                name: 'field_parent',
                                type: 'parent',
                                link: true,
                            }]
                        },
                        {
                            subfields: [{
                                name: 'field_relate',
                                type: 'relate',
                                link: true,
                            }]
                        },
                        {
                            subfields: [{
                                name: 'field_foo',
                            }]
                        },
                        {
                            subfields: [{
                                name: 'field_bar',
                                type: 'bar',
                                link: true,
                            }]
                        },
                    ]
                }]
            };
            view.setSubfieldType(fields, view.meta.panels);
            expect(view.meta).toEqual(expectedMeta);
        });
    });
});
