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
describe('Base.Field.CJ_WebHooks.TriggerEvent', function() {
    var field;
    var app;
    var fieldName = 'trigger-event';
    var module = 'CJ_WebHooks';

    function createField(model) {
        let field;
        field = SugarTest.createField('base', fieldName, 'trigger-event', 'edit', {
            options: 'cj_webhooks_trigger_event_list',
            items: {
                '': '',
                'before_create': 'Before Create',
                'after_create': 'After Create',
                'before_in_progress': 'Before In Progress',
                'after_in_progress': 'After In Progress',
                'before_completed': 'Before Completed',
                'after_completed': 'After Completed',
                'before_not_applicable': 'Before Not Applicable',
                'after_not_applicable': 'After Not Applicable',
                'before_delete': 'Before Delete',
                'after_delete': 'After Delete',
            },
        }, module, model, null, true);
        return field;
    }

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
    });

    describe('_render()', function() {
        it('should call the _modelChangeHandler function', function() {
            let model;
            model = app.data.createBean(module);
            SugarTest.loadComponent('base', 'field', 'base');
            field = createField(model);
            sinon.stub(field, '_modelChangeHandler');
            field._render();
            expect(field._modelChangeHandler).toHaveBeenCalled();
        });
    });

    describe('_modelChangeHandler()', function() {
        using('input', [
            {
                activity_type: 'Calls',
                trigger_event: 'before_in_progress',
                expected_value: '',
            },
            {
                activity_type: 'Meetings',
                trigger_event: 'after_in_progress',
                expected_value: '',
            },
            {
                activity_type: 'Tasks',
                trigger_event: 'after_in_progress',
                expected_value: 'after_in_progress',
            },
        ],
            function(input) {
                it('The _modelChangeHandler function', function() {
                    let model;
                    model = app.data.createBean(module);
                    SugarTest.loadComponent('base', 'field', 'base');
                    model.set(fieldName, input.trigger_event);
                    field = createField(model);
                    field.model.set('parent_type', 'DRI_Workflow_Task_Templates');
                    field.model.set('parent_id', '1234-5678');
                    taskTemplateBean = app.data.createBean('DRI_Workflow_Task_Templates', {
                        id: '1234-5678',
                        activity_type: input.activity_type,
                    });
                    sinon.stub(taskTemplateBean, 'fetch').callsFake(function(callbacks) {
                        callbacks.success({
                            get: function(fieldName) {
                                return this[fieldName];
                            },
                        });
                    });
                    sinon.stub(app.data, 'createBean').returns(taskTemplateBean);
                    field.model.trigger('change:' + fieldName);
                    expect(field.model.get(fieldName)).toEqual(input.expected_value);
                });
            });
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        app = undefined;
        field = undefined;
        SugarTest.testMetadata.dispose();
    });
});
