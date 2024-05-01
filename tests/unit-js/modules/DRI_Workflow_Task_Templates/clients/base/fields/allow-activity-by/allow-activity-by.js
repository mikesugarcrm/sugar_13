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
describe('Base.Field.DRI_Workflow_Task_Templates.AllowActivityBy', function() {
    let field;
    let model;
    let fieldType = 'cj-select-to';
    let app;
    let fieldName = 'allow_activity_by';
    let module = 'DRI_Workflow_Task_Templates';
    let viewName = 'record_view';

    function createField(model) {
        let field;
        field = SugarTest.createField('base', fieldName, 'allow-activity-by', 'detail', {
            dropdownName: 'cj_allow_activity_by_list',
            items: {
                '': '',
                'users': 'Users',
                'teams': 'Teams',
                'roles': 'Roles',
            },
        }, module, model, null, true);
        return field;
    }

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        model = app.data.createBean(module);
        SugarTest.loadComponent('base', 'field', 'cj-select-to');
        field = createField(model);
        model = app.data.createBean('DRI_Workflow_Task_Templates');
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        model.dispose();
        field = null;
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    describe('initialize', function() {
        using('input', [
            {
                allowed_participants: 'roles',
                expected_value: 'allow_activity_by_allow-activity-by_role_id',
                expected_variable: 'id_name',
            },
            {
                allowed_participants: 'teams',
                expected_value: 'Teams',
                expected_variable: 'module',
            },
            {
                allowed_participants: 'users',
                expected_value: 'allow_activity_by_allow-activity-by_user_name',
                expected_variable: 'name',
            },
        ],

        function(input) {
            it('should initialize the cj-select-to', function() {
                let participants = input.allowed_participants;
                let variable = input.expected_variable;
                sinon.stub(field, '_prepareOptionsList');
                expect(field.subFieldsMapping[participants][variable]).toBe(input. expected_value);
            });
        });
    });
});
