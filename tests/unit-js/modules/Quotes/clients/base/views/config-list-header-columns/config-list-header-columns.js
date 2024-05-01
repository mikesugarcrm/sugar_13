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
describe('Quotes.View.ConfigListHeaderColumns', function() {
    var app;
    var view;
    var options;
    var context;
    var viewCollection;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        viewCollection = new Backbone.Collection();
        viewCollection.name = 'test';
        context.set('collection', viewCollection);
        var meta = {
            label: 'testLabel',
            panels: [{
                fields: []
            }],
            selection: {
                type: 'multi',
                actions: [{
                    name: 'group_button',
                    type: 'rowaction',
                    label: 'LBL_CREATE_GROUP_SELECTED_BUTTON_TOOLTIP'
                }, {
                    name: 'massdelete_button',
                    type: 'rowaction',
                    label: 'LBL_DELETE_SELECTED_LABEL'
                }]
            }
        };
        options = {
            collection: viewCollection,
            context: context,
            meta: meta
        };
        SugarTest.loadPlugin('MassCollection');
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        view = SugarTest.createView('base', 'Quotes', 'config-list-header-columns', meta, context, true);
        view.options.eventViewName = 'worksheet_columns';
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        view = null;
        options = null;
    });

    describe('initialize()', function() {
        beforeEach(function() {
            sinon.stub(view, 'addMultiSelectionAction');
            sinon.stub(app.template, 'getView').callsFake(function() {
                return 'template';
            });

            view.initialize(options);
        });

        it('should set massCollection to be this.collection', function() {
            expect(view.massCollection).toEqual(viewCollection);
        });

        it('should initialize leftColumns', function() {
            expect(view.leftColumns).toEqual([]);
        });

        it('should call addMultiSelectionAction', function() {
            expect(view.addMultiSelectionAction).toHaveBeenCalled();
        });

        it('should set the template', function() {
            expect(view.template).toBe('template');
        });
    });

    describe('bindDataChange()', function() {
        beforeEach(function() {
            sinon.stub(view, 'on');

            view.bindDataChange();
        });

        it('should set a listener on the view for list:reorder:columns', function() {
            expect(view.on).toHaveBeenCalledWith(
                'list:reorder:columns',
                view.onSheetColumnsOrderChanged,
                view
            );
        });
    });

    describe('onSheetColumnsOrderChanged()', function() {
        var oldFieldOrder;
        var newFieldOrder;
        var newFieldNameOrder;

        beforeEach(function() {
            sinon.spy(view.model, 'set');
            oldFieldOrder = [{
                name: 'aaa'
            }, {
                name: 'bbb'
            }];
            newFieldOrder = [{
                name: 'bbb'
            }, {
                name: 'aaa'
            }];
            newFieldNameOrder = ['bbb', 'aaa'];
            view.model.set('worksheet_columns', oldFieldOrder);
            view.onSheetColumnsOrderChanged({}, newFieldNameOrder);
        });

        afterEach(function() {
            oldFieldOrder = null;
            newFieldOrder = null;
            newFieldNameOrder = null;
        });

        it('should order the fields by the passed in field name order', function() {
            var results = view.model.get('worksheet_columns');

            expect(results).toEqual(newFieldOrder);
        });

        it('should set the fields back on the model', function() {
            expect(view.model.set).toHaveBeenCalledWith('worksheet_columns', newFieldOrder);
        });
    });

    describe('addMultiSelectionAction()', function() {
        it('should set leftColumns', function() {
            view.leftColumns = [];
            view.addMultiSelectionAction();

            expect(view.leftColumns).toEqual([{
                name: 'quote-data-mass-actions',
                type: 'fieldset',
                fields: [{
                    type: 'quote-data-actionmenu',
                    buttons: [{
                        name: 'group_button',
                        type: 'rowaction',
                        label: 'LBL_CREATE_GROUP_SELECTED_BUTTON_TOOLTIP'
                    }, {
                        name: 'massdelete_button',
                        type: 'rowaction',
                        label: 'LBL_DELETE_SELECTED_LABEL'
                    }],
                    disable_select_all_alert: false
                }],
                value: false,
                sortable: false
            }]);
        });
    });

    describe('render()', function() {
        var groupSetDisabledStub;
        var massDeleteSetDisabledStub;

        beforeEach(function() {
            groupSetDisabledStub = sinon.stub();
            massDeleteSetDisabledStub = sinon.stub();
            view.nestedFields = [{
                name: 'group_button',
                setDisabled: groupSetDisabledStub
            }, {
                name: 'massdelete_button',
                setDisabled: massDeleteSetDisabledStub
            }];
            sinon.stub(view, '_super');

            view.render();
        });

        afterEach(function() {
            groupSetDisabledStub = null;
            massDeleteSetDisabledStub = null;
            view.nestedFields = null;
        });

        it('it should set the mass group button disabled', function() {
            expect(groupSetDisabledStub).toHaveBeenCalledWith(true);
        });

        it('it should set the mass delete button disabled', function() {
            expect(massDeleteSetDisabledStub).toHaveBeenCalledWith(true);
        });
    });

    describe('setColumnHeaderFields()', function() {
        var headerFieldList;

        beforeEach(function() {
            sinon.spy(view, 'parseFields');
            sinon.stub(view, 'render');
            headerFieldList = [{
                name: 'aaa'
            }, {
                name: 'bbb'
            }];

            view.setColumnHeaderFields(headerFieldList);
        });

        afterEach(function() {
            headerFieldList = null;
        });

        it('should set meta based on passed in fields', function() {
            expect(view.meta).toEqual({
                label: 'testLabel',
                panels: [{
                    fields: headerFieldList
                }],
                selection: {
                    type: 'multi',
                    actions: [{
                        name: 'group_button',
                        type: 'rowaction',
                        label: 'LBL_CREATE_GROUP_SELECTED_BUTTON_TOOLTIP'
                    }, {
                        name: 'massdelete_button',
                        type: 'rowaction',
                        label: 'LBL_DELETE_SELECTED_LABEL'
                    }]
                },
                type: 'list',
                action: 'list'
            });
        });

        it('should set the fields on the model', function() {
            expect(view.model.get('worksheet_columns')).toEqual(headerFieldList);
        });

        it('should call parseFields', function() {
            expect(view.parseFields).toHaveBeenCalled();
        });

        it('should set _fields', function() {
            expect(view._fields).toEqual({
                _byId: {
                    aaa: {
                        selected: true,
                        position: 1,
                        name: 'aaa'
                    },
                    bbb: {
                        selected: true,
                        position: 2,
                        name: 'bbb'
                    }
                },
                visible: [{
                    selected: true,
                    position: 1,
                    name: 'aaa'
                }, {
                    selected: true,
                    position: 2,
                    name: 'bbb'
                }],
                all: [{
                    selected: true,
                    position: 1,
                    name: 'aaa'
                }, {
                    selected: true,
                    position: 2,
                    name: 'bbb'
                }]
            });
        });

        it('should call render', function() {
            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('addColumnHeaderField()', function() {
        beforeEach(function() {
            sinon.stub(view, 'render');
            view.meta = {
                panels: [{
                    fields: [{
                        name: 'bbb'
                    }]
                }]
            };
            view.model.set('worksheet_columns', [{
                name: 'bbb'
            }]);
            view.addColumnHeaderField({
                name: 'aaa'
            });
        });

        it('should put the passed in field at the beginning of the meta list', function() {
            expect(view.meta.panels[0].fields[0].name).toBe('aaa');
        });

        it('should put the passed in field at the beginning of the worksheet_columns', function() {
            expect(view.model.get('worksheet_columns')[0].name).toBe('aaa');
        });

        it('should re-set _fields', function() {
            expect(view._fields).toEqual({
                _byId: {
                    aaa: {
                        selected: true,
                        position: 1,
                        name: 'aaa'
                    },
                    bbb: {
                        selected: true,
                        position: 2,
                        name: 'bbb'
                    }
                },
                visible: [{
                    selected: true,
                    position: 1,
                    name: 'aaa'
                }, {
                    selected: true,
                    position: 2,
                    name: 'bbb'
                }],
                all: [{
                    selected: true,
                    position: 1,
                    name: 'aaa'
                }, {
                    selected: true,
                    position: 2,
                    name: 'bbb'
                }]
            });
        });

        it('should call render', function() {
            expect(view.render).toHaveBeenCalled();
        });
    });

    describe('removeColumnHeaderField()', function() {
        beforeEach(function() {
            sinon.stub(view, 'render');
            view.meta = {
                panels: [{
                    fields: [{
                        name: 'aaa'
                    }, {
                        name: 'bbb'
                    }]
                }]
            };
            view.model.set('worksheet_columns', [{
                name: 'aaa'
            }, {
                name: 'bbb'
            }]);
            view.removeColumnHeaderField({
                name: 'aaa'
            });
        });

        it('should remove the passed in field from panels fields list', function() {
            expect(view.meta.panels[0].fields[0].name).toBe('bbb');
        });

        it('should remove the passed in field from worksheet_columns list', function() {
            expect(view.model.get('worksheet_columns')[0].name).toBe('bbb');
        });

        it('should re-set _fields', function() {
            expect(view._fields).toEqual({
                _byId: {
                    bbb: {
                        selected: true,
                        position: 1,
                        name: 'bbb'
                    }
                },
                visible: [{
                    selected: true,
                    position: 1,
                    name: 'bbb'
                }],
                all: [{
                    selected: true,
                    position: 1,
                    name: 'bbb'
                }]
            });
        });

        it('should call render', function() {
            expect(view.render).toHaveBeenCalled();
        });
    });
});
