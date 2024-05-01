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
describe('SetRequiredAction dependency', function() {
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
                model: model
            },
            description: {
                name: 'description',
                model: model
            }
        };

        slContext = new SUGAR.expressions.SidecarExpressionContext(view, model);
        sinon.stub(slContext, 'setFieldRequired');

        action = new SUGAR.forms.SetRequiredAction(targetField, expression);
    });

    afterEach(function() {
        sinon.restore();
        view.fields = {};
        model.dispose();
        view.dispose();
    });

    describe('exec', function() {
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
            it('should store the correct expression result in the field dependencyStates required data', function() {
                model.set('name', values.fieldValue);
                action.exec(slContext);
                expect(view.getField(targetField).dependencyStates).toEqual(jasmine.objectContaining({
                    required: values.expected
                }));
            });
        });

        describe('when the field is visible', function() {
            it('should affect actual field requiredness', function() {
                model.set('name', 'test');
                action.exec(slContext);
                expect(slContext.setFieldRequired).toHaveBeenCalledWith(targetField, 'true');
            });
        });

        describe('when the field is hidden by SugarLogic', function() {
            beforeEach(function() {
                view.fields.description.dependencyStates = {
                    visible: false
                };
            });

            it('should not affect actual field requiredness', function() {
                model.set('name', 'test');
                action.exec(slContext);
                expect(slContext.setFieldRequired).not.toHaveBeenCalled();
            });
        });
    });
});
