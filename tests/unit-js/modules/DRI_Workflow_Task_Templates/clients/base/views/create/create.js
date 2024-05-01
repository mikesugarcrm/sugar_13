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

describe('DRI_Workflow_Task_Templates.Views.Create', function() {
    let app;
    let model;
    let view;
    let layout;
    let context;
    let viewName = 'create';
    let module = 'DRI_Workflow_Task_Templates';

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean(module);
        context = app.context.getContext({
            module: module,
            model: model,
            create: true
        });
        context.prepare(true);

        SugarTest.testMetadata.init();
        SugarTest.app.data.declareModels();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'layout', viewName);

        sinon.stub(app.api, 'buildURL');
        sinon.stub(app.api, 'call');

        layout = SugarTest.createLayout(
            'base',
            module,
            viewName,
            {},
            null,
            false
        );
        view = SugarTest.createView(
            'base',
            module,
            viewName,
            null,
            context,
            true,
            layout,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        app.data.reset();
        view.dispose();
        layout.dispose();
        model.dispose();
        SugarTest.testMetadata.dispose();

        layout = null;
        context = null;
    });

    describe('setSortOrder', function() {
        using('input', [
            {
                id: undefined,
                success: true,
                sort_order: 9,
                expected: undefined,
            },
            {
                id: '102bacfe-f838-11e6-a213-5254009e5526',
                success: true,
                sort_order: 4,
                expected: 5,
            },
            {
                id: '102bacfe-f838-11e6-a213-5254009e5526',
                success: false,
                sort_order: 3,
                expected: 1,
            },
        ],

        function(input) {
            it('sort order should be as input expected value', function() {
                app.api.call.callsFake(function(method, url, parameter, callbacks) {
                    if (input.success) {
                        callbacks.success({sort_order: input.sort_order});
                    } else {
                        callbacks.error();
                    }
                });

                view.model.set('dri_subworkflow_template_id', input.id);
                view.setSortOrder();

                expect(view.model.get('sort_order')).toBe(input.expected);
            });
        });
    });

    describe('setRelatedJourneyTemplate', function() {
        using('input', [
            {
                id: undefined,
                expected_id: undefined,
                shouldCall: false,
            },
            {
                id: '102bacfe-f838-11e6-a213-5254009e5526',
                expected_id: 'c108bb4a-775a-11e9-b570-f218983a1c3e',
                shouldCall: true,
            },
        ],

        function(input) {
            it('should call createBean function if expected and dri_workflow_template_id should be as expected id',
                function() {
                    let stage = {
                        fetch: function(request) {
                            request.success();
                        },
                        get: function() {
                            return input.expected_id;
                        },
                    };
                    sinon.stub(app.data, 'createBean').returns(stage);

                    view.model.set('dri_subworkflow_template_id', input.id);
                    view.setRelatedJourneyTemplate();

                    if (input.shouldCall) {
                        expect(app.data.createBean).toHaveBeenCalled();
                    } else {
                        expect(app.data.createBean).not.toHaveBeenCalled();
                    }

                    expect(view.model.get('dri_workflow_template_id')).toBe(input.expected_id);
                }
            );
        });
    });

    describe('validateModelWaterfall', function() {
        it('should call view getField, getFields and model doValidate functions', function() {
            let fields = {
                address: {
                    name: 'address',
                    type: 'varchar',
                    required: false,
                    len: 255,
                },
                description: {
                    name: 'description',
                    type: 'varchar',
                    required: false,
                    len: 255,
                },
            };
            let populateFields = {
                addedFieldsDefs: {
                    is_empty: {
                        name: 'is_empty',
                        type: 'bool',
                        required: false,
                        default: 0,
                    },
                },
            };

            sinon.stub(view, 'getFields').returns(fields);
            sinon.stub(view, 'getField').returns(populateFields);
            sinon.stub(view.model, 'doValidate').returns(true);
            view.validateModelWaterfall();

            expect(view.getFields).toHaveBeenCalled();
            expect(view.getField).toHaveBeenCalled();
            expect(view.model.doValidate).toHaveBeenCalled();
        });
    });
});
