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
describe('Base.Field.DRI_Workflow_Task_Templates.ActivityType', function() {
    let field;
    let populateFieldType = 'select-to-guests';
    let app;
    let populateField;
    let model;
    let fieldName = 'activity_type';
    let module = 'DRI_Workflow_Task_Templates';

    function createField(model) {
        let field;
        field = SugarTest.createField('base', fieldName, 'activity-type', 'detail', {
            options: 'dri_workflows_parent_type_list',
            items: {
                'Tasks': 'Current Task',
                'Calls': 'Current Call',
                'Meetings': 'Current Meeting',
            },
        }, module, model, null, true);
        return field;
    }

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean(module);
        field = createField(model);
        SugarTest.loadComponent('base', 'field', 'cj-select-to');
        model = app.data.createBean('DRI_Workflow_Task_Templates');
        populateField = SugarTest.createField(
            'base',
            'select_to_guests',
            populateFieldType,
            'detail',
            {},
            module,
            model,
            null,
            true
        );
        sinon.stub(field.view, 'getField').returns(populateField);
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        model.dispose();
        populateField.dispose();
        app = null;
        field = null;
        SugarTest.testMetadata.dispose();
    });

    describe('initialize', function() {
        it('should initialize the enum', function() {
            expect(field.type).toBe('enum');
        });
    });

    describe('modelSyncHandler', function() {
        it('should call hideOrShowGuestsField function', function() {
            sinon.stub(field, 'hideOrShowGuestsField');
            field.modelSyncHandler();
        });
    });

    describe('bindDataChangeHandler', function() {
        beforeEach(function() {
            sinon.stub(field, 'hideOrShowGuestsField');
        });
        it('should call bindDataChangeHandler function', function() {
            field.bindDataChangeHandler();
        });
    });

    describe('render', function() {
        it('should call hideOrShowRelatedDateFields function accordingly', function() {
            sinon.stub(field, 'hideOrShowGuestsField');
            field._render();
        });
    });

    describe('hideOrShowGuestsField', function() {
        using('input', [
            {
                activity_type: 'Calls',
                expected_value: '',
            },
            {
                activity_type: 'Meetings',
                expected_value: '',
            },
            {
                activity_type: 'Tasks',
                expected_value: '',
            },
        ],

        function(input) {
            it('The hideOrShowRelatedDateFields function', function() {
                field.model.set(fieldName, input.activity_type);
                field.hideOrShowGuestsField();
            });
        });
    });
});
