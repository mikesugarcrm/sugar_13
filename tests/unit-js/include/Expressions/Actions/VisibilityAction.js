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
describe('VisibilityAction dependency', function() {
    const targetField = 'description';
    const expression = 'equal($name, "test")';
    let app;
    let model;
    let context;
    let view;
    let slContext;
    let action;

    beforeEach(function() {
        app = SugarTest.app;

        model = app.data.createBean('Contacts');
        model.fields = {
            name: {
                name: 'name'
            },
            description: {
                name: 'description'
            }
        };
        context = new app.Context({
            module: model.module,
            model: model
        });

        view = SugarTest.createView('base', 'Contacts', 'record', {}, context);
        view.fields = {
            name: {
                name: 'name',
                model: model,
                el: $('<span></span>')
            },
            description: {
                name: 'description',
                model: model,
                el: $('<span></span>'),
                def: {
                    name: 'description',
                    calculated: false
                }
            }
        };

        slContext = new SUGAR.expressions.SidecarExpressionContext(view, model);
        sinon.stub(slContext, 'setFieldRequired');
        sinon.stub(slContext, 'setFieldDisabled');
        sinon.stub(slContext, 'addClass');
        sinon.stub(slContext, 'removeClass');

        action = new SUGAR.forms.SetVisibilityAction(targetField, expression);
    });

    afterEach(function() {
        sinon.restore();
        view.fields = {};
        model.dispose();
        view.dispose();
    });

    describe('exec', function() {
        beforeEach(function() {
            sinon.stub(action, 'checkRowSidecar');
            sinon.stub(action, 'checkPanelSidecar');
        });

        using('different trigger field values', [
            {
                fieldValue: 'test',
                expected: true
            },
            {
                fieldValue: 'tset',
                expected: false
            }
        ], function(values) {
            it('should store the correct expression result in the field dependencyStates visibility data', function() {
                model.set('name', values.fieldValue);
                action.exec(slContext);
                expect(view.getField(targetField).dependencyStates).toEqual(jasmine.objectContaining({
                    visible: values.expected
                }));
            });
        });

        describe('when the visibility expression evaluates to false', function() {
            beforeEach(function() {
                model.set('name', 'tset');
            });

            it('should hide the field in the view', function() {
                action.exec(slContext);
                expect(slContext.addClass).toHaveBeenCalledWith(targetField, 'vis_action_hidden', true);
            });

            using('different dependencyState and def required values', [
                {
                    dependencyStates: undefined,
                    required: false,
                    expectedStoredRequired: false
                },
                {
                    dependencyStates: {},
                    required: false,
                    expectedStoredRequired: false
                },
                {
                    dependencyStates: {
                        required: false
                    },
                    required: false,
                    expectedStoredRequired: false
                },
                {
                    dependencyStates: undefined,
                    required: true,
                    expectedStoredRequired: true
                },
                {
                    dependencyStates: {},
                    required: true,
                    expectedStoredRequired: true
                },
                {
                    dependencyStates: {
                        required: true
                    },
                    required: true,
                    expectedStoredRequired: true
                },
                {
                    dependencyStates: {
                        required: true
                    },
                    required: false,
                    expectedStoredRequired: true
                },
                {
                    dependencyStates: {
                        required: false
                    },
                    required: true,
                    expectedStoredRequired: false
                },
            ], function(values) {
                it('should store the current requiredness of the field for later', function() {
                    view.fields.description.dependencyStates = values.dependencyStates;
                    view.fields.description.def.required = values.required;
                    action.exec(slContext);
                    expect(view.getField(targetField).dependencyStates).toEqual(jasmine.objectContaining({
                        required: values.expectedStoredRequired
                    }));
                });
            });

            it('should make the field not required', function() {
                action.exec(slContext);
                expect(slContext.setFieldRequired).toHaveBeenCalledWith(targetField, false);
            });

            it('should disable the field so it cannot be edited in any way', function() {
                action.exec(slContext);
                expect(slContext.setFieldDisabled).toHaveBeenCalledWith(targetField, true);
            });
        });

        describe('when the visibility expression evaluates to true', function() {
            beforeEach(function() {
                model.set('name', 'test');
            });

            it('should show the field in the view', function() {
                action.exec(slContext);
                expect(slContext.removeClass).toHaveBeenCalledWith(targetField, 'vis_action_hidden', true);
            });

            using('different dependencyState required settings', [
                {
                    dependencyStates: {
                        required: true
                    }
                },
                {
                    dependencyStates: {
                        required: false
                    }
                }
            ], function(values) {
                it('should set the requiredness of the field if necessary', function() {
                    view.fields.description.dependencyStates = values.dependencyStates;
                    action.exec(slContext);
                    expect(slContext.setFieldRequired).toHaveBeenCalledWith(
                        targetField,
                        values.dependencyStates.required
                    );
                });
            });

            using('different field editatbility settings', [
                {
                    fieldDefs: {
                        calculated: false,
                        enforced: false,
                        readOnlyProp: false
                    },
                    expectDisabled: false
                },
                {
                    fieldDefs: {
                        calculated: true,
                        enforced: false,
                        readOnlyProp: false
                    },
                    expectDisabled: false
                },
                {
                    fieldDefs: {
                        calculated: true,
                        enforced: true,
                        readOnlyProp: false
                    },
                    expectDisabled: true
                },
                {
                    fieldDefs: {
                        calculated: false,
                        enforced: false,
                        readOnlyProp: true
                    },
                    expectDisabled: true
                }
            ], function(values) {
                it('should set field to editability correctly', function() {
                    _.extend(view.fields.description.def, values.fieldDefs);
                    action.exec(slContext);
                    expect(slContext.setFieldDisabled).toHaveBeenCalledWith(targetField, values.expectDisabled);
                });
            });
        });
    });
});
