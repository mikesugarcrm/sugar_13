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
describe('RevenueLineItems.Base.View.SubpanelForOpportunitiesCreate', function() {
    var app,
        view,
        layout,
        options,
        parentLayout,
        sandbox;
    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.createSandbox();

        var context = app.context.getContext();
        context.set({
            model: new Backbone.Model(),
            collection: new Backbone.Collection()
        });
        context.parent = new Backbone.Model();

        layout = SugarTest.createLayout("base", null, "subpanels", null, null);
        parentLayout = SugarTest.createLayout("base", null, "list", null, null);
        layout.layout = parentLayout;

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.seedMetadata();

        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'subpanel-list');
        SugarTest.loadComponent('base', 'view', 'subpanel-list-create');

        if (!_.isFunction(app.utils.generateUUID)) {
            app.utils.generateUUID = function() {}
        }
        sinon.stub(app.utils, 'generateUUID').callsFake(function() {
            return 'testUUID'
        });

        sinon.stub(app.metadata, 'getCurrency').callsFake(function() {
            return {
                currency_id: '-99',
                conversion_rate: '1.0'
            }
        });

        sinon.stub(app.user, 'getPreference').callsFake(function() {
            return '-99';
        });

        sinon.stub(app.currency, 'getBaseCurrencyId').callsFake(function() {
            return '-98';
        });

        sinon.stub(app.metadata, 'getModule').callsFake(function() {
            return {
                is_setup: 1,
                layouts: {
                    subpanels: {
                        meta: {
                            components: [
                                {
                                    context: {
                                        link: 'revenuelineitems'
                                    },
                                    override_subpanel_list_view: 'subpanel-for-opportunities',
                                }
                            ]
                        },
                    }
                },
                views: {
                    'subpanel-for-opportunities': {
                        meta: {
                            panels: [
                                {
                                    fields: [
                                        {
                                            name: 'test'
                                        }
                                    ]
                                }
                            ]
                        }
                    }
                },
                sales_stage_won: ['Closed Won'],
                sales_stage_lost: ['Closed Lost']
            }
        });

        sinon.stub(app.lang, 'getAppListStrings').callsFake(function() {
            return {
                Prospecting: 10
            }
        });
        app.routing.start();

        options = {
            meta: {
                panels: [
                    {
                        fields: []
                    }
                ]
            }
        };

        view = SugarTest.createView(
            'base',
            'RevenueLineItems',
            'subpanel-for-opportunities-create',
            options.meta,
            context,
            true,
            layout,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        view.dispose();
        options = null;
        view = null;
        app.router.stop();
    });

    describe('initialize()', function() {
        describe('when there is a overriden subpanel layout', function() {
            beforeEach(function() {
                sinon.stub(view, '_super').callsFake(function() {});
                view.initialize(options);
            });

            afterEach(function() {
                bean = null;
                result = null;
            });

            it('should set the metadata for fields', function() {
                expect(options.meta.panels[0].fields[0].name).toBe('test');
            });
        });
    });

    describe('_addCustomFieldsToBean()', function() {
        var bean;
        var result;

        beforeEach(function() {
            view.model.set({
                sales_stage: 'Prospecting'
            });
            bean = app.data.createBean('RevenueLineItems', {
                name: 'testName1',
                currency_id: 'testId1',
                base_rate: '1',
                likely_case: '100',
                best_case: '150'
            });
            view.model.fields = {
                likely_case: {
                    name: 'likely_case',
                    type: 'currency'
                },
                best_cast: {
                    name: 'best_case',
                    type: 'currency'
                }
            };
        });

        afterEach(function() {
            result = null;
            bean = null;
        });

        describe('when passing skipCurrency true', function() {
            beforeEach(function() {
                sinon.stub(app.user, 'getCurrency').callsFake(function() {
                    return {
                        currency_create_in_preferred: false
                    };
                });
                result = view._addCustomFieldsToBean(bean, true);
            });

            describe('should populate bean with default fields', function() {
                it('should have commit_stage', function() {
                    expect(result.get('commit_stage')).toBe('exclude');
                });

                it('should have quantity', function() {
                    expect(result.get('quantity')).toBe(1);
                });

                it('should have probability', function() {
                    expect(result.get('probability')).toBe(10);
                });

                it('should have currency_id', function() {
                    expect(result.get('currency_id')).toBe('testId1');
                });

                it('should have base_rate', function() {
                    expect(result.get('base_rate')).toBe('1');
                });
            });
        });

        describe('when passing skipCurrency true and create in preferred is true', function() {
            beforeEach(function() {
                sinon.stub(app.user, 'getCurrency').callsFake(function() {
                    return {
                        currency_create_in_preferred: true,
                        currency_id: '-50',
                        currency_rate: '0.5'
                    };
                });
                result = view._addCustomFieldsToBean(bean, true);
            });

            it('should convert currency fields to the new rate', function() {
                expect(result.get('likely_case')).toBe('50.000000');
                expect(result.get('best_case')).toBe('75.000000');
            });
        });

        describe('when not passing skipCurrency', function() {
            beforeEach(function() {
                sinon.stub(app.user, 'getCurrency').callsFake(function() {
                    return {
                        currency_create_in_preferred: false
                    };
                });
                result = view._addCustomFieldsToBean(bean);
            });

            describe('should populate bean with default fields', function() {
                it('should have commit_stage', function() {
                    expect(result.get('commit_stage')).toBe('exclude');
                });

                it('should have quantity', function() {
                    expect(result.get('quantity')).toBe(1);
                });

                it('should have probability', function() {
                    expect(result.get('probability')).toBe(10);
                });

                it('should have currency_id', function() {
                    expect(result.get('currency_id')).toBe('-98');
                });

                it('should have base_rate', function() {
                    expect(result.get('base_rate')).toBe('1.0');
                });
            });
        });

        describe('should use base defaults if no user prefs exist', function() {
            var result;
            beforeEach(function() {
                view.model.set({
                    sales_stage: 'Prospecting'
                });
                view.collection.reset();
            });

            afterEach(function() {
                result = null;
            });

            it('should have use base currency if no user preferred currency exists', function() {
                sinon.stub(app.user, 'getCurrency').callsFake(function() {
                    return {
                        currency_create_in_preferred: false
                    };
                });
                result = view._addCustomFieldsToBean(bean);

                expect(result.get('currency_id')).toBe('-98');
            });
        });

        using('different values for cascade fields', [
            // If a cascade field is set and the checkbox is checked, then we should cascade
            {
                oppFields: {
                    sales_stage_cascade: 'Qualification',
                    sales_stage_cascade_checked: true
                },
                rliFields: {},
                expectedValues: {
                    sales_stage: 'Qualification'
                }
            },
            // If a checkbox is unchecked, then we shouldn't cascade
            {
                oppFields: {
                    sales_stage_cascade: '',
                    sales_stage_cascade_checked: false
                },
                rliFields: {},
                expectedValues: {
                    sales_stage: 'Prospecting'
                }
            },
            // If a cascade field is set and the checkbox is checked, but the field is not valid to cascade
            // to, then we shouldn't cascade
            {
                oppFields: {
                    service_start_date_cascade: '2020-01-01',
                    service_start_date_cascade_checked: true
                },
                rliFields: {},
                expectedValues: {
                    service_start_date: undefined
                }
            },
            // If a cascade field is set and the checkbox is checked, and the field is valid to cascade to,
            // then we should cascade
            {
                oppFields: {
                    service_start_date_cascade: '2020-01-01',
                    service_start_date_cascade_checked: true
                },
                rliFields: {
                    service: true
                },
                expectedValues: {
                    service_start_date: '2020-01-01'
                }
            },
            // Multiple fields valid to cascade - all should cascade
            {
                oppFields: {
                    sales_stage_cascade: 'Qualification',
                    sales_stage_cascade_checked: true,
                    date_closed_cascade: '2021-01-01',
                    date_closed_cascade_checked: true,
                    service_start_date_cascade: '2021-02-01',
                    service_start_date_cascade_checked: true
                },
                rliFields: {
                    service: true
                },
                expectedValues: {
                    sales_stage: 'Qualification',
                    date_closed: '2021-01-01',
                    service_start_date: '2021-02-01'
                }
            }
        ], values => {
            it('should properly set the cascadable fields', () => {
                sinon.stub(view, '_getOppModel').callsFake(() => ({
                    get: fieldName => values.oppFields[fieldName]
                }));

                bean.set(values.rliFields);

                let result = view._addCustomFieldsToBean(bean);

                Object.entries(values.expectedValues).forEach(([fieldName, fieldValue]) => {
                    expect(result.get(fieldName)).toBe(fieldValue);
                });
            });
        });

        it('should set fields from add on PLI and then clear add on data', () => {
            sinon.stub(view, '_getOppModel');
            let addOnData = {
                service: true,
                service_start_date: '2021-01-01'
            };
            view.context.parent = {
                get: name => name === 'addOnToData' ? addOnData : {},
                set: sinon.stub(),
                off: sinon.stub()
            };

            let result = view._addCustomFieldsToBean(bean);

            Object.entries(addOnData).forEach(([fieldName, fieldValue]) => {
                expect(result.get(fieldName)).toBe(fieldValue);
            });
            expect(view.context.parent.set).toHaveBeenCalledWith('addOnToData', null);
        });
    });

    describe('getCascadeFieldsFromOpp', () => {
        const cascadableFields = [
            'service_duration_value',
            'service_duration_unit',
            'service_start_date',
            'date_closed',
            'sales_stage',
            'commit_stage',
        ];

        it('should return all values when they exist and are valid to cascade', () => {
            let values = {
                service_duration_value_cascade: '10',
                service_duration_unit_cascade: 'month',
                service_start_date_cascade: '2021-01-01',
                date_closed_cascade: '2021-06-01',
                sales_stage_cascade: 'Qualification',
                commit_stage_cascade: ''
            };
            sinon.stub(view, '_getOppModel').callsFake(() => {
                return {
                    get: fieldName => {
                        return values[fieldName];
                    }
                };
            });
            sinon.stub(app.utils, 'isRliFieldValidForCascade').callsFake(() => true);

            let result = view.getCascadeFieldsFromOpp({});
            cascadableFields.forEach(fieldName => {
                expect(result[fieldName]).toBe(values[fieldName + '_cascade']);
            });
        });

        it('should default to an empty string for undefined values', () => {
            let values = {
                service_duration_value_cascade: '10',
                service_duration_unit_cascade: 'month'
            };
            let expectedValues = {
                service_duration_value: '10',
                service_duration_unit: 'month',
                service_start_date: '',
                date_closed: '',
                sales_stage: '',
                commit_stage: ''
            };
            sinon.stub(view, '_getOppModel').callsFake(() => {
                return {
                    get: fieldName => {
                        return values[fieldName];
                    }
                };
            });
            sinon.stub(app.utils, 'isRliFieldValidForCascade').callsFake(() => true);

            let result = view.getCascadeFieldsFromOpp({});
            cascadableFields.forEach(fieldName => {
                expect(result[fieldName]).toBe(expectedValues[fieldName]);
            });
        });

        it('should default to an empty string when the field is not valid to cascade', () => {
            let values = {
                date_closed_cascade: '2021-01-01',
                sales_stage_cascade: 'Qualification'
            };
            sinon.stub(view, '_getOppModel').callsFake(() => {
                return {
                    get: fieldName => {
                        return values[fieldName];
                    }
                };
            });
            sinon.stub(app.utils, 'isRliFieldValidForCascade').callsFake((model, field) => field !== 'sales_stage');

            let result = view.getCascadeFieldsFromOpp({});
            expect(result.date_closed).toBe(values.date_closed_cascade);
            expect(result.sales_stage).toBe('');
        });
    });

    describe('checkCascadePrereqChanges', () => {
        let model;

        beforeEach(() => {
            sinon.stub(view, '_checkCascadeFieldEditability').callsFake(() => null);

            sinon.stub(view, 'getCascadeFieldsFromOpp').callsFake(() => ({
                service_duration_value: '10',
                service_duration_unit: 'month',
                sales_stage: 'Qualification'
            }));

            model = {
                attributes: {},
                set: function(field, value) {
                    this.attributes[field] = value;
                }
            };
        });

        it('should set the field when cascade is checked and the field is valid to cascade', () => {
            const oppModel = {
                get: fieldName => {
                    return {
                        service_duration_cascade_checked: true,
                        sales_stage_cascade_checked: true
                    }[fieldName];
                }
            };
            sinon.stub(view, '_getOppModel').callsFake(() => oppModel);
            sinon.stub(app.utils, 'isRliFieldValidForCascade').callsFake((model, field) => true);

            view.checkCascadePrereqChanges(model);

            expect(model.attributes.service_duration_value).toBe('10');
            expect(model.attributes.service_duration_unit).toBe('month');
            expect(model.attributes.sales_stage).toBe('Qualification');
        });

        it('should not set the field when cascade is unchecked', () => {
            const oppModel = {
                get: fieldName => {
                    return {
                        service_duration_cascade_checked: true,
                        sales_stage_cascade_checked: false
                    }[fieldName];
                }
            };
            sinon.stub(view, '_getOppModel').callsFake(() => oppModel);
            sinon.stub(app.utils, 'isRliFieldValidForCascade').callsFake((model, field) => true);

            view.checkCascadePrereqChanges(model);

            expect(model.attributes.service_duration_value).toBe('10');
            expect(model.attributes.service_duration_unit).toBe('month');
            expect(model.attributes.sales_stage).toBeUndefined();
        });

        it('should not set the field when it is not valid to cascade', () => {
            const oppModel = {
                get: fieldName => {
                    return {
                        service_duration_cascade_checked: true,
                        sales_stage_cascade_checked: true
                    }[fieldName];
                }
            };
            sinon.stub(view, '_getOppModel').callsFake(() => oppModel);
            sinon.stub(app.utils, 'isRliFieldValidForCascade').callsFake((model, field) => field !== 'sales_stage');

            view.checkCascadePrereqChanges(model);

            expect(model.attributes.service_duration_value).toBe('10');
            expect(model.attributes.service_duration_unit).toBe('month');
            expect(model.attributes.sales_stage).toBeUndefined();
        });
    });
});
