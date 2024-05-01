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

describe('View.Fields.Base.DRIWorkflowTaskTemplates.BlockedByField', function() {
    let app;
    let field;
    let model;
    let module = 'DRI_Workflow_Task_Templates';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'enum');

        model = app.data.createBean(module);
        field = SugarTest.createField(
            'base',
            'blocked_by',
            'blocked-by',
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
        field.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        model.dispose();
        sinon.restore();
    });

    describe('initialize', function() {
        it('field type should be enum', function() {
            expect(field.type).toBe('enum');
        });
    });

    describe('_getLabel', function() {
        using('input', [
            {
                name: 'blocked_by',
                record: {
                    name: 'Book Final Meeting',
                    label: 'Blocked By',
                    sort_order: '01',
                    stage_template_label: 'Qualification',
                },
                result: 'Qualification - 01. Book Final Meeting',
            },
            {
                name: 'blocked_by_stages',
                record: {
                    name: 'Update Account info',
                    label: 'Present Solution',
                    sort_order: '01',
                    stage_template_label: 'Qualification',
                },
                result: 'Present Solution',
            },
        ],

        function(input) {
            it('response should match with expected result', function() {
                field.name = input.name;
                let response = field._getLabel(input.record);

                expect(response).toBe(input.result);
            });
        });
    });

    describe('loadEnumOptions', function() {
        it('app.api.relationships function should be called and context key should return "Testing"', function() {
            sinon.stub(app.api, 'relationships').returns('Testing');
            field.model.set('dri_workflow_template_id', '1459d08a-fcca-11e6-98d3-5254009e5526');
            field.loadEnumOptions();

            expect(app.api.relationships).toHaveBeenCalled();
            expect(field.context.get('request:DRI_Workflow_Task_Templates:blocked_by')).toBe('Testing');
        });
    });

    describe('_readRelationshipsSuccess', function() {
        beforeEach(function() {
            sinon.stub(field, '_getLabel').callsFake(function(record) {
                return record.label;
            });
            sinon.stub(app.data, 'createBean').callsFake(function() {
                return {
                    fetch: function() {},
                };
            });
            field.items = {};
        });

        afterEach(function() {
            sinon.restore();
        });

        it('should call createBean and should not call _getLabel and items should be empty object', function() {
            field.name = 'blocked_by_stages';
            field.model.set('id', '102bacfe-f838-11e6-a213-5254009e5526');
            field.model.set('is_parent', true);
            field.model.set('parent_id', '04b5e4ba-5eec-11e6-a411-5254009e5526');
            field._readRelationshipsSuccess(function() {}, {'records': []});

            expect(field._getLabel).not.toHaveBeenCalled();
            expect(app.data.createBean).toHaveBeenCalled();
            expect(field.items).toEqual({});
        });

        using('input', [
            {
                id: '04b5e4ba-5eec-11e6-a411-5254009e5526',
                parent_id: '1a972264-f837-11e6-8694-5254009e5526',
                is_parent: false,
                records: [
                    {
                        'id': '102bacfe-f838-11e6-a213-5254009e5526',
                        'parent_id': '1a972264-f837-11e6-8694-5254009e5526',
                        'is_parent': false,
                        'label': 'Qualification',
                    },
                    {
                        'id': '1a972264-f837-11e6-8694-5254009e5526',
                        'parent_id': '1a972264-f837-11e6-8694-5254009e5526',
                        'is_parent': true,
                        'label': 'Support System',
                    },
                ],
                result: {
                    '102bacfe-f838-11e6-a213-5254009e5526': 'Qualification',
                }
            },
            {
                id: '102bacfe-f838-11e6-a213-5254009e5526',
                parent_id: '04b5e4ba-5eec-11e6-a411-5254009e5526',
                is_parent: true,
                records: [
                    {
                        'id': '1a972264-f837-11e6-8694-5254009e5526',
                        'parent_id': '102bacfe-f838-11e6-a213-5254009e5526',
                        'is_parent': false,
                        'label': 'Support System',
                    },
                ],
                result: {}
            },
        ],

        function(input) {
            it('should call _getLabel and should not call createBean and items should match with input result',
                function() {
                    field.name = 'blocked_by';
                    field.model.set('id', input.id);
                    field.model.set('is_parent', input.is_parent);
                    field.model.set('parent_id', input.parent_id);
                    field._readRelationshipsSuccess(function() {}, {'records': input.records});

                    expect(field._getLabel).toHaveBeenCalled();
                    expect(app.data.createBean).not.toHaveBeenCalled();
                    expect(field.items).toEqual(input.result);
                }
            );
        });
    });

    describe('_beanFetchSuccess', function() {
        using('input', [
            {
                records: [
                    {
                        'id': '102bacfe-f838-11e6-a213-5254009e5526',
                        'label': 'Qualification',
                    },
                    {
                        'id': '1a631c99-ec49-4aae-850f-aebef82a909c',
                        'label': 'Support System',
                    },
                ],
                currentActivity: {
                    attributes: {},
                },
                result: {
                    '102bacfe-f838-11e6-a213-5254009e5526': 'Qualification',
                    '1a631c99-ec49-4aae-850f-aebef82a909c': 'Support System',
                }
            },
            {
                records: [
                    {
                        'id': '216c5012-6f59-11e6-ade5-5254009e5526',
                        'label': 'Detailed Review',
                    },
                ],
                currentActivity: {
                    attributes: {},
                },
                result: {},
            },
        ],

        function(input) {
            it('field items should match with input result', function() {
                sinon.stub(field, '_getLabel').callsFake(function(record) {
                    return record.label;
                });

                field.items = {};
                field.model.set('dri_subworkflow_template_id', '216c5012-6f59-11e6-ade5-5254009e5526');
                field._beanFetchSuccess(input.records, function() {}, input.currentActivity);

                expect(field.items).toEqual(input.result);
            });
        });
    });
});
