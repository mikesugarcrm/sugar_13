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
describe('Base.Field.CjTime', function() {
    var field;
    var app;
    var fieldName = 'test_cj_time';

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base', fieldName, 'cj-time', 'edit');
    });

    describe('_render()', function() {
        it('should call the _setupTimePicker function', function() {
            sinon.stub(field, '_setupTimePicker');
            field._render();
            expect(field._setupTimePicker).toHaveBeenCalled();
        });

        it('should not call the _setupTimePicker function', function() {
            sinon.stub(field, '_setupTimePicker');
            field = SugarTest.createField('base', fieldName, 'cj-time', 'detail');
            field._render();
            expect(field._setupTimePicker).not.toHaveBeenCalled();
        });
    });

    describe('_setupTimePicker()', function() {
        beforeEach(function() {
            sinon.stub(field, 'getUserTimeFormat').callsFake(function() {
                return 'h:ia';
            });
        });

        var timeFieldOptions = [
            {key: 'disable_text_input', value: true, name: 'disableTextInput'},
            {key: 'step', value: 15, name: 'step'},
        ];

        using('time field options', timeFieldOptions, function(option) {
            it('should use the ' + option.key + ' option from the field def', function() {
                var def;
                var spy;
                def = {time: {}};
                def.time[option.key] = option.value;
                field = SugarTest.createField('base', fieldName, 'cj-time', 'edit', def);
                spy = sinon.spy();
                sinon.stub(field, '$').withArgs(field.fieldTag).returns({
                    timepicker: function() {
                        spy(arguments);
                    }
                });
                field._setupTimePicker();
                expect(spy.args[0][0][0][option.name]).toBe(option.value);
            });
        });

    });

    describe('format and unformat', function() {
        beforeEach(function() {
            sinon.spy(app, 'date');
            sinon.spy(app.date, 'convertFormat');
            sinon.spy(app.date.fn, 'format');
            sinon.stub(field, 'getUserTimeFormat').callsFake(function() {
                return 'h:ia';
            });
        });
        describe('unformat', function() {
            it('should unformat based on user preferences and according to server format', function() {
                expect(field.unformat('07:20pm')).toBe('19:20');
                expect(app.date.convertFormat).toHaveBeenCalledWith('h:ia');
                expect(app.date.getCall(0).args[0]).toBe('07:20pm');
                expect(app.date.getCall(0).args[2]).toBe(true);
                expect(app.date.fn.format).toHaveBeenCalled();
            });

            it('should return undefined if an invalid time is supplied', function() {
                expect(field.unformat()).toBeUndefined();
                expect(field.unformat('19:20:42')).toBeUndefined();
            });

            it('should return \'\' if an empty string is supplied', function() {
                expect(field.unformat('')).toBe('');
            });
        });

        describe('format', function() {
            beforeEach(function() {
                sinon.spy(app.date.fn, 'formatUser');
            });

            it('should format according to user preferences for edit mode', function() {
                expect(field.format('19:20')).toEqual('07:20pm');
                expect(app.date).toHaveBeenCalled();
                expect(app.date.convertFormat.getCall(0)).toHaveBeenCalledWith('h:ia');
                field.dispose();
            });

            it('should format according to user preferences for detail mode', function() {
                field = SugarTest.createField('base', fieldName, 'cj-time', 'detail');
                sinon.stub(field, 'getUserTimeFormat').callsFake(function() {
                    return 'h:ia';
                });
                expect(field.format('19:20:42')).toEqual('07:20pm');
                expect(app.date).toHaveBeenCalled();
                field.dispose();
            });

            it('should return undefined if an invalid datetime is supplied', function() {
                expect(field.format()).toBeUndefined();
                expect(field.format('19:20:42')).not.toBeUndefined();
                field.dispose();
            });
        });
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        app = undefined;
        field = undefined;
    });
});
