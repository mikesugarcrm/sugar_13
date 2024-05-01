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
describe('View.Views.Base.ActivityCardView', function() {
    var app;
    var view;

    beforeEach(function() {
        app = SugarTest.app;

        view = SugarTest.createView('base', '', 'activity-card');
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        view = null;
        app = null;
    });

    describe('getFieldDefFromFieldMeta', function() {
        using('different field def', [
            {
                fieldsMeta: {},
                fieldName: '',
                expected: {}
            },
            {
                fieldsMeta: {
                    panels: [
                        {
                            fields: [
                                {
                                    name: 'my_field_2'
                                }
                            ]
                        }
                    ]
                },
                fieldName: 'my_field_1',
                expected: {}
            },
            {
                fieldsMeta: {
                    panels: [
                        {
                            fields: [
                                {
                                    name: 'my_field_1'
                                }
                            ]
                        }
                    ]
                },
                fieldName: 'my_field_1',
                expected: {
                    name: 'my_field_1'
                }
            },
            {
                fieldsMeta: {
                    panels: [
                        {
                            fields: [
                                {
                                    name: 'my_field_1',
                                    type: 'fieldset',
                                    fields: [
                                        {
                                            name: 'my_fieldset_field_1'
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                },
                fieldName: 'my_fieldset_field_1',
                expected: {
                    name: 'my_fieldset_field_1'
                }
            }
        ], function(values) {
            it('should get field definition from fieldsMeta', function() {
                var model = app.data.createBean('');
                model.set('fieldsMeta', values.fieldsMeta);

                view.activity = model;

                var actual = view.getFieldDefFromFieldMeta(values.fieldName);

                expect(actual).toEqual(values.expected);
            });
        });
    });

    describe('setFieldsToRenderForPanels', function() {
        using('different panel metadata', [
            {
                fieldsMeta: {},
                meta: {
                    panels: [
                        {
                            fields: [
                                {
                                    name: 'my_field_1'
                                }
                            ]
                        }
                    ]
                },
                expected: {
                    panels: [
                        {
                            fields: [
                                {
                                    name: 'my_field_1',
                                    empty: true,
                                }
                            ],
                            defaultFields: [
                                {
                                    name: 'my_field_1',
                                    empty: true,
                                }
                            ],
                            empty: true,
                        }
                    ]
                }
            },
            {
                fieldsMeta: {
                    panels: [
                        {
                            fields: [
                                {
                                    name: 'my_field_1',
                                    type: 'name'
                                }
                            ]
                        }
                    ]
                },
                meta: {
                    panels: [
                        {
                            fields: [
                                {
                                    name: 'my_field_1'
                                },
                                {
                                    name: 'my_field_2'
                                }
                            ]
                        }
                    ]
                },
                expected: {
                    panels: [
                        {
                            fields: [
                                {
                                    name: 'my_field_1',
                                    type: 'name',
                                    empty: true,
                                },
                                {
                                    name: 'my_field_2',
                                    empty: true,
                                }
                            ],
                            defaultFields: [
                                {
                                    name: 'my_field_1',
                                },
                                {
                                    name: 'my_field_2',
                                    empty: true,
                                }
                            ],
                            empty: true,
                        }
                    ]
                }
            }
        ], function(values) {
            it('should set picked fields and store default fields', function() {
                var model = app.data.createBean('');
                model.set('fieldsMeta', values.fieldsMeta);

                view.activity = model;
                view.meta = values.meta;

                view.setFieldsToRenderForPanels();

                expect(view.meta).toEqual(values.expected);
            });
        });
    });

    describe('getMetaPanel', function() {
        using('different metadata', [
            {
                panelName: 'my_panel_1',
                meta: {
                    panels: [
                        {
                            name: 'my_panel_2'
                        }
                    ]
                },
                expected: undefined
            },
            {
                panelName: 'my_panel_1',
                meta: {
                    panels: [
                        {
                            name: 'my_panel_1'
                        }
                    ]
                },
                expected: {
                    name: 'my_panel_1'
                }
            }
        ], function(values) {
            it('should get the specified panel from metadata', function() {
                view.meta = values.meta;

                var actual = view.getMetaPanel(values.panelName);

                expect(actual).toEqual(values.expected);
            });
        });
    });

    describe('isFieldEmpty', function() {
        using('field value provider', [
            {
                value: '',
                expected: true
            },
            {
                // undefined
                expected: true
            },
            {
                value: '0',
                expected: false
            },

        ], function(data) {
            it('should determine field emptyness', function() {
                const fieldName = 'product_template_name';
                const fieldDef = {name: fieldName};

                view.activity = app.data.createBean('');
                view.activity.set(fieldName, data.value);

                const actual = view.isFieldEmpty(fieldDef);

                expect(actual).toEqual(data.expected);
            });
        });
    });
});
