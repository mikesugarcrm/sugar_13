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
describe('View.Fields.Base.DRIWorkflowTaskTemplates.ModuleDataField', function() {
    let app;
    let field;
    let model;
    let startDateField;
    let module = 'DRI_Workflow_Task_Templates';

    function createField(fieldName, fieldDef) {
        return SugarTest.createField('base', fieldName, 'module-data', 'detail', fieldDef, module, model, null, true);
    }

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        app.data.declareModels();
        model = app.data.createBean(module);
        sandbox = sinon.createSandbox();
        field = createField('start_date_module', {
            onChangeTriggerField: 'task_start_date_type',
            onChangeTriggerValueEqualTo: 'days_from_parent_date_field',
        });
    });

    afterEach(function() {
        model.dispose();
        field.dispose();
        model = null;
        SugarTest.testMetadata.dispose();
    });

    describe('initialize', function() {
        it('field type should be enum', function() {
            expect(field.type).toBe('enum');
        });
    });

    describe('setModuleList', function() {
        beforeEach(function() {
            sinon.stub(model, 'fetch');
            sinon.stub(app.data, 'createBean').returns(model);
        });

        afterEach(function() {
            sinon.restore();
        });

        using('input', [
            {
                id: '',
                taskStartDateType: 'days_from_parent_date_field',
                expectedItems: undefined,
                expectFunctionCalled: false,
            },
            {
                id: '189f-11ed-80a3',
                taskStartDateType: '',
                expectedItems: undefined,
                expectFunctionCalled: false,
            },
            {
                id: '189f-11ed-80a3',
                taskStartDateType: 'days_from_parent_date_field',
                expectFunctionCalled: true,
            },
        ],

        function(input) {
            it('function call should be as expectFunctionCalled', function() {
                field.model.set('dri_workflow_template_id', input.id);
                field.model.set('task_start_date_type', input.taskStartDateType);
                field.setModuleList();
                if (input.expectFunctionCalled) {
                    expect(app.data.createBean).toHaveBeenCalled();
                } else {
                    expect(app.data.createBean).not.toHaveBeenCalled();
                }
            });
        });
    });

    describe('_setModuleListSuccess', function() {
        beforeEach(function() {
            sinon.stub(model, 'fetch').callsFake(function(callbacks) {
                callbacks.success({
                    get: function(fieldName) {
                        return this[fieldName];
                    },
                });
            });
            sinon.stub(field, 'render');
            sinon.stub(field, 'setFieldList');
        });

        afterEach(function() {
            sinon.restore();
        });

        using('input', [
            {
                availableModules: [],
                expectedItems: {},
            },
            {
                availableModules: ['Accounts', 'Contacts'],
                expectedItems: {
                    'Accounts': 'Account',
                    'Contacts': 'Contact',
                },
            },
        ],

        function(input) {
            it('field.items should be as expectedItems and functions should be called', function() {
                field.value = [''];
                model.set('available_modules', input.availableModules);
                field._setModuleListSuccess(model);
                expect(field.setFieldList).toHaveBeenCalled();
                expect(field.render).toHaveBeenCalled();
                expect(field.items).toEqual(input.expectedItems);
            });
        });
    });

    describe('setFieldList', function() {
        beforeEach(function() {
            startDateField = createField('start_date_field', {});
            sinon.stub(app.data, 'createBean').returns({
                fields: {
                    'date_created': {
                        name: 'date_created',
                        type: 'date',
                        source: 'db',
                        vname: 'LBL_DATE_CREATED'
                    },
                },
                module: 'Accounts',
            });
            sinon.stub(app.lang, 'get').returns('Date Created');
            sinon.stub(field.view, 'getField').returns(startDateField);
        });

        afterEach(function() {
            sinon.restore();
        });

        using('input', [
            {
                startDateModule: null,
                expectedItems: undefined,
                expectFunctionCalled: false,
            },
            {
                startDateModule: 'Accounts',
                expectedItems: {
                    'date_created': 'Date Created',
                },
                expectFunctionCalled: true,
            },
        ],

        function(input) {
            it('function call should be as expectFunctionCalled and items should be as expectedItems', function() {
                field.def.fieldListName = 'start_date_field';
                field.model.set('start_date_module', input.startDateModule);
                field.setFieldList();
                if (input.expectFunctionCalled) {
                    expect(field.view.getField).toHaveBeenCalled();
                    expect(app.data.createBean).toHaveBeenCalled();
                    expect(app.lang.get).toHaveBeenCalled();
                } else {
                    expect(field.view.getField).not.toHaveBeenCalled();
                    expect(app.data.createBean).not.toHaveBeenCalled();
                    expect(app.lang.get).not.toHaveBeenCalled();
                }
                expect(startDateField.items).toEqual(input.expectedItems);
            });
        });
    });

    describe('_dispose', function() {
        it('should call field.stopListening and field._super with _dispose', function() {
            sinon.stub(field, 'stopListening').callsFake(function() {});
            sinon.stub(field, '_super').callsFake(function() {});
            field._dispose();
            expect(field.stopListening).toHaveBeenCalledWith(field.model);
            expect(field._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
