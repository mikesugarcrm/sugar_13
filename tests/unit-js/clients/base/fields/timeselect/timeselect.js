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
describe('Base.Field.Timeselect', function() {
    var app;
    var field;
    var fieldName = 'sunday_open';
    var model;
    var module = 'BusinessCenters';
    var timeFormat;
    var typeName = 'timeselect';
    var hourFieldName = 'sunday_open_hour';
    var minuteFieldName = 'sunday_open_minute';
    var $stub;
    var timeFormatStub;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadHandlebarsTemplate(typeName, 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate(typeName, 'field', 'base', 'disabled');

        model = app.data.createBean(module, {id: '5'});

        field = SugarTest.createField(
            'base',
            fieldName,
            typeName,
            'detail',
            {fields: [{name: hourFieldName}, {name: minuteFieldName}]},
            module,
            model,
            null,
            false
        );
        timeFormat = 'hh:mma';
        timeFormatStub = sinon.stub(app.date, 'getUserTimeFormat').returns(timeFormat);
        $stub = sinon.stub(field, '$');
    });

    afterEach(function() {
        sinon.restore();
        field.dispose();
        timeFormat = null;
    });

    describe('data binding', function() {
        beforeEach(function() {
            $stub.withArgs(field.fieldTag).returns({val: sinon.stub()});
        });

        describe('from this field to the hour and minute fields', function() {
            it('should set the hour and minute fields and put the value in the time input', function() {
                field.action = 'edit';
                field.model.set(fieldName, {hour: 5, minute: 22});
                expect(field.model.get(hourFieldName)).toEqual(5);
                expect(field.model.get(minuteFieldName)).toEqual(22);
            });
        });
    });

    describe('render', function() {
        it('should set up the timepicker', function() {
            field.action = 'edit';
            var timepickerStub = sinon.stub();
            $stub.withArgs(field.fieldTag).returns({timepicker: timepickerStub});
            sinon.stub(field, '_super');
            sinon.stub(app.user, 'getPreference').withArgs('timepref').returns('my time pref');

            var meridiemStub = sinon.stub();
            meridiemStub.withArgs(1, 0, true).returns('am');
            meridiemStub.withArgs(13, 0, true).returns('pm');
            meridiemStub.withArgs(1, 0, false).returns('AM');
            meridiemStub.withArgs(13, 0, false).returns('PM');
            sinon.stub(app.date, 'localeData')
                .returns({meridiem: meridiemStub});

            field.render();

            expect(timepickerStub).toHaveBeenCalledWith({
                timeFormat: 'my time pref',
                scrollDefaultNow: true,
                step: 15,
                disableTextInput: false,
                className: 'prevent-mousedown',
                appendTo: field.$el,
                lang: {
                    am: 'am',
                    pm: 'pm',
                    AM: 'AM',
                    PM: 'PM'
                }
            });
        });
    });

    describe('format', function() {
        it('should return an empty string if null, undefined, or NaN', function() {
            expect(field.format(void 0)).toEqual('');
            expect(field.format(null)).toEqual('');
            expect(field.format(NaN)).toEqual('');
        });

        it('should return a string in the user time format if given an hour-minute object', function() {
            expect(field.format({hour: 3, minute: 25})).toEqual('03:25am');
        });
    });

    describe('unformat', function() {
        it('should return the original value if falsy', function() {
            expect(field.unformat(void 0)).toBeUndefined();
            expect(field.unformat(null)).toEqual(null);
            expect(field.unformat(false)).toEqual(false);
        });

        it('should return undefined if the time is not valid', function() {
            expect(field.unformat('not a valid time')).toBeUndefined();
        });

        using(
            'different times and time formats',
            [
                {value: '03:00am', expected: {hour: 3, minute: 0}, timeFormat: 'hh:mma', time: [3, 0]},
                {value: '12:00am' /*midnight*/, expected: {hour: 0, minute: 0}, timeFormat: 'hh:mma', time: [0, 0]},
                {value: '12:00pm' /*noon*/, expected: {hour: 12, minute: 0}, timeFormat: 'hh:mma', time: [12, 0]},
                {value: '04:00pm', expected: {hour: 16, minute: 0}, timeFormat: 'hh:mma', time: [16, 0]},
                {value: '7:30pm', expected: {hour: 19, minute: 30}, timeFormat: 'hh:mma', time: [19, 30]},
                {value: '00.00', expected: {hour: 0, minute: 0}, timeFormat: 'HH.mm', time: [0, 0]},
                {value: '01.00', expected: {hour: 1, minute: 0}, timeFormat: 'HH.mm', time: [1, 0]},
                {value: '01.15', expected: {hour: 1, minute: 15}, timeFormat: 'HH.mm', time: [1, 15]},
                {value: '12.00', expected: {hour: 12, minute: 0}, timeFormat: 'HH.mm', time: [12, 0]},
                {value: '14.45', expected: {hour: 14, minute: 45}, timeFormat: 'HH.mm', time: [14, 45]},
                {value: '23.59', expected: {hour: 23, minute: 59}, timeFormat: 'HH.mm', time: [23, 59]}
            ],
            function(data) {
                it('should unformat the value into an object', function() {
                    timeFormatStub.returns(data.timeFormat);

                    // Fixing the date to January 5, 2019 to avoid any potential DST issues
                    var obj = app.date(new Date(2019, 0, 5, data.time[0], data.time[1]));
                    sinon.stub(app, 'date')
                        .withArgs(data.value, data.timeFormat)
                        .returns(obj);

                    expect(field.unformat(data.value)).toEqual(data.expected);
                });
            }
        );
    });

    describe('showTimepicker', function() {
        it('should show the timepicker', function() {
            var fakeElement = {focus: sinon.stub()};
            $stub.withArgs(field.fieldTag).returns([fakeElement]);
            field.showTimepicker();
            expect(fakeElement.focus).toHaveBeenCalled();
        });
    });

    describe('hideTimepicker', function() {
        it('should hide the timepicker', function() {
            var timepickerStub = sinon.stub();
            $stub.withArgs(field.fieldTag).returns({timepicker: timepickerStub});
            field.hideTimepicker();
            expect(timepickerStub).toHaveBeenCalledWith('hide');
        });
    });
});
