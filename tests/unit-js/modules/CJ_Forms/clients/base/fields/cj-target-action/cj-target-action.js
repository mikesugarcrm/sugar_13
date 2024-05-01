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
describe('Base.Field.CJ_Forms.CjTargetAction', function() {
    let app;
    let field;
    let model;
    let module = 'CJ_Forms';
    let fieldName = 'cj-target-action';
    let fieldType = 'cj-target-action';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean(module);
        field = SugarTest.createField(
            'base',
            fieldName,
            fieldType,
            'detail',
            {},
            module,
            model,
            null,
            true
        );
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        app = null;
        field = null;
        SugarTest.testMetadata.dispose();
    });

    describe('_initProperties', () => {
        it('field option list should be empty', () => {
            field._initProperties();
            expect(field.optionsListLength).toEqual(0);
        });

        it('field current index should be 0', () => {
            field._initProperties();
            expect(field._currentIndex).toEqual(0);
        });

        it('check field name prefix', () => {
            field._initProperties();
            expect(field.namePrefix).toBe(`${fieldName}_${fieldType}_`);
        });

        it('check field option list field tag', () => {
            field._initProperties();
            expect(field.optionListFieldTag).toBe(`select.select2[name*=${fieldName}_${fieldType}__options]`);
        });

        it('check field action list field tag', () => {
            field._initProperties();
            expect(field.actionListFieldTag).toBe(`select.select2[name*=${fieldName}_${fieldType}__actions]`);
        });

        it('check target module mapping', () => {
            field._initProperties();
            expect(field.targetModuleMapping.DRI_Workflow_Templates).toBe('Smart Guide');
            expect(field.targetModuleMapping.DRI_SubWorkflow_Templates).toBe('Smart Guide Stage');
            expect(field.targetModuleMapping.DRI_Workflow_Task_Templates).toBe('Smart Guide Activities');
        });
    });

    describe('_validateField', () => {
        beforeEach(() => {
            sinon.stub(field, '_toggleValidationErrorClass');
        });

        it('should return true', () => {
            expect(field._validateField()).toBeTruthy();
        });

        it('should call the _toggleValidationErrorClass function', () => {
            field.model.set('main_trigger_type', 'sugar_action_to_smart_guide');
            field.model.set(field.name, JSON.stringify([{id: 'Smart Guide'}]));
            field._validateField();

            expect(field._toggleValidationErrorClass).toHaveBeenCalled();
        });
    });

    describe('prepareOptionsListAndFieldsMetaData', () => {
        beforeEach(() => {
            sinon.stub(app.lang, 'getAppListStrings').returns({'Smart Guide': 'Smart Guide'});
            sinon.stub(field, 'loadPopulateAddedOptionsArray');
            sinon.stub(field, 'bindEventsForSubFields');
        });

        it('should update the options list', () => {
            field._prepareOptionsListAndFieldsMetaData();
            expect(field.optionsListLength).toEqual(1);
        });

        it('should call the getAppListStrings function', () => {
            field._prepareOptionsListAndFieldsMetaData();
            expect(app.lang.getAppListStrings).toHaveBeenCalled();
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

    describe('loadPopulateAddedOptionsArray', () => {
        beforeEach(() => {
            sinon.stub(field, 'format').returns('[{"id":"test-id-1"},{"id":"test-id-2"}]');
            sinon.stub(field, 'getRelateFieldModelData');
            sinon.stub(field, 'populateMetaAndOptions');
        });

        it('should call format function', () => {
            field.value = [];
            field.tplName = 'detail';
            field.loadPopulateAddedOptionsArray();
            expect(field.format).toHaveBeenCalled();
        });

        it('should call populateMetaAndOptions function', () => {
            field.value = [
                {
                    id: 'test-id-1'
                }
            ];
            field.loadPopulateAddedOptionsArray();
            expect(field.populateMetaAndOptions).toHaveBeenCalledWith('test-id-1');
        });

        it('should not call populateMetaAndOptions function', () => {
            field.value = [
                {
                    id: 'Smart Guide',
                    action_id: 'cancelled'
                }
            ];
            field.loadPopulateAddedOptionsArray();
            expect(field.populateMetaAndOptions).not.toHaveBeenCalled();
        });
    });

    describe('populateMetaAndOptions', () => {
        beforeEach(() => {
            sinon.stub(field, 'format').returns('[{"id":"test-id-1"},{"id":"test-id-2"}]');
            sinon.stub(field, 'populateAddedOptionsArray');
            sinon.stub(field, 'populateAddedActionsArray');
        });

        it('check populate fields meta data with Smart Guide Activities id', () => {
            let fieldName = `${field.targetFieldName}_0`;

            field.populateMetaAndOptions('Smart Guide Activities', 0);
            expect(field.populateFieldsMetaData[fieldName].name).toEqual(fieldName);
        });

        it('should call populateAddedOptionsArray function', () => {
            field.populateMetaAndOptions('Smart Guide Activities', 0);
            expect(field.populateAddedOptionsArray).toHaveBeenCalledWith(`${field.targetFieldName}_0`);
        });

        it('check action fields meta data with Smart Guide Stage id', () => {
            let fieldName = `${field.actionFieldName}_1`;

            field.populateMetaAndOptions('Smart Guide Stage', 1);
            expect(field.actionFieldsMetaData[fieldName].name).toEqual(fieldName);
        });

        it('should call populateAddedActionsArray function', () => {
            field.populateMetaAndOptions('Smart Guide Stage', 1);
            expect(field.populateAddedActionsArray).toHaveBeenCalledWith(`${field.actionFieldName}_1`);
        });
    });

    describe('populateAddedActionsArray', () => {
        it('should add option to array', () => {
            field.addedActionsArray = ['option1'];

            field.populateAddedActionsArray('option2');
            expect(field.addedActionsArray).toEqual(['option1', 'option2']);
        });

        it('should remove option from array', () => {
            field.addedActionsArray = ['option1', 'option2', 'option3'];

            field.populateAddedActionsArray('option2', 'remove');
            expect(field.addedActionsArray).toEqual(['option1', 'option3']);
        });

        it('should not add duplicated to array', () => {
            field.addedActionsArray = ['option1', 'option2', 'option3'];

            field.populateAddedActionsArray('option2');
            expect(field.addedActionsArray).toEqual(['option1', 'option2', 'option3']);
        });
    });

    describe('_render', () => {
        beforeEach(() => {
            sinon.stub(field, 'fetchTemplateData');
            sinon.stub(field, '_prepareOptionsListAndFieldsMetaData');
            sinon.stub(field, '_initialRender');
            sinon.stub(field, '_renderMainDropdown');
            sinon.stub(field, '_renderSubFields');
            sinon.stub(field, '_renderActionField');
            sinon.stub(field, '_removeErrorClassOnMainFieldComponent');
        });

        it('should call fetchTemplateData function', () => {
            field._render();
            expect(field.fetchTemplateData).toHaveBeenCalled();
        });

        it('should call _initialRender function', () => {
            field._render();
            expect(field._initialRender).toHaveBeenCalled();
        });

        it('should call _renderMainDropdown function', () => {
            field._render();
            expect(field._renderMainDropdown).toHaveBeenCalled();
        });

        it('should call _renderActionField function', () => {
            field._render();
            expect(field._renderActionField).toHaveBeenCalled();
        });
    });

    describe('_initialRender', () => {
        it('should call addSelectTo function', () => {
            sinon.stub(field, 'addSelectTo');
            field.tplName = 'edit';
            field.value = [];

            field._initialRender();
            expect(field.addSelectTo).toHaveBeenCalled();
        });
    });

    describe('_renderSubFields', () => {
        let subFieldName = 'test-field';

        beforeEach(() => {
            sinon.stub(field, '_setSubFieldsModelData');
            sinon.stub(field, '_renderSubField');
        });

        it('should call _setSubFieldsModelData function', () => {
            field.addedOptionsArray = [subFieldName];
            field.value = [{id: 'test'}];

            field._renderSubFields();
            expect(field._setSubFieldsModelData).toHaveBeenCalled();
        });

        it('should not call _setSubFieldsModelData function', () => {
            field.value = [];

            field._renderSubFields();
            expect(field._setSubFieldsModelData).not.toHaveBeenCalled();
        });

        it('should call _renderSubField function', () => {
            field.addedOptionsArray = [subFieldName];
            field.value = [{id: 'test'}];

            field._renderSubFields();
            expect(field._renderSubField).toHaveBeenCalledWith(subFieldName);
        });

        it('should not call _renderSubField function', () => {
            field.value = [];

            field._renderSubFields();
            expect(field._renderSubField).not.toHaveBeenCalled();
        });
    });

    describe('_renderSubField', () => {
        let subField;
        let subFieldName = 'subfield-test';

        beforeEach(() => {
            subField = SugarTest.createField('base', subFieldName, 'fieldset');

            sinon.stub(field.view, 'getField').returns(subField);
            sinon.stub(field.$el, 'find').returns(subField.$el);
            sinon.stub(field, '$').returns(field.$el);
            sinon.stub(subField, 'setElement');
            sinon.stub(subField, 'setMode');
            sinon.stub(field, '_removeErrorClassOnMainFieldComponent');
            sinon.stub(field, 'getFieldOptions').returns({});
            sinon.stub(field, 'shouldRenderField').returns(true);
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

    describe('shouldRenderField', () => {
        beforeEach(() => {
            field.value = [{id: 'Smart Guide', action_id: 'in_progress'}];
        });

        it('should return true', () => {
            let response = field.shouldRenderField(0, field.targetFieldName);
            expect(response).toBeTruthy();
        });

        it('should return false', () => {
            let response = field.shouldRenderField(0, 'test-field');
            expect(response).toBeFalsy();
        });

        it('should return true', () => {
            let response = field.shouldRenderField(0, field.actionFieldName);
            expect(response).toBeTruthy();
        });
    });

    describe('getFieldOptions', () => {
        beforeEach(() => {
            field.templateActivities = {'111-111-111': 'Test Activity'};
            field.templateStages = {'222-222-222': 'Test Stage'};
            field.stageActivities = {
                '222-222-222': {
                    '333-333-333': 'Test Stage Activity'
                }
            };
        });

        it('should return template activities', () => {
            field.value = [{id: 'Smart Guide Activities'}];
            let options = field.getFieldOptions(0);

            expect(options.items).toEqual(field.templateActivities);
        });

        it('should return template stages', () => {
            field.value = [{id: 'Smart Guide Stage'}];
            let options = field.getFieldOptions(0, field.targetFieldName);

            expect(options.items).toEqual(field.templateStages);
        });

        it('should return stage activities', () => {
            field.value = [{id: 'Smart Guide Stage', value: '222-222-222', action_id: 'in_progress'}];
            let options = field.getFieldOptions(0, field.actionFieldName);

            expect(options.items).toEqual(field.stageActivities['222-222-222']);
        });

        it('should return complete action list', () => {
            sinon.stub(app.lang, 'getAppListStrings').returns({'complete_all': 'Complete All'});

            field.value = [{id: 'Smart Guide Stage', action_id: 'completed'}];
            let options = field.getFieldOptions(0, field.actionFieldName);

            expect(options.items).toEqual({'complete_all': 'Complete All'});
        });
    });

    describe('fetchTemplateData', () => {
        beforeEach(() => {
            sinon.stub(app.api, 'call');
            sinon.stub(app.api, 'buildURL');
            sinon.stub(field, '_render');
            sinon.stub(field, '_resetSubFieldsModel');
            sinon.stub(field, '_updateAndTriggerChange');
        });

        it('should call app.api.call function', () => {
            field.fetchTemplateData('111-111-111');
            expect(app.api.call).toHaveBeenCalled();
        });

        it('should call app.api.buildURL function', () => {
            field.fetchTemplateData('111-111-111');
            expect(app.api.buildURL).toHaveBeenCalled();
        });

        it('should call _resetSubFieldsModel function', () => {
            field.templateStages = {};
            field.fetchTemplateData('111-111-111');
            expect(field._resetSubFieldsModel).toHaveBeenCalled();
        });

        it('should call _render function', () => {
            field.fetchTemplateData('111-111-111');
            expect(field._render).toHaveBeenCalled();
        });
    });

    describe('loadCompleted', () => {
        let response;

        beforeEach(() => {
            sinon.stub(field, 'setInitialValues');
            sinon.stub(field, '_render');
            response = {
                stages: [{
                    id: '111-111-111',
                    name: 'Test Stage',
                    activities: [{
                        id: '222-222-222',
                        name: 'Test Activity'
                    }]
                }]
            };
        });

        it('check template activities', () => {
            field.loadCompleted(response);
            expect(field.templateActivities).toEqual({'222-222-222': 'Test Activity'});
        });

        it('check template stages', () => {
            field.loadCompleted(response);
            expect(field.templateStages['111-111-111']).toEqual('Test Stage');
        });

        it('check stage activities', () => {
            field.loadCompleted(response);
            expect(field.stageActivities['111-111-111']['222-222-222']).toEqual('Test Activity');
        });

        it('should call setInitialValues function', () => {
            field.loadCompleted(response);
            expect(field.setInitialValues).toHaveBeenCalledWith(0);
        });

        it('should call _render function', () => {
            field.loadCompleted(response);
            expect(field._render).toHaveBeenCalled();
        });
    });

    describe('_setSubFieldsModelData', () => {
        beforeEach(() => {
            sinon.stub(field.model, 'set');
        });

        it('should not call model.set function', () => {
            field.value = [{id: 'Smart Guide', value: '111-111-111'}];

            field._setSubFieldsModelData();
            expect(field.model.set).not.toHaveBeenCalled();
        });

        it('should call model.set function', () => {
            field.value = [{id: 'Smart Guide Activities', value: '111-111-111'}];

            field._setSubFieldsModelData();
            expect(field.model.set).toHaveBeenCalledWith(`${field.targetFieldName}_0`, '111-111-111', {silent: true});
        });
    });

    describe('_setActionFieldsModelData', () => {
        beforeEach(() => {
            sinon.stub(field.model, 'set');
        });

        it('should not call model.set function', () => {
            field.value = [{id: 'Smart Guide Activities', value: '111-111-111'}];

            field._setActionFieldsModelData();
            expect(field.model.set).not.toHaveBeenCalled();
        });

        it('should call model.set function', () => {
            field.value = [{id: 'Smart Guide Stage', action_value: '222-222-222'}];

            field._setActionFieldsModelData();
            expect(field.model.set).toHaveBeenCalledWith(`${field.actionFieldName}_0`, '222-222-222', {silent: true});
        });
    });

    describe('_renderMainDropdown', () => {
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

    describe('handleActionChange', () => {
        let event;

        beforeEach(() => {
            sinon.stub(field, '_render');
            sinon.stub(field, '_updateAndTriggerChange');
            event = {
                val: 'test',
                currentTarget: {
                    getAttribute: sinon.stub().returns(0)
                }
            };
            field.value = [{}];
        });

        it('should set action_id', () => {
            field.handleActionChange(event);
            expect(field.value[0].action_id).toEqual('test');
        });

        it('should reset action_value', () => {
            field.value[0].action_value = 'complete_all';
            field.handleActionChange(event);
            expect(field.value[0].action_value).toEqual(undefined);
        });

        it('should call _render function', () => {
            field.handleActionChange(event);
            expect(field._render).toHaveBeenCalled();
        });

        it('should call _updateAndTriggerChange function', () => {
            field.handleActionChange(event);
            expect(field._updateAndTriggerChange).toHaveBeenCalled();
        });
    });

    describe('_renderActionField', () => {
        let subFieldName = 'test-field';

        beforeEach(() => {
            sinon.stub(field, '_setActionFieldsModelData');
            sinon.stub(field, '_renderSubField');
            sinon.stub(field, 'bindEventsForSubFields');
        });

        it('should call _setActionFieldsModelData function', () => {
            field._renderActionField();
            expect(field._setActionFieldsModelData).toHaveBeenCalled();
        });

        it('should call bindEventsForSubFields function', () => {
            field._renderActionField();
            expect(field.bindEventsForSubFields).toHaveBeenCalled();
        });

        it('should call _renderSubField function', () => {
            field.addedActionsArray = [subFieldName];

            field._renderActionField();
            expect(field._renderSubField).toHaveBeenCalledWith(subFieldName);
        });
    });

    describe('handleChange', () => {
        let aux;

        beforeEach(() => {
            aux = {
                val: 'Smart Guide',
                removed: {
                    id: 'Smart Guide Stage'
                },
                currentTarget: {
                    getAttribute: sinon.stub().returns(0)
                }
            };

            field.value = [
                {
                    id: 'Smart Guide',
                    value: '333-333-333',
                    action_value: 'complete_all'
                },
                {
                    id: 'Smart Guide Stage',
                    value: '111-111-111',
                    action_value: '222-222-222'
                }
            ];

            field.populateFieldsMetaData['old-value-id'] = {
                name: 'test-subfield'
            };

            sinon.stub(field, 'populateAddedOptionsArray');
            sinon.stub(field, '_updateAndTriggerChange');
            sinon.stub(field, '_render');
        });

        it('should delete value attribute from field value', () => {
            field.handleChange(aux);
            expect(field.value[0].value).toEqual(undefined);
        });

        it('should delete action_value attribute from field value', () => {
            field.handleChange(aux);
            expect(field.value[0].action_value).toEqual(undefined);
        });

        it('should remove values from field other then first', () => {
            field.handleChange(aux);
            expect(field.value.length).toEqual(1);
        });

        it('should call populateAddedOptionsArray function', () => {
            field.handleChange(aux);
            expect(field.populateAddedOptionsArray).toHaveBeenCalledWith('Smart Guide');
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
            model = {id: 'new-id'};
            sinon.stub(field, '_render');
            sinon.stub(field, '_updateAndTriggerChange');
            sinon.stub(field, 'setInitialValues');
        });

        it('should call _updateAndTriggerChange function', () => {
            field.setValue(model);
            expect(field._updateAndTriggerChange).toHaveBeenCalled();
        });

        it('should call _render function', () => {
            field.setValue(model);
            expect(field._render).toHaveBeenCalled();
        });

        it('should call setInitialValues function', () => {
            field.initialRender = true;

            field.setValue(model);
            expect(field.setInitialValues).toHaveBeenCalledWith(0);
        });
    });

    describe('setInitialValues', () => {
        beforeEach(() => {
            field.value = [{}];
            field.action = 'edit';
        });

        it('should set value', () => {
            let parent = new Backbone.Model({id: '222-222-222'});
            parent._module = 'DRI_SubWorkflow_Templates';
            field.templateStages = {
                '222-222-222': 'Test Stage'
            };
            field.view.model = new Backbone.Model();
            field.view.model.set('parent', parent);

            field.setInitialValues(0);
            expect(field.value[0].value).toEqual('222-222-222');
        });

        it('should set remove_button attribute of field value to false', () => {
            field.setInitialValues(0);
            expect(field.value[0].remove_button).toBeFalsy();
        });

        it('should set add_button attribute of field value to true', () => {
            field.setInitialValues(0);
            expect(field.value[0].add_button).toBeTruthy();
        });

        it('should set value in field model', () => {
            let parent = new Backbone.Model({id: '333-333-333'});
            parent._module = 'DRI_Workflow_Task_Templates';
            field.templateActivities = {
                '333-333-333': 'Test Activity'
            };
            field.view.model = new Backbone.Model();
            field.view.model.set('parent', parent);

            field.setInitialValues(0);
            expect(field.value[0].value).toEqual('333-333-333');
        });
    });

    describe('prepareData', () => {
        beforeEach(() => {
            sinon.stub(field, '_checkAccessToAction').returns(true);
            sinon.stub(field, 'restVariablesAndData');
            sinon.stub(field, '_traverseValueForSingleOption');
        });

        it('should call restVariablesAndData function', () => {
            field.value = [];
            field.action = 'edit';

            field.prepareData();
            expect(field.restVariablesAndData).toHaveBeenCalled();
        });

        it('should call _traverseValueForSingleOption function', () => {
            field.value = [{id: 'Smart Guide'}];
            field.prepareData();

            expect(field._traverseValueForSingleOption).toHaveBeenCalled();
        });

        it('check prepareData response', () => {
            field.value = 'Smart Guide Stage';
            let response = field.prepareData();

            expect(response).toEqual('[{"id":"Smart Guide Stage","remove_button":false,"add_button":true}]');
        });
    });

    describe('populateAddedOptionsArray', () => {
        beforeEach(() => {
            sinon.stub(field, 'populateAddedFieldsDefs');
        });

        it('should add option to addedOptionsArray array', () => {
            field.addedOptionsArray = ['Smart Guide', 'Smart Guide Stage'];

            field.populateAddedOptionsArray('Smart Guide Activities');
            expect(field.addedOptionsArray).toEqual(['Smart Guide', 'Smart Guide Stage', 'Smart Guide Activities']);
        });

        it('should remove option from addedOptionsArray array', () => {
            field.addedOptionsArray = ['Smart Guide', 'Smart Guide Stage'];

            field.populateAddedOptionsArray('Smart Guide', 'remove');
            expect(field.addedOptionsArray).toEqual(['Smart Guide Stage']);
        });

        it('should call the populateAddedFieldsDefs function', () => {
            field.populateAddedOptionsArray('option');
            expect(field.populateAddedFieldsDefs).toHaveBeenCalledWith('add', 'option', undefined);
        });

        it('should not add duplicated option to addedOptionsArray array', () => {
            field.addedOptionsArray = ['Smart Guide', 'Smart Guide Activities'];

            field.populateAddedOptionsArray('Smart Guide Activities');
            expect(field.addedOptionsArray).toEqual(['Smart Guide', 'Smart Guide Activities']);
        });
    });

    describe('removeSelectTo', () => {
        beforeEach(() => {
            field.value = [
                {id: 'test_id_1'},
                {id: 'test_id_2'},
                {id: 'test_id_3'}
            ];
            field.populateFieldsMetaData[`${field.targetFieldName}_1`] = 'test-value-1';
            field.actionFieldsMetaData[`${field.actionFieldName}_2`] = 'test-value-2';

            sinon.stub(field, 'populateAddedOptionsArray');
            sinon.stub(field, 'populateAddedActionsArray');
            sinon.stub(field, '_disposeSubFields');
            sinon.stub(field, '_updateSubFieldModel');
            sinon.stub(field, '_updateAndTriggerChange');
            sinon.stub(field, '_render');
        });

        it('should call populateAddedOptionsArray function', () => {
            field.removeSelectTo(1);
            expect(field.populateAddedOptionsArray).toHaveBeenCalledWith(`${field.targetFieldName}_1`, 'remove');
        });

        it('should call populateAddedActionsArray function', () => {
            field.removeSelectTo(2);
            expect(field.populateAddedActionsArray).toHaveBeenCalledWith(`${field.actionFieldName}_2`, 'remove');
        });

        it('should call _disposeSubFields function', () => {
            field.removeSelectTo(1);
            expect(field._disposeSubFields).toHaveBeenCalledWith('test-value-1');
        });

        it('should call _disposeSubFields function', () => {
            field.removeSelectTo(2);
            expect(field._disposeSubFields).toHaveBeenCalledWith('test-value-2');
        });

        it('should call _updateSubFieldModel function', () => {
            field.removeSelectTo(1);
            expect(field._updateSubFieldModel).toHaveBeenCalledWith(1);
        });

        it('should remove element from value at 0 index', () => {
            field.removeSelectTo(0);
            expect(field.value[0].id).toBe('test_id_2');
        });
    });

    describe('_updateSubFieldModel', () => {
        beforeEach(() => {
            field.value = [
                {id: '111-111-111', value: 'Test Stage'},
                {id: '222-222-222', value: 'Test Activity'}
            ];

            field.model.set(`${field.targetFieldName}_0`, 'Test Stage');
            field.model.set(`${field.targetFieldName}_1`, 'Test Activity');
            field.model.set(`${field.targetFieldName}_2`, 'Test Second Stage');

            field.model.set(`${field.actionFieldName}_0`, '111-111-111');
            field.model.set(`${field.actionFieldName}_1`, '222-222-222');
            field.model.set(`${field.actionFieldName}_2`, 'complete_all');
        });

        it('should update target field model', () => {
            field._updateSubFieldModel(1);
            expect(field.model.get(`${field.targetFieldName}_1`)).toBe('Test Second Stage');
        });

        it('should update action field model', () => {
            field._updateSubFieldModel(1);
            expect(field.model.get(`${field.actionFieldName}_1`)).toBe('complete_all');
        });

        it('should unset target field model', () => {
            field._updateSubFieldModel(1);
            expect(field.model.get(`${field.targetFieldName}_2`)).toBe(undefined);
        });

        it('should unset action field model', () => {
            field._updateSubFieldModel(1);
            expect(field.model.get(`${field.actionFieldName}_2`)).toBe(undefined);
        });
    });

    describe('bindEventsForSubFields', () => {
        it('should call _bindSubFieldsModelEventHelper function', () => {
            sinon.stub(field, '_bindSubFieldsModelEventHelper');
            let addedOptionsArray = ['test_field_id'];
            let populateFieldsMetaData = {
                test_field_id: {
                    name: 'cj-target-action',
                    type: 'enum',
                }
            };

            field.bindEventsForSubFields(addedOptionsArray, populateFieldsMetaData);
            expect(field._bindSubFieldsModelEventHelper).toHaveBeenCalledWith('cj-target-action', 'test_field_id');
        });
    });

    describe('_traverseValueForSingleOption', () => {
        let option;

        beforeEach(() => {
            sinon.stub(field, 'getRelateFieldModelData').returns('test_id_value');

            option = {id: 'test_option_id'};
            field.populateFieldsMetaData = {
                test_option_id: {
                    name: 'option_name',
                },
            };
        });

        it('should set the value attribute of option variable', () => {
            field._traverseValueForSingleOption(option);
            expect(option.value).toEqual('test_id_value');
        });

        it('should not call getRelateFieldModelData function', () => {
            field._traverseValueForSingleOption();
            expect(field.getRelateFieldModelData).not.toHaveBeenCalled();
        });

        it('should call getRelateFieldModelData function', () => {
            field._traverseValueForSingleOption(option, 0);
            expect(field.getRelateFieldModelData).toHaveBeenCalledWith(`${field.actionFieldName}_0`);
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
            let event = {currentTarget: 'test-value'};
            field.value = [{id: 'Smart Guide', action_id: 'in_progress'}];

            field.addItem(event);
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
            let event = {currentTarget: 'test-value'};

            field.removeItem(event);
            expect(field.removeSelectTo).toHaveBeenCalledWith(1);
        });
    });
});
