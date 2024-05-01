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
describe('Opportunities.Base.Plugins.Cascade', () => {
    var app;
    var plugin;
    var moduleName = 'Opportunitites';
    var model;

    beforeEach(() => {
        app = SUGAR.App;
        SugarTest.loadFile(
            '../modules/Opportunities/clients/base/plugins',
            'Cascade',
            'js',
            (data) => {
                app.events.off('app:init');
                eval(data);
                app.events.trigger('app:init');
            }
        );
        plugin = app.plugins.plugins.field.Cascade;
        model = app.data.createBean(moduleName, {
            id: '123test',
            name: 'Lorem ipsum dolor sit amet'
        });
    });

    afterEach(() => {
        sinon.restore();
        plugin = null;
        model = null;
    });

    describe('onAttach', () => {
        using(
            'different opps+rlis configs', [
                {
                    oppsConfig: 'RevenueLineItems',
                    displayCheckbox: true,
                    setupMethodCallCount: 1,
                    hasCascadeFields: true,
                }, {
                    oppsConfig: 'OpportunitiesOnly',
                    displayCheckbox: false,
                    setupMethodCallCount: 0,
                    hasCascadeFields: false,
                }
            ],
            (values) => {
                it('should set field properties appropriately', () => {
                    sinon.stub(app.metadata, 'getModule').callsFake(() => {
                        return {opps_view_by: values.oppsConfig};
                    });

                    sinon.spy(plugin, '_initPluginProperties');
                    sinon.spy(plugin, '_initModelProperties');
                    sinon.spy(plugin, '_initViewProperties');
                    sinon.spy(plugin, '_initFieldProperties');
                    sinon.spy(plugin, '_initDefaultValues');
                    sinon.spy(plugin, '_initListeners');

                    plugin.listenTo = sinon.spy();

                    var field = {
                        options: {
                            def: {
                                name: 'testField'
                            },
                            view: {
                                action: 'edit',
                            },
                            model: {
                                on: sinon.stub(),
                                addValidationTask: sinon.stub(),
                            }
                        },
                        on: sinon.stub(),
                    };

                    plugin.onAttach(field, plugin);

                    // Check to see the execution of setup methods
                    expect(plugin._initPluginProperties.callCount).toBe(values.setupMethodCallCount);
                    expect(plugin._initModelProperties.callCount).toBe(values.setupMethodCallCount);
                    expect(plugin._initViewProperties.callCount).toBe(values.setupMethodCallCount);
                    expect(plugin._initFieldProperties.callCount).toBe(values.setupMethodCallCount);
                    expect(plugin._initDefaultValues.callCount).toBe(values.setupMethodCallCount);
                    expect(plugin._initListeners.callCount).toBe(values.setupMethodCallCount);

                    // Determine whether or not there will be a cascade checkbox
                    expect(field.displayCheckbox).toEqual(values.displayCheckbox);

                    // Check to see whether the cascadeFields are setup
                    expect(_.has(field.options.view, 'cascadeFields')).toEqual(values.hasCascadeFields);
                });
            }
        );

        using('create vs other modes', [
            {
                viewAction: 'create',
                displayCheckbox: true,
                fieldAction: undefined
            }, {
                viewAction: 'detail',
                displayCheckbox: true,
                fieldAction: undefined,
            }, {
                viewAction: 'edit',
                displayCheckbox: true,
                fieldAction: undefined,
            }
        ], (values) => {
            it('should hide checkbox and disable field on modes other than edit or detail', () => {
                sinon.stub(app.metadata, 'getModule').callsFake(() => {
                    return {opps_view_by: 'RevenueLineItems'};
                });

                plugin.listenTo = sinon.spy();

                var field = {
                    options: {
                        def: {
                            name: 'testField'
                        },
                        view: {
                            action: values.viewAction,
                        },
                        model: {
                            on: sinon.stub(),
                            set: sinon.stub(),
                            get: sinon.stub(),
                            addValidationTask: sinon.stub()
                        }
                    },
                    on: sinon.stub()
                };
                plugin.onAttach(field, plugin);
                expect(field.displayCheckbox).toEqual(values.displayCheckbox);
                expect(field.action).toEqual(values.fieldAction);
            });

            it('should add a cascade validation handler on create mode only', () => {
                sinon.stub(app.metadata, 'getModule').callsFake(() => ({opps_view_by: 'RevenueLineItems'}));

                plugin.listenTo = sinon.spy();

                let field = {
                    baseFieldName: 'sales_stage',
                    options: {
                        def: {
                            name: 'testField'
                        },
                        view: {
                            action: values.viewAction,
                        },
                        model: {
                            on: sinon.stub(),
                            set: sinon.stub(),
                            get: sinon.stub(),
                            addValidationTask: sinon.stub()
                        }
                    },
                    on: sinon.stub(),
                    cid: 'test-cid',
                    name: 'testField'
                };

                plugin.onAttach(field, plugin);

                if (values.viewAction === 'create') {
                    expect(field.options.model.addValidationTask).toHaveBeenCalled();
                } else {
                    expect(field.options.model.addValidationTask).not.toHaveBeenCalled();
                }
            });
        });

        it('should set the default values of the field from the model', () => {
            sinon.stub(app.metadata, 'getModule').callsFake(() => ({opps_view_by: 'RevenueLineItems'}));

            plugin.listenTo = sinon.spy();

            var field = {
                baseFieldName: 'sales_stage',
                options: {
                    def: {
                        name: 'testField',
                        type: 'enum-cascade',
                    },
                    view: {
                        action: 'create',
                    },
                    model: {
                        on: sinon.stub(),
                        set: sinon.stub(),
                        get: sinon.stub().returns({sales_stage: 'Prospecting'}),
                        addValidationTask: sinon.stub(),
                    }
                },
                on: sinon.stub(),
            };

            plugin.onAttach(field, plugin);

            expect(
                field.options.view.cascadeFields[plugin.baseFieldName].defaultValues[field.baseFieldName]
            ).toEqual({sales_stage: 'Prospecting'});
        });

        using('different values for Lead convert layout meta', [
            [
                [
                    {
                        id: 'Contacts',
                        text: 'Contacts',
                        required: true,
                    },
                    {
                        id: 'Opportunities',
                        text: 'Opportunities',
                        required: true,
                        enableRlis: true,
                    }
                ],
                true,
            ],
            [
                [
                    {
                        id: 'Contacts',
                        text: 'Contacts',
                        required: true,
                    },
                    {
                        id: 'Opportunities',
                        text: 'Opportunities',
                        required: true,
                        enableRlis: false,
                    }
                ],
                false,
            ]
        ], (meta, expected) => {
            it('should properly enable or disable the checkbox and field', () => {
                sinon.stub(app.metadata, 'getModule').callsFake(() => ({opps_view_by: 'RevenueLineItems'}));

                plugin.listenTo = sinon.spy();

                let field = {
                    options: {
                        def: {
                            name: 'testField'
                        },
                        view: {
                            action: 'edit',
                        },
                        context: {
                            parent: {
                                get: () => meta
                            }
                        },
                        model: {
                            on: sinon.stub(),
                            addValidationTask: sinon.stub()
                        }
                    },
                    on: sinon.stub()
                };
                plugin.onAttach(field, plugin);

                expect(field.displayCheckbox).toEqual(expected);
                expect(field.action).toEqual(expected ? undefined : 'disabled');
            });
        });
    });

    describe('_beforeRender', function() {
        beforeEach(function() {
            plugin.model = model;
            plugin.field = {
                _removeViewClass: sinon.stub(),
                options: {
                    view: {
                        action: 'create'
                    }
                }
            };
            plugin._isOnLeadConvert = sinon.stub();
            plugin._isOnLeadConvertWithProperty = sinon.stub();
            plugin._hasNoRliCollection = sinon.stub();
            plugin.baseFieldName = 'test';
        });

        describe('when the field is readonly', function() {
            beforeEach(function() {
                plugin.readOnlyProp = true;
            });

            it('should set the field action to disabled', function() {
                plugin._beforeRender();
                expect(plugin.field.action).toEqual('disabled');
            });

            it('should prepare the checkbox element to be unchecked', function() {
                plugin._beforeRender();
                expect(plugin.field.shouldCascade).toEqual(false);
            });

            it('should disable the checkbox', function() {
                plugin._beforeRender();
                expect(plugin.field.cascadeCheckboxDisabled).toEqual(true);
            });
        });

        describe('when the field has errors', function() {
            beforeEach(function() {
                plugin.model.set(plugin.baseFieldName + '_cascade_checked', true);
                plugin.field._errors = {
                    super_cool_error: true
                };
            });

            it('should not set the field action to disabled', function() {
                plugin._beforeRender();
                expect(plugin.field.action).not.toEqual('disabled');
            });

            it('should prepare the checkbox element to be checked', function() {
                plugin._beforeRender();
                expect(plugin.field.shouldCascade).toEqual(true);
            });

            it('should enable the checkbox', function() {
                plugin._beforeRender();
                expect(plugin.field.cascadeCheckboxDisabled).toEqual(false);
            });
        });

        describe('when the field is not readonly', function() {
            beforeEach(function() {
                plugin.readOnlyProp = false;
            });

            describe('when the checkbox is checked', function() {
                beforeEach(function() {
                    plugin.model.set(plugin.baseFieldName + '_cascade_checked', true);
                });

                it('should not set the field action to disabled', function() {
                    plugin._beforeRender();
                    expect(plugin.field.action).not.toEqual('disabled');
                });

                it('should prepare the checkbox element to be checked', function() {
                    plugin._beforeRender();
                    expect(plugin.field.shouldCascade).toEqual(true);
                });
            });

            describe('when the checkbox is unchecked', function() {
                beforeEach(function() {
                    plugin.model.set(plugin.baseFieldName + '_cascade_checked', false);
                });

                it('should set the field action to disabled', function() {
                    plugin._beforeRender();
                    expect(plugin.field.action).toEqual('disabled');
                });

                it('should prepare the checkbox element to be unchecked', function() {
                    plugin._beforeRender();
                    expect(plugin.field.shouldCascade).toEqual(false);
                });
            });
        });
    });

    describe('_afterRender', function() {
        beforeEach(function() {
            plugin.view = {
                el: {
                    classList: {
                        contains: sinon.stub().returns('flex-list-view'),
                        add: sinon.stub()
                    }
                }
            };

            sinon.stub(plugin, 'bindEditActions');
        });

        it('should call bindEditActions', function() {
            plugin._afterRender();
            expect(plugin.bindEditActions).toHaveBeenCalled();
        });
    });

    describe('handleModeChange', function() {
        var setDisabled;
        beforeEach(function() {
            setDisabled = sinon.stub();
            plugin.field = {setDisabled: setDisabled, $el: true};
            sinon.stub(plugin, 'handleReadOnly');
        });
        afterEach(function() {
            setDisabled = null;
        });
        it('should bind edit listeners in edit mode', function() {
            plugin.handleModeChange('edit');
            expect(setDisabled).not.toHaveBeenCalled();
            expect(plugin.handleReadOnly).toHaveBeenCalled();
        });
        it('should bind edit listeners in create mode', function() {
            plugin.handleModeChange('create');
            expect(setDisabled).not.toHaveBeenCalled();
            expect(plugin.handleReadOnly).toHaveBeenCalled();
        });

        it('should enable field in non-edit mode', function() {
            plugin.handleModeChange('detail');
            expect(setDisabled).toHaveBeenCalledWith(false, {trigger: true});
            expect(plugin.handleReadOnly).not.toHaveBeenCalled();
        });
    });

    describe('resetModelValue', function() {
        it('should set model value to synced value, and clear cascade field', function() {
            plugin.baseFieldName = 'sales_stage';
            plugin.model = model;
            plugin.fieldNames = ['sales_stage'];
            sinon.stub(model, 'getSynced').callsFake(function() {
                return 'beforeValue';
            });
            model.unset = sinon.stub();
            model.set = sinon.stub();
            plugin.setRliValueForField = sinon.stub();

            plugin.resetModelValue();
            expect(model.unset.callCount).toBe(1);
            expect(model.set.callCount).toBe(1);
            expect(model.getSynced).toHaveBeenCalledWith('sales_stage');
            expect(model.unset).toHaveBeenCalledWith('sales_stage_cascade');
            expect(model.set).toHaveBeenCalledWith('sales_stage', 'beforeValue');
            expect(plugin.setRliValueForField).toHaveBeenCalledWith('sales_stage', '');
        });
    });

    describe('handleReadOnly', function() {
        beforeEach(function() {
            plugin.field = {
                def: {
                    readOnlyProp: false,
                },
                $el: {
                    find: function() {
                        return true;
                    }
                }
            };
            plugin.options = {
                def: {
                    disable_field: 'test',
                },
                view: {
                    name: 'record'
                }
            };
            plugin.render = sinon.stub();
            plugin.model = model;
            sinon.stub(plugin.model, 'get');
            plugin.model.get.withArgs('test').returns(4);
        });

        describe('when the field is readonly according to its definition', function() {
            beforeEach(function() {
                plugin.field.def.readOnlyProp = true;
            });

            it('should set readOnlyProp to true', function() {
                plugin.handleReadOnly();
                expect(plugin.readOnlyProp).toEqual(true);
            });
        });

        describe('when the field is not readonly according to its definition', function() {
            beforeEach(function() {
                plugin.field.def.readOnlyProp = false;
            });

            describe('with one disable field', function() {
                describe('when disable_positive is true', function() {
                    beforeEach(function() {
                        plugin.options.def.disable_positive = true;
                    });

                    it('should mark the field as readonly for a positive disable field value', function() {
                        plugin.handleReadOnly();
                        expect(plugin.readOnlyProp).toEqual(true);
                    });

                    it('should mark the field as not readonly for a non-positive disable field value', function() {
                        plugin.model.get.withArgs('test').returns(0);
                        plugin.handleReadOnly();
                        expect(plugin.readOnlyProp).toEqual(false);
                    });
                });

                describe('when disable_positive is false', function() {
                    beforeEach(function() {
                        plugin.options.def.disable_positive = false;
                    });

                    it('should mark the field as not readonly for a positive disable field value', function() {
                        plugin.handleReadOnly();
                        expect(plugin.readOnlyProp).toEqual(false);
                    });

                    it('should mark the field as readonly for a non-positive disable field value', function() {
                        plugin.model.get.withArgs('test').returns(0);
                        plugin.handleReadOnly();
                        expect(plugin.readOnlyProp).toEqual(true);
                    });
                });
            });

            describe('with two disable fields', function() {
                beforeEach(function() {
                    plugin.options.def.disable_field = ['test', 'potato'];
                    plugin.model.get.withArgs('potato').returns(2);
                });

                describe('when disable_positive is true', function() {
                    beforeEach(function() {
                        plugin.options.def.disable_positive = true;
                    });

                    it('should mark the field as readonly for a positive disable fields value', function() {
                        plugin.handleReadOnly();
                        expect(plugin.readOnlyProp).toEqual(true);
                    });

                    it('should mark the field as not readonly for a non-positive disable fields value', function() {
                        plugin.model.get.withArgs('test').returns(1);
                        plugin.handleReadOnly();
                        expect(plugin.readOnlyProp).toEqual(false);
                    });
                });

                describe('when disable_positive is false', function() {
                    beforeEach(function() {
                        plugin.options.def.disable_positive = false;
                    });

                    it('should mark the field as not readonly for a positive disable fields value', function() {
                        plugin.handleReadOnly();
                        expect(plugin.readOnlyProp).toEqual(false);
                    });

                    it('should mark the field as readonly for a non-positive disable fields value', function() {
                        plugin.model.get.withArgs('test').returns(1);
                        plugin.handleReadOnly();
                        expect(plugin.readOnlyProp).toEqual(true);
                    });
                });
            });
        });

        it('should re-render the field', function() {
            plugin.handleReadOnly();
            expect(plugin.render).toHaveBeenCalled();
        });

        it('should not set readOnlyProp on the create view', () => {
            plugin.options = {
                def: {
                    disable_field: 'test',
                },
                view: {
                    name: 'create'
                }
            };

            plugin.handleReadOnly();
            expect(plugin.readOnlyProp).toBeNull();
        });
    });

    describe('getCheckboxLabel', () => {
        beforeEach(() => {
            sinon.stub(app.lang, 'get').callsFake(key => {
                return {
                    'LBL_CASCADE_RLI_CREATE': 'Set Across Revenue Line Items',
                    'LBL_CASCADE_RLI_EDIT': 'Update Open Revenue Line Items'
                }[key];
            });
        });
        using('different states', [
            ['create', 'Set Across Revenue Line Items'],
            ['edit', 'Update Open Revenue Line Items']
        ], (state, expected) => {
            it('should get the proper string for the checkbox', () => {
                plugin.field =  {
                    options: {
                        view: {
                            action: state
                        }
                    }
                };
                expect(plugin.getCheckboxLabel()).toBe(expected);
            });
        });
    });

    describe('_validateCascadeOnCreate', () => {
        using('different cascade field and RLI field values', [
            // Date closed, no issues - validation passes
            {
                name: 'date_closed',
                fieldNames: ['date_closed'],
                modelValues: {
                    date_closed: '2021-01-01',
                    date_closed_cascade_checked: true
                },
                rliModels: [{
                    date_closed: ''
                }],
                expectedErrors: {},
                expectedCascadeError: false
            },
            // Date closed, cascade field blank and checkbox checked - fails
            {
                name: 'date_closed',
                fieldNames: ['date_closed'],
                modelValues: {
                    date_closed: '',
                    date_closed_cascade_checked: true
                },
                rliModels: [{
                    date_closed: ''
                }],
                expectedErrors: {
                    date_closed: {
                        required: true
                    }
                },
                expectedCascadeError: true
            },
            // Date closed, cascade field blank and checkbox unchecked - passes
            {
                name: 'date_closed',
                fieldNames: ['date_closed'],
                modelValues: {
                    date_closed: '',
                    date_closed_cascade_checked: false
                },
                rliModels: [{
                    date_closed: ''
                }],
                expectedErrors: {},
                expectedCascadeError: false
            },
            // Service start date, cascade field blank and checkbox checked, field valid to cascade - fails
            {
                name: 'service_start_date',
                fieldNames: ['service_start_date'],
                modelValues: {
                    service_start_date: '',
                    service_start_date_cascade_checked: true
                },
                rliModels: [{
                    service: true,
                    service_start_date: ''
                }],
                expectedErrors: {
                    service_start_date: {
                        required: true
                    }
                },
                expectedCascadeError: true
            },
            // Service start date, cascade field blank and checkbox checked, field not valid to cascade - passes
            {
                name: 'service_start_date',
                fieldNames: ['service_start_date'],
                modelValues: {
                    service_start_date: '',
                    service_start_date_cascade_checked: true
                },
                rliModels: [{
                    service: false,
                    service_start_date: ''
                }],
                expectedErrors: {},
                expectedCascadeError: false
            },
            // Service start date, cascade field blank and checkbox checked, one RLI is valid to cascade,
            // one RLI is not valid to cascade - fails
            {
                name: 'service_start_date',
                fieldNames: ['service_start_date'],
                modelValues: {
                    service_start_date: '',
                    service_start_date_cascade_checked: true
                },
                rliModels: [
                    {
                        service: false,
                        service_start_date: ''
                    },
                    {
                        service:  true,
                        service_start_date: ''
                    }
                ],
                expectedErrors: {
                    service_start_date: {
                        required: true
                    }
                },
                expectedCascadeError: true
            },
            // Service duration, both cascade fields blank and checkbox checked - fails for both
            {
                name: 'service_duration',
                fieldNames: ['service_duration_value', 'service_duration_unit'],
                modelValues: {
                    service_duration_value: '',
                    service_duration_unit: '',
                    service_duration_cascade_checked: true
                },
                rliModels: [{
                    service: true,
                    service_duration_value: '',
                    service_duration_unit: ''
                }],
                expectedErrors: {
                    service_duration_value: {
                        required: true
                    },
                    service_duration_unit: {
                        required: true
                    }
                },
                expectedCascadeError: true
            },
            // Service duration, one cascade field blank and checkbox checked - fails for only one field
            {
                name: 'service_duration',
                fieldNames: ['service_duration_value', 'service_duration_unit'],
                modelValues: {
                    service_duration_value: '',
                    service_duration_unit: 'month',
                    service_duration_cascade_checked: true
                },
                rliModels: [{
                    service: true,
                    service_duration_value: '',
                    service_duration_unit: ''
                }],
                expectedErrors: {
                    service_duration_value: {
                        required: true
                    }
                },
                expectedCascadeError: true
            }
        ], values => {
            it('should properly validate cascade fields', () => {
                plugin.hasCascadeError = false;
                plugin.name = values.name;
                plugin.model = {
                    get: fieldName => values.modelValues[fieldName]
                };
                plugin.fieldNames = values.fieldNames;
                sinon.stub(plugin, '_getRliCollection').callsFake(() => {
                    return {
                        models: values.rliModels.map(rliModel => ({
                            get: fieldName => rliModel[fieldName]
                        }))
                    };
                });

                let callback = sinon.stub();

                plugin._validateCascadeOnCreate([], {}, callback);
                expect(callback).toHaveBeenCalledWith(null, [], values.expectedErrors);
                expect(plugin.hasCascadeError).toBe(values.expectedCascadeError);
            });
        });
    });

    describe('setDefaultValue', () => {
        beforeEach(() => {
            plugin.baseFieldName = 'sales_stage';
            plugin.view = {
                cascadeFields: {
                    sales_stage: {
                        defaultValues: {
                            sales_stage: 'Prospecting'
                        }
                    }
                }
            };
            plugin.model = model;
        });

        it('should do nothing if the field is not on a create view', () => {
            plugin.options = {
                view: {
                    name: 'edit'
                }
            };
            plugin.fieldNames = ['sales_stage'];
            plugin.model.set('sales_stage', 'Qualification');

            plugin.setDefaultValue();
            expect(plugin.model.get('sales_stage')).toBe('Qualification');
        });

        it('should set the default value if the field is on a create view', () => {
            plugin.options = {
                view: {
                    name: 'create'
                }
            };
            plugin.fieldNames = ['sales_stage'];
            plugin.model.set('sales_stage', 'Qualification');

            plugin.setDefaultValue();
            expect(plugin.model.get('sales_stage')).toBe('Prospecting');
        });

        it('should clear the field if the field has no default value', () => {
            plugin.options = {
                view: {
                    name: 'create'
                }
            };
            plugin.fieldNames = ['date_closed'];
            plugin.model.set('date_closed', '2021-01-01');

            plugin.setDefaultValue();
            expect(plugin.model.get('date_closed')).toBe('');
        });
    });
});
