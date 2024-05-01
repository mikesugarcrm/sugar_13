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
describe('View.Fields.Base.RepeatDaysField', function() {
    var app,
        field,
        createFieldProperties,
        fieldDefs,
        module = 'Meetings';

    beforeEach(function() {
        app = SugarTest.app;
        fieldDefs = {
            name: 'repeat_days',
            type: 'repeat-days',
            label: 'Repeat Days',
            options: [{'': ''}],
            isMultiSelect: true
        };

        createFieldProperties = {
            client: 'base',
            name: 'repeat_days',
            type: 'repeat-days',
            viewName: 'edit',
            fieldDef: fieldDefs,
            module: module
        };
        field = SugarTest.createField(createFieldProperties);


        field.$ = function() {
            return {
                on: function() {},
                off: function() {}
            }
        };

        field.select2Field = {
            modeSetTo: 'abc',
            setMode: function(mode) {
                this.modeSetTo = mode;
            },
            $el: {
                on: function() {},
                off: function() {}
            },
            setElement: function() {},
            _render: function() {},
            render: function() {},
            dispose: function() {}
        };

        sinon.stub(field, '_super').callsFake(function() {});
        sinon.stub(field.model, 'addValidationTask');
        sinon.stub(field.model, 'removeValidationTask');
        sinon.stub(app.lang, 'getAppListStrings').callsFake(function() {
            return [['1', '2', '3']];
        });
    });

    afterEach(function() {
        if (field) {
            field.model.off();
            field.dispose();
        }
        sinon.restore();

        field = null;
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('initialize()', function() {
        it('should set datesDom', function() {
            field.initialize();

            expect(field.datesDom).toEqual([['1', '2', '3']]);
        });

        it('should add model validation task', function() {
            field.initialize();

            expect(field.model.addValidationTask).toHaveBeenCalled();
        });

        it('should initialize selectedDates with existing selected dates', function() {
            field.model.set('repeat_days', '1,2,3', {silent: true});
            sinon.stub(field, 'format').callsFake(function(val) { return val; });
            field.initialize();

            expect(field.selectedDates).toEqual('1,2,3');
        });

        it('should initialize selectedDates to empty array if no selected dates', function() {
            field.model.set('repeat_days', undefined, {silent: true});
            field.initialize();

            expect(field.selectedDates).toEqual([]);
        });
    });

    describe('setMode()', function() {
        beforeEach(function() {
            sinon.stub(field, '_updateSelect2SelectedDates').callsFake(function() {});
            sinon.stub(field, 'decorateCalendarDates').callsFake(function() {});
        });

        it('should not call getSelect2Field if model is new', function() {
            field.model.set('id', null);
            field.setMode('edit');

            expect(field.getSelect2Field).not.toHaveBeenCalled();
        });

        it('should not call getSelect2Field if mode is not edit', function() {
            field.model.set('id', 'id');
            field.action = 'detail';
            field.setMode('detail');

            expect(field.getSelect2Field).not.toHaveBeenCalled();
        });

        it('should call setMode on select2 field when mode is edit', function() {
            field.model.set('id', 'id');
            field.action = 'edit';
            field.setMode('edit');

            expect(field.select2Field.modeSetTo).toBe('edit');
        });

        it('should call _updateSelect2SelectedDates with true, false', function() {
            field.model.set('id', 'id');
            field.action = 'edit';
            field.setMode('edit');

            expect(field._updateSelect2SelectedDates).toHaveBeenCalledWith(true, false);
        });

        it('should call decorateCalendarDates', function() {
            field.model.set('id', 'id');
            field.action = 'edit';
            field.setMode('edit');

            expect(field.decorateCalendarDates).toHaveBeenCalled();
        });
    });

    describe('_render()', function() {
        beforeEach(function() {
            sinon.stub(field, '_addDateFieldEvents').callsFake(function() {});
            sinon.stub(field.select2Field, 'setElement').callsFake(function() {});
            sinon.stub(field.select2Field, 'render').callsFake(function() {});
        });

        it('should call setElement on the select2 field', function() {
            field._render();

            expect(field.select2Field.setElement).toHaveBeenCalled();
        });

        it('should call render on the select2 field', function() {
            field._render();

            expect(field.select2Field.render).toHaveBeenCalled();
        });

        it('should call _addDateFieldEvents', function() {
            field._render();

            expect(field._addDateFieldEvents).toHaveBeenCalled();
        });
    });

    describe('format()', function() {
        var result;
        it('should return the value if value is not a string', function() {
            result = field.format(true);

            expect(result).toEqual(true);
        });

        it('should return an empty array if value is an empty string', function() {
            result = field.format("");

            expect(result).toEqual([]);
        });

        it('should return an array of values if passed csv string', function() {
            result = field.format('1,2,3');

            expect(result).toEqual(['1', '2', '3']);
        });
    });

    describe('unformat()', function() {
        var result;
        it('should return the value if value is not an array', function() {
            result = field.unformat('1,2');

            expect(result).toBe('1,2');
        });

        it('should return a csv string if value is an array', function() {
            result = field.unformat([1, 2]);

            expect(result).toBe('1,2');
        });
    });

    describe('_addDateFieldEvents()', function() {
        var onStub,
            select2FieldSpy;

        it('should add event listeners to items in the dropdown', function() {
            onStub = sinon.stub();
            field.$ = function() {
                return {
                    on: onStub,
                    off: function() {}
                }
            };
            select2FieldSpy = sinon.stub(field.select2Field.$el, 'on');
            field._addDateFieldEvents();

            expect(onStub).toHaveBeenCalled();
            expect(select2FieldSpy).toHaveBeenCalled();
        });
    });

    describe('decorateCalendarDates()', function() {
        var $el1,
            $el2;

        beforeEach(function() {
            $el1 = $('<div id="repeat-on-day-1">');
            $el2 = $('<div id="repeat-on-day-2">');
            field.$ = function(val) {
                if(val === '#repeat-on-day-1') {
                    return $el1;
                } else {
                    return $el2;
                }
            };
        });

        it('should add selected class to no elements if selectedDates is empty', function() {
            field.selectedDates = [];
            field.decorateCalendarDates();

            expect($el1.hasClass('selected')).toBeFalsy();
            expect($el2.hasClass('selected')).toBeFalsy();
        });

        it('should add selected class to selectedDates elements only', function() {
            field.selectedDates = [1];
            field.decorateCalendarDates();

            expect($el1.hasClass('selected')).toBeTruthy();
            expect($el2.hasClass('selected')).toBeFalsy();
        });

        it('should add selected class to all selectedDates elements', function() {
            field.selectedDates = ['1', '2'];
            field.decorateCalendarDates();

            expect($el1.hasClass('selected')).toBeTruthy();
            expect($el2.hasClass('selected')).toBeTruthy();
        });
    });

    describe('_onDatePicked()', function() {
        var evt,
            targetEl;

        beforeEach(function() {
            targetEl = $('<div id="repeat-on-day-1">1</div>');
            evt = {
                target: targetEl
            };
            field.selectedDates = [];
            sinon.stub(field, '_updateSelect2SelectedDates').callsFake(function() {});
        });

        it('should add selected class if not selected', function() {
            field._onDatePicked(evt);

            expect(evt.target.hasClass('selected')).toBeTruthy();
        });

        it('should remove selected class if selected class already exists', function() {
            targetEl = $('<div id="repeat-on-day-1" class="selected">1</div>');
            evt.target = targetEl;
            field._onDatePicked(evt);

            expect($(evt.target).hasClass('selected')).toBeFalsy();
        });

        it('should add selected date to selectedDates if not selected', function() {
            field._onDatePicked(evt);

            expect(field.selectedDates).toEqual(['1']);
        });

        it('should remove selected date if selectedDates already has the value', function() {
            targetEl = $('<div id="repeat-on-day-1" class="selected">1</div>');
            evt.target = targetEl;
            field.selectedDates = ['1', '2'];
            field._onDatePicked(evt);

            expect(field.selectedDates).toEqual(['2']);
        });

        it('should call _updateSelect2SelectedDates', function() {
            field.selectedDates = [1];
            field._onDatePicked(evt);

            expect(field._updateSelect2SelectedDates).toHaveBeenCalled();
        });
    });

    describe('_onSelect2Change()', function() {
        var evt;
        beforeEach(function() {
            evt = {};
        });

        it('should do nothing if not a removed event', function() {
            field._onSelect2Change(evt);

            expect(field.$).not.toHaveBeenCalled();
        });

        describe('should handle the change if it is a removed event', function() {
            var $el1,
                $el2;

            beforeEach(function() {
                evt.removed = {};
                evt.removed.id = '1';
                $el1 = $('<div id="repeat-on-day-1">');
                $el2 = $('<div id="repeat-on-day-2">');
                field.$ = function(val) {
                    if(val === '#repeat-on-day-1') {
                        return $el1;
                    } else {
                        return $el2;
                    }
                };
                $el1.addClass('selected');
                field.selectedDates = ['1', '2'];
            });

            it('should remove the selected class from the element removed', function() {
                field._onSelect2Change(evt);

                expect($el1.hasClass('selected')).toBeFalsy();
            });

            it('should remove the removed option from selectedDates', function() {
                field._onSelect2Change(evt);

                expect(field.selectedDates).toEqual(['2']);
            });

            it('should call _updateSelect2SelectedDates if there are still selectedDates', function() {
                sinon.stub(field, '_updateSelect2SelectedDates').callsFake(function() {});
                sinon.stub(field, '_setSelectedDatesOnModel').callsFake(function() {});
                field._onSelect2Change(evt);

                expect(field._updateSelect2SelectedDates).toHaveBeenCalledWith(false);
                expect(field._setSelectedDatesOnModel).not.toHaveBeenCalled();
            });

            it('should not call _updateSelect2SelectedDates if there are no more selectedDates', function() {
                sinon.stub(field, '_updateSelect2SelectedDates').callsFake(function() {});
                sinon.stub(field, '_setSelectedDatesOnModel').callsFake(function() {});
                field.selectedDates = ['1'];
                field._onSelect2Change(evt);

                expect(field._updateSelect2SelectedDates).not.toHaveBeenCalled();
                expect(field._setSelectedDatesOnModel).toHaveBeenCalledWith(null);
                expect(field.select2Field.items).toBe(null);
            });
        });
    });

    describe('_updateSelect2SelectedDates()', function() {
        beforeEach(function() {
            field.selectedDates = ['3', '1', '2'];

            sinon.stub(field.select2Field, 'render').callsFake(function() {});
            sinon.stub(field, '_setSelectedDatesOnModel').callsFake(function() {});
        });

        it('should sort selected dates numerically', function() {
            field._updateSelect2SelectedDates();

            expect(field.selectedDates).toEqual(['1', '2', '3']);
        });

        it('should set select2Field.items to an object version of selectedDates array', function() {
            field._updateSelect2SelectedDates();

            expect(field.select2Field.items).toEqual({
                1: '1',
                2: '2',
                3: '3'
            });
        });

        it('should call render field by default', function() {
            field._updateSelect2SelectedDates();

            expect(field.select2Field.render).toHaveBeenCalled();
        });

        it('should call _setSelectedDatesOnModel by default', function() {
            field._updateSelect2SelectedDates();

            expect(field._setSelectedDatesOnModel).toHaveBeenCalled();
        });

        it('should not call render field on false param passed in', function() {
            field._updateSelect2SelectedDates(false, true);

            expect(field.select2Field.render).not.toHaveBeenCalled();
        });

        it('should not call _setSelectedDatesOnModel on false param passed in', function() {
            field._updateSelect2SelectedDates(true, false);

            expect(field._setSelectedDatesOnModel).not.toHaveBeenCalled();
        });
    });

    describe('_setSelectedDatesOnModel()', function() {
        it('should set array selected dates to string on the model when passed an array', function() {
            field._setSelectedDatesOnModel(['1', '2']);

            expect(field.model.get('repeat_days')).toBe('1,2');
        });

        it('should set array selected dates to string on the model when passed a string', function() {
            field._setSelectedDatesOnModel('1,2');

            expect(field.model.get('repeat_days')).toBe('1,2');
        });
    });

    describe('getSelect2Field()', function() {
        beforeEach(function() {
            sinon.stub(app.view, 'createField').callsFake(function() {
                return {
                    setMode: function(mode) {},
                    sfId: '1'
                }
            });
        });

        it('should return the select2Field if it is already created', function() {
            field.getSelect2Field();

            expect(app.view.createField).not.toHaveBeenCalled();
        });

        it('should create the field if it doesnt exist', function() {
            var oldSelect2Field = field.select2Field;
            field.select2Field = undefined;
            field.getSelect2Field();

            expect(app.view.createField).toHaveBeenCalled();

            field.select2Field = oldSelect2Field;
        });
    });

    describe('_doValidateRepeatDays', function() {
        var errorsResults = {},
            callback;
        beforeEach(function() {
            callback = function(model, fields, errors) {
                errorsResults = errors;
            }
        });

        it('should add no errors with no repeat_selector', function() {
            field.model.set({
                repeat_selector: '',
                repeat_days: 'test'
            });
            field._doValidateRepeatDays({}, {}, callback);

            expect(errorsResults.repeat_days).not.toBeDefined();
        });

        it('should add no errors if value is a string', function() {
            field.model.set({
                repeat_selector: 'Each',
                repeat_days: 'test'
            });
            field._doValidateRepeatDays({}, {}, callback);

            expect(errorsResults.repeat_days).not.toBeDefined();
        });

        it('should add no errors if value is empty', function() {
            field.model.set({
                repeat_selector: 'Each',
                repeat_days: 'test'
            });
            field._doValidateRepeatDays({}, {}, callback);

            expect(errorsResults.repeat_days).not.toBeDefined();
        });

        it('should errors if value is not a string', function() {
            field.model.set({
                repeat_selector: 'Each',
                repeat_days: ['test']
            });
            field._doValidateRepeatDays({}, {}, callback);

            expect(errorsResults.repeat_days).toEqual({required: true});
        });
    });

    describe('_dispose', function() {
        it('should remove custom listeners on dispose', function() {
            let fieldElOffStub = sinon.stub();
            let selectFieldOffStub = sinon.stub();
            sinon.stub(field.select2Field, 'dispose').callsFake(function() {});
            field.$ = function() {
                return {
                    on: function() {},
                    off: fieldElOffStub
                }
            };
            field.select2Field.$el.off = selectFieldOffStub;
            field.model.off();
            field._dispose();

            expect(field.model.removeValidationTask).toHaveBeenCalled();
            expect(fieldElOffStub).toHaveBeenCalled();
            expect(selectFieldOffStub).toHaveBeenCalled();
            expect(field.select2Field.dispose).toHaveBeenCalled();
        });
    });

    using('variations of repeat selector and repeat days values',[
        {
            expectation: 'should error when repeat days has no value and repeat_selector is Each',
            repeatSelector: 'Each',
            repeatDays: '',
            isErrorExpected: true
        },
        {
            expectation: 'should not error when repeat days has a value and repeat_selector is Each',
            repeatSelector: 'Each',
            repeatDays: '31',
            isErrorExpected: false
        },
        {
            expectation: 'should not error when repeat days has no value and repeat_selector is not Each',
            repeatSelector: 'On',
            repeatDays: '',
            isErrorExpected: false
        }
    ], function (value) {
        it(value.expectation, function() {
            var errors = {};
            field.model.set('repeat_selector', value.repeatSelector, {silent: true});
            field.model.set(field.name, value.repeatDays, {silent: true});
                field._doValidateRepeatDays(null, errors, $.noop);
            expect(!_.isEmpty(errors)).toBe(value.isErrorExpected);
        });
    });
});
