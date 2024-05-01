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
describe('Base.Field.Duration', function() {
    var app, field, data;
    var sandbox;

    beforeEach(function () {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'fieldset');
        field = SugarTest.createField('base', 'duration', 'duration');
        SugarTest.testMetadata.set();
        data = field.model.get(field.name);

        sandbox = sinon.createSandbox();
        sandbox.stub(app.user, 'getPreference')
            .withArgs('datepref')
            .returns('m/d/Y')
            .withArgs('timepref')
            .returns('h:ia');

        app.metadata._dev_data.app_strings.LBL_START_AND_END_DATE_SAME_DAY = '{{date}} {{start}} - {{end}} ({{duration}})';
        app.metadata._dev_data.app_strings.LBL_START_AND_END_DATE = '{{start}} - {{end}} ({{duration}})';
        app.metadata._dev_data.app_strings.LBL_DURATION_HOUR = 'hour';
        app.metadata._dev_data.app_strings.LBL_DURATION_HOURS = 'hours';
        app.metadata._dev_data.app_strings.LBL_DURATION_DAYS = 'days';
        app.metadata._dev_data.app_strings.LBL_DURATION_DAY = 'day';
        app.metadata._dev_data.app_strings.LBL_DURATION_MINUTES = 'minutes';
        app.metadata._dev_data.app_strings.LBL_DURATION_MINUTE = 'minute';
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('render', function() {
        it('should hide any open date or time pickers', function() {
            var picker = sandbox.spy();
            var $ = sandbox.stub().returns({
                datepicker: picker,
                timepicker: picker
            });
            sandbox.stub(field.view, 'getField').returns({$: $});

            field.render();

            expect(picker).toHaveBeenCalledWith('hide');
            expect(picker.callCount).toBe(4);
        });
    });

    describe('format()', function() {
        it('should display start and end dates if they are more than a day apart', function() {
            field.model.set({
                date_start: '2014-07-17T11:28',
                date_end: '2014-07-18T12:28'
            });
            expect(field.format(data)).toBe('07/17/2014 11:28am - 07/18/2014 12:28pm (1 day 1 hour)');
        });

        it('should display start and end dates if they are different even if they are less than a day apart', function() {
            field.model.set({
                date_start: '2014-07-17T23:28',
                date_end: '2014-07-18T02:28'
            });
            expect(field.format(data)).toBe('07/17/2014 11:28pm - 07/18/2014 02:28am (3 hours)');
        });

        it('should display date once if they are the same', function() {
            field.model.set({
                date_start: '2014-07-17T11:28',
                date_end: '2014-07-17T12:28'
            });

            expect(field.format(data)).toBe('07/17/2014 11:28am - 12:28pm (1 hour)');
        });

        it('should display 0 duration if the start date is not before the end date', function() {
            field.model.set({
                date_start: '2014-07-17T11:28',
                date_end: '2014-07-17T10:28'
            });

            expect(field.format(data)).toBe('07/17/2014 11:28am - 10:28am (0 minutes)');
        });

        it('should display 0 duration if the start and end dates are the same', function() {
            field.model.set({
                date_start: '2014-07-17T11:28',
                date_end: '2014-07-17T11:28'
            });

            expect(field.format(data)).toBe('07/17/2014 11:28am - 11:28am (0 minutes)');
        });
    });

    describe('setDefaultStartDateTime()', function() {
        it('should set the default start date time to be the upcoming hour if the current time is past the half hour', function() {
            var dateTime = app.date('2014-07-17T09:31');
            field.setDefaultStartDateTime(dateTime);
            expect(field.model.get('date_start')).toMatch('2014-07-17T10:00');
        });

        it('should set the default start date time to be the upcoming half hour if the current time is before the half hour', function() {
            var dateTime = app.date('2014-07-17T09:29');
            field.setDefaultStartDateTime(dateTime);
            expect(field.model.get('date_start')).toMatch('2014-07-17T09:30');
        });

        it('should set the default start date time to be at half hour if the current time is at the half hour', function() {
            var dateTime = app.date('2014-07-17T09:30');
            field.setDefaultStartDateTime(dateTime);
            expect(field.model.get('date_start')).toMatch('2014-07-17T09:30');
        });

        it('should set the default start date time to be at the hour if the current time is at the hour', function() {
            var dateTime = app.date('2014-07-17T09:00');
            field.setDefaultStartDateTime(dateTime);
            expect(field.model.get('date_start')).toMatch('2014-07-17T09:00');
        });
    });

    describe('modifyEndDateToRetainDuration()', function() {
        it('should set the end date to be a half hour from the start date if the end date is empty', function() {
            field.model.unset('date_end');
            field.model.set('date_start', '2014-07-17T09:00');
            field.modifyEndDateToRetainDuration();
            expect(field.model.get('date_end')).toMatch('2014-07-17T09:30');
        });

        it('should not modify the end date if start date and end date have been set at the same time', function() {
            field.model.set({
                date_start: '2014-07-17T11:00',
                date_end: '2014-07-17T12:30'
            });
            field.modifyEndDateToRetainDuration();
            expect(field.model.get('date_end')).toMatch('2014-07-17T12:30');
        });

        it('should set the end date to match the previous duration if start date and end date had already been set', function() {
            field.model.set({
                date_start: '2014-07-17T11:00',
                date_end: '2014-07-17T12:30'
            });
            field.model.set('date_start', '2014-07-17T10:00');
            field.modifyEndDateToRetainDuration();
            expect(field.model.get('date_end')).toMatch('2014-07-17T11:30');
        });

        it('should not modify the end date if the previous duration had a negative duration', function() {
            field.model.set({
                date_start: '2014-07-17T11:00',
                date_end: '2014-07-17T10:00'
            });
            field.model.set('date_start', '2014-07-17T09:30');
            field.modifyEndDateToRetainDuration();
            expect(field.model.get('date_end')).toMatch('2014-07-17T10:00');
        });

        it('should modify the end date if the previous duration had a zero duration', function() {
            field.model.set({
                date_start: '2014-07-17T11:00',
                date_end: '2014-07-17T11:00'
            });
            field.model.set('date_start', '2014-07-17T09:30');
            field.modifyEndDateToRetainDuration();
            expect(field.model.get('date_end')).toMatch('2014-07-17T09:30');
        });

        it('should not modify the end date if start date is empty', function() {
            field.model.unset('date_start');
            field.model.unset('date_end');
            field.modifyEndDateToRetainDuration();
            expect(field.model.get('date_end')).toBeUndefined();
        });
    });

    describe('Verify that when the end date changes, the duration fields are correctly recalculated', function() {
        it('should calculate the duration based on the new end date value', function() {
            field.model.set({
                date_start: '2014-07-17T08:00',
                date_end: '2014-07-17T08:00'
            });
            field.model.set('date_end', '2014-07-17T09:15');
            expect(field.model.get('duration_hours')).toEqual(1);
            expect(field.model.get('duration_minutes')).toEqual(15);
        });

        it('should calculate the duration when the new end date is more than 24 hours from the start date', function() {
            field.model.set({
                date_start: '2014-07-17T08:00',
                date_end: '2014-07-17T08:00'
            });
            field.model.set('date_end', '2014-07-18T09:15');
            expect(field.model.get('duration_hours')).toEqual(25);
            expect(field.model.get('duration_minutes')).toEqual(15);
        });

        it('should calculate to 0 duration hours and minutes if the start and end dates are the same', function() {
            field.model.set({
                date_start: '2014-07-17T08:00',
                date_end: '2014-07-17T09:00'
            });
            field.model.set('date_end', '2014-07-17T08:00');
            expect(field.model.get('duration_hours')).toEqual(0);
            expect(field.model.get('duration_minutes')).toEqual(0);
        });
    });

    describe('isDateRangeValid()', function() {
        it('should have valid date range if the start date is before the end date', function() {
            field.model.set({
                date_start: '2014-07-17T10:00',
                date_end: '2014-07-17T11:00'
            });
            expect(field.isDateRangeValid()).toBe(true);
        });

        it('should not have valid date range if the start date is after the end date', function() {
            field.model.set({
                date_start: '2014-07-17T12:00',
                date_end: '2014-07-17T11:00'
            });
            expect(field.isDateRangeValid()).toBe(false);
        });

        it('should have valid date range if the start date is the same as the end date', function() {
            field.model.set({
                date_start: '2014-07-17T12:00',
                date_end: '2014-07-17T12:00'
            });
            expect(field.isDateRangeValid()).toBe(true);
        });

        it('should not have valid date range if the start date is empty', function() {
            field.model.set({
                date_end: '2014-07-17T12:00'
            });
            expect(field.isDateRangeValid()).toBe(false);
        });

        it('should not have valid date range if the end date is empty', function() {
            field.model.set({
                date_start: '2014-07-17T11:00',
                date_end: '2014-07-17T12:00'
            });
            field.model.unset('date_end');
            expect(field.isDateRangeValid()).toBe(false);
        });
    });

    describe('Validate model', function() {
        it('should add custom validation to the model', function() {
            expect(field.model._validationTasks['duration_date_range_' + field.cid]).toBeDefined();
        });

        it('should remove custom validation once it has been disposed', function() {
            var model = field.model;
            field.dispose();

            expect(model._validationTasks['duration_date_range_' + field.cid]).not.toBeDefined();
        });
    });
});
