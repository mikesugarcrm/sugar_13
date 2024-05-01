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
describe('ConsoleConfiguration.Fields.filter-field', function() {
    var app;
    var field;
    var fieldName = 'test_filter_field';
    var model;
    var module = 'ConsoleConfiguration';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.testMetadata.set();

        model = app.data.createBean(module);
        model.set({
            enabled_module: 'Cases',
            order_by_primary: 'follow_up_datetime',
            order_by_secondary: '',
            filter_def: [
                {
                    $owner: ''
                }
            ]
        });

        field = SugarTest.createField(
            'base',
            fieldName,
            'filter-field',
            'edit',
            {},
            module,
            model,
            null,
            true
        );

    });

    afterEach(function() {
        sinon.restore();
        model = null;
        field = null;
    });

    describe('initialize', function() {
        beforeEach(function() {
            sinon.stub(field, '_super');
            sinon.stub(field, 'loadFilterFields');
            sinon.stub(field, 'loadFilterOperators');
            sinon.stub(app.template, 'getField');
        });

        it('should load the field and operator lists for the correct module', function() {
            field.initialize();
            expect(field.loadFilterFields).toHaveBeenCalledWith('Cases');
            expect(field.loadFilterOperators).toHaveBeenCalledWith('Cases');
        });
    });

    describe('loadFilterFields', function() {
        var module = 'Accounts';

        beforeEach(function() {
            model.set('filterableFields', {
                'next_renewal_date': {
                    'name': 'next_renewal_date',
                    'type': 'date',
                    'label': 'LBL_NEXT_RENEWAL_DATE'
                }
            });

            field.fieldList = {
                $owner: {
                    'predefined_filter': true,
                    'label': 'LBL_CURRENT_USER_FILTER'
                },
                $favorite: {
                    'predefined_filter': true,
                    'label': 'LBL_FAVORITES_FILTER'
                }
            };

            field.filterOperatorMap = {
                'date': {
                    '$in': 'In'
                }
            };
        });

        it('should load filter fields properly', function() {
            field.loadFilterFields(module);
            expect(field.filterFields).toEqual({
                '$owner': 'LBL_CURRENT_USER_FILTER',
                '$favorite': 'LBL_FAVORITES_FILTER',
                'next_renewal_date': 'LBL_NEXT_RENEWAL_DATE'
            });
        });

    });

    describe('loadFilterOperators', function() {
        var module = 'Cases';

        it('should load filter operators properly', function() {
            var operatorMap = {
                enum: {
                    $empty: 'LBL_OPERATOR_EMPTY',
                    $in: 'LBL_OPERATOR_CONTAINS',
                    $not_empty: 'LBL_OPERATOR_NOT_EMPTY',
                    $not_in: 'LBL_OPERATOR_NOT_CONTAINS'
                }
            };

            sinon.stub(app.metadata, 'getFilterOperators')
                .withArgs(module).returns(operatorMap);

            field.loadFilterOperators(module);

            expect(app.metadata.getFilterOperators).toHaveBeenCalledOnce();
            expect(app.metadata.getFilterOperators).toHaveBeenCalledWith(module);

            expect(field.filterOperatorMap).toEqual(operatorMap);
        });
    });

    describe('_render', function() {
        beforeEach(function() {
            sinon.stub(field, '_super');
            sinon.stub(field, 'populateFilter');
            sinon.stub(field, 'addRow');
        });

        it('should populate the rows of the filter from the filter definition', function() {
            field._render();
            expect(field.populateFilter).toHaveBeenCalledWith([
                {
                    $owner: ''
                }
            ]);
        });

        it('should add a row if the filter definition is empty', function() {
            model.set('filter_def', []);
            field._render();
            expect(field.addRow).toHaveBeenCalled();
        });
    });

    describe('populateFilter', function() {
        it('should populate rows', function() {
            sinon.stub(app.data, 'getBeanClass').returns({
                prototype: {
                    populateFilterDefinition: function() {
                        return [
                            {
                                first_name: 'FirstName'
                            },
                            {
                                last_name: {
                                    '$starts': 'LastName'
                                }
                            }
                        ];
                    }
                }
            });

            var populateRowStub = sinon.stub(field, 'populateRow');
            field.populateFilter();
            expect(populateRowStub.secondCall).toBeDefined();
        });
    });

    describe('populateRow', function() {
        var addRowStub;
        var initRowStub;
        var _select2Obj;
        var _rowObj;

        beforeEach(function() {
            field.fieldList = {
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
            addRowStub = sinon.stub(field, 'addRow').returns(_rowObj);
            initRowStub = sinon.stub(field, 'initRow');

            field.formRowTemplate = function() {
                return '<div>';
            };
        });

        afterEach(function() {
            _select2Obj = null;
            _rowObj = null;
        });

        it('should not populate the row if the field does not exist in the metadata', function() {
            field.populateRow({
                first_name: 'FirstName'
            });

            expect(initRowStub).not.toHaveBeenCalled();
            expect(_select2Obj.select2).not.toHaveBeenCalled();
        });

        it('should not populate the row if the field does not exist in the `fieldList`', function() {
            field.populateRow({
                case_number: '123456'
            });
            expect(initRowStub).not.toHaveBeenCalled();
            expect(_select2Obj.select2).not.toHaveBeenCalled();
        });

        describe('rows for currency fields', function() {
            beforeEach(function() {
                field.fieldList = {
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
                    field.populateRow(data.rowObj);
                    expect(initRowStub).toHaveBeenCalledOnce();
                    expect(initRowStub.lastCall.args[1]).toEqual(data.expectedObj);
                });
            });
        });
    });

    describe('addRow', function() {
        it('should add a filter row to the field', function() {
            sinon.stub(field, 'initRow');
            field.addRow();
            expect(field.initRow).toHaveBeenCalled();
        });
    });

    describe('removeRow', function() {
        var $event;

        beforeEach(function() {
            $event = $('<div>');
            sinon.stub(field, 'addRow').callsFake(function() {
                var $row = $('<div data-filter="row">').appendTo(field.$el);
                return $row;
            });
            $('<div data-filter="row">').appendTo(field.$el);
            $('<div data-filter="row">').appendTo(field.$el);
            $('<div data-filter="row">').appendTo(field.$el);
        });

        it('should remove the row from the view', function() {
            $event.appendTo(field.$('[data-filter=row]:last'));
            field.removeRow({currentTarget: $event});
            expect(_.size(field.$('[data-filter=row]'))).toEqual(2);

            $event.appendTo(field.$('[data-filter=row]:last'));
            field.removeRow({currentTarget: $event});
            expect(_.size(field.$('[data-filter=row]'))).toEqual(1);

            //it should add another row when the form becomes empty
            $event.appendTo(field.$('[data-filter=row]:last'));
            field.removeRow({currentTarget: $event});
            expect(_.size(field.$('[data-filter=row]'))).toEqual(1);
        });
    });

    describe('initRow', function() {
        var _rowObj;
        var $row;

        beforeEach(function() {
            var _select2Obj = {
                select2: sinon.stub($.fn, 'select2').callsFake(function(sel) {
                    return $(sel);
                })
            };
            $row = $('<div data-filter="row">').appendTo(field.$el);
            _rowObj = {
                remove: sinon.stub(),
                data: sinon.stub(),
                find: sinon.stub().returns(_select2Obj)
            };

            sinon.stub(field, 'createField').returns({
                render: function() {},
                model: {
                    get: function() {
                        return 'follow_up_datetime';
                    }
                },
                type: 'enum',
                def: {
                    options: ['test_field']
                }
            });

        });

        it('should set the field from the filter object, then set the operator', function() {
            var initOperatorFieldSpy = sinon.stub(field, 'initOperatorField');
            var subfield = {
                model: {
                    get: function() {
                        return '$in';
                    }
                }
            };
            $row.data('operatorField', subfield);
            field.filterOperatorMap.text = {'$equals': 'is'};
            field.fieldList = {
                primary_address_state: {
                    dbFields: ['primary_address_state', 'alt_address_state'],
                    type: 'text'
                }
            };
            field.initRow($row, {name: 'primary_address_state', operator: '$equals', value: '12'});

            expect(initOperatorFieldSpy).toHaveBeenCalled();
        });

        it('should initialize a row', function() {
            field.filterFields = ['test_field'];
            field.initRow($row);
            expect($row.data('nameField')).toBeDefined();
            expect($row.data('nameField').type).toEqual('enum');
            expect($row.data('nameField').def.options).toEqual(['test_field']);
        });

        it('should store both the `id` and the `type` in the row data for flex relate fields', function() {
            sinon.stub(field, 'initOperatorField');
            field.fieldList = {
                parent: {
                    name: 'parent',
                    id_name: 'parent_id',
                    type: 'parent'
                }
            };
            field.filterOperatorMap.parent = {'$equals': 'is'};
            field.initRow($row,
                {name: 'parent', operator: '$equals', value: {parent_id: '12345', parent_type: 'Accounts'}});

            expect($row.data().value.parent_id).toEqual('12345');
            expect($row.data().value.parent_type).toEqual('Accounts');
        });
    });

    describe('initOperatorField', function() {
        var $row;
        var $filterField;
        var $operatorField;
        var model;
        var subfield;
        var createFieldSpy;

        beforeEach(function() {
            field.fieldList = {
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
            field.filterOperatorMap.name = {'$in': 'is'};

            $row = $('<div data-filter="row">').appendTo(field.$el);
            $filterField = $('<div data-filter="field">').val('test').appendTo($row);
            $operatorField = $('<div data-filter="operator">').appendTo($row);
            model = app.data.createBean('Accounts', {filter_row_name: 'name'});
            subfield = field.createField(model, {
                name: 'filter_row_name',
                type: 'enum',
                options: this.filterFields
            });
            $row.data('nameField', subfield);

            createFieldSpy = sinon.stub(field, 'createField').returns({
                render: function() {},
                model: {
                    get: function() {
                        return '$in';
                    }
                },
                type: 'enum',
                def: {
                    options: {
                        '$in': 'is'
                    }
                }
            });
        });

        afterEach(function() {
            subfield.dispose();
            model = null;
        });

        it('should create an enum field for operators', function() {
            field.initOperatorField($row);

            expect(createFieldSpy).toHaveBeenCalled();
            expect(createFieldSpy.lastCall.args[1]).toEqual({
                name: 'filter_row_operator',
                type: 'enum',
                options: {
                    '$in': 'is'
                },
                searchBarThreshold: 9999
            });
        });

        it('should set data attributes', function() {
            field.initOperatorField($row);
            expect($row.data('name')).toBeDefined();
            expect($row.data('operatorField')).toBeDefined();
        });

        it('should not create an operator field for predefined filters', function() {
            field.fieldList.name.predefined_filter = true;
            field.initOperatorField($row);
            expect(createFieldSpy).not.toHaveBeenCalled();
            expect($row.data('isPredefinedFilter')).toBeTruthy();
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
                    expect(field.isCollectiveValue($row)).toEqual(params.returnValue);
                });
            });
    });

    describe('createField', function() {
        it('should instantiate a field', function() {
            var def = {type: 'enum', options: {test: ''}};
            var subfield = field.createField(new Backbone.Model(), def);
            expect(subfield instanceof app.view.Field).toBeTruthy();
            expect(subfield.type).toEqual('enum');
            expect(subfield.def).toEqual(def);

            subfield.dispose();
        });
    });

    describe('handleFieldSelected', function() {
        var $row;
        var $filterField;
        var $operatorField;

        beforeEach(function() {
            field.fieldList = {
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
            field.filterOperatorMap.name = {'$in': 'is'};

            $row = $('<div data-filter="row">').appendTo(field.$el);
            $filterField = $('<div data-filter="field">').val('test').appendTo($row);
            $operatorField = $('<div data-filter="operator">').appendTo($row);
        });

        it('should dispose previous operator and value fields and initialize operator value', function() {
            var initOperatorFieldSpy = sinon.stub(field, 'initOperatorField');
            var disposeStub = sinon.stub(field, '_disposeRowFields');
            field.handleFieldSelected({currentTarget: $filterField});
            expect(disposeStub).toHaveBeenCalled();
            expect(disposeStub.lastCall.args[1]).toEqual([
                {field: 'operatorField', value: 'operator'},
                {field: 'valueField', value: 'value'}
            ]);
            expect(initOperatorFieldSpy).toHaveBeenCalled();
        });
    });

    describe('handleOperatorSelected', function() {
        var $row;
        var $filterField;
        var $operatorField;
        var $valueField;

        beforeEach(function() {
            field.fieldList = {
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
            field.moduleName = 'Cases';
            $row = $('<div data-filter="row">').appendTo(field.$el);
            $filterField = $('<input type="hidden">');
            $('<div data-filter="field">').html($filterField).appendTo($row);
            $operatorField = $('<div data-filter="operator">').val('$in').appendTo($row);
            $valueField = $('<div data-filter="value">').appendTo($row);

            sinon.stub(field, '_disposeRowFields');
            sinon.stub(field, 'initValueField');
            sinon.stub(field.model, 'set');
            sinon.stub(field, 'buildFilterDef').returns('built filter def');
        });

        it('should dispose the previous value field', function() {
            field.handleOperatorSelected({currentTarget: $operatorField});
            expect(field._disposeRowFields).toHaveBeenCalled();
        });

        it('should initialize the new value field', function() {
            field.handleOperatorSelected({currentTarget: $operatorField});
            expect(field.initValueField).toHaveBeenCalled();
        });

        it('should set the model filter def correctly', function() {
            field.handleOperatorSelected({currentTarget: $operatorField});
            expect(field.model.set).toHaveBeenCalledWith('filter_def', 'built filter def', {silent: true});
        });
    });

    describe('buildRowFilterDef', function() {
        var $row;
        var filter;
        var expected;

        beforeEach(function() {
            field.fieldList = {
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
            filter = field.buildRowFilterDef($row, true);
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
            filter = field.buildRowFilterDef($row, true);
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
            filter = field.buildRowFilterDef($row, true);
            expect(filter).toBeUndefined();

            var validate = field.validateRow($row);
            expect(validate).toBe(false);
        });

        describe('build an ad-hoc filter definition', function() {
            it('should have empty operator and value', function() {
                $row = $('<div>').data({
                    name: 'address_street'
                });
                var validate = field.validateRow($row);
                expect(validate).toBe(false);

                filter = field.buildRowFilterDef($row);
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
                var validate = field.validateRow($row);
                expect(validate).toBe(false);

                filter = field.buildRowFilterDef($row);
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
                filter = field.buildRowFilterDef($row);
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
            filter = field.buildRowFilterDef($row, true);
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
            filter = field.buildRowFilterDef($row, true);
            expected = {
                $favorite: ''
            };
            expect(filter).toEqual(expected);
        });

        it('should pick id_name for relate fields', function() {
            var filterModel = new Backbone.Model();
            filterModel.set('assigned_user_id', 'seed_sarah_id');
            var fieldMock = {model: filterModel};
            $row = $('<div>').data({
                name: 'assigned_user_name',
                id_name: 'assigned_user_id',
                operator: '$equals',
                valueField: fieldMock
            });
            field._updateFilterData($row);
            filter = field.buildRowFilterDef($row, true);
            expected = {
                assigned_user_id: {$equals: 'seed_sarah_id'}
            };
            expect(filter).toEqual(expected);
        });

        describe('currency fields', function() {
            var bean;
            var row;
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
                    sinon.stub(field, 'validateRow').returns(true);
                    field._updateFilterData(row);
                    var filter = field.buildRowFilterDef(row, true);
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
            sinon.stub(field, 'validateRow').returns(true);

            field._updateFilterData($row);
            filter = field.buildRowFilterDef($row, true);
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
            filter = field.buildRowFilterDef($row, true);
            expected = {date_created: {$dateRange: 'last_year'}};
            expect(filter).toEqual(expected);
        });

        it('should delegate to the field when it knows how to compile its own value into a filter def', function() {
            var subfield = {
                model: new Backbone.Model(),
                delegateBuildFilterDefinition: sinon.stub().returns('def')
            };

            $row = $('<div>').data({
                name: 'description',
                operator: '$starts',
                value: 'abc',
                valueField: subfield
            });
            filter = field.buildRowFilterDef($row);

            expect(subfield.delegateBuildFilterDefinition).toHaveBeenCalled();
            expect(filter).toEqual({
                description: {
                    '$starts': 'def'
                }
            });
        });
    });
});
