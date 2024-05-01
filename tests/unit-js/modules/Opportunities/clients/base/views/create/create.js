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

describe('Opportunities.Base.Views.Create', function() {
    var app, view, options;

    beforeEach(function() {
        app = SugarTest.app;
        options = {
            meta: {
                panels: [{
                    fields: [{
                        name: 'name'
                    },{
                        name: 'commit_stage',
                        label: 'LBL_COMMIT_STAGE'
                    }]
                }]
            }
        };

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'create');
        SugarTest.testMetadata.set();

        SugarTest.seedMetadata(true, './fixtures');
        view = SugarTest.createView('base', 'Opportunities', 'create', options.meta, null, true);
    });

    afterEach(function() {
        sinon.restore();
    });


    describe('hasUnsavedChanges', function() {
        beforeEach(function() {
            sinon.stub(view, '_super').callsFake(function() {
                return false;
            });
        });

        it('will return false from _super call', function() {
            view.viewBy = 'Opportunities';
            expect(view.hasUnsavedChanges()).toBeFalsy();
        });

        describe('when not using rlis', function() {
            beforeEach(function() {
                view.viewBy = 'Opportunities';
                sinon.stub(view.context, 'getChildContext');
            });

            it('should not call getChildContext', function() {
                view.hasUnsavedChanges();
                expect(view.context.getChildContext).not.toHaveBeenCalled();
            });
        });

        describe('when using rlis', function() {
            beforeEach(function() {
                view.viewBy = 'RevenueLineItems';
            });

            it('will not call getChildContext when hasRLIAccess is false', function() {
                sinon.stub(view.context, 'getChildContext');
                view.hasRliAccess = false;
                view.hasUnsavedChanges();
                expect(view.context.getChildContext).not.toHaveBeenCalled();
            });

            describe('and has access will call getChildContext', function() {
                it('and return true with a collection length > 1', function() {
                    sinon.stub(view.context, 'getChildContext').callsFake(function() {
                        return {
                            prepare: function() {},
                            get: function() {
                                return {
                                    length: 2
                                };
                            }
                        };
                    });
                    expect(view.hasUnsavedChanges()).toBeTruthy();
                });

                it('and return false with a collection length = 0', function() {
                    sinon.stub(view.context, 'getChildContext').callsFake(function() {
                        return {
                            prepare: function() {},
                            get: function() {
                                return {
                                    length: 0
                                };
                            }
                        };
                    });
                    expect(view.hasUnsavedChanges()).toBeFalsy();
                });

                it('and return false with no model changes', function() {
                    sinon.stub(view.context, 'getChildContext').callsFake(function() {
                        return {
                            prepare: function() {},
                            get: function() {
                                return {
                                    at: function() {
                                        return new app.Bean();
                                    },
                                    length: 1
                                };
                            }
                        };
                    });
                    expect(view.hasUnsavedChanges()).toBeFalsy();
                });

                it('and return false with no default data changes', function() {
                    var model = new app.Bean();
                    model.setDefault('name', 'test');

                    sinon.stub(view.context, 'getChildContext').callsFake(function() {
                        return {
                            prepare: function() {},
                            get: function() {
                                return {
                                    at: function() {
                                        return model;
                                    },
                                    length: 1
                                };
                            }
                        };
                    });
                    expect(view.hasUnsavedChanges()).toBeFalsy();
                });

                it('and return true with bean changes', function() {

                    var model = new app.Bean();
                    model.setDefault('name', 'test');
                    model.set('name', 'changed');

                    sinon.stub(view.context, 'getChildContext').callsFake(function() {
                        return {
                            prepare: function() {},
                            get: function() {
                                return {
                                    at: function() {
                                        return model;
                                    },
                                    length: 1
                                };
                            }
                        };
                    });
                    expect(view.hasUnsavedChanges()).toBeTruthy();
                });
            });
        });
    });

    describe('getCustomSaveOptions', function() {
        var opts;

        beforeEach(function() {
            opts = {
                success: function() {}
            };
        });

        afterEach(function() {
            opts = null;
        });

        it('createdModel should not be undefined', function() {
            view.getCustomSaveOptions(opts);
            expect(_.isUndefined(view.createdModel)).toBeFalsy();
        });
        it('listContext should not be undefined', function() {
            view.getCustomSaveOptions(opts);
            expect(_.isUndefined(view.listContext)).toBeFalsy();
        });
    });

    describe('_checkForRevenueLineItems', function() {
        var model, options = {};
        beforeEach(function() {
            sinon.stub(view, 'showRLIWarningMessage');
            model = new Backbone.Model();
            view.listContext = new Backbone.Model();
        });
        afterEach(function() {
            view.listContext = undefined;
        });

        it('will not call showRLIWarningMessage due to not having access', function() {
            view.hasRliAccess = false;
            view._checkForRevenueLineItems(model, options);
            expect(view.showRLIWarningMessage).not.toHaveBeenCalled();
        });
        it('will not call showRLIWarningMessage due having added RLIs', function() {
            model.set('revenuelineitems', {create: ['one']});
            view._checkForRevenueLineItems(model, options);

            expect(view.showRLIWarningMessage).not.toHaveBeenCalled();
        });
        it('will call showRLIWarningMessage', function() {
            model.set('revenuelineitems', {});
            view._checkForRevenueLineItems(model, options);
            expect(view.showRLIWarningMessage).toHaveBeenCalled();
        });
    });

    describe('createLinkModel', function() {
        var parentModel, createBeanStub, relateFieldStub;

        beforeEach(function() {
            parentModel = new Backbone.Model({
                id: '101-model-id',
                name: 'parent product name',
                account_id: 'abc-111-2222',
                account_name: 'parent account name',
                assigned_user_name: 'admin'
            }),
            createBeanStub = sinon.stub(app.data, 'createRelatedBean').callsFake(function() {
               return new Backbone.Model();
            }),
            relateFieldStub = sinon.stub(app.data, 'getRelateFields').callsFake(function() {
                return [{
                    name: 'product_template_name',
                    rname: 'name',
                    id_name: 'product_template_id',
                    populate_list: {
                        account_id: 'account_id',
                        account_name: 'account_name',
                        assigned_user_name: 'user_name'
                    }
                }];
            });
        });
        afterEach(function() {
            parentModel = null;
        });

        it('should populate related fields when it creates linked record', function() {
            var newModel = view.createLinkModel(parentModel, 'blah');
            expect(newModel.get('product_template_id')).toBe(parentModel.get('id'));
            expect(newModel.get('product_template_name')).toBe(parentModel.get('name'));
            expect(newModel.get('account_id')).toBe(parentModel.get('account_id'));
            expect(newModel.get('account_name')).toBe(parentModel.get('account_name'));
            expect(newModel.get('user_name')).toBe(parentModel.get('assigned_user_name'));
        });
        it('should store the relate fields in default to keep the values when creating a new linked model', function() {
            var newModel = view.createLinkModel(parentModel, 'blah');
            expect(newModel.relatedAttributes['product_template_id']).toBe(parentModel.get('id'));
            expect(newModel.relatedAttributes['product_template_name']).toBe(parentModel.get('name'));
            expect(newModel.relatedAttributes['account_id']).toBe(parentModel.get('account_id'));
            expect(newModel.relatedAttributes['account_name']).toBe(parentModel.get('account_name'));
            expect(newModel.relatedAttributes['user_name']).toBe(parentModel.get('assigned_user_name'));
        });
    });

    describe('_checkForNonServiceRlis', () => {
        using('different RLIs', [
            [
                [{service: true}, {service: false}, {service: true}, {service: false}],
                false
            ],
            [
                [{service: true}, {service: true}, {service: true}],
                false
            ],
            [
                [{service: false}, {service: false}, {service: false}],
                true
            ],
            [
                [],
                true
            ]
        ], (rliModels, expected) => {
            it('should return whether or not there are RLIs matching the condition', () => {
                let rliCollection = {
                    models: rliModels.map(rliModel => ({
                        get: fieldName => rliModel[fieldName]
                    }))
                };

                expect(view._checkForNonServiceRlis(rliCollection)).toBe(expected);
            });
        });
    });

    describe('_checkForLockedDurationServiceRlis', () => {
        using('different RLIs', [
            [
                [
                    {
                        service: true,
                        add_on_to_id: 'my_add_on_id'
                    }
                ],
                true
            ],
            [
                [
                    {
                        service: true,
                        lock_duration: true
                    }
                ],
                true
            ],
            [
                [
                    {
                        service: false
                    }
                ],
                false
            ],
            [
                [
                    {
                        service: true
                    }
                ],
                false
            ],
            [
                [],
                false
            ],
            [
                [
                    {
                        service: false
                    },
                    {
                        service: true,
                    },
                    {
                        service: true,
                        add_on_to_id: 'my_add_on_id'
                    }
                ],
                false
            ],
            [
                [
                    {
                        service: false
                    },
                    {
                        service: true,
                        add_on_to_id: 'my_add_on_id'
                    },
                    {
                        service: true,
                        add_on_to_id: 'my_add_on_id'
                    }
                ],
                true
            ]
        ], (rliModels, expected) => {
            it('should return whether or not there are RLIs matching the condition', () => {
                let rliCollection = {
                    models: rliModels.map(rliModel => ({
                        get: fieldName => rliModel[fieldName]
                    }))
                };

                expect(view._checkForLockedDurationServiceRlis(rliCollection)).toBe(expected);
            });
        });
    });

    describe('validateCascadeFields', () => {
        it('should not validate if the user cannot work with RLIs', () => {
            view.hasRliAccess = false;
            view.viewBy = 'RevenueLineItems';

            expect(view.validateCascadeFields()).toBeNull();
        });

        it('should not validate if the user is in Opps-only mode', () => {
            view.hasRliAccess = true;
            view.viewBy = 'Opportunities';

            expect(view.validateCascadeFields()).toBeNull();
        });

        using('different field values', [
            // Service duration and service start date both checked and have values - expect both in warning
            [
                {
                    service_duration_value: '10',
                    service_duration_unit: 'month',
                    service_duration_cascade_checked: true,
                    service_start_date: '2021-01-01',
                    service_start_date_cascade_checked: true
                },
                true,
                [['LBL_SERVICE_DURATION', 'LBL_SERVICE_START_DATE'], 'LBL_CASCADE_SERVICE_WARNING']
            ],
            // Service duration and service start date both checked and have values but only partial value for
            // service duration - expect both in warning
            [
                {
                    service_duration_value: '',
                    service_duration_unit: 'month',
                    service_duration_cascade_checked: true,
                    service_start_date: '2021-01-01',
                    service_start_date_cascade_checked: true
                },
                true,
                [['LBL_SERVICE_DURATION', 'LBL_SERVICE_START_DATE'], 'LBL_CASCADE_SERVICE_WARNING']
            ],
            // Service duration has value and is checked, service start date has value but is unchecked - only
            // expect service duration in warning
            [
                {
                    service_duration_value: '10',
                    service_duration_unit: 'month',
                    service_duration_cascade_checked: true,
                    service_start_date: '2021-01-01',
                    service_start_date_cascade_checked: false
                },
                true,
                [['LBL_SERVICE_DURATION'], 'LBL_CASCADE_SERVICE_WARNING']
            ],
            // Service duration has value and is checked, service start date is checked but does not have value -
            // only expect service duration in warning
            [
                {
                    service_duration_value: '10',
                    service_duration_unit: 'month',
                    service_duration_cascade_checked: true,
                    service_start_date: '',
                    service_start_date_cascade_checked: true
                },
                true,
                [['LBL_SERVICE_DURATION'], 'LBL_CASCADE_SERVICE_WARNING']
            ],
            // Service duration and service start date are both unchecked - do not expect a warning
            [
                {
                    service_duration_value: '10',
                    service_duration_unit: 'month',
                    service_duration_cascade_checked: false,
                    service_start_date: '2021-01-01',
                    service_start_date_cascade_checked: false
                },
                false,
                []
            ]
        ], (values, warningExpected, warningParams) => {
            it('should properly warn when cascade fields are set and there are no service RLIs', () => {
                sinon.stub(view, '_getRliCollection').callsFake(() => ({models: []}));
                sinon.stub(view, '_checkForNonServiceRlis').callsFake(() => true);
                sinon.stub(view, '_checkForLockedDurationServiceRlis').callsFake(() => false);

                let buildCascadeWarningStub = sinon.stub(view, '_buildCascadeWarning').callsFake(
                    (fields, baseWarning) => {
                        return 'warning';
                    }
                );

                view.model = {
                    get: fieldName => values[fieldName],
                    off: sinon.stub()
                };

                let result = view.validateCascadeFields();
                expect(result !== null).toBe(warningExpected);
                if (warningExpected) {
                    expect(buildCascadeWarningStub).toHaveBeenCalledWith(warningParams[0], warningParams[1]);
                }
            });
        });

        using('different field values', [
            [
                {
                    service_duration_value: '10',
                    service_duration_unit: 'month',
                    service_duration_cascade_checked: true
                },
                true,
                [['LBL_SERVICE_DURATION'], 'LBL_CASCADE_DURATION_WARNING']
            ],
            [
                {
                    service_duration_value: '',
                    service_duration_unit: 'month',
                    service_duration_cascade_checked: true
                },
                true,
                [['LBL_SERVICE_DURATION'], 'LBL_CASCADE_DURATION_WARNING']
            ],
            [
                {
                    service_duration_value: '',
                    service_duration_unit: '',
                    service_duration_cascade_checked: true
                },
                false,
                [],
            ],
            [
                {
                    service_duration_value: '10',
                    service_duration_unit: 'month',
                    service_duration_cascade_checked: false
                },
                false,
                []
            ],
        ], (values, warningExpected, warningParams) => {
            it('should properly warn when cascade fields are set and there are locked duration RLIs', () => {
                sinon.stub(view, '_getRliCollection').callsFake(() => ({models: []}));
                sinon.stub(view, '_checkForNonServiceRlis').callsFake(() => false);
                sinon.stub(view, '_checkForLockedDurationServiceRlis').callsFake(() => true);

                let buildCascadeWarningStub = sinon.stub(view, '_buildCascadeWarning').callsFake(
                    (fields, baseWarning) => {
                        return 'warning';
                    }
                );

                view.model = {
                    get: fieldName => values[fieldName],
                    off: sinon.stub()
                };

                let result = view.validateCascadeFields();
                expect(result !== null).toBe(warningExpected);
                if (warningExpected) {
                    expect(buildCascadeWarningStub).toHaveBeenCalledWith(warningParams[0], warningParams[1]);
                }
            });
        });

        it('should pass validation when there are no valid RLIs', () => {
            sinon.stub(view, '_getRliCollection').callsFake(() => ({models: []}));
            sinon.stub(view, '_checkForNonServiceRlis').callsFake(() => false);
            sinon.stub(view, '_checkForLockedDurationServiceRlis').callsFake(() => false);

            view.model = {
                get: fieldName => ({
                    service_duration_value: '10',
                    service_duration_unit: 'month',
                    service_start_date_checked: true
                }[fieldName]),
                off: sinon.stub()
            };

            expect(view.validateCascadeFields()).toBeNull();
        });
    });
});
