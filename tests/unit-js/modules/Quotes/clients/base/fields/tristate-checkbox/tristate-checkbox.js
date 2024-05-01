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
describe('Quotes.Base.Fields.TristateCheckbox', function() {
    var app;
    var field;
    var fieldDef;
    var context;
    var options;

    beforeEach(function() {
        app = SugarTest.app;
        fieldDef = {
            type: 'tristate-checkbox',
            name: 'subtotal',
            dependentFields: {},
            eventViewName: 'config-columns'
        };

        context = app.context.getContext();
        options = {
            context: context
        };

        field = SugarTest.createField(
            'base',
            fieldDef.name,
            fieldDef.type,
            'detail',
            fieldDef,
            'Quotes',
            null,
            context,
            true
        );
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        fieldDef = null;
        field = null;
        options = null;
    });

    describe('initialize()', function() {
        var depFields;
        var statesData;

        beforeEach(function() {
            depFields = {
                test1: {
                    hi: 'there'
                }
            };
            statesData = {
                state1: {
                    hi: 'there'
                }
            };

            sinon.stub(field, '_getStatesData').callsFake(function() {
                return statesData;
            });
            sinon.stub(field, '_getIsRequired').callsFake(function() {
                return true;
            });
            sinon.stub(field, '_getInitialState').callsFake(function() {
                return 'test1';
            });
            sinon.stub(field, 'changeState');
            sinon.stub(app.lang, 'get').callsFake(function(lbl) {
                return lbl;
            });
        });

        afterEach(function() {
            depFields = null;
            statesData = null;
        });

        it('should set dependentFields', function() {
            field.initialize({
                def: {
                    dependentFields: depFields
                }
            });

            expect(field.dependentFields).toEqual(depFields);
        });

        it('should set statesData', function() {
            field.initialize({});

            expect(field.statesData).toEqual(statesData);
        });

        it('should set isRequired', function() {
            field.initialize({
                def: {
                    required: true
                }
            });

            expect(field.isRequired).toBeTruthy();
        });

        describe('when currentState is not defined', function() {
            it('should call _getInitialState', function() {
                field.initialize({});

                expect(field._getInitialState).toHaveBeenCalled();
            });

            it('should call changeState', function() {
                field.initialize({});

                expect(field.changeState).toHaveBeenCalledWith('test1');
            });
        });

        describe('when currentState is defined', function() {
            beforeEach(function() {
                options.viewDefs = {
                    currentState: 'checked'
                };
            });
            it('should not call _getInitialState', function() {
                field.initialize(options);

                expect(field._getInitialState).not.toHaveBeenCalled();
            });

            it('should call changeState with checked', function() {
                field.initialize(options);

                expect(field.changeState).toHaveBeenCalledWith('checked');
            });
        });

        it('should set serviceRelatedFieldArr', function() {
            field.initialize({});

            expect(field.serviceRelatedFieldArr).toEqual([
                'service_start_date',
                'service_end_date',
                'renewable',
                'service_duration',
                'service',
        ]);
        });

        it('should set tooltipLabel', function() {
            expect(field.tooltipLabel).toBe('LBL_CONFIG_TOOLTIP_FIELD_REQUIRED_BY');
        });

        describe('when this.name is service_duration', function() {
            beforeEach(function() {
                sinon.stub(field, '_super');
                field.name = 'service_duration';
            });

            afterEach(function() {
                field.name = null;
            });

            it('should call changeState with checked when worksheet_columns has service_duration',
                function() {
                field.context.set('worksheet_columns', {
                    test: {
                        name: 'service_duration'
                    }
                });
                field.initialize({});

                expect(field.changeState).toHaveBeenCalledWith('checked');
                field.context.unset();
            });

            it('should not call changeState when worksheet_columns does not have service_duration',
                function() {
                    field.context.set('worksheet_columns', {
                        test: {
                            name: 'test'
                        }
                    });
                    field.initialize({});

                    expect(field.changeState).not.toHaveBeenCalledWith('checked');
                });
        });
    });

    describe('bindDataChange()', function() {
        beforeEach(function() {
            sinon.stub(field, '_super');
            sinon.stub(field.context, 'on');
        });

        it('should call parent bindDataChange', function() {
            field.bindDataChange();

            expect(field._super).toHaveBeenCalledWith('bindDataChange');
        });

        it('should set context listener for config:<eventName>:<fieldName>:related:toggle', function() {
            field.bindDataChange();

            expect(field.context.on).toHaveBeenCalledWith(
                'config:' + fieldDef.eventViewName + ':' + fieldDef.name + ':related:toggle'
            );
        });

        it('should set context listener for config:<eventName>:<fieldName>:reset', function() {
            field.bindDataChange();

            expect(field.context.on).toHaveBeenCalledWith('config:fields:' + fieldDef.eventViewName + ':reset');
        });
    });

    describe('changeState()', function() {
        var currentState;
        var currentStateName;
        var nextStateName;
        var statesData;

        beforeEach(function() {
            currentState = {
                currentState: 'yup'
            };
            field.currentState = currentState;
            currentStateName = 'currentState';
            field.currentStateName = currentStateName;
            nextStateName = 'nextState';
            statesData = {
                currentState: 'currentStateData',
                nextState: 'nextStateData'
            };
            field.statesData = statesData;
            sinon.stub(field, 'render');

            field.changeState(nextStateName);
        });

        afterEach(function() {
            currentState = null;
            currentStateName = null;
            nextStateName = null;
            statesData = null;
        });

        it('should set previousState from currentState', function() {
            expect(field.previousState).toBe(currentState);
        });

        it('should set previousStateName from currentStateName', function() {
            expect(field.previousStateName).toBe(currentStateName);
        });

        it('should set currentStateName from next state', function() {
            expect(field.currentStateName).toBe(nextStateName);
        });

        it('should set currentState from currentState', function() {
            expect(field.currentState).toBe(statesData[nextStateName]);
        });

        it('should call render', function() {
            expect(field.render).toHaveBeenCalled();
        });
    });

    describe('render()', function() {
        var jQuery;
        var propStub;

        beforeEach(function() {
            propStub = sinon.stub();
            jQuery = function() {
                return {
                    prop: propStub
                };
            };
            field.$ = jQuery;
            sinon.stub(field, '_updateTooltipText');
            sinon.stub(field, '_super');
        });

        afterEach(function() {
            jQuery = null;
            propStub = null;
        });

        it('should call _updateTooltipText', function() {
            field.render();

            expect(field._updateTooltipText).toHaveBeenCalled();
        });

        it('should call parent render', function() {
            field.render();

            expect(field._super).toHaveBeenCalledWith('render');
        });

        it('should jQuery prop if current state isIndeterminate is true', function() {
            field.currentState = {
                isIndeterminate: true
            };
            field.render();

            expect(propStub).toHaveBeenCalledWith('indeterminate', true);
        });

        it('should not jQuery prop if current state isIndeterminate is false', function() {
            field.currentState = {
                isIndeterminate: false
            };
            field.render();

            expect(propStub).not.toHaveBeenCalled();
        });
    });

    describe('_getIsRequired()', function() {
        it('should return field.def.required when that is set', function() {
            field.def.required = true;

            expect(field._getIsRequired()).toBeTruthy();
        });

        it('should return an false when field.def.required is not set', function() {
            field.def.required = undefined;

            expect(field._getIsRequired()).toBeFalsy();
        });
    });

    describe('_getStatesData()', function() {
        var statesData;

        beforeEach(function() {
            statesData = {
                state1: 'hi',
                state2: 'hi',
                state3: 'sup'
            };

            sinon.stub(field, '_getStatesData').callsFake(function() {
                return statesData;
            });
        });

        afterEach(function() {
            statesData = null;
        });

        it('should return state data', function() {
            expect(field._getStatesData()).toEqual(statesData);
        });
    });

    describe('_onToggleRelatedField()', function() {
        var relatedField;
        var relatedField2;
        var relatedField3;
        var relatedFields;

        beforeEach(function() {
            sinon.stub(field, 'changeState');
            sinon.stub(field.context, 'trigger');
            sinon.stub(field, 'render');

            relatedField = {
                name: 'relField1',
                def: {
                    labelModule: 'Quotes'
                }
            };
            relatedField2 = {
                name: 'relField2',
                def: {
                    labelModule: 'Quotes'
                }
            };
            relatedField3 = {
                name: 'renewable',
                def: {
                    labelModule: 'Products'
                }
            };
            relatedFields = [relatedField, relatedField2];
        });

        describe('when toggleFieldOn is true', function() {
            beforeEach(function() {
                field.dependentFields = {};
                field.isRequired = false;
            });

            describe('when the related field is a service field', function() {
                it('should add all the service fields to dependent fields', function() {
                    field._onToggleRelatedField(relatedField3, true);

                    expect(field.dependentFields).toEqual({
                        service_start_date: {
                            module: 'Products',
                            field: 'service_start_date',
                            reason: 'related_fields'
                        },
                        service_end_date: {
                            module: 'Products',
                            field: 'service_end_date',
                            reason: 'related_fields'
                        },
                        renewable: {
                            module: 'Products',
                            field: 'renewable',
                            reason: 'related_fields'
                        },
                        service: {
                            module: 'Products',
                            field: 'service',
                            reason: 'related_fields'
                        },
                        service_duration: {
                            module: 'Products',
                            field: 'service_duration',
                            reason: 'related_fields'
                        }
                    });
                });
            });

            it('should add the relatedField to dependentFields', function() {
                field._onToggleRelatedField(relatedField, true);

                expect(field.dependentFields).toEqual({
                    relField1: {
                        module: 'Quotes',
                        field: 'relField1',
                        reason: 'related_fields'
                    }
                });
            });

            it('should set required to be true', function() {
                field._onToggleRelatedField(relatedField, true);

                expect(field.isRequired).toBeTruthy();
            });

            describe('when currentStatName is unchecked', function() {
                beforeEach(function() {
                    field.currentStateName = 'unchecked';
                });

                it('should call changeState with checked when the related field is a service field',
                    function() {
                    field.name = 'renewable';
                    field._onToggleRelatedField(relatedField3, true);

                    expect(field.changeState).toHaveBeenCalledWith('checked');
                });

                it('should call changeState with filled when the related field is not a service field',
                    function() {
                    field._onToggleRelatedField(relatedField, true);

                    expect(field.changeState).toHaveBeenCalledWith('filled');
                });
            });

            it('should not call changeState when currentStateName is not unchecked', function() {
                field.currentStateName = 'checked';
                field._onToggleRelatedField(relatedField, true);

                expect(field.changeState).not.toHaveBeenCalled();
            });
        });

        describe('when toggleFieldOn is false', function() {
            beforeEach(function() {
                field.isRequired = true;
            });

            it('should delete all the service fields from dependentFields if related field is service field',
                function() {
                field.dependentFields = {
                    renewable: relatedField3,
                    service_duration: {
                        name: 'service_duration',
                        def: {
                            labelModule: 'Products'
                        }
                    },
                    service_end_date: {
                        name: 'service_end_date',
                        def: {
                            labelModule: 'Products'
                        }
                    }
                };
                field.name = 'renewable';
                field._onToggleRelatedField(relatedField3, false);

                expect(field.dependentFields).toEqual({});
            });

            it('should delete only the related fields from dependentFields if related field is not service field',
                function() {
                    field.dependentFields = {
                        relField1: relatedField,
                        relField2: relatedField2,
                        renewable: relatedField3
                    };
                    field._onToggleRelatedField(relatedFields, false);

                    expect(field.dependentFields).toEqual({renewable: relatedField3});
                });

            describe('when dependentFields gets cleared out', function() {
                it('should call changeState with unchecked when currentStateName is filled', function() {
                    field.dependentFields = {
                        relField1: relatedField,
                        relField2: relatedField2
                    };
                    field.currentStateName = 'filled';
                    field._onToggleRelatedField(relatedFields, false);

                    expect(field.changeState).toHaveBeenCalledWith('unchecked');
                });

                it('should call changeState with unchecked when currentStateName is checked for a service field',
                    function() {
                    field.dependentFields = {
                        renewable: relatedField3
                    };
                    field.name = 'renewable';
                    field.currentStateName = 'checked';
                    field._onToggleRelatedField(relatedField3, false);

                    expect(field.changeState).toHaveBeenCalledWith('unchecked');
                });

                it('should set isRequired false', function() {
                    field.dependentFields = {
                        relField1: relatedField,
                        relField2: relatedField2
                    };
                    field._onToggleRelatedField(relatedFields, false);

                    expect(field.isRequired).toBeFalsy();
                });
            });

            it('should not set isRequired to false when dependentFields is not empty', function() {
                field.dependentFields = {
                    relField1: relatedField,
                    relField2: relatedField2
                };
                field._onToggleRelatedField(relatedField2, false);

                expect(field.isRequired).toBeTruthy();
            });
        });

        describe('when a field does not have relatedFields', function() {
            beforeEach(function() {
                field.def.relatedFields = undefined;
            });

            it('should not trigger anything on the context', function() {
                field._onToggleRelatedField(relatedField, false);

                expect(field.context.trigger).not.toHaveBeenCalled();
            });
        });

        describe('when a field has relatedFields', function() {
            describe('when toggleFieldOn is true and related field is not a service field',
                function() {
                it('should trigger context event for each relatedField', function() {
                    field.def.relatedFields = ['relField3'];
                    field._onToggleRelatedField(relatedFields, true);

                    expect(field.context.trigger).toHaveBeenCalledWith(
                        'config:' + field.def.eventViewName + ':relField3:related:toggle',
                        relatedFields,
                        true
                    );
                });
            });

            describe('when toggleFieldOn is true and it is a service field',
                function() {
                it('should trigger context event for each relatedField', function() {
                    field.def.relatedFields = ['service_duration'];
                    field._onToggleRelatedField(relatedFields, true);

                    expect(field.context.trigger).not.toHaveBeenCalledWith(
                        'config:' + field.def.eventViewName + ':service_duration:related:toggle',
                        relatedFields,
                        true
                    );
                });
            });

            describe('when toggleFieldOn is false', function() {
                beforeEach(function() {
                    field.def.relatedFields = ['relField3'];
                });

                it('should trigger context event for each relatedField', function() {
                    field._onToggleRelatedField(relatedFields, false);

                    expect(field.context.trigger).toHaveBeenCalledWith(
                        'config:' + field.def.eventViewName + ':relField3:related:toggle',
                        relatedFields,
                        false
                    );
                });

                it('should include the current field in relatedFields if the field' +
                    ' is not required and current state is unchecked', function() {
                    relatedFields.push(field);
                    field.isRequired = false;
                    field.currentStateName = 'unchecked';
                    field._onToggleRelatedField(relatedFields, false);

                    expect(field.context.trigger).toHaveBeenCalledWith(
                        'config:' + field.def.eventViewName + ':relField3:related:toggle',
                        relatedFields,
                        false
                    );
                });
            });
        });

        it('should call render', function() {
            field._onToggleRelatedField(relatedField, false);

            expect(field.render).toHaveBeenCalled();
        });
    });

    describe('_onFieldsReset()', function() {
        beforeEach(function() {
            sinon.stub(field, 'changeState');
            sinon.stub(field, '_getInitialState');
        });

        it('should set dependentFields from the defs', function() {
            field.dependentFields = undefined;
            field.def.dependentFields = {
                test: '123'
            };
            field._onFieldsReset();

            expect(field.dependentFields).toEqual(field.def.dependentFields);
        });

        it('should set isRequired true when dependentFields is not empty', function() {
            field.dependentFields = undefined;
            field.def.dependentFields = {
                test: '123'
            };
            field._onFieldsReset();

            expect(field.isRequired).toBeTruthy();
        });

        it('should set isRequired false when dependentFields is empty', function() {
            field.dependentFields = undefined;
            field.def.dependentFields = {};
            field._onFieldsReset();

            expect(field.isRequired).toBeFalsy();
        });
    });

    describe('onCheckboxClicked()', function() {
        var currentState;
        var event;

        beforeEach(function() {
            currentState = {
                nextState: 'nextState',
                nextStateIfRequired: 'nextStateIfRequired'
            };
            field.currentState = currentState;
            event = {
                preventDefault: sinon.stub()
            };
            field.currentStateName = 'currentStateName';
            sinon.stub(field, '_onCheckboxClicked');
            sinon.stub(field, 'changeState');
            field.view.model = new Backbone.Model();
        });

        afterEach(function() {
            event = null;
            currentState = null;
        });

        it('should call preventDefault on event', function() {
            field.onCheckboxClicked(event);

            expect(event.preventDefault).toHaveBeenCalled();
        });

        it('should set next state to be nextStateIfRequired when field isRequired is true', function() {
            field.isRequired = true;
            field.onCheckboxClicked(event);

            expect(field._onCheckboxClicked).toHaveBeenCalledWith(
                field.currentStateName,
                currentState.nextStateIfRequired
            );
        });

        it('should set next state to be nextState when field isRequired is false', function() {
            field.isRequired = false;
            field.onCheckboxClicked(event);

            expect(field._onCheckboxClicked).toHaveBeenCalledWith(
                field.currentStateName,
                currentState.nextState
            );
        });

        it('should call changeState with nextStateIfRequired when field isRequired is true', function() {
            field.isRequired = true;
            field.onCheckboxClicked(event);

            expect(field.changeState).toHaveBeenCalledWith(currentState.nextStateIfRequired);
        });

        it('should call changeState with nextState when field isRequired is false', function() {
            field.isRequired = false;
            field.onCheckboxClicked(event);

            expect(field.changeState).toHaveBeenCalledWith(currentState.nextState);
        });

        it('should call changeState with unchecked when a service related field has nextState as filled',
            function() {
            field.isRequired = true;
            field.name = 'renewable';
            field.currentState.nextStateIfRequired = 'filled';
            field.onCheckboxClicked(event);

            expect(field.changeState).toHaveBeenCalledWith('unchecked');
        });
    });

    describe('_onCheckboxClicked()', function() {
        beforeEach(function() {
            sinon.stub(field.context, 'trigger');
        });

        afterEach(function() {

        });

        it('should trigger event on context with passed in params', function() {
            field._onCheckboxClicked('state1', 'state2');

            expect(field.context.trigger).toHaveBeenCalledWith(
                'config:' + field.def.eventViewName + ':field:change',
                field,
                'state1',
                'state2'
            );
        });
    });

    describe('_updateTooltipText()', function() {
        var oldDirection;
        beforeEach(function() {
            oldDirection = app.lang.direction;
        });

        afterEach(function() {
            app.lang.direction = oldDirection;
        });

        it('should set tooltipText based on dependentFields - LTR', function() {
            app.lang.direction = 'ltr';
            field.dependentFields = {
                test1: {
                    module: 'ProductBundles',
                    field: 'test1',
                    reason: 'rollup'
                }
            };
            field._updateTooltipText();

            expect(field.tooltipText).toBe(
                '<div class="tristate-checkbox-config-tooltip">' +
                'LBL_CONFIG_TOOLTIP_FIELD_REQUIRED_BY<ul>' +
                '<li>ProductBundles - test1</li></ul></div>'
            );
        });

        it('should set tooltipText based on dependentFields - RTL', function() {
            app.lang.direction = 'rtl';
            field.dependentFields = {
                test1: {
                    module: 'ProductBundles',
                    field: 'test1',
                    reason: 'rollup'
                }
            };
            field._updateTooltipText();

            expect(field.tooltipText).toBe(
                '<div class="tristate-checkbox-config-tooltip">' +
                'LBL_CONFIG_TOOLTIP_FIELD_REQUIRED_BY<ul>' +
                '<li>test1 - ProductBundles</li></ul></div>'
            );
        });

        it('should set tooltipText to be empty if dependentFields is empty', function() {
            app.lang.direction = 'ltr';
            field.dependentFields = {};
            field._updateTooltipText();

            expect(field.tooltipText).toBe('');
        });
    });

    describe('_getInitialState()', function() {
        it('should return field.def.initialState when that is set', function() {
            field.def.initialState = 'filled';

            expect(field._getInitialState()).toBe(field.def.initialState);
        });

        it('should return unchecked when field.def.initialState is not set', function() {
            field.def.initialState = undefined;

            expect(field._getInitialState()).toBe('unchecked');
        });
    });
});
