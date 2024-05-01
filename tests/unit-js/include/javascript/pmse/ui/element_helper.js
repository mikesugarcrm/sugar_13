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
describe('includes.javascript.pmse.ui.element_helper', function() {

    var helper;

    afterEach(function() {
        sinon.restore();
    });

    describe('init', function() {

        it('Check ElementHelper initialization', function() {
            var params = {
                mode: 'EmailPickerField',
                decimalSeparator: ',',
                numberGroupingSeparator: '.'
            };
            helper = new PMSE.ElementHelper(params);
            helper.OPERATORS.comparison = [
                {
                    value: 'equals'
                }
            ];
            helper.OPERATORS.changes = [
                {
                    value: 'changes_to'
                }
            ];
            expect(helper._decimalSeparator).toEqual(params.decimalSeparator);
            expect(helper._numberGroupingSeparator).toEqual(params.numberGroupingSeparator);
            expect(PROJECT_MODULE).toEqual('Leads');
        });

    });

    describe('fieldDependencyHandler', function() {

        var module = new FormPanelDropdown({
            name: 'module',
            dependantFields: ['field', 'emailAddressField']
        });
        var field = new FormPanelDropdown({
            name: 'field',
            disabled: true
        });
        var email = new FormPanelDropdown({
            name: 'emailAddressField',
            disabled: true
        });
        var ret = {type: 'many'};

        beforeEach(function() {
            sinon.stub(FormPanelDropdown.prototype, 'getSelectedData').returns(ret);
            sinon.stub(PMSE.ElementHelper.prototype, 'loadFieldControl');
        });

        it('Check logic of disabling the "field" field', function() {
            helper.fieldDependencyHandler(field, module, '');
            expect(field._disabled).toEqual(true);
            helper.fieldDependencyHandler(field, module, 'Leads');
            expect(field._disabled).toEqual(true);
            ret.type = 'one';
            helper.fieldDependencyHandler(field, module, 'Accounts');
            expect(field._disabled).toEqual(true);
            ret.type = 'many';
            helper.fieldDependencyHandler(field, module, 'Accounts');
            expect(field._disabled).toEqual(false);
        });

        it('Check logic of not disabling the "emailAddressField" field', function() {
            helper.fieldDependencyHandler(email, module, '');
            expect(email._disabled).toEqual(false);
            helper.fieldDependencyHandler(email, module, 'Leads');
            expect(email._disabled).toEqual(false);
            ret.type = 'one';
            helper.fieldDependencyHandler(email, module, 'Accounts');
            expect(email._disabled).toEqual(false);
            ret.type = 'many';
            helper.fieldDependencyHandler(email, module, 'Accounts');
            expect(email._disabled).toEqual(false);
        });

    });

    describe('valueDependencyHandler', function() {
        var field = new FormPanelDropdown({
            name: 'field',
            disabled: true,
            dependantFields: ['value']
        });
        var operator = new FormPanelDropdown({
            name: 'operator',
            disabled: true,
            dependantFields: ['value']
        });
        var value = new FormPanelText({
            name: 'value',
            disabled: true
        });
        var ret = {operator: operator, value: value};

        beforeEach(function() {
            sinon.stub(PMSE.ElementHelper.prototype, 'doValueDependency').returns(ret);
        });

        it('Check logic of disabling the "operator" and "value" fields', function() {
            helper.valueDependencyHandler(value, field, 'Created By');
            expect(operator._disabled).toEqual(true);
            expect(value._disabled).toEqual(true);
            field.enable();
            helper.valueDependencyHandler(value, field, 'Created By');
            expect(operator._disabled).toEqual(false);
            expect(value._disabled).toEqual(false);
        });
    });

    describe('relatedDependencyHandler', function() {

        var module = new FormPanelDropdown({
            name: 'module',
            dependantFields: ['related']
        });
        var related = new FormPanelDropdown({
            name: 'related',
            disabled: true
        });

        beforeEach(function() {
            sinon.stub(PMSE.ElementHelper.prototype, 'loadRelatedControl');
        });

        it('Check logic of disabling the "related" field', function() {
            helper.relatedDependencyHandler(related, module, '');
            expect(related._disabled).toEqual(true);
            helper.relatedDependencyHandler(related, module, 'Leads');
            expect(related._disabled).toEqual(true);
            helper.relatedDependencyHandler(related, module, 'Accounts');
            expect(related._disabled).toEqual(false);
        });

    });

    describe('processValueDependency', function() {
        var parentField;
        var dependantField;
        var operatorField;
        var form;
        var body;
        var moduleField;
        var optionOne;
        var optionTwo;
        var optionThree;
        var radioAll;
        var radioAny;
        var items;
        var ret = new FormPanelDate();

        var checkProcessValueDependency = function(addChanges, disableField, selVal) {
            body.appendChild(radioAll);
            body.appendChild(radioAny);
            body[1] = radioAll;
            body[2] = radioAny;

            form._htmlBody = body;
            form._htmlBody.length = 3;
            form.id = 'form-module-field-evaluation';
            form.getItem('module').html = body[0];

            helper._parent = new ExpressionControl({});
            helper._parent._name = 'evn_criteria';
            helper.processValueDependency(dependantField, parentField, operatorField, 'date', selVal, form);
            var operatorExist = checkOperator(operatorField, 'changes_to');

            expect(operatorExist).toEqual(addChanges);
            expect(parentField._disabled).toEqual(disableField);
        };

        var checkOperator = function(operatorField, operator) {
            var op = false;
            if (operatorField._htmlControl && operatorField._htmlControl[0]) {
                for (var i = 0; i < operatorField._htmlControl[0].length; i++) {
                    if (operatorField._htmlControl[0][i].value === operator) {
                        op = true;
                        break;
                    }
                }
            }
            return op;
        };
        var ret = new FormPanelDate();
        beforeEach(function() {
            parentField = new FormPanelDropdown({
                _name: 'field'
            });

            dependantField = new FormPanelText({
                _name: 'value',
                _label: 'Value',
                id: '123'
            });

            operatorField = new FormPanelDropdown({
                _name: 'operator'
            });

            operatorField.html = {innerHTML: '<div/>'};
            operatorField._htmlControl[0] = document.createElement('select');

            form = new FormPanel();

            items = [
                {type: 'dropdown', name: 'module', dependantFields: ['rel', 'field']},
                {type: 'radiobutton', name: 'rel'},
                {type: 'dropdown', name: 'field'},
                {type: 'dropdown', name: 'operator'},
                {type: 'text', name: 'value', id: '123'}
            ];
            for (var i = 0; i < items.length; i++) {
                form.addItem(items[i]);
            }

            form.getItem('module').addOption({module: 'Tasks', type: '<Tasks>', value: 'Tasks'});
            form.getItem('module').addOption({module: 'Accounts', type: 'one', value: 'accounts'});
            form.getItem('module').addOption({module: 'Calls', type: 'many', value: 'calls'});

            body = document.createElement('div');
            moduleField = document.createElement('select');

            optionOne = document.createElement('option');
            $(optionOne).data('data', {module: 'Tasks', type: '<Tasks>', value: 'Tasks'});
            optionTwo = document.createElement('option');
            $(optionTwo).data('data', {module: 'Accounts', type: 'one', value: 'accounts'});
            optionThree = document.createElement('option');
            $(optionThree).data('data', {module: 'Calls', type: 'many', value: 'calls'});

            optionOne.setAttribute('value', 'Tasks');
            optionTwo.setAttribute('value', 'Accounts');
            optionThree.setAttribute('value', 'Calls');

            moduleField.appendChild(optionOne);
            moduleField.appendChild(optionTwo);
            moduleField.appendChild(optionThree);
            body.appendChild(moduleField);
            body[0] = moduleField;

            radioAll = document.createElement('input');
            radioAll.setAttribute('type', 'radio');
            radioAll.setAttribute('value', 'All');
            radioAny = document.createElement('input');
            radioAny.setAttribute('type', 'radio');
            radioAny.setAttribute('value', 'Any');

            sinon.stub(FormPanel.prototype, '_createField').returns(ret);
        });

        describe('multiselect fields', function() {
            var type = 'multiselect';
            var parent;
            var elementHelper;
            beforeEach(function() {
                elementHelper = new PMSE.ElementHelper({});
                parent =  new FormPanelDropdown({
                    _name: 'field',
                    dataURL: 'pmse_Project/CrmData/fields/Contacts'
                });
                parent.setAttributes({base_module: 'Contacts', call_type: 'PD'});
                sinon.stub(parent, 'getSelectedData').returns({optionItem: []});
            });

            it('should include the correct operators for New Records Only', function() {
                var setVal = 'new';
                elementHelper.processValueDependency(dependantField, parent, operatorField, type, setVal, form);
                expect(checkOperator(operatorField, 'equals')).toBeTruthy();
                expect(checkOperator(operatorField, 'not_equals')).toBeTruthy();
                expect(checkOperator(operatorField, 'array_has_any')).toBeTruthy();
                expect(checkOperator(operatorField, 'array_has_none')).toBeTruthy();
                // Multiselects have only 4 operators on New Records Only
                expect(operatorField._htmlControl[0].length).toEqual(4);
            });

            it('should include the correct operators for updated records', function() {
                var setVal = 'updated';
                elementHelper.processValueDependency(dependantField, parent, operatorField, type, setVal, form);
                expect(checkOperator(operatorField, 'equals')).toBeTruthy();
                expect(checkOperator(operatorField, 'not_equals')).toBeTruthy();
                expect(checkOperator(operatorField, 'array_has_any')).toBeTruthy();
                expect(checkOperator(operatorField, 'array_has_none')).toBeTruthy();
                // Multiselects have 7 operators for first update and all updates
                expect(operatorField._htmlControl[0].length).toEqual(7);
            });
        });

        describe('relate fields', function() {
            var type = 'related';
            var parent;
            var elementHelper;

            beforeEach(function() {
                elementHelper = new PMSE.ElementHelper({});
                parent =  new FormPanelDropdown({
                    _name: 'field',
                    dataURL: 'pmse_Project/CrmData/fields/Contacts'
                });
                parent.setAttributes({base_module: 'Contacts', call_type: 'PD'});
            });

            it('should include the basic operators for most fields', function() {
                sinon.stub(parent, 'getSelectedData').returns({
                    optionItem: [],
                    value: 'account_name'
                });

                elementHelper.processValueDependency(dependantField, parent, operatorField, type, 'new', form);
                expect(checkOperator(operatorField, 'equals')).toBeTruthy();
                expect(checkOperator(operatorField, 'not_equals')).toBeTruthy();
                expect(operatorField._htmlControl[0].length).toEqual(2);
            });

            it('should include additional operators for team name', function() {
                sinon.stub(parent, 'getSelectedData').returns({
                    optionItem: [],
                    value: 'team_name'
                });

                elementHelper.processValueDependency(dependantField, parent, operatorField, type, 'new', form);
                expect(checkOperator(operatorField, 'equals')).toBeTruthy();
                expect(checkOperator(operatorField, 'not_equals')).toBeTruthy();
                expect(checkOperator(operatorField, 'array_has_any')).toBeTruthy();
                expect(checkOperator(operatorField, 'array_has_none')).toBeTruthy();
                expect(operatorField._htmlControl[0].length).toEqual(4);
            });
        });

        describe('dropdown fields', function() {
            var type = 'dropdown';
            var parent;
            var elementHelper;
            beforeEach(function() {
                elementHelper = new PMSE.ElementHelper({});
                parent =  new FormPanelDropdown({
                    _name: 'field',
                    dataURL: 'pmse_Project/CrmData/fields/Contacts'
                });
                parent.setAttributes({base_module: 'Contacts', call_type: 'PD'});
                sinon.stub(parent, 'getSelectedData').returns({optionItem: []});
            });

            it('should include the correct operators for New Records Only', function() {
                var setVal = 'new';
                elementHelper.processValueDependency(dependantField, parent, operatorField, type, setVal, form);
                expect(checkOperator(operatorField, 'equals')).toBeTruthy();
                expect(checkOperator(operatorField, 'not_equals')).toBeTruthy();
                // Dropdowns don't have array_has_any operator
                expect(checkOperator(operatorField, 'array_has_any')).toBeFalsy();
                // Dropdowns have only 2 operators on New Records Only
                expect(operatorField._htmlControl[0].length).toEqual(2);

            });

            it('should include the correct operators for updated records', function() {
                var setVal = 'updated';
                elementHelper.processValueDependency(dependantField, parent, operatorField, type, setVal, form);
                expect(checkOperator(operatorField, 'equals')).toBeTruthy();
                expect(checkOperator(operatorField, 'not_equals')).toBeTruthy();
                // Dropdowns don't have array_has_any operator
                expect(checkOperator(operatorField, 'array_has_any')).toBeFalsy();
                // Dropdowns have 5 operators for first update and all updates
                expect(operatorField._htmlControl[0].length).toEqual(5);
            });
        });

        it('Include changes operator and enable field when a target module is chosen', function() {

            // Tasks module is chosen that is a target (base) module
            optionOne.setAttribute('selected', true);
            parentField._attributes = {base_module: 'Tasks'};

            // addChanges = true, disableField = false
            checkProcessValueDependency(true, false, undefined);
        });

        it('Include changes operator and enable field when a target module is chosen for start events', function() {

            // Tasks module is chosen that is a target (base) module
            optionOne.setAttribute('selected', true);
            parentField._attributes = {base_module: 'Tasks'};

            // addChanges = false, disableField = false
            checkProcessValueDependency(false, false, 'allupdates');
        });

        it('Include changes operator and enable field when an one related module is chosen', function() {

            // Accounts module is chosen that is an one related module to Tasks (base module)
            optionTwo.setAttribute('selected', true);
            parentField._attributes = {base_module: 'Tasks'};

            // addChanges = true, disableField = false
            checkProcessValueDependency(true, false, undefined);
        });

        it('Include changes operator and enable field when an one related module is chosen\ ' +
            'for start events', function() {

                // Accounts module is chosen that is an one related module to Tasks (base module)
                optionTwo.setAttribute('selected', true);
                parentField._attributes = {base_module: 'Tasks'};

                // addChanges = false, disableField = false
                checkProcessValueDependency(false, false, 'allupdates');
        });

        it('Exclude changes operator/disable field when many related module/no related record is chosen', function() {

            // Calls module is chosen that is a many related module to Tasks (base module)
            // and no related record is chosen
            optionThree.setAttribute('selected', true);
            parentField._attributes = {base_module: 'Tasks'};

            // addChanges = false, disableField = true
            checkProcessValueDependency(false, true, undefined);
        });

        it('Exclude changes operator/disable field when many related module/no related record is chosen\ ' +
            'for start events', function() {

                // Calls module is chosen that is a many related module to Tasks (base module)
                // and no related record is chosen
                optionThree.setAttribute('selected', true);
                parentField._attributes = {base_module: 'Tasks'};

                // addChanges = false, disableField = true
                checkProcessValueDependency(false, true, 'updated');
        });

        it('Include changes operator/enable field when many related module/any related records are chosen', function() {

            // Calls module is chosen that is a many related module to Tasks (base module)
            // and ANY related records are chosen
            optionThree.setAttribute('selected', true);
            parentField._attributes = {base_module: 'Tasks'};
            radioAny.setAttribute('checked', true);

            // addChanges = true, disableField = false
            checkProcessValueDependency(true, false, undefined);
        });

        it('Include changes operator/enable field when many related module/any related records are chosen\ ' +
            'for start events', function() {

                // Calls module is chosen that is a many related module to Tasks (base module)
                // and ANY related records are chosen
                optionThree.setAttribute('selected', true);
                parentField._attributes = {base_module: 'Tasks'};
                radioAny.setAttribute('checked', true);

                // addChanges = false, disableField = false
                checkProcessValueDependency(false, false, 'updated');
        });

        it('Exclude changes operator/enable field when many related module/all related records are chosen', function() {

            // Tasks module is chosen that is a many related module to Tasks (base module)
            // and ALL related records are chosen
            optionThree.setAttribute('selected', true);
            parentField._attributes = {base_module: 'Tasks'};
            radioAll.setAttribute('checked', true);

            // addChanges = false, disableField = false
            checkProcessValueDependency(false, false, undefined);
        });

        it('Exclude changes operator/enable field when many related module/all related records are chosen\ ' +
            'for start events', function() {

                // Tasks module is chosen that is a many related module to Tasks (base module)
                // and ALL related records are chosen
                optionThree.setAttribute('selected', true);
                parentField._attributes = {base_module: 'Tasks'};
                radioAll.setAttribute('checked', true);

                // addChanges = false, disableField = false
                checkProcessValueDependency(false, false, null);
        });
    });

    describe('processValueDependency', function() {
        var parentField;
        var dependantField;
        var operatorField;
        beforeEach(function() {
            parentField = new FormPanelDropdown({
                _name: 'field'
            });
            dependantField = new FormPanelText({
                _name: 'value',
                _label: 'Value',
            });
            operatorField = new FormPanelDropdown({
                _name: 'operator'
            });
        });

        it('Check relate field has Search and Select criteria set', function() {
            helper._parent = new ExpressionControl({});
            helper._parent._name = 'evn_criteria';

            var select = document.createElement('select');
            select.id = 'testSelect';
            var option1 = document.createElement('option');
            option1.text = 'Name';
            option1.value = 'name';
            select.add(option1, null);

            var option2 = document.createElement('option');
            option2.text = 'Account Id';
            option2.value = 'account_id';
            $(option2).data('data', {module: 'Cases', type: '<Cases>', value: 'account_id'});
            select.add(option2, null);
            select.selectedIndex = '1';

            parentField.html = select;
            sinon.stub(App.data, 'createBean').returns({fields: {account_id: {module: 'Cases'}}});
            var ret = new FormPanelFriendlyDropdown();
            var createFieldStub = sinon.stub(FormPanel.prototype, '_createField').returns(ret);
            var form = new FormPanel();

            helper.processValueDependency(
                dependantField,
                parentField,
                operatorField,
                'related',
                null,
                form
            );

            expect(createFieldStub.args[0][0].searchValue).toEqual('id');
            expect(createFieldStub.args[0][0].searchLabel).toEqual('name');
            expect(createFieldStub.args[0][0].searchURL).toEqual('Cases?filter[0][$and][1][$or][0][name][$starts]=' +
                '{%TERM%}&fields=id,name&max_num={%PAGESIZE%}&offset={%OFFSET%}');
        });
    });
});
