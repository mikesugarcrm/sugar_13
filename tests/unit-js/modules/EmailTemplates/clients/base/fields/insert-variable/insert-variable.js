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

describe('EmailTemplates.Field.InsertVariale', function() {
    var app;
    var field;
    var context;
    var model;
    var sandbox;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'field', 'insert-variable', 'EmailTemplates');
        app = SugarTest.app;

        context = app.context.getContext({module: 'EmailTemplates'});
        context.prepare(true);
        model = context.get('model');

        sandbox = sinon.createSandbox();

        field = SugarTest.createField({
            name: 'insert-variable',
            type: 'insert-variable',
            module: 'EmailTemplates',
            model: model,
            context: context,
            loadFromModule: true
        });
    });

    afterEach(function() {
        field = null;
        sandbox.restore();
    });

    describe('_prepareLabels', function() {
        using('different options', [
            {
                moduleList: [
                    {'label': ['Contacts', 'Leads', 'Prospects']}
                ],
                hasAccess: true,
                getCalled: false,
                getModuleNameCalled: true
            },
            {
                moduleList: [
                    {'label': 'LBL_CURRENT_USER'}
                ],
                hasAccess: true,
                getCalled: true,
                getModuleNameCalled: false
            },
            {
                moduleList: [
                    {'label': ['Contacts', 'Leads', 'Prospects']},
                    {'label': 'LBL_CURRENT_USER'}
                ],
                hasAccess: true,
                getCalled: true,
                getModuleNameCalled: true
            },
            {
                moduleList: [
                    {'label': ['Contacts', 'Leads', 'Prospects']},
                    {'label': 'LBL_CURRENT_USER'}
                ],
                hasAccess: false,
                getCalled: true,
                getModuleNameCalled: false
            }
        ], function(values) {
            it('should translate list of modules appropriately', function() {
                sandbox.stub(app.acl, 'hasAccess').returns(values.hasAccess);
                sandbox.stub(app.lang, 'get');
                sandbox.stub(app.lang, 'getModuleName');
                var options = {
                    def: {
                        moduleList: values.moduleList
                    }
                };
                field._prepareLabels(options);
                expect(app.lang.get.called).toEqual(values.getCalled);
                expect(app.lang.getModuleName.called).toEqual(values.getModuleNameCalled);
            });
        });
    });

    describe('_generateOptions', function() {
        using('different moduleList values', [
            {
                moduleList: [],
                callCount: 0,
            },
            {
                moduleList: [
                    {value: 'test'},
                    {value: 'test2'}
                ],
                callCount: 2,
            }
        ], function(values) {
            it('should call _getVariablesByModule once per module in list', function() {
                sandbox.stub(field, '_getVariablesByModule');
                field.def.moduleList = values.moduleList;
                field._generateOptions();
                expect(field._getVariablesByModule.callCount).toEqual(values.callCount);
            });
        });
    });

    describe('_shouldOmitField', function() {
        using('different field definitions', [
            {
                fieldDef: {name: '', type: 'test'},
                expected: true
            },
            {
                fieldDef: {name: 'test', type: ''},
                expected: true
            },
            {
                fieldDef: {name: 'test', type: 'relate', custom_type: ''},
                expected: true
            },
            {
                fieldDef: {name: 'test', type: 'relate', custom_type: 'test'},
                expected: false
            },
            {
                fieldDef: {name: 'test', type: 'assigned_user_name'},
                expected: true
            },
            {
                fieldDef: {name: 'test', type: 'assigned_user_name'},
                expected: true
            },
            {
                fieldDef: {name: 'user_hash', type: 'text'},
                expected: true
            },
            {
                fieldDef: {name: 'test', type: 'test'},
                expected: false
            }
        ], function(values) {
            it('should omit fields that fail any of the sequential checks', function() {
                var actual = field._shouldOmitField(values.fieldDef);
                expect(actual).toEqual(values.expected);
            });
        });
    });

    describe('_getVariablesByModule', function() {
        using('different module field sets', [
            {
                bean: {
                    fields: [{name: 'test', vname: 'Test', type: 'text'}]
                },
                moduleDef: {
                    variable_source: ['Bean'],
                    variable_prefix: 'bean_'
                },
                expected: [{
                    'name': 'bean_test',
                    'value': 'Test'
                }]
            },
            {
                bean: {
                    fields: [{name: 'test', vname: 'Test', type: 'text'}]
                },
                moduleDef: {
                    variable_source: ['Bean'],
                    variable_prefix: 'bean_'
                },
                expected: [{
                    'name': 'bean_test',
                    'value': 'Test'
                }]
            },
            {
                bean: {
                    fields: [
                        {name: 'test', vname: 'Test', type: 'text'},
                        {name: 'test1', vname: 'Test1', type: 'text'},
                        {name: 'test', vname: 'Test', type: 'text'}
                    ]
                },
                moduleDef: {
                    variable_source: ['Bean'],
                    variable_prefix: 'bean_'
                },
                expected: [
                    {
                        'name': 'bean_test',
                        'value': 'Test'
                    }, {
                        'name': 'bean_test1',
                        'value': 'Test1'
                    }
                ]
            },
        ], function(values) {
            it('should return the expected field sets', function() {
                sandbox.stub(field, '_shouldOmitField').returns(false);
                sandbox.stub(app.data, 'createBean').returns(values.bean);

                var actual = field._getVariablesByModule(values.moduleDef);
                expect(actual).toEqual(values.expected);

                field._shouldOmitField.returns(true);
                actual = field._getVariablesByModule(values.moduleDef);
                expect(actual).toEqual([]);
            });
        });
    });
});
