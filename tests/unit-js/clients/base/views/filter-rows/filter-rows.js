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
describe('Base.View.FilterRows', function() {
    var view, layout, app;
    var parentLayout;
    var filterLayout;

    beforeEach(function() {
        app = SUGAR.App;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadComponent('base', 'layout', 'filter');
        SugarTest.loadComponent('base', 'layout', 'togglepanel');
        SugarTest.loadComponent('base', 'layout', 'filterpanel');
        SugarTest.loadComponent('base', 'view', 'filter-rows');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        parentLayout = app.view.createLayout({type: 'base'});
        layout = SugarTest.createLayout('base', 'Cases', 'filterpanel', {}, null, null, {layout: parentLayout});
        filterLayout = SugarTest.createLayout('base', 'Cases', 'filter', {}, null, null, {layout: layout});
        layout._components.push(filterLayout);
        view = SugarTest.createView("base", "Cases", "filter-rows", null, null, null, layout);
        view.layout = layout;
        view.context.editingFilter = new Backbone.Model();
    });

    afterEach(function() {
        sinon.restore();
        SugarTest.testMetadata.dispose();
        view.dispose();
        layout.dispose();
        parentLayout.dispose();
        filterLayout.dispose();
        app.cache.cutAll();
        Handlebars.templates = {};
    });

    describe('handleFilterChange', function() {
        var module = 'Cases';

        beforeEach(function() {
            sinon.stub(view, 'loadFilterFields');
            sinon.stub(view, 'loadFilterOperators');
        });

        it('should update `moduleName` and load filter fields and operators based on supplied module', function() {
            sinon.stub(app.metadata, 'getModule')
                .withArgs(module, 'filters').returns({basic: {}});

            view.handleFilterChange(module);

            expect(app.metadata.getModule).toHaveBeenCalledOnce();
            expect(app.metadata.getModule).toHaveBeenCalledWith(module, 'filters');

            expect(view.moduleName).toEqual(module);

            expect(view.loadFilterFields).toHaveBeenCalledOnce();
            expect(view.loadFilterFields).toHaveBeenCalledWith(module);

            expect(view.loadFilterOperators).toHaveBeenCalledOnce();
            expect(view.loadFilterOperators).toHaveBeenCalledWith(module);
        });

        it('should not update `moduleName` nor load filter fields and operators if no filters defined', function() {
            sinon.stub(app.metadata, 'getModule')
                .withArgs(module, 'filters').returns({});

            view.handleFilterChange(module);

            expect(app.metadata.getModule).toHaveBeenCalledOnce();
            expect(app.metadata.getModule).toHaveBeenCalledWith(module, 'filters');

            expect(view.moduleName).toBeUndefined();

            expect(view.loadFilterFields).not.toHaveBeenCalled();
            expect(view.loadFilterOperators).not.toHaveBeenCalled();
        });

        it('should not update `moduleName` nor load filter fields and operators if supplied `module` didn\'t changed', function() {
            sinon.stub(app.metadata, 'getModule')
                .withArgs(module, 'filters').returns({basic: {}});

            view.handleFilterChange(module);
            view.handleFilterChange(module);

            expect(app.metadata.getModule).toHaveBeenCalledTwice();
            expect(app.metadata.getModule).toHaveBeenCalledWith(module, 'filters');

            expect(view.moduleName).toBe(module);

            expect(view.loadFilterFields).toHaveBeenCalledOnce();
            expect(view.loadFilterFields).toHaveBeenCalledWith(module);

            expect(view.loadFilterOperators).toHaveBeenCalledOnce();
            expect(view.loadFilterOperators).toHaveBeenCalledWith(module);
        });
    });

    describe('loadFilterFields', function() {
        var module = 'Cases';

        it('should load filter fields properly', function() {
            var filterableFields = {status: {vname: 'LBL_STATUS'}};
            var expectedFilterFields = {status: 'Status'};

            sinon.stub(app.metadata, 'getModule')
                .withArgs(module, 'filters').returns({basic: {}});

            var beanClass = $.noop;
            beanClass.prototype.getFilterableFields = $.noop;

            sinon.stub(beanClass.prototype, 'getFilterableFields')
                .withArgs(module).returns(filterableFields);
            sinon.stub(app.data, 'getBeanClass')
                .withArgs('Filters').returns(beanClass);

            sinon.stub(app.lang, 'get')
                .withArgs('LBL_STATUS').returns('Status');

            view.loadFilterFields(module);

            expect(app.metadata.getModule).toHaveBeenCalledOnce();
            expect(app.metadata.getModule).toHaveBeenCalledWith(module, 'filters');

            expect(beanClass.prototype.getFilterableFields).toHaveBeenCalledOnce();
            expect(beanClass.prototype.getFilterableFields).toHaveBeenCalledWith(module);

            expect(view.filterFields).toEqual(expectedFilterFields);
            expect(view.fieldList).toEqual(filterableFields);
        });

        it('should not load filter fields if no filters defined', function() {
            sinon.stub(app.metadata, 'getModule')
                .withArgs(module, 'filters').returns({});

            var beanClass = $.noop;
            beanClass.prototype.getFilterableFields = $.noop;

            sinon.stub(beanClass.prototype, 'getFilterableFields');

            view.loadFilterFields(module);

            expect(app.metadata.getModule).toHaveBeenCalledOnce();
            expect(app.metadata.getModule).toHaveBeenCalledWith(module, 'filters');

            expect(beanClass.prototype.getFilterableFields).not.toHaveBeenCalled();

            expect(view.filterFields).toEqual([]);
            expect(view.fieldList).toBeUndefined();
        });
    });

    describe('loadFilterOperators', function() {
        var module = 'Cases';

        it('should load filter operators properly', function() {
            let operatorMap = {
                enum: {
                    $empty: 'LBL_OPERATOR_EMPTY',
                    $in: 'LBL_OPERATOR_CONTAINS',
                    $not_empty: 'LBL_OPERATOR_NOT_EMPTY',
                    $not_in: 'LBL_OPERATOR_NOT_CONTAINS'
                }
            };

            sinon.stub(app.metadata, 'getFilterOperators')
                .withArgs(module).returns(operatorMap);

            view.loadFilterOperators(module);

            expect(app.metadata.getFilterOperators).toHaveBeenCalledOnce();
            expect(app.metadata.getFilterOperators).toHaveBeenCalledWith(module);

            expect(view.filterOperatorMap).toEqual(operatorMap);
        });
    });

    describe('openForm', function() {
        var renderStub, addRowStub, populateFilterStub, saveEditStateStub,
            filterModel;
        beforeEach(function() {
            renderStub = sinon.stub(view, 'render');
            addRowStub = sinon.stub(view, 'addRow').returns($('<div></div>').data('nameField', {}));
            saveEditStateStub = sinon.stub(view, 'saveFilterEditState');
            populateFilterStub = sinon.stub(view, 'populateFilter');
            filterModel = new Backbone.Model();
        });

        it('should render the view and add a row', function() {
            view.openForm(filterModel);
            expect(renderStub).toHaveBeenCalled();
            expect(addRowStub).toHaveBeenCalled();
            expect(populateFilterStub).not.toHaveBeenCalled();
            expect(saveEditStateStub).toHaveBeenCalled();
        });

        it('should populate filter', function() {
            filterModel.set('filter_definition', [{ /* ... */ }]);
            view.openForm(filterModel);
            expect(renderStub).not.toHaveBeenCalled();
            expect(addRowStub).not.toHaveBeenCalled();
            expect(populateFilterStub).toHaveBeenCalled();
            expect(saveEditStateStub).toHaveBeenCalled();
        });
    });

    describe('saveFilter', function() {
        it('should trigger events', function() {
            let layoutTriggerStub = sinon.stub(view.layout, 'trigger');
            let ctxTriggerStub = sinon.stub(view.context, 'trigger');
            sinon.stub(view.context.editingFilter, 'sync').callsFake(function(method, model, options) {
                if (options.success) options.success(model, {}, options);
            });
            view.saveFilter();
            expect(ctxTriggerStub).toHaveBeenCalledWith('filter:add', view.context.editingFilter);
            expect(layoutTriggerStub).toHaveBeenCalledWith('filter:toggle:savestate', false);
        });
    });

    describe('deleteFilter', function() {
        it('should trigger events', function() {
            var triggerStub = sinon.stub(view.layout, 'trigger');
            view.deleteFilter();
            expect(triggerStub).toHaveBeenCalledWith('filter:remove', view.context.editingFilter);
            expect(triggerStub).toHaveBeenCalledWith('filter:create:close');
        });
    });

    describe('getFilterableFields', function() {
        it('should return the list of filterable fields with fields definition', function() {
            sinon.stub(app.metadata, 'getModule').returns(
                {
                    fields: {
                        name: {
                            name: 'name',
                            type: 'varchar',
                            len: 100
                        },
                        date_modified: {
                            name: 'date_modified',
                            options: 'date_range_search_dom',
                            type: 'datetime',
                            vname: 'LBL_DATE_MODIFIED'
                        },
                        number: {
                            name: 'number',
                            type: 'varchar',
                            len: 100,
                            readonly: true
                        }
                    },
                    filters: {
                        'default': {
                            meta: {
                                default_filter: 'all_records',
                                fields: {
                                    account_name_related: {
                                        dbFields: ['accounts.name'],
                                        type: 'text',
                                        vname: 'LBL_ACCOUNT_NAME'
                                    },
                                    date_modified: {},
                                    number: {}
                                },
                                filters: [
                                    {
                                        id: 'test_filter',
                                        name: 'Test Filter',
                                        filter_definition: {
                                            '$starts': 'Test'
                                        }
                                    }
                                ]
                            }
                        }
                    }
                }
            );
            var fields = view.getFilterableFields('Cases');
            var expected = {
                account_name_related: {
                    name: 'account_name_related',
                    dbFields: ['accounts.name'],
                    type: 'text',
                    vname: 'LBL_ACCOUNT_NAME'
                },
                date_modified: {
                    name: 'date_modified',
                    options: 'date_range_search_dom',
                    type: 'datetime',
                    vname: 'LBL_DATE_MODIFIED'
                },
                number: {
                    name: 'number',
                    type: 'varchar',
                    len: 100
                }
            };
            expect(fields).toEqual(expected);
            expect(fields.number['readonly']).not.toBe(true);
        });
    });

    describe('createField', function() {
        it('should instanciate a field', function() {
            var def = {type: 'enum', options:{test: ''}};
            var field = view.createField(new Backbone.Model(), def);
            expect(field instanceof app.view.Field).toBeTruthy();
            expect(field.type).toEqual('enum');
            expect(field.def).toEqual(def);

            field.dispose();
        });
    });

    describe('addRow', function() {
        it('should add a filter row to the view', function() {
            sinon.spy(view, 'createField');
            view.formRowTemplate = function() {
                return '<div>';
            };
            var $row = view.addRow();
            expect($row).toBeDefined();
        });
    });

    describe('removeRow', function() {
        var $event;

        beforeEach(function() {
            $event = $('<div>');
            sinon.stub(view, 'addRow').callsFake(function() {
                var $row = $('<div data-filter="row">').appendTo(view.$el);
                return $row;
            });
            $('<div data-filter="row">').appendTo(view.$el);
            $('<div data-filter="row">').appendTo(view.$el);
            $('<div data-filter="row">').appendTo(view.$el);
        });

        it('should remove the row from the view', function() {
            $event.appendTo(view.$('[data-filter=row]:last'));
            view.removeRow({currentTarget: $event});
            expect(_.size(view.$('[data-filter=row]'))).toEqual(2);

            $event.appendTo(view.$('[data-filter=row]:last'));
            view.removeRow({currentTarget: $event});
            expect(_.size(view.$('[data-filter=row]'))).toEqual(1);

            //it should add another row when the form becomes empty
            $event.appendTo(view.$('[data-filter=row]:last'));
            view.removeRow({currentTarget: $event});
            expect(_.size(view.$('[data-filter=row]'))).toEqual(1);
        });

        it('should dispose fields', function() {
            var disposeStub = sinon.stub(view, '_disposeRowFields');
            view.removeRow({currentTarget: $event});
            expect(disposeStub).toHaveBeenCalled();
            expect(disposeStub.lastCall.args[1]).toEqual([
                {field: 'nameField', value: 'name'},
                {field: 'operatorField', value: 'operator'},
                {field: 'valueField', value: 'value'}
            ]);
        });
    });

    describe('rows validation', function() {
        it('should return true if all rows have a value set', function() {
            var $rows = [
                $('<div>').data({ name: 'abc', value: 'ABC'}),
                $('<div>').data({ name: '123', value: '123'})
            ];
            expect(view.validateRows($rows)).toBe(true);
        });

        it('should return false if one row has a value not set', function() {
            var $rows = [
                $('<div>').data({ name: 'abc', value: 'ABC'}),
                $('<div>').data({ name: '123'})
            ];
            expect(view.validateRows($rows)).toBe(false);
        });

        using('possible filters', [{
            filter: $('<div>').data({ name: 'abc', isDateRange: true}),
            expected: true
        },{
            filter: $('<div>').data({ name: '$favorite', isPredefinedFilter: true}),
            expected: true
        },{
            filter: $('<div>').data({ name: 'abc', operator: '$dateBetween', value: ['12-12-12']}),
            expected: false
        },{
            filter: $('<div>').data({ name: 'abc', operator: '$dateBetween', value: ['', '12-12-12']}),
            expected: false
        },{
            filter: $('<div>').data({ name: 'abc', operator: '$dateBetween', value: ['12-12-12', '12-13-12']}),
            expected: true
        },{
            filter: $('<div>').data({ name: 'abc', operator: '$between', value: [11]}),
            expected: false
        },{
            filter: $('<div>').data({ name: 'abc', operator: '$between', value: [11, 22]}),
            expected: true
        },{
            filter: $('<div>').data({ name: 'abc', operator: '$between', value: ['11', 22]}),
            // FIXME: This is a temporary fix because some fields do not set a true number (see SC-3138).
            expected: true
        }, {
            filter: $('<div>').data({ isFlexRelate: true, name: 'abc', operator: '$equals',
                value: {parent_id: '', parent_type: ''}}),
            expected: false
        }, {
            filter: $('<div>').data({ isFlexRelate: true, name: 'abc', operator: '$equals',
                value: {parent_id: '', parent_type: 'Contacts'}}),
            expected: false
        }, {
            filter: $('<div>').data({ isFlexRelate: true, name: 'abc', operator: '$equals',
                value: {parent_id: '12345', parent_type: 'Accounts'}}),
            expected: true
        }], function(value) {
            it('should validate a filter correctly', function() {
                expect(view.validateRows(value.filter)).toBe(value.expected);
            });
        });
    });

    describe('populateFilter', function() {
        it('should trigger filter:set:name and populate rows', function() {
            view.context.editingFilter = new Backbone.Model({ name: 'Test',
                filter_definition: [
                    {
                        first_name: 'FirstName'
                    },
                    {
                        last_name: {
                            '$starts': 'LastName'
                        }
                    }
                ]
            });
            var triggerStub = sinon.stub(view.layout, 'trigger');
            var populateRowStub = sinon.stub(view, 'populateRow');
            view.populateFilter();
            expect(triggerStub).toHaveBeenCalledWith('filter:set:name', 'Test');
            expect(populateRowStub.secondCall).toBeDefined();
        });
    });

    describe('populateRow', function() {
        var addRowStub, initRowStub, _select2Obj, _rowObj;

        beforeEach(function() {
            view.fieldList = {
                name: {},
                account_name: {
                    name: 'account_name',
                    id_name: 'account_id'
                }
            };
            _select2Obj = {
                select2: sinon.stub($.fn, 'select2').callsFake(function(sel) {
                    return $(sel);
                })
            };
            _rowObj = {
                remove: sinon.stub(),
                data: sinon.stub(),
                find: sinon.stub().returns(_select2Obj)
            };
            addRowStub = sinon.stub(view, 'addRow').returns(_rowObj);
            initRowStub = sinon.stub(view, 'initRow');

            view.formRowTemplate = function() {
                return '<div>';
            };
        });

        afterEach(function() {
            _select2Obj = null;
            _rowObj = null;
        });

        it('should not populate the row if the field does not exist in the metadata', function() {

            view.populateRow({
                first_name: 'FirstName'
            });

            expect(initRowStub).not.toHaveBeenCalled();
            expect(_select2Obj.select2).not.toHaveBeenCalled();
        });

        it('should not populate the row if the field does not exist in the `fieldList`', function() {
            view.populateRow({
                case_number: '123456'
            });
            expect(initRowStub).not.toHaveBeenCalled();
            expect(_select2Obj.select2).not.toHaveBeenCalled();
        });

        describe('rows for currency fields', function() {
            beforeEach(function() {
                view.fieldList = {
                    name: {},
                    likely_case: {
                        name: 'likely_case',
                        type: 'currency'
                    }
                };
            });

            using('different filter definitions', [
                {
                    rowObj: {
                        $and: [
                            {
                                likely_case:
                                {
                                    $between: ['1000', '4000']
                                }
                            },
                            {
                                currency_id: 'aaa-bbb-ccc'
                            }
                        ]
                    },
                    expectedObj: {
                        name: 'likely_case',
                        operator: '$between',
                        value: {
                            likely_case: ['1000', '4000'],
                            currency_id: 'aaa-bbb-ccc'
                        }
                    }
                },
                {
                    rowObj: {
                        $and: [
                        {
                            likely_case:
                            {
                                $gte: '1000'
                            }
                        },
                        {
                            currency_id: 'aaa-bbb-ccc'
                        }
                    ]},
                    expectedObj: {
                        name: 'likely_case',
                        operator: '$gte',
                        value: {
                            likely_case: '1000',
                            currency_id: 'aaa-bbb-ccc'
                        }
                    }
                }
            ], function(data) {
                it('should call initRow with the right values to set in the fields', function() {
                    view.populateRow(data.rowObj);
                    expect(initRowStub).toHaveBeenCalledOnce();
                    expect(initRowStub.lastCall.args[1]).toEqual(data.expectedObj);
                });
            });
        });
    });

    describe('initRow', function() {
        var _rowObj, $row;

        beforeEach(function() {
            var _select2Obj = {
                select2: sinon.stub($.fn, 'select2').callsFake(function(sel) {
                    return $(sel);
                })
            };
            $row = $('<div data-filter="row">').appendTo(view.$el);
            _rowObj = {
                remove: sinon.stub(),
                data: sinon.stub(),
                find: sinon.stub().returns(_select2Obj)
            };
        });

        it('should set the field, the operator and the value from the filter object', function() {
            var initOperatorFieldSpy = sinon.spy(view, 'initOperatorField');
            var initValueFieldSpy = sinon.stub(view, 'initValueField');
            var field = {
                model: {
                    get: function() {
                        return '$in';
                    }
                }
            };
            $row.data('operatorField', field);
            view.filterOperatorMap['text'] = {'$equals': 'is'};
            view.fieldList = {
                primary_address_state: {
                    dbFields: ['primary_address_state', 'alt_address_state'],
                    type: 'text'
                }
            };
            view.initRow($row, {name: 'primary_address_state', operator: '$equals', value: '12'});

            expect(initOperatorFieldSpy).toHaveBeenCalled();
            expect(initValueFieldSpy).toHaveBeenCalled();
        });

        it('should initialize a row', function() {
            view.filterFields = ['test_field'];
            view.initRow($row);
            expect($row.data('nameField')).toBeDefined();
            expect($row.data('nameField').type).toEqual('enum');
            // FIXME (SC-2833): add empty select option to not set the first option as default in filter row
            expect($row.data('nameField').def.options).toEqual(['test_field']);
        });

        it('should store both the `id` and the `type` in the row data for flex relate fields', function() {
            sinon.stub(view, 'initOperatorField');
            view.fieldList = {
                parent: {
                    name: 'parent',
                    id_name: 'parent_id',
                    type: 'parent'
                }
            };
            view.filterOperatorMap['parent'] = {'$equals': 'is'};
            view.initRow($row,
                {name: 'parent', operator: '$equals', value: {parent_id: '12345', parent_type: 'Accounts'}});

            expect($row.data().value.parent_id).toEqual('12345');
            expect($row.data().value.parent_type).toEqual('Accounts');
        });
    });

    describe('handleFieldSelected', function() {
        var $row, $filterField, $operatorField;

        beforeEach(function() {
            view.fieldList = {
                test: {
                    type: 'enum'
                },
                $favorite: {
                    predefined_filter: true
                },
                name: {
                    type: 'name'
                }
            };
            view.filterOperatorMap['name'] = {'$in': 'is'};

            $row = $('<div data-filter="row">').appendTo(view.$el);
            $filterField = $('<div data-filter="field">').val('test').appendTo($row);
            $operatorField = $('<div data-filter="operator">').appendTo($row);
        });

        it('should dispose previous operator and value fields and initialize operator value', function() {
            var initOperatorFieldSpy = sinon.stub(view, 'initOperatorField');
            var disposeStub = sinon.stub(view, '_disposeRowFields');
            view.handleFieldSelected({currentTarget: $filterField});
            expect(disposeStub).toHaveBeenCalled();
            expect(disposeStub.lastCall.args[1]).toEqual([
                {field: 'operatorField', value: 'operator'},
                {field: 'valueField', value: 'value'}
            ]);
            expect(initOperatorFieldSpy).toHaveBeenCalled();
        });
    });

    describe('initOperatorField', function() {
        var $row, $filterField, $operatorField, model, field;

        beforeEach(function() {
            view.fieldList = {
                test: {
                    type: 'enum'
                },
                $favorite: {
                    predefined_filter: true
                },
                name: {
                    type: 'name'
                }
            };
            view.filterOperatorMap['name'] = {'$in': 'is'};

            $row = $('<div data-filter="row">').appendTo(view.$el);
            $filterField = $('<div data-filter="field">').val('test').appendTo($row);
            $operatorField = $('<div data-filter="operator">').appendTo($row);
            model = app.data.createBean('Accounts', {filter_row_name: 'name'});
            field = view.createField(model, {
                name: 'filter_row_name',
                type: 'enum',
                options: this.filterFields
            });
            $row.data('nameField', field);
        });

        afterEach(function() {
            field.dispose();
            model = null;
        });

        it('should create an enum field for operators', function() {
            var createFieldSpy = sinon.spy(view, 'createField');

            view.initOperatorField($row);

            expect(createFieldSpy).toHaveBeenCalled();
            expect(createFieldSpy.lastCall.args[1]).toEqual({
                name: 'filter_row_operator',
                type: 'enum',
                options: {
                    '$in': 'is'
                },
                searchBarThreshold: 9999
            });
            expect(_.isEmpty($operatorField.html())).toBeFalsy();
        });

        it('should set data attributes', function() {
            view.initOperatorField($row);
            expect($row.data('name')).toBeDefined();
            expect($row.data('operatorField')).toBeDefined();
        });

        it('should not create an operator field for predefined filters', function() {
            var createFieldSpy = sinon.spy(view, 'createField');
            var applyFilterStub = sinon.stub(view, 'fireSearch');
            view.fieldList['name'].predefined_filter = true;

            view.initOperatorField($row);
            expect(createFieldSpy).not.toHaveBeenCalled();
            expect(_.isEmpty($operatorField.html())).toBeTruthy();
            expect(applyFilterStub).toHaveBeenCalled();
            expect($row.data('isPredefinedFilter')).toBeTruthy();
        });

        it('should hide flex-relate operator', function() {
            sinon.stub(view, 'initValueField');
            model = app.data.createBean('Accounts', {filter_row_name: 'relatedTo'});
            field = view.createField(model, {
                name: 'filter_row_name',
                type: 'enum',
                options: this.filterFields
            });
            $row.data('nameField', field);
            var $valueField = $('<div data-filter="value">').appendTo($row);
            view.fieldList['relatedTo'] = {type : 'parent'};
            view.filterOperatorMap['parent'] = {'$equals': 'is'};

            view.initOperatorField($row);

            expect($operatorField.hasClass('hide')).toBeTruthy();
            expect($valueField.hasClass('span8')).toBeTruthy();
        });
    });

    describe('isCollectiveValue', function() {
        var $row;
        var $operatorField;

        beforeEach(function() {
            $row = $('<div data-filter="row">');
            $operatorField = $('<div data-filter="operator">').val('$in').appendTo($row);
        });

        using('different operators', [
            {
                operator: '$in',
                returnValue: true,
            },
            {
                operator: '$not_in',
                returnValue: true,
            },
            {
                operator: '$equals',
                returnValue: false,
            },
            {
                operator: '$notEquals',
                returnValue: false,
            },
            ],
            function(params) {
                it('should identify collective values', function() {
                    $operatorField = $('<div data-filter="operator">').val(params.operator).appendTo($row);
                    $row.data('operator', params.operator);
                    expect(view.isCollectiveValue($row)).toEqual(params.returnValue);
                });
            });
    });

    describe('initValueField', function() {
        var $row, $filterField, $operatorField, $valueField, createFieldSpy, field;

        beforeEach(function() {
            view.fieldList = {
                case_number: {
                    type: 'int'
                },
                status: {
                    type: 'enum',
                    options: 'status_dom'
                },
                priority: {
                    type: 'bool',
                    options: 'boolean_dom'
                },
                test_bool_field: {
                    type: 'bool'
                },
                date_created: {
                    type: 'datetime'
                },
                team_name: {
                    type: 'teamset',
                    id_name: 'team_id'
                },
                flex_relate: {
                    type: 'parent',
                    id_name: 'parent_id',
                    type_name: 'parent_type'
                },
                account_type: {
                    type: 'enum'
                },
                relate_field: {
                    type: 'relate',
                    rname: 'relate_rname',
                },
            };
            view.moduleName = 'Cases';
            $row = $('<div data-filter="row">').appendTo(view.$el);
            $filterField = $('<input type="hidden">');
            $('<div data-filter="field">').html($filterField).appendTo($row);
            $operatorField = $('<div data-filter="operator">').val('$in').appendTo($row);
            $valueField = $('<div data-filter="value">').appendTo($row);

            createFieldSpy = sinon.spy(view, 'createField');
            field = {
                model: {
                    get: function() {
                        return '$in';
                    }
                }
            };
            $row.data('operatorField', field);
        });

        afterEach(function() {
            field = null;
        });

        it('should make enum fields multi selectable', function() {
            sinon.stub($.fn, 'select2').returns('status'); //return `status` as field
            view.initValueField($row);
            expect(createFieldSpy).toHaveBeenCalled();
            expect(createFieldSpy.lastCall.args[1]).toEqual({
                name: 'status',
                type: 'enum',
                options: 'status_dom',
                isMultiSelect: true,
                searchBarThreshold: -1,
                required: false,
                readonly: false
            });
            expect(_.isEmpty($valueField.html())).toBeFalsy();
            expect($row.data('valueField').action).toEqual('detail');
        });

        it('should convert a boolean field into an enum field', function() {
            sinon.stub($.fn, 'select2').returns('priority'); //return `priority` as field
            view.initValueField($row);
            expect(createFieldSpy).toHaveBeenCalled();
            expect(createFieldSpy.lastCall.args[1]).toEqual({
                name: 'priority',
                type: 'enum',
                options: 'boolean_dom',
                required: false,
                readonly: false
            });
            expect(_.isEmpty($valueField.html())).toBeFalsy();
        });

        it('should use filter_checkbox_dom by default for bools', function() {
            // Note that `test_bool_field` is not an actual field of Cases
            // module, so does not have vardefs. It would be cleaner to test
            // only fields that exist or to patch the module metadata and add
            // `test_bool_field` as a valid field.
            sinon.stub($.fn, 'select2').returns('test_bool_field'); //return `test_bool_field` as field
            view.initValueField($row);
            expect(createFieldSpy).toHaveBeenCalled();
            expect(createFieldSpy.lastCall.args[1]).toEqual({
                name: 'test_bool_field',
                type: 'enum',
                options: 'filter_checkbox_dom',
                required: false,
                readonly: false
            });
            expect(_.isEmpty($valueField.html())).toBeFalsy();
        });

        it('should set auto_increment to false for an integer field', function() {
            field = {
                model: {
                    get: function() {
                        return '$equals';
                    }
                }
            };
            $row.data('operatorField', field);
            sinon.stub($.fn, 'select2').returns('case_number'); //return `case_number` as field
            view.initValueField($row);
            expect(createFieldSpy).toHaveBeenCalled();
            expect(createFieldSpy.lastCall.args[1]).toEqual({
                name: 'case_number',
                type: 'int',
                auto_increment: false,
                required: false,
                readonly: false
            });
            expect(_.isEmpty($valueField.html())).toBeFalsy();
        });

        it('should convert to varchar and join values for an integer field when operator is $in', function() {
            $operatorField.val('$in');
            $row.data('value', [1, 20, 35]);
            sinon.stub($.fn, 'select2').returns('case_number'); //return `case_number` as field
            view.initValueField($row);
            expect(createFieldSpy).toHaveBeenCalled();
            expect(createFieldSpy.lastCall.args[1]).toEqual({
                name: 'case_number',
                type: 'varchar',
                auto_increment: false,
                len: 200,
                required: false,
                readonly: false
            });
            expect(_.isEmpty($valueField.html())).toBeFalsy();
            expect($row.data('value')).toEqual('1,20,35');
        });

        it('should create two inputs if the operator is in between', function() {
            field = {
                model: {
                    get: function() {
                        return '$between';
                    }
                }
            };
            $row.data('operatorField', field);
            sinon.stub($.fn, 'select2').returns('case_number'); //return `case_number` as field
            view.initValueField($row);
            expect(createFieldSpy).toHaveBeenCalledTwice();
            expect(createFieldSpy.firstCall.args[1]).toEqual({
                type: 'int',
                name: 'case_number_min',
                auto_increment: false,
                required: false,
                readonly: false
            });
            expect(createFieldSpy.lastCall.args[1]).toEqual({
                type: 'int',
                name: 'case_number_max',
                auto_increment: false,
                required: false,
                readonly: false
            });
            expect(_.isEmpty($valueField.html())).toBeFalsy();
            expect(_.size($valueField.find('input'))).toEqual(2);
            _.each($row.data('valueField'), function(data) {
                expect(data.action).toEqual('detail');
            });
        });

        describe('teamset, relate, and flex-relate fields', function() {
            var fetchStub, model, field;

            beforeEach(function() {
                //return `team_name` as field
                fetchStub = sinon.stub(Backbone.Collection.prototype, 'fetch');

                model = app.data.createBean('Accounts', {filter_row_operator: '$equals'});
                field = view.createField(model, {
                    name: 'filter_row_operator',
                    type: 'enum'
                });
                $row.data('operatorField', field);
            });

            afterEach(function() {
                field.dispose();
                model = null;
            });

            it('should convert teamset field to a relate field and fetch name like other relate fields', function() {
                sinon.stub($.fn, 'select2').returns('team_name');
                $row.data('value', 'West');
                view.initValueField($row);
                expect(createFieldSpy).toHaveBeenCalled();
                expect(createFieldSpy.lastCall.args[1]).toEqual({
                    name: 'team_name',
                    type: 'relate',
                    id_name: 'team_id',
                    required: false,
                    readonly: false,
                    isMultiSelect: false
                });
                expect(_.isEmpty($valueField.html())).toBeFalsy();
                expect(fetchStub).toHaveBeenCalled();
            });

            it('should convert teamset field to a relate field but not fetch because no value set', function() {
                sinon.stub($.fn, 'select2').returns('team_name');
                view.initValueField($row);
                expect(createFieldSpy).toHaveBeenCalled();
                expect(createFieldSpy.lastCall.args[1]).toEqual({
                    name: 'team_name',
                    type: 'relate',
                    id_name: 'team_id',
                    required: false,
                    readonly: false,
                    isMultiSelect: false
                });
                expect(_.isEmpty($valueField.html())).toBeFalsy();
                expect(fetchStub).not.toHaveBeenCalled();
            });

            it('should use the `parent_type` module when fetching name according to `parent_id`', function() {
                sinon.stub($.fn, 'select2').returns('flex_relate');
                sinon.spy(app.data, 'createBeanCollection');
                $row.data('value', {parent_type: 'My_Module', parent_id: '12345'});
                view.initValueField($row);

                expect(createFieldSpy).toHaveBeenCalled();
                expect(createFieldSpy.lastCall.args[1]).toEqual({
                    name: 'flex_relate',
                    type: 'parent',
                    id_name: 'parent_id',
                    type_name: 'parent_type',
                    required: false,
                    readonly: false
                });
                expect(app.data.createBeanCollection).toHaveBeenCalledWith('My_Module');
                expect(fetchStub).toHaveBeenCalled();
            });

            it('should not fetch when `parent_id` is not selected', function() {
                sinon.stub($.fn, 'select2').returns('flex_relate');
                $row.data('value', {parent_type: 'My_Module', 'parent_id': ''});
                view.initValueField($row);
                expect(fetchStub).not.toHaveBeenCalled();
            });

            it('should fetch relate fields with $equals if necessary', function() {
                sinon.stub($.fn, 'select2').returns('relate_field');
                $row.data('value', 'West');
                view.initValueField($row);
                var relateFilter = [{id: {$equals: 'West'}}];
                expect(fetchStub).toHaveBeenCalledWith(jasmine.objectContaining({
                    fields: ['relate_rname'],
                    params: {filter: relateFilter},
                }));
            });
        });

        describe('date type fields', function() {
            it('should create a value field when the operator is not a date range', function() {
                field = {
                    model: {
                        get: function() {
                            return 'next_30_days';
                        }
                    }
                };
                $row.data('operatorField', field);
                var rowData = $row.data();
                sinon.stub($.fn, 'select2').returns('date_created'); //return `date_created` as field
                var buildFilterDefStub = sinon.stub(view, 'buildFilterDef');

                // Set a date range
                rowData.value = 'next_30_days';

                view.initValueField($row);

                expect(rowData.operator).toEqual('next_30_days');
                expect(rowData.value).toEqual('next_30_days');
                expect(rowData.valueField).toBeUndefined();
                expect(createFieldSpy).not.toHaveBeenCalled();
                expect(_.isEmpty($valueField.html())).toBeTruthy();
                expect(buildFilterDefStub).toHaveBeenCalled();
                buildFilterDefStub.resetHistory();

                // Change the operator
                field = {
                    model: {
                        get: function() {
                            return '$equals';
                        }
                    }
                };
                $row.data('operatorField', field);
                rowData.value = '';
                view.initValueField($row);
                expect(rowData.operator).toEqual('$equals');
                expect(rowData.value).toEqual('');
                expect(createFieldSpy).toHaveBeenCalled();
                expect(rowData.valueField).toBeDefined();
                expect(_.isEmpty($valueField.html())).toBeFalsy();
            });
        });

        it('should set data attributes', function() {
            sinon.stub($.fn, 'select2').returns('case_number'); //return `case_number` as field
            view.initValueField($row);
            expect($row.data('operator')).toBeDefined();
            expect($row.data('valueField')).toBeDefined();
        });

        it('should trigger filter:apply when value change', function() {
            sinon.stub($.fn, 'select2').returns('case_number'); //return `case_number` as field
            var triggerStub = sinon.stub(view.layout, 'trigger');
            sinon.stub(app.view.Field.prototype, 'render');
            view.initValueField($row);
            view.lastFilterDef = undefined;
            $row.data('valueField').model.set('status', 'firesModelChangeEvent');
            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalledWith('filter:apply');
        });

        it('should trigger filter:apply when keyup', function() {
            sinon.stub($.fn, 'select2').returns('case_number'); //return `case_number` as field
            $filterField.val('case_number');
            $operatorField.val('$in');
            view.initValueField($row);
            $row.data('valueField').model.set('case_number', 200);
            var triggerStub = sinon.stub(view.layout, 'trigger');
            view.lastFilterDef = undefined;
            $operatorField.closest('[data-filter="row"]').find('[data-filter=value] input').trigger('keyup');
            expect(triggerStub).toHaveBeenCalled();
            expect(triggerStub).toHaveBeenCalledWith('filter:apply');
        });

        using('different operators', [{
            operator: '$empty',
            hasValueField: false
        },{
            operator: '$not_empty',
            hasValueField: false
        },{
            operator: '$equals',
            hasValueField: true
        }], function(params) {
            it('should initialize value field properly corresponding to the operator', function() {
                sinon.stub($.fn, 'select2').returns('account_type'); //returns an 'enum' field
                sinon.stub(view, '_renderField');
                var model = app.data.createBean('Accounts', {
                    filter_row_operator: params.operator
                });
                $operatorField = view.createField(model, {
                    name: 'filter_row_operator',
                    type: 'enum',
                    options: {}
                });
                $row = $('<div>').data({
                    name: 'account_type',
                    operator: params.operator,
                    operatorField: $operatorField,
                    value: ''
                });

                view.initValueField($row);

                var data = $row.data();
                expect(_.isUndefined((data['valueField']))).not.toEqual(params.hasValueField);
            });
        });
    });

    describe('handleOperatorSelected', function() {
        var $row, $filterField, $operatorField, $valueField;

        beforeEach(function() {
            view.fieldList = {
                case_number: {
                    type: 'int'
                },
                status: {
                    type: 'enum',
                    options: 'status_dom'
                },
                priority: {
                    type: 'bool',
                    options: 'boolean_dom'
                },
                test_bool_field: {
                    type: 'bool'
                },
                date_created: {
                    type: 'datetime'
                },
                team_name: {
                    type: 'teamset',
                    id_name: 'team_id'
                },
                flex_relate: {
                    type: 'parent',
                    id_name: 'parent_id',
                    type_name: 'parent_type'
                }
            };
            view.moduleName = 'Cases';
            $row = $('<div data-filter="row">').appendTo(view.$el);
            $filterField = $('<input type="hidden">');
            $('<div data-filter="field">').html($filterField).appendTo($row);
            $operatorField = $('<div data-filter="operator">').val('$in').appendTo($row);
            $valueField = $('<div data-filter="value">').appendTo($row);
        });

        it('should dispose previous value field', function() {
            var field = {
                model: {
                    get: function() {
                        return '$lte';
                    }
                }
            };
            $row.data('operatorField', field);

            var rowData = $row.data();
            sinon.stub($.fn, 'select2').returns('case_number'); //return `case_number` as field
            // Set a row
            rowData.value = '50';
            view.handleOperatorSelected({currentTarget: $operatorField});
            rowData = $row.data();
            expect(rowData.operator).toEqual('$lte');
            expect(rowData.valueField).toBeDefined();
            var disposeSpy = sinon.spy(rowData.valueField, 'dispose');
            expect(rowData.value).toEqual('50');

            // Change the operator
            var field = {
                model: {
                    get: function() {
                        return '$gte';
                    }
                }
            };
            $row.data('operatorField', field);
            view.handleOperatorSelected({currentTarget: $operatorField});
            expect(disposeSpy).toHaveBeenCalled();
            rowData = $row.data();
            expect(rowData.operator).toEqual('$gte');
            expect(rowData.valueField).toBeDefined();
            expect(rowData.value).toEqual('');
        });
    });

    describe('buildRowFilterDef', function() {
        var $row, filter, expected;

        beforeEach(function() {
            view.fieldList = {
                case_number: {
                    name: 'case_number',
                    type: 'int'
                },
                description: {
                    name: 'description',
                    type: 'text'
                },
                address_street: {
                    name: 'address_street',
                    dbFields: ['primary_address_street', 'alt_address_street'],
                    type: 'text'
                },
                assigned_user_name: {
                    name: 'assigned_user_name',
                    id_name: 'assigned_user_id',
                    type: 'relate'
                },
                date_created: {
                    name: 'date_created',
                    type: 'datetimecombo'
                },
                flex_relate: {
                    name: 'relatedTo',
                    type: 'parent',
                    id_name: 'parent_id',
                    type_name: 'parent_type'
                }
            };
        });

        afterEach(function() {
            filter = expected = null;
        });

        it('should build a simple filter definition', function() {
            $row = $('<div>').data({
                name: 'description',
                operator: '$starts',
                value: 'abc'
            });
            filter = view.buildRowFilterDef($row, true);
            expected = {
                description: {
                    '$starts': 'abc'
                }
            };
            expect(filter).toEqual(expected);
        });

        it('should build a complex filter definition', function() {
            $row = $('<div>').data({
                name: 'address_street',
                operator: '$starts',
                value: 'abc'
            });
            filter = view.buildRowFilterDef($row, true);
            expected = {
                '$or': [
                    {
                        primary_address_street: {
                            '$starts': 'abc'
                        }
                    },
                    {
                        alt_address_street: {
                            '$starts': 'abc'
                        }
                    }

                ]
            };
            expect(filter).toEqual(expected);
        });

        it('should build empty filter definition if the displaying column is invalid', function() {
            $row = $('<div>').data({
                name: 'address_street'
            });
            filter = view.buildRowFilterDef($row, true);
            expect(filter).toBeUndefined();

            var validate = view.validateRow($row);
            expect(validate).toBe(false);
        });

        describe('build an ad-hoc filter definition', function() {
            it('should have empty operator and value', function() {
                $row = $('<div>').data({
                    name: 'address_street'
                });
                var validate = view.validateRow($row);
                expect(validate).toBe(false);

                filter = view.buildRowFilterDef($row);
                //build ad-hoc filter
                expected = {
                    '$or': [
                        {
                            primary_address_street: {
                                'undefined': ''
                            }
                        },
                        {
                            alt_address_street: {
                                'undefined': ''
                            }
                        }

                    ]
                };
                expect(filter).toEqual(expected);
            });

            it('should have empty value when value is not unassigned', function() {
                $row = $('<div>').data({
                    name: 'address_street',
                    operator: '$starts'
                });
                var validate = view.validateRow($row);
                expect(validate).toBe(false);

                filter = view.buildRowFilterDef($row);
                //build ad-hoc filter
                expected = {
                    '$or': [
                        {
                            primary_address_street: {
                                '$starts': ''
                            }
                        },
                        {
                            alt_address_street: {
                                '$starts': ''
                            }
                        }

                    ]
                };
                expect(filter).toEqual(expected);
            });

            it('should return an empty array if operator is $in and value is an empty string', function() {
                $row = $('<div>').data({
                    name: 'case_number',
                    operator: '$in',
                    value: ''
                });
                filter = view.buildRowFilterDef($row);
                expected = {
                    case_number: {
                        '$in': []
                    }
                };
                expect(filter).toEqual(expected);
            });
        });

        it('should split values if operator is $in and value is a string', function() {
            $row = $('<div>').data({
                name: 'case_number',
                operator: '$in',
                value: '1,20,35'
            });
            filter = view.buildRowFilterDef($row, true);
            expected = {
                case_number: {
                    '$in': ['1','20','35']
                }
            };
            expect(filter).toEqual(expected);
        });

        it('should make an exception for predefined filters', function() {
            $row = $('<div>').data({
                name: '$favorite',
                isPredefinedFilter: true
            });
            filter = view.buildRowFilterDef($row, true);
            expected = {
                $favorite: ''
            };
            expect(filter).toEqual(expected);
        });

        it('should pick id_name for relate fields', function() {
            var filterModel = new Backbone.Model();
            filterModel.set("assigned_user_id", "seed_sarah_id");
            var fieldMock = {model: filterModel};
            $row = $('<div>').data({
                name: 'assigned_user_name',
                id_name: 'assigned_user_id',
                operator: '$equals',
                valueField: fieldMock
            });
            view._updateFilterData($row);
            filter = view.buildRowFilterDef($row, true);
            expected = {
                assigned_user_id: {$equals: 'seed_sarah_id'}
            };
            expect(filter).toEqual(expected);
        });

        describe('currency fields', function() {
            var bean, row;
            beforeEach(function() {
                bean = SUGAR.App.data.createBean(
                    'RevenueLineItems',
                    {
                        currency_id: '-99'
                    });
            });

            using('valid values', [
                {
                    operator: '$gte',
                    amount: '111',
                    expected: {
                        $and: [
                            {
                                likely_case:
                                {
                                    $gte: '111'
                                }
                            },
                            {
                                currency_id: '-99'
                            }
                        ]
                    }
                },
                {
                    operator: '$between',
                    amount: ['1000', '4000'],
                    expected: {
                        $and: [
                            {
                                likely_case:
                                {
                                    $between: ['1000', '4000']
                                }
                            },
                            {
                                currency_id: '-99'
                            }
                        ]
                    }
                }
            ], function(data) {
                it('should use `$and` for currency fields properly', function() {
                    bean.set('likely_case', data.amount);
                    row = $('<div>').data({
                        name: 'likely_case',
                        operator: data.operator,
                        valueField: {
                            model: bean,
                            type: 'currency',
                            getCurrencyField: function() {
                                return {
                                    'name': 'currency_id'
                                };
                            }
                        }
                    });
                    sinon.stub(view, 'validateRow').returns(true);
                    view._updateFilterData(row);
                    var filter = view.buildRowFilterDef(row, true);
                    expect(filter).toEqual(data.expected);
                });
            });
        });

        it('should use $and for flex-relate fields', function() {
            var filterModel = new Backbone.Model();
            filterModel.set({parent_id: '12345', parent_type: 'My_Module'});
            var fieldMock = {model: filterModel};
            $row = $('<div>').data({
                name: 'relatedTo',
                id_name: 'parent_id',
                type_name: 'parent_type',
                operator: '$equals',
                isFlexRelate: true,
                valueField: fieldMock
            });
            sinon.stub(view, 'validateRow').returns(true);

            view._updateFilterData($row);
            filter = view.buildRowFilterDef($row, true);
            expected = {
                $and: [
                    {parent_id: '12345'},
                    {parent_type: 'My_Module'}
                ]
            };
            expect(filter).toEqual(expected);
        });

        it('should format date range filter', function() {
            SugarTest.loadComponent('base', 'field', 'date');
            SugarTest.loadComponent('base', 'field', 'datetimecombo');
            $row = $('<div>').data({
                isDate: true,
                isDateRange: true,
                name: 'date_created'
            });
            $row.data({
                operator: 'last_year'
            });
            filter = view.buildRowFilterDef($row, true);
            expected = { date_created: { $dateRange: 'last_year' } };
            expect(filter).toEqual(expected);
        });

        it('should delegate to the field when it knows how to compile its own value into a filter def', function() {
            var field = {
                model: new Backbone.Model(),
                delegateBuildFilterDefinition: sinon.stub().returns('def')
            };

            $row = $('<div>').data({
                name: 'description',
                operator: '$starts',
                value: 'abc',
                valueField: field
            });
            filter = view.buildRowFilterDef($row);

            expect(field.delegateBuildFilterDefinition).toHaveBeenCalled();
            expect(filter).toEqual({
                description: {
                    '$starts': 'def'
                }
            });
        });
    });

    describe('saveFilterEditState', function() {
        var component,
            buildFilterDefStub, saveFilterEditStateStub;

        beforeEach(function() {
            component = {
                '$': function(sel) { return [sel]; },
                getFilterName: $.noop,
                saveFilterEditState: $.noop
            };
            view.layout.getComponent = function() { return component; };
            buildFilterDefStub = sinon.stub(view, 'buildFilterDef').returns(
                [{'$favorites': ''}]
            );
            sinon.stub(component, 'getFilterName').returns('AwesomeName');
            saveFilterEditStateStub = sinon.stub(component, 'saveFilterEditState');
        });

        afterEach(function() {
            component = null;
        });

        it('should build filter def when no param', function() {
            view.saveFilterEditState();
            expect(buildFilterDefStub).toHaveBeenCalled();
            expect(saveFilterEditStateStub).toHaveBeenCalled();
            var expectedFilter = {
                filter_definition: [
                    {'$favorites': ''}
                ],
                filter_template: [
                    {'$favorites': ''}
                ],
                name: 'AwesomeName'
            };
            expect(saveFilterEditStateStub).toHaveBeenCalledWith(expectedFilter);
        });

        it('should get the filter def passed in params', function() {
            view.saveFilterEditState([{my_filter: {is: 'cool'}}], [{my_filter: {is: 'cool'}}]);
            expect(buildFilterDefStub).not.toHaveBeenCalled();
            expect(saveFilterEditStateStub).toHaveBeenCalled();
            var expectedFilter = {
                filter_definition: [
                    {my_filter: {is: 'cool'}}
                ],
                filter_template: [
                    {my_filter: {is: 'cool'}}
                ],
                name: 'AwesomeName'
            };
            expect(saveFilterEditStateStub).toHaveBeenCalledWith(expectedFilter);
        });
    });

    describe('resetFilterValues', function() {
        it('should call clear on value field models so all value fields are cleared', function() {
            var model1 = new Backbone.Model();
            var model2 = new Backbone.Model();
            var stubs = [sinon.stub(model1, 'clear'), sinon.stub(model2, 'clear')];
            $('<div data-filter="row">').data('valueField', {model: model1 }).appendTo(view.$el);
            $('<div data-filter="row">').data('valueField', {model: model2 }).appendTo(view.$el);
            view.resetFilterValues();
            expect(stubs[0]).toHaveBeenCalled();
            expect(stubs[1]).toHaveBeenCalled();
        });

        it('should call clear on each field model if valueField is an array', function() {
            var model1 = new Backbone.Model();
            var model2 = new Backbone.Model();
            var stubs = [sinon.stub(model1, 'clear'), sinon.stub(model2, 'clear')];
            $('<div data-filter="row">')
                .data('valueField', [{model: model1 }, {model: model2 }])
                .appendTo(view.$el);
            view.resetFilterValues();
            expect(stubs[0]).toHaveBeenCalled();
            expect(stubs[1]).toHaveBeenCalled();
        });
    });
});
