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
describe('View.Views.Base.Audit.ActivityCardContentView', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'view', 'activity-card');
        SugarTest.loadComponent('base', 'view', 'activity-card-content');
        view = SugarTest.createView(
            'base',
            'Audit',
            'activity-card-content',
            null,
            null,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        app.cache.cutAll();
        app.view.reset();
        view = null;
        app = null;
    });

    describe('getChangePanel', function() {
        using('different metadata', [
            {
                meta: {
                    panels: [
                        {
                            name: 'panel_header'
                        }
                    ]
                },
                expected: undefined
            },
            {
                meta: {
                    panels: [
                        {
                            name: 'panel_change'
                        }
                    ]
                },
                expected: {
                    name: 'panel_change'
                }
            }
        ], function(values) {
            it('should get the specified change panel from metadata', function() {
                view.meta = values.meta;

                var actual = view.getChangePanel();

                expect(actual).toEqual(values.expected);
            });
        });
    });

    describe('getFieldDefFromChangePanelFields', function() {
        using('different metadata', [
            {
                fieldName: 'my_field_2',
                panel: {
                    name: 'panel_change',
                    defaultFields: [
                        {
                            name: 'my_field_1'
                        }
                    ]
                },
                expected: undefined
            },
            {
                fieldName: 'my_field_1',
                panel: {
                    name: 'panel_change',
                    defaultFields: [
                        {
                            name: 'my_field_1'
                        }
                    ]
                },
                expected: {
                    name: 'my_field_1'
                }
            }
        ], function(values) {
            it('should get specified field def from change panel', function() {
                sinon.stub(view, 'getChangePanel').returns(values.panel);

                var actual = view.getFieldDefFromChangePanelFields(values.fieldName);

                expect(actual).toEqual(values.expected);
            });
        });
    });

    describe('getChangeFieldDefFromModel', function() {
        var model;

        beforeEach(function() {
            model = app.data.createBean('');
            sinon.stub(view, 'getFieldDefFromChangePanelFields').returns({
                css_class: 'custom_css_class'
            });
        });

        using('different data', [
            {
                fieldName: 'my_field_1',
                type: '',
                value: '',
                fields: [],
                expected: {
                    css_class: 'custom_css_class'
                }
            },
            {
                fieldName: 'priority',
                type: 'before',
                value: 'Assigned',
                fields: [
                    {
                        name: 'priority'
                    }
                ],
                expected: {
                    css_class: 'custom_css_class',
                    name: 'priority'
                }
            },
            {
                fieldName: 'status',
                type: 'after',
                value: 'New',
                fields: [
                    {
                        name: 'status',
                        type: 'enum'
                    }
                ],
                expected: {
                    css_class: 'custom_css_class',
                    name: 'status',
                    type: 'enum-colorcoded',
                    template: 'list'
                }
            }
        ], function(values) {
            it('should get specified change field def from specified model', function() {
                model.set(values.fieldName, values.value);
                model.fields = values.fields;

                var actual = view.getChangeFieldDefFromModel(model, values.fieldName, values.type);
                var expected = values.expected.name ?
                    _.extend(values.expected, {
                        model: model
                    }) :
                    values.expected;

                expect(actual).toEqual(expected);
            });
        });
    });

    describe('getChangeValue', function() {
        using('different values', [
            {
                type: 'before',
                typeMeta: 'New',
                expected: 'New'
            },
            {
                type: 'before',
                typeMeta: [
                    'New',
                    'Old'
                ],
                expected: 'New'
            }
        ], function(values) {
            it('should get change value as string', function() {
                view.activity = app.data.createBean('');
                view.activity.set(values.type, values.typeMeta);

                var actual = view.getChangeValue(values.type);

                expect(actual).toEqual(values.expected);
            });
        });
    });

    describe('setChangeDef', function() {
        var model;
        var beforeModel;
        var afterModel;
        var changePanel;

        beforeEach(function() {
            model = app.data.createBean('');

            beforeModel = app.data.createBean('');
            beforeModel.set('status', 'New');
            beforeModel.fields = [
                {
                    name: 'status'
                }
            ];

            afterModel = app.data.createBean('');
            afterModel.set('status', 'New');
            afterModel.fields = [
                {
                    name: 'status',
                    type: 'enum'
                }
            ];

            changePanel = {
                defaultFields: [
                    {
                        name: 'field_name',
                        css_class: 'name-field-css'
                    },
                    {
                        name: 'before',
                        css_class: 'before-field-css'
                    },
                    {
                        name: 'after',
                        css_class: 'after-field-css'
                    }
                ]
            };
        });

        it('should set change def appropriately', function() {
            var fieldName = 'status';

            var parentModule = 'Cases';
            var parentModel = app.data.createBean('');
            parentModel.set('_module', parentModule);

            model.set('parent_model', parentModel);
            model.set('field_name', fieldName);

            view.activity = model;

            // prepare name field
            sinon.stub(view, 'getChangePanel').returns(changePanel);

            sinon.stub(view, 'getTypeChangeModel')
                .withArgs(parentModule, 'before') // prepare before field
                .returns(beforeModel)
                .withArgs(parentModule, 'after') // prepare after field
                .returns(afterModel);

            var expected = [
                {
                    name: 'field_name',
                    css_class: 'name-field-css',
                    model: model
                },
                {
                    name: 'status',
                    css_class: 'before-field-css',
                    model: beforeModel
                },
                {
                    name: 'status',
                    css_class: 'after-field-css',
                    model: afterModel,
                    type: 'enum-colorcoded',
                    template: 'list'
                }
            ];

            view.setChangeDef(true);

            expect(view.changeDef).toEqual(expected);
        });
    });
});
