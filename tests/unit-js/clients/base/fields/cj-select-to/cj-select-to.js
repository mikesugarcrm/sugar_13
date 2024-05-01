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
describe('Base.Field.CjSelectTo', function() {
    let app;
    let model;
    let initOptions;
    let fieldName = 'cj_select_to';
    let context;
    let field;

    function createField(model) {
        return SugarTest.createField('base', fieldName, 'cj-select-to');
    }

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = new Backbone.Model();
        SugarTest.loadComponent('base', 'field', 'base');
        field = createField(model);
        SugarTest.testMetadata.set();
        context = new app.Context();
        context.set('model', new Backbone.Model());
        context.prepare();
        context.parent = app.context.getContext();
        initOptions = {
            context: context,
        };
        sinon.stub(field, '_super');
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        field = null;
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete app.drawer;
    });

    describe('initialize', function() {
        using('input', [
            {
                allowed_participants: 'specific_users',
                expected_value: 'cj_select_to_cj-select-to_specific_user_name',
                expected_variable: 'name',
            },
            {
                allowed_participants: 'specific_contacts',
                expected_value: 'relate',
                expected_variable: 'type',
            },
            {
                allowed_participants: 'related_parent_users',
                expected_value: 'cj_blank_list',
                expected_variable: 'options',
            },
            {
                allowed_participants: 'related_parent_contacts',
                expected_value: 'Contacts',
                expected_variable: 'relatedModuleForOptions',
            },
        ],

        function(input) {
            it('should call the initialize function and initialze some properties', function() {
                let participants = input.allowed_participants;
                let variable = input.expected_variable;
                sinon.stub(field, '_prepareOptionsList');
                field.initialize(initOptions);
                expect(field.subFieldsMapping[participants][variable]).toBe(input.expected_value);
                expect(field._prepareOptionsList).toHaveBeenCalled();
                expect(field.dropdownName).toBe('cj_select_to_options_list');
                expect(field.optionListFieldTag).toBe('select.select2[name*=cj_select_to_cj-select-to__options]');
            });
        });
    });

    describe('_prepareOptionsList', function() {
        it('should call the  _prepareOptionsList function to get the options list', function() {
            sinon.stub(app.lang, 'getAppListStrings').returns('{}');
            field._prepareOptionsList();
            expect(field.optionsList).toBe('{}');
            expect(app.lang.getAppListStrings).toHaveBeenCalled();
        });
    });

    describe('_loadTemplate', function() {
        it('should change default behavior when doing inline editing on a List view', function() {
            field.view.meta = {
                'header_label': 'LBL_CONFIGURE_RECORDVIEW_DISPLAY_TITLE',
                'fields': [
                    {
                        'name': 'enabled_modules',
                        'label': 'LBL_ENABLED_MODULES',
                        'type': 'enum',
                    },
                ],
                template: 'Template',
            };
            sinon.stub(app.template, 'getField').returns(field);
            field._loadTemplate();
            expect(field.template).toBe(field);
            expect(field._super).toHaveBeenCalledWith('_loadTemplate');
            expect(app.template.getField).toHaveBeenCalled();
        });
    });

    describe('_render', function() {
        it('should call renderMainDropdown,renderSubFields functions properly', function() {
            field.tplName = 'edit';
            field.callRenderEnumFieldsOptions = true;
            field.value = {
                length: 0,
            };
            sinon.stub(field, 'addSelectTo');
            sinon.stub(field, '_renderMainDropdown');
            sinon.stub(field, '_renderSubFields');
            sinon.stub(field, '_renderEnumFieldsOptions');
            sinon.stub(field, '_renderEnumFields');
            expect(field._render()).toBe(field);
            expect(field.addSelectTo).toHaveBeenCalled();
            expect(field._super).toHaveBeenCalledWith('_render');
            expect(field._renderSubFields).toHaveBeenCalled();
            expect(field._renderEnumFieldsOptions).toHaveBeenCalled();
            expect(field._renderEnumFields).toHaveBeenCalled();
            expect(field._renderMainDropdown).toHaveBeenCalled();
            expect(field.callRenderEnumFieldsOptions).toBe(false);
        });
    });

    describe('_renderEnumFields', function() {
        it('should Render the enum fields against the relate field', function() {
            sinon.stub(field, '_getAvailableModulesApiURL').returns('www.google.com');
            sinon.stub(app.api, 'call');
            field._renderEnumFields();
            expect(app.api.call).toHaveBeenCalled();
            expect(field._getAvailableModulesApiURL).toHaveBeenCalled();
        });
    });

    describe('moduleReadSuccess', function() {
        it('should Render the enum fields against the relate field', function() {
            field.templateAvailableModules = 'modules';
            let availableModules = ['Contacts,Accounts,Opportunities,Cases,Meetings,Leads'];
            sinon.stub(field, '_renderEnumFieldsOptions').returns('www.google.com');
            sinon.stub(field, '_render');
            field.moduleReadSuccess(availableModules);
            expect(field._renderEnumFieldsOptions).toHaveBeenCalled();
            expect(field._render).toHaveBeenCalled();
            expect(field.templateAvailableModules).toBe(availableModules);
            expect(field.callRenderEnumFieldsOptions).toBe(true);
        });
    });

    describe('moduleReadSuccess', function() {
        it('should show alert On Error', function() {
            field.templateAvailableModules = 'modules';
            let error = {
                message: 'error404',
            };
            sinon.stub(app.alert, 'show');
            field.moduleReadError(error);
            expect(app.alert.show).toHaveBeenCalled();
        });
    });

    describe('_renderEnumFieldsOptions', function() {
        it('should render the enum fields options against the relate fields', function() {
            field.subFieldsMapping = {
                'subField': {
                    type: 'enum',
                },
                'optionName': 'optionName',
            };
            sinon.stub(field, '_renderEnumFieldOptions');
            field._renderEnumFieldsOptions();
            expect(field._renderEnumFieldOptions).toHaveBeenCalled();
        });
    });

    describe('_renderEnumFieldOptions', function() {
        it('should render the enum field options against the relate fields', function() {
            field.templateAvailableModules = ['Contacts,Accounts,Opportunities,Cases,Meetings,Leads'];
            let subField = {
                name: 'specific_contact_name',
            };
            let optionName = 'optionName';
            field.view = {
                getField: function() {
                    return field;
                },
            };
            field.value = [
                currentOption = {
                    id: 'related_parent_users',
                },
                index = {
                    id: 'optionName',
                },
                list = {
                    id: 'optionName',
                },
            ];
            sinon.stub(field, '_getEnumOptions');
            sinon.stub(field, 'setMode');
            sinon.stub(field, 'render');
            sinon.stub(field, '_setFormattedIdsAndRnames');
            field._renderEnumFieldsOptions(subField, optionName);
            expect(field._getEnumOptions).toHaveBeenCalled();
            expect(field.setMode).toHaveBeenCalled();
            expect(field.render).toHaveBeenCalled();
            expect(field._setFormattedIdsAndRnames).toHaveBeenCalled();
        });
    });

    describe('_getEnumOptions', function() {
        it('should return the relate fields names from the availablemodules', function() {
            let availableModules = {
                module: ['Contacts,Accounts,Opportunities,Cases,Meetings,Leads'
                ],
            };
            field.fields = {
                'field': {
                    type: 'relate',
                    module: 'Contacts',
                    vname: 'cj-select-to',
                    label: 'cj-select-to'
                },
            };

            let subField = {
                relatedModuleForOptions: 'Contacts',
            };
            sinon.stub(app.metadata, 'getModule').returns(field);
            sinon.stub(app.lang, 'get');
            field._getEnumOptions(availableModules, subField);
            expect(app.metadata.getModule).toHaveBeenCalled();
            expect(app.lang.get).toHaveBeenCalled();
        });
    });

    describe('_renderMainDropdown', function() {
        it('should Render the Main dropdown that have certian', function() {
            field.tplName = 'edit';
            sinon.stub(app.acl, 'hasAccessToModel').returns(false);
            field._renderMainDropdown();
            expect(app.acl.hasAccessToModel).toHaveBeenCalled();
        });
    });

    describe('handleChange', function() {
        it('should hide or show cj_selective_date_type based on if value is relative', function() {
            let e = {
                val: '99',
                removed: {
                    id: '99',
                },
                currentTarget: {
                    getAttribute: function() {
                        return 0;
                    },
                },
            };
            field.value = [
                currentOption = {
                    id: '99',
                },
                formattedIds = 0,
            ];
            sinon.stub(field, 'populateAddedOptionsArray');
            sinon.stub(field, '_updateAndTriggerChange');
            field.handleChange(e);
            expect(field.populateAddedOptionsArray).toHaveBeenCalled();
            expect(field._updateAndTriggerChange).toHaveBeenCalled();
        });
    });

    describe('_renderMainDropdown', function() {
        it('should render the SubFields against the Main dropdown field', function() {
            field.value = 'value';
            field.subFieldsMapping = {
                'subField': {
                    type: 'enum',
                    name: 'cj-select-to',
                },
                'optionName': 'optionName',
                'list': 'list',
            };
            sinon.stub(field, '_setSubFieldsModelData');
            sinon.stub(field, '_renderSubField');
            field._renderSubFields();
            expect(field._setSubFieldsModelData).toHaveBeenCalled();
            expect(field._renderSubField).toHaveBeenCalled();
        });
    });

    describe('_renderSubField', function() {
        it('should render the SubField according to action', function() {
            let subField = {
                type: 'enum',
                name: 'cj-select-to',
            };
            field.view = {
                getField: function() {
                    return field;
                }
            };
            sinon.stub(field.$el, 'find').returns(5);
            field._renderSubField(subField);
            expect(field.$el.find).toHaveBeenCalled();
        });
    });

    describe('_setSubFieldsModelData', function() {
        it('should set the subFields data in model for edit view only', function() {
            field.tplName = 'edit';
            field.view.action = 'edit';
            field.model = new Backbone.Model();
            field.value = [
                currentOption = {
                    type: 'enum',
                    name: 'cj-select-to',
                    id: '0',
                },
                index = 'optionName',
                list = 'list',
            ];
            field.subFieldsMapping = [
                currentOption = {
                    type: 'enum',
                    name: 'cj-select-to',
                    id: '0',
                },
                index = 'optionName',
                list = 'list',
            ];
            field.context.set('copiedFromModelId','copiedFromModelId');
            sinon.stub(field, '_convertDefaultStringInToArray');
            field._setSubFieldsModelData();
            expect(field._convertDefaultStringInToArray).toHaveBeenCalled();
        });
    });

    describe('_convertDefaultStringInToArray', function() {
        it('should convert the string into array according to separator', function() {
            let defaultString = 'string';
            sinon.stub(_, 'isString').returns(true);
            expect(field._convertDefaultStringInToArray(defaultString)).toEqual(['string']);
            expect(_.isString).toHaveBeenCalled();
        });
    });

    describe('_disposeSubFields', function() {
        it('should dispose the field', function() {
            let fieldDef = {
                name: 'cj-select-to',
            };
            field.view = {
                getField: function() {
                    return field;
                },
            };
            sinon.stub(field, 'dispose');
            sinon.stub(field, '_resetSubFieldModel');
            field._disposeSubFields(fieldDef);
            expect(field.dispose).toHaveBeenCalled();
            expect(field._resetSubFieldModel).toHaveBeenCalled();
        });
    });

    describe('_resetSubFieldsModel', function() {
        it('should unset the model data of all subfields', function() {
            field.subFieldsMapping = {
                'subField': {
                    type: 'enum',
                    name: 'cj-select-to',
                },
                'optionName': 'optionName',
                'list': 'list',
            };
            sinon.stub(field, '_resetSubFieldsModel');
            field._resetSubFieldsModel();
            expect(field._resetSubFieldsModel).toHaveBeenCalled();
        });
    });

    describe('_resetSubFieldModel', function() {
        it('should unset the model data of particular subfields', function() {
            let subField = {
                id_name: 'cj',
                name: 'cj-select-to',
            };
            sinon.stub(field.model, 'has').returns(true);
            field._resetSubFieldModel(subField);
            expect(field.model.has).toHaveBeenCalled();
            expect(field.model.get('cj-select-to')).toBe('');
            expect(field.model.get('cj')).toBe('');
        });
    });

    describe('_resetSubFieldModel', function() {
        it('should update value when a selection is made from options and set the value in model', function() {
            let mod = {
                id: '99',
            };
            sinon.stub(field, '_updateAndTriggerChange');
            field.setValue(mod);
            expect(field._updateAndTriggerChange).toHaveBeenCalled();
            expect(field.value).toEqual([{
                id: '99',
            }]);
        });
    });

    describe('getRelateFieldModelData', function() {
        it('should return the relate field model data', function() {
            let mod = 'cj-select-to';
            sinon.stub(field.model, 'has').returns(true);
            sinon.stub(field.model, 'get').returns(mod);
            expect(field.getRelateFieldModelData(mod)).toBe(mod);
            expect(field.model.has).toHaveBeenCalled();
            expect(field.model.get).toHaveBeenCalled();
        });
    });

    describe('format', function() {
        it('should parse value properly', function() {
            let mod = 99;
            expect(field.format(mod)).toBe(mod);
        });
    });

    describe('prepareData', function() {
        it('should prepare data according to the format which will be saved in DB', function() {
            field.value = [
                currentOption = {
                    remove_button: false,
                },
            ];
            sinon.stub(field.model, 'isNew').returns(true);
            sinon.stub(field.model, 'get').returns(true);
            sinon.stub(field, 'addButton');
            expect(field.prepareData()).toBe(JSON.stringify([{remove_button: true}]));
            expect(field.model.isNew).toHaveBeenCalled();
            expect(field.model.get).toHaveBeenCalled();
            expect(field.addButton).toHaveBeenCalled();
        });
    });

    describe('addButton', function() {
        it('should Add button if needed', function() {
            field.subFieldsMapping = [
                currentOption = {
                    type: 'enum',
                    name: 'cj-select-to',
                    id: '0',
                },
                index = 'optionName',
                list = 'list',
            ];
            field.value = [
                currentOption = {
                    remove_button: false,
                    id: '0',
                    add_button: false,
                    formattedIds: true,
                    formattedRename: true,
                },
                index = 'optionName',
                list = 'list',
            ];
            let result  = _.first(field.value);
            field.addButton(field.value);
            expect(result.remove_button).toBe(true);
        });
    });

    describe('getValueForNewOption', function() {
        it('should return the option on add new button', function() {
            field.optionsList = [
                key = {
                    key: '0',
                },
            ];
            sinon.stub(_, 'difference').returns(field.optionsList);
            expect(field.getValueForNewOption()).toEqual({key: '0'});
            expect(_.difference).toHaveBeenCalled();
        });
    });

    describe('populateAddedOptionsArray', function() {
        it('should populate the addedOptionsArray', function() {
            let value = '99';
            let op = 'remove';
            sinon.stub(_, 'without').returns('options');
            sinon.stub(_, 'uniq').returns(true);
            field.populateAddedOptionsArray(value, op);
            expect(_.without).toHaveBeenCalled();
            expect(_.uniq).toHaveBeenCalled();
            expect(field.addedOptionsArray).toBe(true);
        });
    });

    describe('addSelectTo', function() {
        it('should add the selectTo option in model and on view and format the data accordingly', function() {
            field.value = [
                currentOption = {
                    type: 'enum',
                    name: 'cj-select-to',
                    id: '0',
                },
                index = 'optionName',
                list = 'list',
                length = '99',
            ];
            field.currentIndex = 0;
            sinon.stub(field, 'populateAddedOptionsArray');
            sinon.stub(field, 'setValue');
            field.addSelectTo();
            expect(field.populateAddedOptionsArray).toHaveBeenCalled();
            expect(field.populateAddedOptionsArray).toHaveBeenCalled();
            expect(field.currentIndex).toBe(5);
        });
    });

    describe('removeSelectTo', function() {
        it('should add the selectTo option in model and on view and format the data accordingly', function() {
            let index = 0;
            field.value = [
                currentOption = {
                    type: 'enum',
                    name: 'cj-select-to',
                    id: '0',
                },
            ];
            field.currentIndex = 5;
            sinon.stub(field, '_disposeSubFields');
            sinon.stub(field, '_updateAndTriggerChange');
            sinon.stub(field, 'populateAddedOptionsArray');
            field.removeSelectTo(index);
            expect(field._disposeSubFields).toHaveBeenCalled();
            expect(field._updateAndTriggerChange).toHaveBeenCalled();
            expect(field.currentIndex).toBe(4);
        });
    });

    describe('bindDataChange', function() {
        it('should call the bindDataChange function properly', function() {
            field.subFieldsMapping = {
                'subField': {
                    type: 'enum',
                    name: 'cj-select-to',
                },
                'optionName': 'optionName',
                'list': 'list',
            };
            sinon.stub(field, 'listenTo');
            sinon.stub(field, '_bindSubFieldsModelEvent');
            field.bindDataChange();
            expect(field.listenTo).toHaveBeenCalled();
            expect(field._bindSubFieldsModelEvent).toHaveBeenCalled();
        });
    });

    describe('_bindSubFieldsModelEvent', function() {
        it('should call the bindDataChange function properly', function() {
            let subField = {
                type: 'relate',
                name: 'cj-select-to',
                id_name: 'cj-select-to',
            };
            let option = 'options';
            sinon.stub(field, 'listenTo');
            field._bindSubFieldsModelEvent(subField, option);
            expect(field.listenTo).toHaveBeenCalled();
        });
    });

    describe('_traverseValueOnChange', function() {
        it('should traverse the value of field and call the formatter', function() {
            let subField = {
                type: 'relate',
                name: 'cj-select-to',
                id_name: 'cj-select-to',
            };
            let option = 'options';
            field.value = [
                currentOption = {
                    type: 'enum',
                    name: 'cj-select-to',
                    id: 'options',
                },
            ];
            sinon.stub(field, '_setFormattedIdsAndRnames');
            field._traverseValueOnChange(subField, option);
            expect(field._setFormattedIdsAndRnames).toHaveBeenCalled();
        });
    });

    describe('_setFormattedIdsAndRnames', function() {
        it('should format the Rnames and Ids', function() {
            let subField = {
                name: 'cj-select-to',
                id_name: 'cj_select_to',
            };
            let currentOption = {
                formattedRname: 'enum',
                formattedIds: 'cj-select-to',
            };
            sinon.stub(field.model, 'get').returns(subField.name);
            field._setFormattedIdsAndRnames(currentOption, subField);
            expect(field.model.get).toHaveBeenCalled();
        });

        it('should format the Ids', function() {
            let subField = {
                name: 'cj-select-to',
                id_name: '',
            };
            let currentOption = {
                formattedRname: 'enum',
                formattedIds: 'cj-select-to',
            };
            sinon.stub(field.model, 'get').returns(subField.name);
            sinon.stub(field, '_setFormattedRnamesForEnum');
            field._setFormattedIdsAndRnames(currentOption, subField);
            expect(field.model.get).toHaveBeenCalled();
            expect(field._setFormattedRnamesForEnum).toHaveBeenCalled();
        });
    });

    describe('_setFormattedRnamesForEnum', function() {
        it('should format the Rnames for Enum fields', function() {
            let subField = [
                {
                    name: 'cj-select-to',
                    id_name: 'cj_select_to',
                },
            ];
            let currentOption = {
                formattedIds: {
                    rname: 0,
                    index: 0,
                },
            };
            field.items = subField;
            sinon.stub(field.view, 'getField').returns(field);
            field._setFormattedRnamesForEnum(currentOption, subField);
            expect(field.view.getField).toHaveBeenCalled();
        });
    });

    describe('resetVariablesAndData', function() {
        it('should Reset the variables', function() {
            sinon.stub(field, '_prepareOptionsList');
            sinon.stub(field, '_resetSubFieldsModel');
            field.addedOptionsArray = {};
            field.currentIndex = 1;
            field.value = {};
            field.callRenderEnumFieldsOptions = false;
            field.templateAvailableModules = {};
            field.resetVariablesAndData();
            expect(field.addedOptionsArray).toEqual([]);
            expect(field.currentIndex).toBe(0);
            expect(field.value).toEqual([]);
            expect(field.callRenderEnumFieldsOptions).toBe(true);
            expect(field.templateAvailableModules).toEqual([]);
            expect(field._prepareOptionsList).toHaveBeenCalled();
            expect(field._resetSubFieldsModel).toHaveBeenCalled();
        });
    });

    describe('_dispose', function() {
        it('should reset variables and data properly', function() {
            sinon.stub(field, 'resetVariablesAndData');
            field._dispose();
            expect(field.resetVariablesAndData).toHaveBeenCalled();
            expect(field._super).toHaveBeenCalledWith('_dispose');
        });
    });

    describe('addItem', function() {
        it('should Trigger on Add button click', function() {
            let evt = {
                currentTarget: 'target',
            };
            sinon.stub(field, 'addSelectTo');
            field.addItem(evt);
            expect(field.addSelectTo).toHaveBeenCalled();
        });
    });
});
