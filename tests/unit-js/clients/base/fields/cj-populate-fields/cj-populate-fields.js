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
describe('Base.Field.CJPopulateFields', () => {
    var app;
    var field;
    var fieldName = 'cj-populate-fields';
    var fieldType = 'cj-populate-fields';

    beforeEach(() => {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'fieldset');
        field = SugarTest.createField(
            'base',
            fieldName,
            fieldType
        );
        SugarTest.testMetadata.set();
    });

    afterEach(() => {
        sinon.restore();
        field.dispose();
        app = null;
        field = null;
        SugarTest.testMetadata.dispose();
    });

    describe('initialize', () => {
        it('field option list should be empty', () => {
            expect(field.optionsListLength).toEqual(0);
        });

        it('field current index should be 0', () => {
            expect(field._currentIndex).toEqual(0);
        });

        it('check field custom type', () => {
            expect(field.customDateFieldType).toBe('cj-fieldset-for-date-in-populate-fields');
        });

        it('check field name prefix', () => {
            expect(field.namePrefix).toBe(`${fieldName}_${fieldType}_`);
        });

        it('check field option list field tag', () => {
            expect(field.optionListFieldTag).toBe(`select.select2[name*=${fieldName}_${fieldType}__options]`);
        });
    });

    describe('handleFieldErrorDecoration', () => {
        beforeEach(() => {
            sinon.stub(field, '_removeErrorClassOnMainFieldComponent');
        });

        it('should call _removeErrorClassOnMainFieldComponent function', () => {
            field.handleFieldErrorDecoration(field, true);
            expect(field._removeErrorClassOnMainFieldComponent).toHaveBeenCalled();
        });
    });

    describe('removeErrorClassOnMainFieldComponent', () => {
        let removeClass;

        beforeEach(() => {
            removeClass = sinon.stub();

            sinon.stub(field.$el, 'closest').returns({
                length: 1,
                hasClass: sinon.stub().returns(true),
                removeClass
            });
        });

        it('selective dropdown should exist', () => {
            field._removeErrorClassOnMainFieldComponent();
            expect(removeClass).toHaveBeenCalled();
        });
    });

    describe('prepareOptionsListAndFieldsMetaData', () => {
        let fields = {
            test_field: {
                name: 'test_field',
                vname: 'test_vname',
                id_name: 'test_id_name',
                extensive_filters: true,
                dbType: 'datetime',
                source: 'non-db',
                type: 'relate',
                link_type: 'relationship',
                readonly: false,
                denyListFieldTypes: [],
                denyListFieldNames: []
            },
            date_test_field: {
                name: 'date_test_field',
                vname: 'date_test_vname',
                id_name: 'date_test_id_name',
                extensive_filters: true,
                dbType: 'datetime',
                source: 'db',
                type: 'date',
                link_type: 'relationship',
                readonly: false,
                denyListFieldTypes: [],
                denyListFieldNames: []
            },
            base_rate: {
                name: 'nonvalid_test_field',
                id_name: 'nonvalid_test_id_name',
                extensive_filters: false,
                dbType: 'test',
                source: 'non-db',
                type: 'relate',
                link_type: 'relationship',
                readonly: true
            }
        };

        beforeEach(() => {
            field.selectedModuleName = 'test';
            sinon.stub(field, 'getModuleName').returns('Calls');
            sinon.stub(field, 'restVariablesAndData');
            sinon.stub(field, 'loadPopulateAddedOptionsArray');
            sinon.stub(field, 'bindEventsForSubFields');

            sinon.stub(app.metadata, 'getModule').returns({fields});
            field.namePrefix = 'prefix_';
            field.customDateFieldType = '';
            field.model.fields = [];
        });

        it('should call the restVariablesAndData function', () => {
            field._prepareOptionsListAndFieldsMetaData();
            expect(field.restVariablesAndData).toHaveBeenCalled();
        });

        it('should loop through all the fields, alter them and add them to populateFieldsMetaData', () => {
            field._prepareOptionsListAndFieldsMetaData();
            expect(field.populateFieldsMetaData.prefix_test_field.name).toEqual('prefix_test_field');
        });

        it('using date type field, should add certain subfields', () => {
            field._prepareOptionsListAndFieldsMetaData();
            expect(Object.keys(field.populateFieldsMetaData.prefix_date_test_field.fields).length).toEqual(4);
        });

        it('using a non valid field, should add it to model.fields', () => {
            field._prepareOptionsListAndFieldsMetaData();
            expect(field.model.fields.base_rate).toEqual(fields.base_rate);
        });

        it('should update the options list', () => {
            field._prepareOptionsListAndFieldsMetaData();
            expect(field.optionsListLength).toEqual(3);
        });

        it('should call the loadPopulateAddedOptionsArray function', () => {
            field._prepareOptionsListAndFieldsMetaData();
            expect(field.loadPopulateAddedOptionsArray).toHaveBeenCalled();
        });

        it('should call the bindEventsForSubFields function', () => {
            field._prepareOptionsListAndFieldsMetaData();
            expect(field.bindEventsForSubFields).toHaveBeenCalled();
        });
    });

    describe('includeSubString', () => {
        using('different field names and expected outcome', [
            {
                name: 'cj_name',
                expectedResult: true
            },
            {
                name: 'is_cj_name',
                expectedResult: true
            },
            {
                name: 'repeat_test',
                expectedResult: true
            },
            {
                name: 'cj_customer_journey_test_name',
                expectedResult: true
            },
            {
                name: 'dri_cj_name',
                expectedResult: true
            },
            {
                name: 'dri_workflow_sort_order',
                expectedResult: false
            },
            {
                name: 'test_name',
                expectedResult: false
            }
        ], (data) => {
            it('should output a result accordingly', () => {
                let result = field.includeSubString(data.name);
                expect(result).toEqual(data.expectedResult);
            });
        });
    });

    describe('loadTemplate', () => {
        let testEditField;

        beforeEach(() => {
            testEditField = SugarTest.createField(
                'base',
                fieldName,
                fieldType,
                'edit'
            );
        });

        it('should switch from edit template to list template', () => {
            testEditField.view.action = 'list';
            testEditField.tplName = 'edit';

            testEditField._loadTemplate();

            expect(testEditField.tplName).toEqual('list');
        });
    });

    describe('render', () => {
        let testEditField;

        beforeEach(() => {
            testEditField = SugarTest.createField(
                'base',
                fieldName,
                fieldType,
                'edit'
            );

            sinon.stub(field, '_prepareOptionsListAndFieldsMetaData');
            sinon.stub(testEditField, 'addSelectTo');
            sinon.stub(field, '_renderMainDropdown');
            sinon.stub(field, '_renderSubFields');
            sinon.stub(field, '_removeErrorClassOnMainFieldComponent');
        });

        it('should call _prepareOptionsListAndFieldsMetaData function', () => {
            field._render();
            expect(field._prepareOptionsListAndFieldsMetaData).toHaveBeenCalled();
        });

        it('should call addSelectTo function', () => {
            testEditField.tplName = 'edit';
            testEditField.setValue(null);
            testEditField._render();
            expect(testEditField.addSelectTo).toHaveBeenCalled();
        });

        it('should call _renderMainDropdown function', () => {
            field._render();
            expect(field._renderMainDropdown).toHaveBeenCalled();
        });

        it('should call _renderSubFields function', () => {
            field._render();
            expect(field._renderSubFields).toHaveBeenCalled();
        });

        it('should call _removeErrorClassOnMainFieldComponent function', () => {
            field._render();
            expect(field._removeErrorClassOnMainFieldComponent).toHaveBeenCalled();
        });
    });

    describe('renderSubFields', () => {
        let subFieldName = 'test-name';

        beforeEach(() => {
            sinon.stub(field, '_setSubFieldsModelData');
            sinon.stub(field, '_renderSubField');
        });

        it('should call _setSubFieldsModelData function', () => {
            field.addedOptionsArray = [subFieldName];
            field.setValue('test');
            field._renderSubFields();
            expect(field._setSubFieldsModelData).toHaveBeenCalled();
        });

        it('should call _renderSubField function', () => {
            field.addedOptionsArray = [subFieldName];
            field.setValue('test');
            field._renderSubFields();
            expect(field._renderSubField).toHaveBeenCalledWith(subFieldName);
        });
    });

    describe('renderSubField', () => {
        let subField;
        let subFieldName = 'subfield-test';

        beforeEach(() => {
            subField = SugarTest.createField('base', subFieldName, 'fieldset');
            sinon.stub(field.view, 'getField').returns(subField);
            sinon.stub(field.$el, 'find').returns([subField]);
            sinon.stub(field, '$').returns(field.$el);
            sinon.stub(subField, 'setElement');
            sinon.stub(subField, 'setMode');
        });

        it('should call setElement function', () => {
            field._renderSubField(subFieldName);
            expect(subField.setElement).toHaveBeenCalled();
        });

        it('should call setMode function', () => {
            field._renderSubField(subFieldName);
            expect(subField.setMode).toHaveBeenCalled();
        });
    });

    describe('setSubFieldsModelData', () => {
        beforeEach(() => {
            sinon.stub(field.model, 'set');
        });

        it('should call model.set function', () => {
            field.value = [
                {
                    id: 'test-subfield-id',
                    id_value: '0123',
                    value: 'test'
                }
            ];

            field.populateFieldsMetaData['test-subfield-id'] = {
                id_name: 'test-subfield-id',
                name: 'test-subfield'
            };

            field._setSubFieldsModelData();
            expect(field.model.set).toHaveBeenCalledWith('test-subfield', 'test', {silent: true});
        });
    });

    describe('renderMainDropdown', () => {
        let select;

        beforeEach(() => {
            field.optionListFieldTag = '#fieldTag';
            sinon.stub(field, 'handleChange');
            sinon.stub(field, 'handleFocus');

            const on = sinon.stub();
            on.returns({on});

            select = sinon.stub().returns({on});

            sinon.stub(field, '$').returns({
                select2: select
            });
        });

        it('should call select2 function with enable', () => {
            field.tplName = 'edit';
            field._renderMainDropdown();
            expect(select).toHaveBeenCalledWith('enable');
        });

        it('should call select2 function with disable', () => {
            field.tplName = 'disabled';
            field._renderMainDropdown();
            expect(select).toHaveBeenCalledWith('disable');
        });
    });

    describe('handleChange', () => {
        let aux;
        beforeEach(() => {
            aux = {
                val: 'new-value-id',
                removed: {
                    id: 'old-value-id'
                },
                currentTarget: {
                    getAttribute(param) {
                        return '0';
                    }
                }
            };

            field.value = [
                {
                    id: 'test-subfield-id',
                    value: 'test'
                }
            ];

            field.populateFieldsMetaData['old-value-id'] = {
                id_name: 'test-subfield-id',
                name: 'test-subfield'
            };

            sinon.stub(field.model, 'set');
            sinon.stub(field, 'populateAddedOptionsArray');
            sinon.stub(field, '_updateAndTriggerChange');
            sinon.stub(field, '_render');
        });

        it('should call model.set function', () => {
            field.handleChange(aux);
            expect(field.model.set).toHaveBeenCalledWith('test-subfield', '');
        });

        it('should call populateAddedOptionsArray function', () => {
            field.handleChange(aux);
            expect(field.populateAddedOptionsArray).toHaveBeenCalledWith('new-value-id');
        });

        it('should call _updateAndTriggerChange function', () => {
            field.handleChange(aux);
            expect(field._updateAndTriggerChange).toHaveBeenCalled();
        });

        it('should call _render function', () => {
            field.handleChange(aux);
            expect(field._render).toHaveBeenCalled();
        });
    });

    describe('setValue', () => {
        let model;
        beforeEach(() => {
            model = {
                id: 'new-id'
            };
            field.value = [
                {
                    id: 'test-subfield-id'
                }
            ];
            sinon.stub(field, '_updateAndTriggerChange');
            sinon.stub(field, '_render');
        });

        it('should call _updateAndTriggerChange function', () => {
            field.setValue(model);
            expect(field._updateAndTriggerChange).toHaveBeenCalled();
        });

        it('should call _render function', () => {
            field.setValue(model);
            expect(field._render).toHaveBeenCalled();
        });
    });

    describe('format', () => {
        it('should return expected outcome', () => {
            let value = '[{"id": "test-subfield-id"}]';
            let response = field.format(value);
            expect(response).toEqual(JSON.parse(value));
        });
    });

    describe('prepareData', () => {
        beforeEach(() => {
            field.value = [
                {
                    id: 'test-subfield-id'
                }
            ];
            sinon.stub(field, '_checkAccessToAction').returns(true);
            sinon.stub(field, 'restVariablesAndData');
            sinon.stub(field, '_traverseValueForSingleOption');
        });

        it('prepareData function with empty value param', () => {
            field.value = [];
            field.action = 'edit';
            let response = field.prepareData();
            expect(field.restVariablesAndData).toHaveBeenCalled();
        });

        it('prepareData function with string value param', () => {
            field.value = 'test-value';
            let response = field.prepareData();
            expect(response).toEqual('[{"id":"test-value","remove_button":true,"add_button":true}]');
        });

        it('prepareData function with array value param', () => {
            field.value = [
                {
                    id: 'test-id'
                }
            ];
            let response = field.prepareData();
            expect(response).toEqual('[{"id":"test-id","remove_button":true,"add_button":true}]');
            expect(field._traverseValueForSingleOption).toHaveBeenCalled();
        });
    });

    describe('getRelateFieldModelData', () => {
        let relatedField;

        beforeEach(() => {
            relatedField = SugarTest.createField('base', 'related-field-test', 'fieldset');
            sinon.stub(field.model, 'has').returns(true);
            sinon.stub(field.model, 'get').returns(relatedField);
        });

        it('should return expected output', () => {
            let response = field.getRelateFieldModelData(relatedField);
            expect(response).toEqual(relatedField);
        });
    });

    describe('getValueForNewOption', () => {
        it('should return expected output', () => {
            field.optionsList = {
                option1: 'value1',
                option2: 'value2',
                option3: 'value3',
                option4: 'value4',
                option5: 'value5'
            };
            field.addedOptionsArray = ['option1', 'option4', 'option5'];
            let response = field.getValueForNewOption();
            expect(response).toEqual('option2');
        });

        it('should return empty string value', () => {
            field.optionsList = {
                option1: 'value1',
                option2: 'value2',
                option3: 'value3'
            };
            field.addedOptionsArray = ['option1', 'option2', 'option3'];
            let response = field.getValueForNewOption();
            expect(response).toEqual('');
        });
    });

    describe('loadPopulateAddedOptionsArray', () => {
        beforeEach(() => {
            sinon.stub(field, 'format').returns('[{"id":"test-id-1"},{"id":"test-id-2"}]');
            sinon.stub(field, 'populateAddedOptionsArray');
        });

        it('should call format function', () => {
            field.value = [];
            field.tplName = 'detail';
            field.loadPopulateAddedOptionsArray();
            expect(field.format).toHaveBeenCalled();
        });

        it('should call populateAddedOptionsArray function', () => {
            field.value = [
                {
                    id: 'test-id-1'
                },
                {
                    id: 'test-id-2'
                }
            ];
            field.loadPopulateAddedOptionsArray();
            expect(field.populateAddedOptionsArray).toHaveBeenCalledWith('test-id-1');
        });
    });

    describe('populateAddedOptionsArray', () => {
        beforeEach(() => {
            field.addedOptionsArray = [];
            sinon.stub(field, 'populateAddedFieldsDefs');
        });

        it('should add option to array', () => {
            field.addedOptionsArray = ['option1', 'option2', 'option3'];
            field.populateAddedOptionsArray('option4');
            expect(field.addedOptionsArray).toEqual(['option1', 'option2', 'option3', 'option4']);
        });

        it('should remove option from array', () => {
            field.addedOptionsArray = ['option1', 'option2', 'option3'];
            field.populateAddedOptionsArray('option2', 'remove');
            expect(field.addedOptionsArray).toEqual(['option1', 'option3']);
        });

        it('should call the populateAddedFieldsDefs function', () => {
            field.populateAddedOptionsArray('option');
            expect(field.populateAddedFieldsDefs).toHaveBeenCalledWith('option', 'add');
        });

        it('should not add duplicated to array', () => {
            field.addedOptionsArray = ['option1', 'option2', 'option3'];
            field.populateAddedOptionsArray('option2');
            expect(field.addedOptionsArray).toEqual(['option1', 'option2', 'option3']);
        });
    });

    describe('populateAddedFieldsDefs', () => {
        beforeEach(() => {
            sinon.stub(field, 'populateAddedFieldsDefsHelper');
        });

        it('using custom field type', () => {
            let subField = {name: 'test-name'};
            field.populateFieldsMetaData.option = {
                fields: [subField]
            };
            sinon.stub(field, 'isCustomDateFieldType').returns(true);
            field.populateAddedFieldsDefs('option', 'add');
            expect(field.populateAddedFieldsDefsHelper).toHaveBeenCalledWith('add', 'test-name', subField);
        });

        it('should call populateAddedFieldsDefsHelper function', () => {
            field.populateFieldsMetaData.option = 'test-value';
            field.populateAddedFieldsDefs('option', 'add');
            expect(field.populateAddedFieldsDefsHelper).toHaveBeenCalledWith('add', 'option', 'test-value');
        });
    });

    describe('populateAddedFieldsDefsHelper', () => {
        let auxFieldName = 'test-name';
        let auxField = {name: auxFieldName};

        beforeEach(() => {
            field.addedFieldsDefs = [];
            field.addedFieldsDefs[auxFieldName] = auxField;
        });

        it('should add option to array', () => {
            field.populateAddedFieldsDefsHelper('add', auxFieldName, auxField);
            expect(field.addedFieldsDefs[auxFieldName]).toEqual(auxField);
        });

        it('should remove option from array', () => {
            field.populateAddedFieldsDefsHelper('remove', auxFieldName, auxField);
            expect(field.addedFieldsDefs[auxFieldName]).toBe(undefined);
        });
    });

    describe('isCustomDateFieldType', () => {
        let fieldName = 'test-name';
        let fieldType = 'custom-type';

        it('use custom type should return true', () => {
            field.populateFieldsMetaData[fieldName] = {type: fieldType};
            field.customDateFieldType = fieldType;
            let response = field.isCustomDateFieldType(fieldName);
            expect(response).toBe(true);
        });

        it('use regular type, should return false', () => {
            let response = field.isCustomDateFieldType(fieldName);
            expect(response).toBe(false);
        });
    });

    describe('addSelectTo', () => {
        beforeEach(() => {
            field.value = [
                {
                    id: 'test-id-1'
                },
                {
                    id: 'test-id-2'
                }
            ];
            sinon.stub(field, 'loadPopulateAddedOptionsArray');
            sinon.stub(field, 'setValue');
        });

        it('should call loadPopulateAddedOptionsArray function', () => {
            field.addSelectTo();
            expect(field.loadPopulateAddedOptionsArray).toHaveBeenCalled();
        });

        it('should call setValue function', () => {
            field.addSelectTo();
            expect(field.setValue).toHaveBeenCalledWith({id: ''});
        });

        it('should increment the index', () => {
            field.addSelectTo();
            expect(field._currentIndex).toEqual(3);
        });
    });

    describe('disposeSubFields', () => {
        let fieldDef = {name: 'test-field'};
        let dispose;

        beforeEach(() => {
            dispose = sinon.stub();
            const getField = sinon.stub();
            getField.returns({
                $el: {name: 'test-field'},
                unbindDom: sinon.stub(),
                dispose: dispose
            });
            field.view = {
                getField,
                fields: [{name: 'test-field'}]
            };
            sinon.stub(field, '_resetSubFieldModel');
        });

        it('should call dispose function', () => {
            field._disposeSubFields(fieldDef);
            expect(dispose).toHaveBeenCalled();
        });

        it('should call _resetSubFieldModel function', () => {
            field._disposeSubFields(fieldDef);
            expect(field._resetSubFieldModel).toHaveBeenCalledWith(fieldDef);
        });
    });

    describe('resetSubFieldsModel', () => {
        beforeEach(() => {
            field.populateFieldsMetaData = [
                {name: 'test-field'},
                {name: 'test-field-2'},
                {name: 'test-field-3'}
            ];
            sinon.stub(field, '_resetSubFieldModel');
        });

        it('should call _resetSubFieldModel function', () => {
            field._resetSubFieldsModel();
            expect(field._resetSubFieldModel).toHaveBeenCalledWith({name: 'test-field-3'});
        });
    });

    describe('resetSubFieldModel', () => {
        let subField;
        beforeEach(() => {
            subField = {
                name: 'test-subfield',
                id_name: 'test-subfield-id',
                type: 'custom-type',
                fields: [
                    {name: 'test-field-1'},
                    {name: 'test-field-2'}
                ]
            };
            field.customDateFieldType = 'custom-type';

            sinon.stub(field.model, 'set');
            sinon.stub(field.model, 'has').returns(true);
        });

        it('should reset subField name', () => {
            field._resetSubFieldModel(subField);
            expect(field.model.set).toHaveBeenCalledWith('test-subfield', '');
        });

        it('should reset subField id', () => {
            field._resetSubFieldModel(subField);
            expect(field.model.set).toHaveBeenCalledWith('test-subfield-id', '');
        });

        it('should reset the subfields of the subField', () => {
            field._resetSubFieldModel(subField);
            expect(field.model.set).toHaveBeenCalledWith('test-field-1', '');
        });
    });

    describe('removeSelectTo', () => {
        beforeEach(() => {
            field.value = [
                {id: 'test_id_1'},
                {id: 'test_id_2'},
                {id: 'test_id_3'}
            ];
            field.populateFieldsMetaData = {
                test_id_1: 'test-value-1',
                test_id_2: 'test-value-2',
                test_id_3: 'test-value-3'
            };
            sinon.stub(field, 'populateAddedOptionsArray');
            sinon.stub(field, '_disposeSubFields');
            sinon.stub(field, '_updateAndTriggerChange');
            sinon.stub(field, '_render');
        });

        it('should call populateAddedOptionsArray function', () => {
            field.removeSelectTo(1);
            expect(field.populateAddedOptionsArray).toHaveBeenCalledWith('test_id_2', 'remove');
        });

        it('should call _disposeSubFields function', () => {
            field.removeSelectTo(2);
            expect(field._disposeSubFields).toHaveBeenCalledWith('test-value-3');
        });

        it('should call _updateAndTriggerChange function', () => {
            field.removeSelectTo(1);
            expect(field._updateAndTriggerChange).toHaveBeenCalled();
        });

        it('should call _render function', () => {
            field.removeSelectTo(1);
            expect(field._render).toHaveBeenCalled();
        });
    });

    describe('bindDataChange', () => {
        beforeEach(() => {
            sinon.stub(field, 'bindEventsForSubFields');
            sinon.stub(field, '_render');
            field.listenTo = sinon.stub();
        });

        it('should call bindEventsForSubFields function', () => {
            field.bindDataChange();
            expect(field.bindEventsForSubFields).toHaveBeenCalled();
        });

        it('should call listenTo function', () => {
            field.bindDataChange();
            expect(field.listenTo).toHaveBeenCalled();
        });
    });

    describe('bindEventsForSubFields', () => {
        beforeEach(() => {
            field.addedOptionsArray = ['test_id_1', 'test_id_2'];
            field.populateFieldsMetaData = {
                test_id_1: 'test-value-1',
                test_id_2: 'test-value-2',
                test_id_3: 'test-value-3'
            };
            sinon.stub(field, '_bindSubFieldsModelEvent');
        });

        it('should call _bindSubFieldsModelEvent function', () => {
            field.bindEventsForSubFields();
            expect(field._bindSubFieldsModelEvent).toHaveBeenCalledWith('test-value-2', 'test_id_2');
        });
    });

    describe('bindSubFieldsModelEvent', () => {
        let subField;
        const optionName = 'test-option';

        beforeEach(() => {
            subField = {
                name: 'test-subfield',
                id_name: 'test-subfield-id',
                type: 'custom-type',
                fields: [
                    {name: 'test-field-1'},
                    {name: 'test-field-2'}
                ]
            };
            sinon.stub(field, '_bindSubFieldsModelEventHelper');
        });

        it('should call _bindSubFieldsModelEventHelper function', () => {
            field._bindSubFieldsModelEvent(subField, optionName);
            expect(field._bindSubFieldsModelEventHelper).toHaveBeenCalledWith(subField.name, optionName);
        });

        it('should call _bindSubFieldsModelEventHelper function for all subfields', () => {
            field.customDateFieldType = 'custom-type';
            field._bindSubFieldsModelEvent(subField, optionName);
            expect(field._bindSubFieldsModelEventHelper)
                .toHaveBeenCalledWith(subField.fields[0].name, optionName, subField);
        });
    });

    describe('bindSubFieldsModelEventHelper', () => {
        let parentField;
        const optionName = 'test-option';

        beforeEach(() => {
            parentField = {
                name: 'test-subfield',
                id_name: 'test-subfield-id',
                type: 'custom-type',
                fields: [
                    {name: 'test-field-1'},
                    {name: 'test-field-2'}
                ]
            };
            sinon.stub(field.model, 'off');
            field.listenTo = sinon.stub();
        });

        it('should call field.model.off function', () => {
            field._bindSubFieldsModelEventHelper('test-field-1', optionName, parentField);
            expect(field.model.off).toHaveBeenCalled();
        });

        it('should call _traverseValueOnChange function', () => {
            field._bindSubFieldsModelEventHelper('test-field-1', optionName, parentField);
            expect(field.listenTo).toHaveBeenCalled();
        });
    });

    describe('traverseValueOnChange', () => {
        let parentField;
        const optionName = 'test_id_2';

        beforeEach(() => {
            parentField = {
                name: 'test-subfield',
                id_name: 'test-subfield-id',
                type: 'custom-type',
                fields: [
                    {name: 'test-field-1'},
                    {name: 'test-field-2'}
                ]
            };
            field.value = [
                {id: 'test_id_1'},
                {id: 'test_id_2'},
                {id: 'test_id_3'}
            ];
            sinon.stub(field, '_traverseValueForSingleOption');
        });

        it('should call _traverseValueForSingleOption function', () => {
            field._traverseValueOnChange(optionName, parentField);
            expect(field._traverseValueForSingleOption).toHaveBeenCalledWith({id: 'test_id_2'});
        });
    });

    describe('traverseValueForSingleOption', () => {
        let option;

        beforeEach(() => {
            option = {id: 'test_option_id'};
            field.populateFieldsMetaData = {
                test_option_id: {
                    label: 'option_label',
                    vname: 'option_vname',
                    name: 'option_name',
                    id_name: 'option_id_name',
                    originalType: 'option_ogtype',
                    actualFieldName: 'option_field_name'
                },
            };
            field.selectedModuleName = 'testModule';
            sinon.stub(field, 'getRelateFieldModelData').returns('test_id_value');
        });

        it('should set the attributes of the currentOption variable', () => {
            field._traverseValueForSingleOption(option);
            expect(option).toEqual({
                id: 'test_option_id',
                label: 'option_label',
                value: 'test_id_value',
                module: 'testModule',
                type: 'option_ogtype',
                actualFieldName: 'option_field_name'
            });
        });

        it('using a relate type field', () => {
            field.populateFieldsMetaData[option.id].type = 'relate';
            field._traverseValueForSingleOption(option);
            expect(field.getRelateFieldModelData).toHaveBeenCalledWith('option_id_name');
        });

        it('using a currency type field', () => {
            field.populateFieldsMetaData[option.id].type = 'currency';
            field._traverseValueForSingleOption(option);
            expect(option.id_name).toEqual('currency_id');
        });

        it('using a custom date type field', () => {
            field.populateFieldsMetaData[option.id].type = 'custom-type';
            field.customDateFieldType = 'custom-type';
            field.populateFieldsMetaData[option.id].fields = [
                {
                    name: 'subfield_name_1',
                    shortName: 'subfield_shortName_1'
                },
                {
                    name: 'subfield_name_2',
                    shortName: 'subfield_shortName_2'
                }
            ];
            field._traverseValueForSingleOption(option);
            expect(Object.keys(option.childFieldsData).length).toEqual(2);
        });
    });

    describe('updateAndTriggerChange', () => {
        let setTrigger;

        beforeEach(() => {
            setTrigger = sinon.stub();

            sinon.stub(field.model, 'unset').returns({set: setTrigger});

            sinon.stub(field, 'prepareData');
        });

        it('should call model.unset function', () => {
            field._updateAndTriggerChange();
            expect(field.model.unset).toHaveBeenCalledWith(field.name, {silent: true});
        });

        it('should call model.set function', () => {
            field._updateAndTriggerChange();
            expect(setTrigger).toHaveBeenCalled();
        });
    });

    describe('addItem', () => {
        beforeEach(() => {
            $ = () => ({
                data: () => 0
            });

            sinon.stub(field, 'addSelectTo');
        });

        afterEach(() => {
            $ = jQuery;
        });

        it('should call addSelectTo function', () => {
            let evt = {currentTarget: 'test-value'};
            field.value = [{id: 'test-id'}];
            field.addItem(evt);
            expect(field.addSelectTo).toHaveBeenCalled();
        });
    });

    describe('removeItem', () => {
        beforeEach(() => {
            $ = () => ({
                data: () => 1
            });

            sinon.stub(field, 'removeSelectTo');
        });

        afterEach(() => {
            $ = jQuery;
        });

        it('should call removeSelectTo function', () => {
            let evt = {currentTarget: 'test-value'};
            field.removeItem(evt);
            expect(field.removeSelectTo).toHaveBeenCalledWith(1);
        });
    });

    describe('dispose', () => {
        let pluginClose;

        beforeEach(() => {
            field.stopListening = sinon.stub();

            pluginClose = sinon.stub();
            const pluginData = sinon.stub().returns({close: pluginClose});
            sinon.stub(field, '$').returns({data: pluginData});

            sinon.stub(field, '_super');
            sinon.stub(field, 'restVariablesAndData');
        });

        it('should call stopListening function', () => {
            field._dispose();
            expect(field.stopListening).toHaveBeenCalledWith(field.model);
        });

        it('should call plugin.close function', () => {
            field._dispose();
            expect(pluginClose).toHaveBeenCalled();
        });

        it('should call _super function', () => {
            field._dispose();
            expect(field._super).toHaveBeenCalledWith('_dispose');
        });

        it('should call restVariablesAndData function', () => {
            field._dispose();
            expect(field.restVariablesAndData).toHaveBeenCalled();
        });
    });

    describe('restVariablesAndData', () => {
        beforeEach(() => {
            field.value = [
                {id: 'test_id_1'}
            ];
            field.optionsList = [
                {id: 'test_id_1'},
                {id: 'test_id_2'},
                {id: 'test_id_3'}
            ];
            field.optionsListLength = 3;
            field.addedOptionsArray = ['option1', 'option2', 'option3'];
            field.populateFieldsMetaData = {
                test_id_1: 'test-value-1',
                test_id_2: 'test-value-2',
                test_id_3: 'test-value-3'
            };

            sinon.stub(field, '_resetSubFieldsModel');
            sinon.stub(field.model, 'set');
            sinon.stub(field, '_prepareOptionsListAndFieldsMetaData');
        });

        it('should reset field value', () => {
            field.restVariablesAndData();
            expect(field.value).toEqual([]);
        });

        it('should reset field optionsList', () => {
            field.restVariablesAndData();
            expect(field.optionsList).toEqual([]);
        });

        it('should reset field optionsListLength', () => {
            field.restVariablesAndData();
            expect(field.optionsListLength).toEqual(0);
        });

        it('should reset field addedOptionsArray', () => {
            field.restVariablesAndData();
            expect(field.addedOptionsArray).toEqual([]);
        });

        it('should reset field populateFieldsMetaData', () => {
            field.restVariablesAndData();
            expect(Object.keys(field.populateFieldsMetaData)).toEqual([]);
        });

        it('should call model.set function', () => {
            field.restVariablesAndData();
            expect(field.model.set).toHaveBeenCalledWith(field.name, '', {silent: true});
        });

        it('should call _prepareOptionsListAndFieldsMetaData function', () => {
            field.restVariablesAndData();
            expect(field._prepareOptionsListAndFieldsMetaData).toHaveBeenCalled();
        });

        it('should not call _prepareOptionsListAndFieldsMetaData function', () => {
            field.restVariablesAndData(true);
            expect(field._prepareOptionsListAndFieldsMetaData).not.toHaveBeenCalled();
        });
    });
});
