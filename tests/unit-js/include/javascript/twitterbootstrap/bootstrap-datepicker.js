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
describe('datepicker', function() {

    // Make our lang strings available to all suites
    var enDates;
    var esDates;

    beforeEach(function() {
        enDates = {
            days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            daysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            daysMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'],
            months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October',
                'November', 'December'],
            monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        };
        esDates = {
            days: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
            daysShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            daysMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa', 'Do'],
            months: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre',
                'Noviembre', 'Diciembre'],
            monthsShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
        };
    });
    afterEach(function() {
        enDates = null;
        esDates = null;
    });

    describe('datepicker core and language', function() {

        beforeEach(function() {
            this.mainDiv = $('<div id="main" />');
            this.component = $('<div class="input-append date" id="datepicker">' +
                                    '<input size="16" type="text" value="10-30-2012">' +
                                    '<span class="add-on"><i class="sicon sicon-data-table"></i></span>' +
                                '</div>')
                            .appendTo(this.mainDiv);
            this.input = this.component.find('input');
            this.input.datepicker({
                format: 'mm-dd-yyyy',
                languageDictionary: enDates
            });
            this.addon = this.component.find('.add-on');
            this.dp = this.input.data('datepicker');
            this.picker = this.dp.picker;
        });
        afterEach(function() {
            this.picker.remove();
        });

        it('should set possible characters from date format on initialization', function() {
            var expected;
            // First test with the prebuilt datepicker we already have
            expected = '0123456789-';
            expect(this.dp.possibleChars).toEqual(expected);

            // Rebuild the datepicker from scratch again so we can try another format
            this.component =
                $('<div class="date" id="datepicker"><input type="text"></div>')
                            .appendTo(this.mainDiv);
            this.input = this.component.find('input');
            this.input.datepicker({format: 'mm/dd/yyyy'});
            this.addon = this.component.find('.add-on');
            this.dp = this.input.data('datepicker');
            expected = '0123456789/';
            expect(this.dp.possibleChars).toEqual(expected);
        });
        it('should verify date strings against current date format', function() {
            // Keep in mind beforeEach set format to be: "mm-dd-yyyy",
            var actual = this.dp.verifyDate('1999-11-11');
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('11-1999-11');
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('13-11-1999');
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('11-32-1999');
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('11-11-12345');
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('1-1-1');
            expect(actual).toBeTruthy();
        });
        it('should verify date string against current date format\'s separator', function() {
            var actual = this.dp.verifyDate('11.1999.11');
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('11/1999/11');
            expect(actual).toBeFalsy();
        });
        it('should verify date strings against date format but account for auto year corrections', function() {
            var actual = this.dp.verifyDate('03-24-13');
            expect(actual).toBeTruthy();
            actual = this.dp.verifyDate('03-24-9');
            expect(actual).toBeTruthy();
        });
        it('should verify date strings against date format and NOT auto year correct for 3 digits', function() {
            var actual = this.dp.verifyDate('03-24-113');
            expect(actual).toBeFalsy();
        });
        it('should replace two digit year entered with current century + entered in yyyy', function() {
            //format is currently: "mm-dd-yyyy"
            this.dp.update('03-24-13');
            expect(this.dp.date.getMonth()).toEqual(2);
            expect(this.dp.date.getDate()).toEqual(24);
            expect(this.dp.date.getFullYear()).toEqual(2013);
            this.dp.update('03-24-9');
            expect(this.dp.date.getFullYear()).toEqual(2009);
        });
        it('should add current century + entered when in yy and auto correct', function() {
            // Rebuild the datepicker from scratch again so we can try another format
            this.component =
                $('<div class="date" id="datepicker"><input type="text"></div>')
                            .appendTo(this.mainDiv);
            this.input = this.component.find('input');
            this.input.datepicker({format: 'mm-dd-yy'});
            this.addon = this.component.find('.add-on');
            this.dp = this.input.data('datepicker');

            var actual = this.dp.verifyDate('03-24-9');
            expect(actual).toBeTruthy();
            this.dp.update('03-24-9');
            expect(this.dp.date.getFullYear()).toEqual(2009);

            // Only auto correct (truncate) exactly four digits when in yy. Must be current century.
            actual = this.dp.verifyDate('03-24-2013');
            expect(actual).toBeTruthy();
            actual = this.dp.verifyDate('03-24-1999');
            expect(actual).toBeFalsy();
        });

        it('should verify that date format and value have the same number of separators', function() {
            var actual = this.dp.verifyDate('10-31--2013');
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('1031--2013');
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('---');
            expect(actual).toBeFalsy();
        });
        it('should verify that date format without surrounding values', function() {
            var actual = this.dp.verifyDate('--');
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('1-2-');
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('1--3');
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('-2-3');
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('--3');
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('1--');
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('-2-');
            expect(actual).toBeFalsy();
        });
        it('should verify date strings returning true if invalid string but still parsable', function() {
            var actual = this.dp.verifyDate('1xx-11-1999');
            expect(actual).toBeTruthy();
            actual = this.dp.verifyDate('11-1xx-1999');
            expect(actual).toBeTruthy();
            actual = this.dp.verifyDate('11-11-19xx');
            expect(actual).toBeTruthy();
            actual = this.dp.verifyDate('x1x-x1x-x1x');
            expect(actual).toBeFalsy();
        });
        it('should allow zeroes for years only', function() {
            // year can be zero (since we auto correct those <century> + 0)
            var actual = this.dp.verifyDate('0-11-11');//zero day no
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('11-0-11');//zero month no
            expect(actual).toBeFalsy();
            actual = this.dp.verifyDate('11-11-0');//zero year ok
            expect(actual).toBeTruthy();
        });
        it('should dissallow invalid characters', function() {
            this.dp.update('01abc-01xyz-1999');
            expect(this.dp.date.getMonth()).toEqual(0);
            expect(this.dp.date.getDate()).toEqual(1);
            expect(this.dp.date.getFullYear()).toEqual(1999);
        });
        it('should have format parts', function() {
            expect(this.dp.format.parts[0]).toEqual('mm');
            expect(this.dp.format.parts[1]).toEqual('dd');
            expect(this.dp.format.parts[2]).toEqual('yyyy');
        });
        it('should have element', function() {
            expect(this.dp.element.attr('type')).toEqual('text');
            expect(this.dp.element.attr('value')).toEqual('10-30-2012');
        });
        it('should have date', function() {
            expect(this.dp.date instanceof Date).toBeTruthy();
        });
        it('should have other misc methods', function() {
            expect(this.dp.click).toBeDefined();
            expect(this.dp.hide).toBeDefined();
            expect(this.dp.show).toBeDefined();
            expect(this.dp.click).toBeDefined();
            expect(this.dp.fill).toBeDefined();
            expect(this.dp.viewMode).toBeDefined();
            expect(this.dp.mousedown).toBeDefined();
        });
        it('should render dates', function() {
            this.addon.click();
            this.input.val('10-31-2012');
            this.dp.update();
            this.input.click();
            expect(this.dp.viewDate.getMonth()).toEqual(9);
            expect(this.dp.date.getMonth()).toEqual(9);
            expect(this.dp.viewDate.getFullYear()).toEqual(2012);
            expect(this.dp.date.getFullYear()).toEqual(2012);
        });
        it('should keep track of if the datepicker is hidden or not', function() {
            this.dp.show();
            expect(this.dp.hidden).toBeFalsy();
            this.dp.hide();
            expect(this.dp.hidden).toBeTruthy();
        });
        it('should expose the \'dates\' language dictionary', function() {
            var lastItem;
            // Seems best ROI is to set to non default language verify, set back to english verify
            this.dp.languageDictionary(esDates);
            expect(this.dp.getLanguageDictionary().days[0]).toEqual('Domingo');
            lastItem = esDates.monthsShort.length - 1;
            expect(this.dp.getLanguageDictionary().monthsShort[lastItem]).toEqual('Dic');
            // Now verify we can set it back to English
            this.dp.languageDictionary(enDates);
            expect(this.dp.getLanguageDictionary().days[0]).toEqual('Sunday');
        });

        it('should expose the \'dates\' language dictionary', function() {
            var firstMonth;
            var secondDayOfWeek;
            // Rebuild widget and verify picker markup has appropriate language we just set
            this.picker.remove();
            this.component = $('<div class="input-append date" id="datepicker">' +
                                '<input size="16" type="text" value="10-30-2012" readonly>' +
                                '<span class="add-on"><i class="sicon sicon-data-table"></i></span>' +
                                '</div>')
                            .appendTo(this.mainDiv);
            this.input = this.component.find('input');
            this.input.datepicker({
                format: 'mm-dd-yyyy',
                languageDictionary: esDates
            });
            this.dp = this.input.data('datepicker');
            // See if calendar was built using our esDates from above
            expect(this.dp.picker.text().indexOf('Octubre') !== -1).toBe(true);
            firstMonth = this.dp.picker.find('.datepicker-months tbody span').first().text();
            expect(firstMonth).toEqual('Ene');
            secondDayOfWeek = this.dp.picker.find('.datepicker-days thead tr').last().find('th:nth-child(2)').text();
            expect(secondDayOfWeek).toEqual('Lu');
            this.dp.picker.remove();
        });
    });

    describe('Keyboard Accessibility', function() {
        beforeEach(function() {
            this.mainDiv = $('<div id="main" />');
            // October 31
            this.component = $('<div class="input-append date" id="datepicker">' +
                                    '<input size="16" type="text" value="10-31-2012">' +
                                    '<span class="add-on"><i class="sicon sicon-data-table"></i></span>' +
                                '</div>')
                            .appendTo(this.mainDiv);
            this.input = this.component.find('input');
            this.input.datepicker({
                format: 'mm-dd-yyyy',
                languageDictionary: enDates
            });
            this.addon = this.component.find('.add-on');
            this.dp = this.input.data('datepicker');
            this.picker = this.dp.picker;
        });
        afterEach(function() {
            this.picker.remove();
        });

        it('should navigate days with left and right arrow keys', function() {
            // We should have started out on the 31 day since 10-31-2012 specified
            var selected = this.picker.find('.datepicker-days td.active');
            expect(this.picker.find('.datepicker-days td.active').text()).toEqual('31');
            this.input.trigger({
                type: 'keydown',
                keyCode: 37 // Left arrow
            });
            expect(this.picker.find('.datepicker-days td.active').text()).toEqual('30');

            // Now lets move right 3 times ... should be November 2
            for (var i = 0; i < 3; i++) {
                this.input.trigger({
                    type: 'keydown',
                    keyCode: 39 // Right arrow
                });
            }
            // Expectation - November 2, 2012
            expect(this.picker.find('.datepicker-days td.active').text()).toEqual('2');
            expect(this.picker.find('.datepicker-days thead th.switch').text()).toEqual('November 2012');
        });

        it('should navigate months with Page up Page down keys', function() {
            var selected = this.picker.find('.datepicker-days td.active');
            expect(this.picker.find('.datepicker-days thead th.switch').text()).toEqual('October 2012');
            expect(this.picker.find('.datepicker-days td.active').text()).toEqual('31');
            this.input.trigger({
                type: 'keydown',
                keyCode: 33 // Page up
            });
            // Verify that we moved back to Septiembre 30
            expect(this.picker.find('.datepicker-days thead th.switch').text()).toEqual('September 2012');
            expect(this.picker.find('.datepicker-days td.active').text()).toEqual('30');
            this.input.trigger({
                type: 'keydown',
                keyCode: 34 // Page down
            });
            expect(this.picker.find('.datepicker-days thead th.switch').text()).toEqual('October 2012');
            expect(this.picker.find('.datepicker-days td.active').text()).toEqual('30');
        });

        it('should navigate years with Shift + Page Up, and, Shift + Page Down Key respectively', function() {
            var selected = this.picker.find('.datepicker-days td.active');
            // Trigger keydown with shift key
            this.input.trigger({
                type: 'keydown',
                shiftKey: true,
                keyCode: 33 // Page up

            });
            expect(this.picker.find('.datepicker-days thead th.switch').text()).toEqual('October 2011');
            expect(this.picker.find('.datepicker-days td.active').text()).toEqual('31');
        });

        it('should navigate first and last days of month with Home and End keys respectively', function() {
            var selected = this.picker.find('.datepicker-days td.active');
            // Trigger keydown with shift key
            this.input.trigger({
                type: 'keydown',
                keyCode: 36 // Home
            });
            expect(this.picker.find('.datepicker-days thead th.switch').text()).toEqual('October 2012');
            expect(this.picker.find('.datepicker-days td.active').text()).toEqual('1');
            this.input.trigger({
                type: 'keydown',
                keyCode: 35 // End
            });
            expect(this.picker.find('.datepicker-days thead th.switch').text()).toEqual('October 2012');
            expect(this.picker.find('.datepicker-days td.active').text()).toEqual('31');
        });

        it('should navigate first and last days of year with Shift-Home and Shift-End key combinations', function() {
            var selected = this.picker.find('.datepicker-days td.active');
            // Trigger keydown with shift key
            this.input.trigger({
                type: 'keydown',
                shiftKey: true,
                keyCode: 36 // Home
            });
            expect(this.picker.find('.datepicker-days thead th.switch').text()).toEqual('January 2012');
            expect(this.picker.find('.datepicker-days td.active').text()).toEqual('1');
            this.input.trigger({
                type: 'keydown',
                shiftKey: true,
                keyCode: 35 // End
            });
            expect(this.picker.find('.datepicker-days thead th.switch').text()).toEqual('December 2012');
            expect(this.picker.find('.datepicker-days td.active').text()).toEqual('31');
        });
        it('should ESC out of datepicker', function() {
            // Setting focus to datepicker will trigger show thus making datepicker visible
            this.input.datepicker().focus();
            this.input.trigger({
                type: 'keydown', // ESC
                keyCode: 27
            });
            expect(this.picker.is(':visible')).not.toBeTruthy();
        });

        it('when selecting dd-m-yyyy date format should properly navigate first and last days of year', function() {
            var lastItem;
            // Seems best ROI is to set to non default language verify, set back to english verify
            this.dp.languageDictionary(esDates);
            this.input.datepicker({
                format: 'dd-m-yyyy',
                languageDictionary: esDates
            });
            // Trigger keydown with shift key
            this.input.trigger({
                type: 'keydown',
                shiftKey: true,
                keyCode: 36 // Home
            });
            expect(this.picker.find('.datepicker-days thead th.switch').text()).toEqual('Enero 2012');
            expect(this.picker.find('.datepicker-days td.active').text()).toEqual('1');
            this.input.trigger({
                type: 'keydown',
                shiftKey: true,
                keyCode: 35 // End
            });
            expect(this.picker.find('.datepicker-days thead th.switch').text()).toEqual('Diciembre 2012');
            expect(this.picker.find('.datepicker-days td.active').text()).toEqual('31');

            // Set back to English
            this.dp.languageDictionary(enDates);
        });

    });
});

