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

const DateUtils = require('../../../src/utils/date');

describe('Utils/Date', function() {
    describe('guessFormat', function() {
        it('should guess date string formats with seconds', function () {
            var value = '2012-03-27 01:48:00AM';
            var result = DateUtils.guessFormat(value);
            expect(result).toEqual('Y-m-d h:i:sA');
        });

        it('should guess date string formats without seconds', function () {
            var value = '2012-03-27 01:48 AM';
            var result = DateUtils.guessFormat(value);
            expect(result).toEqual('Y-m-d h:i A');
        });

        it('should guess date string formats without ampm', function () {
            var value = '2012-03-27 01:48:58';
            var result = DateUtils.guessFormat(value);
            expect(result).toEqual('Y-m-d H:i:s');
        });

        it('should guess USA-style date string formats', function () {
            let value = '03/27/2012';
            let result = DateUtils.guessFormat(value);
            expect(result).toEqual('m/d/Y');
        });

        it('should guess date and time string formats with periods for separators', function () {
            let value = '2012.03.27 01.48.58';
            let result = DateUtils.guessFormat(value);
            expect(result).toEqual('Y.m.d H.i.s');
        });
    });

	it('should parse date strings into javascript date objects', function() {
		var result = DateUtils.parse('2012-03-27 01:48:32');
		expect(result.getDate()).toEqual(27);
		expect(result.getFullYear()).toEqual(2012);
		expect(result.getMonth()).toEqual(2);
		expect(result.getHours()).toEqual(1);
		expect(result.getMinutes()).toEqual(48);
		expect(result.getSeconds()).toEqual(32);
	});

    it('should return the same Date object', function () {
        var dateObj = new Date();
        var result = DateUtils.parse(dateObj);

        expect(result).toEqual(dateObj);
    });

	it('should format date objects into strings', function() {
		var value  = new Date(Date.parse("Tue, 15 May 2012 01:48:00"));
        var format = 'Y-m-d H:i:sA';
        var result = DateUtils.format(value, format);
		expect(result).toEqual('2012-05-15 01:48:00AM');

		format = 'Y-m-d H:i:sa';
		result = DateUtils.format(value, format);
		expect(result).toEqual('2012-05-15 01:48:00am');
	});

	it('should format date objects into strings', function() {
		var value  = '2012-03-27 01:48:32';
        var format = 'Y-m-d h:i a';
        var result = DateUtils.parse(value, format);
		expect(result.getDate()).toEqual(27);
		expect(result.getFullYear()).toEqual(2012);
		expect(result.getMonth()).toEqual(2);
		expect(result.getHours()).toEqual(1);
		expect(result.getMinutes()).toEqual(48);
		expect(result.getSeconds()).toEqual(0);// no 's' specified
	});

	it('should format date objects into strings with seconds included', function() {
		var value  = '2012-03-27 01:48:32';
        var format = 'Y-m-d h:i:s a';
        var result = DateUtils.parse(value, format);
		expect(result.getDate()).toEqual(27);
		expect(result.getFullYear()).toEqual(2012);
		expect(result.getMonth()).toEqual(2);
		expect(result.getHours()).toEqual(1);
		expect(result.getMinutes()).toEqual(48);
		expect(result.getSeconds()).toEqual(32);// 's' specified
	});

	it('should format date objects into strings modifier backslash', function() {
		var value  = DateUtils.parse('2012-03-27 01:48:32');
        var format = 'Y-m-d \\at h:i a';
        var result = DateUtils.format(value, format);
		expect(result).toEqual('2012-03-27 at 01:48 am');
	});

	it('should format date objects into strings modifier g', function() {
        var value  = DateUtils.parse('2012-03-27 01:48:32');
        var format = 'Y-m-d g:i a';
        var result = DateUtils.format(value, format);
		expect(result).toEqual('2012-03-27 1:48 am');
	});

	it('should format date objects into strings modifier j', function() {
        var value  = DateUtils.parse('2012-03-04 01:48:32');
        var format = 'Y-m-j';
        var result = DateUtils.format(value, format);
		expect(result).toEqual('2012-03-4');
	});

	it('should format date objects into strings modifier n', function() {
        var value  = DateUtils.parse('2012-03-04 01:48:32');
        var format = 'Y-n-d';
        var result = DateUtils.format(value, format);
		expect(result).toEqual('2012-3-04');
	});

	it('should format even if only time format specified', function() {
        var value  = DateUtils.parse('2012-03-04 12:00:00');
        var format = 'h:ia';
        var result = DateUtils.format(value, format);
		expect(result).toEqual('12:00pm');
	});

	it('should format 12am with time format specified', function() {
        var value = DateUtils.parse('2012-03-04 00:00:00');
        var format = 'h:ia';
		var result = DateUtils.format(value, format);
		expect(result).toEqual('12:00am');
	});

	it('should format date objects given timestamp and no format', function() {
		var result = DateUtils.parse(1332838080000);
		expect(result.getTime()).toEqual(1332838080000);
	});

	it('should return false if bogus inputs', function() {
		var result = DateUtils.parse('XyXyZyW');
		expect(result).toEqual(false);
	});

	it('should round time to nearest fifteen minutes', function() {
		var ts = Date.parse('April 1, 2012 10:01:50');
        var date = new Date(ts);
        var result = DateUtils.roundTime(date);
		expect(result.getMinutes()).toEqual(15);

		// not a proper Date object
		expect(DateUtils.roundTime('April 1, 2012')).toEqual(0);

		ts = Date.parse('April 1, 2012 10:00:32');
		date = new Date(ts);
		result = DateUtils.roundTime(date);
		expect(result.getMinutes()).toEqual(0);

		ts = Date.parse('April 1, 2012 10:16:50');
		date = new Date(ts);
		result = DateUtils.roundTime(date);
		expect(result.getMinutes()).toEqual(30);

		ts = Date.parse('April 1, 2012 10:29:50');
		date = new Date(ts);
		result = DateUtils.roundTime(date);
		expect(result.getMinutes()).toEqual(30);

		ts = Date.parse('April 1, 2012 10:30:50');
		date = new Date(ts);
		result = DateUtils.roundTime(date);
		expect(result.getMinutes()).toEqual(30);

		ts = Date.parse('April 1, 2012 10:31:50');
		date = new Date(ts);
		result = DateUtils.roundTime(date);
		expect(result.getMinutes()).toEqual(45);

		ts = Date.parse('April 1, 2012 10:44:50');
		date = new Date(ts);
		result = DateUtils.roundTime(date);
		expect(result.getHours()).toEqual(10);
		expect(result.getMinutes()).toEqual(45);

		ts = Date.parse('April 1, 2012 10:46:00');
		date = new Date(ts);
		result = DateUtils.roundTime(date);
		expect(result.getMinutes()).toEqual(0);
		expect(result.getHours()).toEqual(11);
	});

	it('should convert a UTC date into a local date', function() {
		var date = new Date('April 1, 2012 10:31:50');
        var offset = date.getTimezoneOffset();
        var UTC = new Date('April 1, 2012 10:31:50 UTC');

		// not a Date
        expect(DateUtils.UTCtoLocalTime(5)).toEqual(5);

		if (offset !== 0) {
			expect(date.toString()).not.toEqual(UTC.toString());
			expect(DateUtils.UTCtoLocalTime(UTC).toString()).not.toEqual(date.toString());
		}
	});

	it('should convert into relative time', function() {
		var ts = new Date().getTime();
        var LBL_TIME_AGO_NOW = new Date(ts - 1 * 1000);
        var LBL_TIME_AGO_SECONDS = new Date(ts - 10 * 1000);
        var LBL_TIME_AGO_MINUTE = new Date(ts - 70 * 1000);
        var LBL_TIME_AGO_MINUTES = new Date(ts - 130 * 1000);
        var LBL_TIME_AGO_HOUR = new Date(ts - 3610 * 1000);
        var LBL_TIME_AGO_HOURS = new Date(ts - 7230 * 1000);
        var LBL_TIME_AGO_DAY = new Date(ts - 90000 * 1000);
        var LBL_TIME_AGO_DAYS = new Date(ts - 200000 * 1000);
        var LBL_TIME_AGO_YEAR = new Date(ts - 400 * 84600 * 1000);
        var LBL_TIME_UNTIL_SECONDS = new Date(ts + 10 * 1000);
        var LBL_TIME_UNTIL_MINUTE = new Date(ts + 70 * 1000);
        var LBL_TIME_UNTIL_MINUTES = new Date(ts + 130 * 1000);
        var LBL_TIME_UNTIL_HOUR = new Date(ts + 3610 * 1000);
        var LBL_TIME_UNTIL_HOURS = new Date(ts + 7230 * 1000);
        var LBL_TIME_UNTIL_DAY = new Date(ts + 90000 * 1000);
        var LBL_TIME_UNTIL_DAYS = new Date(ts + 200000 * 1000);
        var LBL_TIME_UNTIL_YEAR = new Date(ts + 400 * 84600 * 1000);

		// corner case
        expect(DateUtils.getRelativeTimeLabel(new Date(Number.POSITIVE_INFINITY)).str).toEqual('');

        //Time "Ago"
		expect(DateUtils.getRelativeTimeLabel(LBL_TIME_AGO_NOW).str).toEqual('LBL_TIME_AGO_NOW');
		expect(DateUtils.getRelativeTimeLabel(LBL_TIME_AGO_SECONDS).str).toEqual('LBL_TIME_AGO_SECONDS');
		expect(DateUtils.getRelativeTimeLabel(LBL_TIME_AGO_MINUTE).str).toEqual('LBL_TIME_AGO_MINUTE');
		expect(DateUtils.getRelativeTimeLabel(LBL_TIME_AGO_MINUTES).str).toEqual('LBL_TIME_AGO_MINUTES');
		expect(DateUtils.getRelativeTimeLabel(LBL_TIME_AGO_HOUR).str).toEqual('LBL_TIME_AGO_HOUR');
		expect(DateUtils.getRelativeTimeLabel(LBL_TIME_AGO_HOURS).str).toEqual('LBL_TIME_AGO_HOURS');
		expect(DateUtils.getRelativeTimeLabel(LBL_TIME_AGO_DAY).str).toEqual('LBL_TIME_AGO_DAY');
		expect(DateUtils.getRelativeTimeLabel(LBL_TIME_AGO_DAYS).str).toEqual('LBL_TIME_AGO_DAYS');
		expect(DateUtils.getRelativeTimeLabel(LBL_TIME_AGO_YEAR).str).toEqual('LBL_TIME_AGO_YEAR');

        //Time "Until"
        expect(DateUtils.getRelativeTimeLabel(LBL_TIME_UNTIL_SECONDS).str).toEqual('LBL_TIME_UNTIL_SECONDS');
        expect(DateUtils.getRelativeTimeLabel(LBL_TIME_UNTIL_MINUTE).str).toEqual('LBL_TIME_UNTIL_MINUTE');
        expect(DateUtils.getRelativeTimeLabel(LBL_TIME_UNTIL_MINUTES).str).toEqual('LBL_TIME_UNTIL_MINUTES');
        expect(DateUtils.getRelativeTimeLabel(LBL_TIME_UNTIL_HOUR).str).toEqual('LBL_TIME_UNTIL_HOUR');
        expect(DateUtils.getRelativeTimeLabel(LBL_TIME_UNTIL_HOURS).str).toEqual('LBL_TIME_UNTIL_HOURS');
        expect(DateUtils.getRelativeTimeLabel(LBL_TIME_UNTIL_DAY).str).toEqual('LBL_TIME_UNTIL_DAY');
        expect(DateUtils.getRelativeTimeLabel(LBL_TIME_UNTIL_DAYS).str).toEqual('LBL_TIME_UNTIL_DAYS');
        expect(DateUtils.getRelativeTimeLabel(LBL_TIME_UNTIL_YEAR).str).toEqual('LBL_TIME_UNTIL_YEAR');
	});

	it('should parse the format into an object containing each of the format\'s pieces', function() {
		var dataProvider = [
				{
					formatToParse: 'm/d/Y H:i A',
					expected: {
						month: 'm',
						day: 'd',
						year: 'Y',
						hours: 'H',
						minutes: 'i',
						amPm: 'A'
					}
				},
				{
					formatToParse: 'Y-m-d h:i:sa',
					expected: {
						month: 'm',
						day: 'd',
						year: 'Y',
						hours: 'h',
						minutes: 'i',
						seconds: 's',
						amPm: 'a'
					}
				}
			],
			actual;

		_.each(dataProvider, function(value) {
			actual = DateUtils.parseFormat(value.formatToParse);
			expect(actual).toEqual(value.expected);
		});
	});

	describe('UTCtoTimezone', function () {
        it('should return unmodified input if it is not a Date', function () {
            let dateString = 'January 19, 2017 3:34:58 pm';
            expect(DateUtils.UTCtoTimezone(dateString)).toEqual(dateString);
        });

        it('should convert a UTC date into a date according to the specified timezone offset', function() {
            var dateToConvert = new Date('July 12, 2012 10:31:58 am'),
                dataProvider  = [
                    {
                        timezoneOffset: -7, // PDT
                        expected: {
                            month: 6,
                            day: 12,
                            year: 2012,
                            hours: 3,
                            minutes: 31,
                            seconds: 58
                        }
                    },
                    {
                        timezoneOffset: -4, // EDT
                        expected: {
                            month: 6,
                            day: 12,
                            year: 2012,
                            hours: 6,
                            minutes: 31,
                            seconds: 58
                        }
                    },
                    {
                        timezoneOffset: 5.5, // ahead of UTC and a float
                        expected: {
                            month: 6,
                            day: 12,
                            year: 2012,
                            hours: 16,
                            minutes: 1,
                            seconds: 58
                        }
                    }
                ],
                actual;

            _.each(dataProvider, function(value) {
                actual = DateUtils.UTCtoTimezone(dateToConvert, value.timezoneOffset);
                expect(actual.getMonth()).toEqual(value.expected.month);
                expect(actual.getDate()).toEqual(value.expected.day);
                expect(actual.getFullYear()).toEqual(value.expected.year);
                expect(actual.getHours()).toEqual(value.expected.hours);
                expect(actual.getMinutes()).toEqual(value.expected.minutes);
                expect(actual.getSeconds()).toEqual(value.expected.seconds);
            });
        });
    });

	it('should return the number of milliseconds since the Unix epoch while assuming the date is UTC', function() {
		var dateToConvert = new Date('July 12, 2012 10:31:58 am');
        var expected = Date.UTC(
            dateToConvert.getFullYear(),
			dateToConvert.getMonth(),
			dateToConvert.getDate(),
			dateToConvert.getHours(),
			dateToConvert.getMinutes(),
			dateToConvert.getSeconds(),
			dateToConvert.getMilliseconds()
        );
        var actual = DateUtils.toUTC(dateToConvert);
		expect(actual).toEqual(expected);
	});

	it('should return input if not a date', function() {
		var expected = 'asdf';
        var actual = DateUtils.toUTC(expected);
		expect(actual).toEqual(expected);
	});

    it('should handle falsy displayDefault and also displayDefault with no time part', function() {
        expect(DateUtils.parseDisplayDefault('now', new Date('August 10, 2012')).getMonth()).toEqual(7);
        expect(DateUtils.parseDisplayDefault('now', new Date('August 10, 2012')).getDate()).toEqual(10);
        expect(DateUtils.parseDisplayDefault('now', new Date('August 10, 2012')).getFullYear()).toEqual(2012);
        expect(DateUtils.parseDisplayDefault('now' + /*no '&' so time part gets ignored*/"01:30am", new Date('August 10, 2012')).getMonth()).toEqual(7);
        expect(DateUtils.parseDisplayDefault("now"+/*no '&' so time part gets ignored*/"01:30am", new Date('August 10, 2012')).getDate()).toEqual(10);
		expect(DateUtils.parseDisplayDefault("now"+/*no '&' so time part gets ignored*/"01:30am", new Date('August 10, 2012')).getMinutes()).toEqual(0);
        expect(DateUtils.parseDisplayDefault('now&01:30am', new Date('August 10, 2012')).getHours()).toEqual(1);
        expect(DateUtils.parseDisplayDefault('now&01:30am', new Date('August 10, 2012')).getMinutes()).toEqual(30);
        expect(DateUtils.parseDisplayDefault('now&01:30am', new Date('August 10, 2012')).getDate()).toEqual(10);
        expect(DateUtils.parseDisplayDefault(null, new Date('August 10, 2012'))).toEqual(null);
        expect(DateUtils.parseDisplayDefault(undefined, new Date('August 10, 2012'))).toEqual(undefined);
    });

    it('should return now', function() {
        var actual = DateUtils.parseDisplayDefault('now&01:30am', new Date('August 10, 2012'));
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(7);
        expect(actual.getDate()).toEqual(10);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return next Monday', function() {
		var actual = DateUtils.parseDisplayDefault('next monday&01:30am', new Date('August 10, 2012'));
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(7);
        expect(actual.getDate()).toEqual(13);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return next Friday', function() {
		var actual = DateUtils.parseDisplayDefault('next friday&01:30am', new Date('January 2, 2012'));
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(0);
        expect(actual.getDate()).toEqual(6);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return first of next month', function() {
		var actual = DateUtils.parseDisplayDefault('first day of next month&01:30am', new Date('January 2, 2012'));
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(1);
        expect(actual.getDate()).toEqual(1);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return first of next month across years', function() {
		var actual = DateUtils.parseDisplayDefault('first day of next month&01:30am', new Date('December 2, 2012'));
        expect(actual.getFullYear()).toEqual(2013);
        expect(actual.getMonth()).toEqual(0);
        expect(actual.getDate()).toEqual(1);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);

		actual = DateUtils.parseDisplayDefault('first day of next month&01:30am', new Date('October 31, 2012'));
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(10);
        expect(actual.getDate()).toEqual(1);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return -1 day', function() {
		var actual = DateUtils.parseDisplayDefault('-1 day&01:30am', new Date('January 2, 2012'));
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(0);
        expect(actual.getDate()).toEqual(1);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return +1 day', function() {
		var actual = DateUtils.parseDisplayDefault('+1 day&01:30am', new Date('January 2, 2012'));
		//.toEqual("2012-01-03 01:30am");
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(0);
        expect(actual.getDate()).toEqual(3);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return +1 day when hour returned is single digit', function() {
        var actual = DateUtils.parseDisplayDefault('+1 day&4:30am', new Date('January 2, 2012'));
        expect(actual.getHours()).toEqual(4);
        actual = DateUtils.parseDisplayDefault('+1 day&4:30pm', new Date('January 2, 2012'));
        expect(actual.getHours()).toEqual(16);
    });

    it('should return +1 month when month dates not included in next month', function() {
		var actual = DateUtils.parseDisplayDefault('+1 month&01:30am', new Date('August 31, 2012'));
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(8);
        expect(actual.getDate()).toEqual(30);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return +1 month at end of year', function() {
		var actual = DateUtils.parseDisplayDefault('+1 month&01:30am', new Date('December 31, 2012'));
        expect(actual.getFullYear()).toEqual(2013);
        expect(actual.getMonth()).toEqual(0);
        expect(actual.getDate()).toEqual(31);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return -1 month when month dates not included in previous month', function() {
		var actual = DateUtils.parseDisplayDefault('-1 month&01:30am', new Date('October 31, 2012'));
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(8);
        expect(actual.getDate()).toEqual(30);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return -1 month when at the beginning of the year', function() {
		var actual = DateUtils.parseDisplayDefault('-1 month&01:30am', new Date('January 31, 2012'));
        expect(actual.getFullYear()).toEqual(2011);
        expect(actual.getMonth()).toEqual(11);
        expect(actual.getDate()).toEqual(31);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return 3 months from now and adjust for days not included', function() {
		var actual = DateUtils.parseDisplayDefault('+3 months&01:30am', new Date('January 31, 2012'));
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(3);
        expect(actual.getDate()).toEqual(30);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    // **Please do not remove.
    // We have found that if you try to add to a Date and the destination is exactly on a DST hour boundary,
    // the results are not as expected. These tests are xit'd as they should only be done
    // in local, per developer tests.
	xdescribe('DST Tests', function() {
		it('should return 3 months from now across years Munich and also France', function() {
			//Munich: Sunday, March 31, 2013 at 2:00:00 AM clocks are turned forward 1 hour to
			//Sunday, March 31, 2013 at 3:00:00 AM local daylight time instead
			// France too: http://www.timeanddate.com/worldclock/timezone.html?n=195
			var actual = DateUtils.parseDisplayDefault('+3 months&02:30am', new Date('December 31, 2012'));
			expect(actual.getFullYear()).toEqual(2013);
			expect(actual.getMonth()).toEqual(2);
			expect(actual.getDate()).toEqual(31);
			expect(actual.getHours()).toEqual(1);// one might expect 3 since clocks move forward; but I'm
			// observing that when on DST boundary hour FF / Chrome are truncating back an hour
			expect(actual.getMinutes()).toEqual(30);
		});

		it('should return 3 months from now across years Portugal', function() {
			//Sunday, March 31, 2013 at 1:00:00 AM clocks are turned forward 1 hour to
			//Sunday, March 31, 2013 at 2:00:00 AM local daylight time instead in Portugal
			var actual = DateUtils.parseDisplayDefault('+3 months&01:30am', new Date('December 31, 2012'));
			expect(actual.getFullYear()).toEqual(2013);
			expect(actual.getMonth()).toEqual(2);
			expect(actual.getDate()).toEqual(31);
			expect(actual.getHours()).toEqual(0);
			expect(actual.getMinutes()).toEqual(30);
		});

		it('should return 3 months from now across years in SF,CA', function() {
			//Sunday, March 10, 2013 at 2:00:00 AM clocks are turned forward 1 hour to Sunday, March 10,
			//2013 at 3:00:00 AM local daylight time
			var actual = DateUtils.parseDisplayDefault('+3 months&02:00am', new Date('December 10, 2012'));
			expect(actual.getFullYear()).toEqual(2013);
			expect(actual.getMonth()).toEqual(2);
			expect(actual.getDate()).toEqual(10);
			expect(actual.getHours()).toEqual(1);// one might expect 3 since clocks move forward; but I'm
			// observing that when on DST boundary hour FF / Chrome are truncating back an hour
			expect(actual.getMinutes()).toEqual(0);
		});
	});

    it('should return 6 months from now', function() {
		var actual = DateUtils.parseDisplayDefault('+6 months&01:30am', new Date('January 31, 2012'));
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(6);
        expect(actual.getDate()).toEqual(31);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return 6 months from now across years and also adjust for days not included', function() {
		var actual = DateUtils.parseDisplayDefault('+6 months&01:30am', new Date('December 31, 2012'));
        expect(actual.getFullYear()).toEqual(2013);
        expect(actual.getMonth()).toEqual(5);
        expect(actual.getDate()).toEqual(30);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return arbitrary days away', function() {
		var actual = DateUtils.parseDisplayDefault('-2 days&01:30am', new Date('October 31, 2012'));
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(9);
        expect(actual.getDate()).toEqual(29);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);

		actual = DateUtils.parseDisplayDefault('+2 day&01:30am', new Date('October 31, 2012'));
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(10);
        expect(actual.getDate()).toEqual(2);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return arbitrary weeks away', function() {
		var actual = DateUtils.parseDisplayDefault('+2 weeks&01:30am', new Date('August 2, 2012'));
		//.toEqual("2012-08-16 01:30am");
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(7);
        expect(actual.getDate()).toEqual(16);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);

		actual = DateUtils.parseDisplayDefault('-2 weeks&01:30am', new Date('August 2, 2012'));
		//.toEqual("2012-07-19 01:30am");
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(6);
        expect(actual.getDate()).toEqual(19);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return arbitrary months away', function() {
		var actual = DateUtils.parseDisplayDefault('-2 month&01:30am', new Date('October 31, 2012'));
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(7);
        expect(actual.getDate()).toEqual(31);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);

		actual = DateUtils.parseDisplayDefault('+2 month&01:30am', new Date('October 31, 2012'));
        expect(actual.getFullYear()).toEqual(2012);
        expect(actual.getMonth()).toEqual(11);
        expect(actual.getDate()).toEqual(31);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });
    it("should return arbitrary years away", function() {
		var actual = DateUtils.parseDisplayDefault('-2 years&01:30am', new Date('October 31, 2012'));
        expect(actual.getFullYear()).toEqual(2010);
        expect(actual.getMonth()).toEqual(9);
        expect(actual.getDate()).toEqual(31);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);

		actual = DateUtils.parseDisplayDefault('+2 years&01:30am', new Date('October 31, 2012'));
        expect(actual.getFullYear()).toEqual(2014);
        expect(actual.getMonth()).toEqual(9);
        expect(actual.getDate()).toEqual(31);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return 1 year from now', function() {
		var actual = DateUtils.parseDisplayDefault('+1 year&01:30am', new Date('December 31, 2012'));
        expect(actual.getFullYear()).toEqual(2013);
        expect(actual.getMonth()).toEqual(11);
        expect(actual.getDate()).toEqual(31);
        expect(actual.getHours()).toEqual(1);
        expect(actual.getMinutes()).toEqual(30);
    });

    it('should return empty string for non-date value', function() {
        expect(DateUtils.format('test','Y/m/d')).toEqual('');
    });

    // Datepicker normalization tests
	it('should convert y to yy, Y to yyyy, m to mm, and d to dd', function() {
        expect(DateUtils.toDatepickerFormat('y/m/d')).toEqual('yy/mm/dd');
        expect(DateUtils.toDatepickerFormat('Y/m/d')).toEqual('yyyy/mm/dd');
        expect(DateUtils.toDatepickerFormat('y.m.d')).toEqual('yy.mm.dd');
        expect(DateUtils.toDatepickerFormat('m-d-Y')).toEqual('mm-dd-yyyy');
        expect(DateUtils.toDatepickerFormat('')).toEqual('');
        expect(DateUtils.toDatepickerFormat(null)).toEqual('');
    });

	it('should _stripIsoTZ if stripIsoTZ set true', function() {
        expect(DateUtils.stripIsoTimeDelimterAndTZ('2012-11-06T20:00:06.651Z')).toEqual('2012-11-06 20:00:06');
        expect(DateUtils.stripIsoTimeDelimterAndTZ('2012-11-07T04:28:52+00:00')).toEqual('2012-11-07 04:28:52');
	});

	it('should determine if iso 8601 compatible with likely TimeDate values', function() {
        expect(DateUtils.isIso('2012-11-06T20:00:06.651Z')).toBeTruthy();
		expect(DateUtils.isIso('2012-12-12T10:35:15-0700')).toBeTruthy();
		expect(DateUtils.isIso('2012-12-12T09:35:15-0800')).toBeTruthy();
		expect(DateUtils.isIso('2012-12-12T17:35:15-0000')).toBeTruthy();
		expect(DateUtils.isIso('2012-12-12T17:35:15+0000')).toBeTruthy();
		expect(DateUtils.isIso('2012-12-12T17:35:15Z')).toBeTruthy();
		expect(DateUtils.isIso('2012-12-12')).toBeTruthy();
		expect(DateUtils.isIso('2012-12-12 17:35:15Z')).toBeTruthy();
		expect(DateUtils.isIso('2012-12-12 17:35:15')).toBeTruthy();
		expect(DateUtils.isIso('2012-12-12 17:35')).toBeTruthy();
		expect(DateUtils.isIso('2012-12-12 17')).toBeTruthy();
		expect(DateUtils.isIso('xxxx-12-12')).toBeFalsy();
		expect(DateUtils.isIso('2012-xx-12')).toBeFalsy();
		expect(DateUtils.isIso('2012-12-xx')).toBeFalsy();
	});

    function createDate(dateString) {
        var date = new Date();
        var pieces = dateString.split(/[-\s:]+/);
        date.setFullYear(pieces[0]);
        date.setMonth(pieces[1] - 1);
        date.setDate(pieces[2]);
        date.setHours(pieces[3]);
        date.setMinutes(pieces[4]);
        date.setSeconds(pieces[5]);
        date.setMilliseconds(0);
        return date;
    }

    it('tests default time parser for 12-hour format with am and pm', function () {
        var tsFormat = 'h:ia';

        expect(DateUtils.parse("12:00am", tsFormat)).toEqual(createDate('1970-01-01 00:00:00'));
        expect(DateUtils.parse("11:59am", tsFormat)).toEqual(createDate('1970-01-01 11:59:00'));
        expect(DateUtils.parse("01:00am", tsFormat)).toEqual(createDate('1970-01-01 01:00:00'));
        expect(DateUtils.parse("00:00am", tsFormat)).toEqual(createDate('1970-01-01 00:00:00'));
        expect(DateUtils.parse("12:00pm", tsFormat)).toEqual(createDate('1970-01-01 12:00:00'));
        expect(DateUtils.parse("11:59pm", tsFormat)).toEqual(createDate('1970-01-01 23:59:00'));
        expect(DateUtils.parse("00:00pm", tsFormat)).toEqual(createDate('1970-01-01 12:00:00'));
    });

    it('tests default time parser for 24-hour format', function () {
        var tsFormat = 'H:i';

        expect(DateUtils.parse("00:00", tsFormat)).toEqual(createDate('1970-01-01 00:00:00'));
        expect(DateUtils.parse("12:00", tsFormat)).toEqual(createDate('1970-01-01 12:00:00'));
        expect(DateUtils.parse("11:59", tsFormat)).toEqual(createDate('1970-01-01 11:59:00'));
        expect(DateUtils.parse("01:00", tsFormat)).toEqual(createDate('1970-01-01 01:00:00'));
    });

    it('tests formatting to am and pm', function () {
        var tsFormat = 'h:ia';
        var date = new Date("Jan 1, 1970 00:00:00").toUTCString().split(/\d\d:/)[0];

        expect(DateUtils.format(new Date(date + ' 00:00:00'), tsFormat)).toEqual('12:00am');
        expect(DateUtils.format(new Date(date + ' 00:01:00'), tsFormat)).toEqual('12:01am');
        expect(DateUtils.format(new Date(date + ' 01:01:00'), tsFormat)).toEqual('01:01am');
        expect(DateUtils.format(new Date(date + ' 12:00:00'), tsFormat)).toEqual('12:00pm');
        expect(DateUtils.format(new Date(date + ' 12:01:00'), tsFormat)).toEqual('12:01pm');
    });

    describe('convertFormat', function() {
        it('should convert PHP date formats to Moment.js date formats', function() {
            expect(DateUtils.convertFormat('Y-m-d')).toEqual('YYYY-MM-DD');
            expect(DateUtils.convertFormat('m/d/Y')).toEqual('MM/DD/YYYY');
            expect(DateUtils.convertFormat('d.m.Y')).toEqual('DD.MM.YYYY');

            // test the memoization
            expect(DateUtils.convertFormat('Y-m-d')).toEqual('YYYY-MM-DD');
        });

        it('should convert PHP time formats to Moment.js time formats', function() {
            expect(DateUtils.convertFormat('H:i')).toEqual('HH:mm');
            expect(DateUtils.convertFormat('h:ia')).toEqual('hh:mma');
            expect(DateUtils.convertFormat('G:i')).toEqual('H:mm');
            expect(DateUtils.convertFormat('g:i')).toEqual('h:mm');
        });
    });

    describe('compare()', function() {
        var result, date1, date2;

        it('should return -1 when date1 < date2', function() {
            date1 = '2013-01-10';
            date2 = '2013-01-15';
            result = DateUtils.compare(date1, date2);
            expect(result).toEqual(-1);
        });

        it('should return 0 when date1 = date2', function() {
            date1 = '2013-01-10';
            date2 = '2013-01-10';
            result = DateUtils.compare(date1, date2);
            expect(result).toEqual(0);
        });

        it('should return 1 when date1 > date2', function() {
            date1 = '2013-01-15';
            date2 = '2013-01-10';
            result = DateUtils.compare(date1, date2);
            expect(result).toEqual(1);
        });

        it('should throw an exception when dates are invalid', function() {
            date1 = new Date('wat');
            date2 = new Date('2013-01-10');
            expect(function() {
                DateUtils.compare(date1, date2);
            }).toThrow(new DateUtils.InvalidException('Invalid date passed for comparison.', date1));

            date1 = new Date('2013-01-10');
            date2 = new Date('wat');
            expect(function() {
                DateUtils.compare(date1, date2);
            }).toThrow(new DateUtils.InvalidException('Invalid date passed for comparison.', date1));
        });
    });

	describe('isAfter()', function() {
		var result, date1, date2;
		it('should return true when date1 is after date2', function() {
			date1 = new DateUtils('2016-08-08');
			date2 = new DateUtils('2016-08-07');
			result = date1.isAfter(date2);
			expect(result).toBeTruthy();
		});

		it('should return false when date1 is not after date2', function() {
			date1 = new DateUtils('2016-08-08');
			date2 = new DateUtils('2016-12-31');
			result = date1.isAfter(date2);
			expect(result).toBeFalsy();
		});
	});

	describe('isBefore()', function() {
		var result, date1, date2;
		it('should return true when date1 is before date2', function() {
			date1 = new DateUtils('2016-08-08');
			date2 = new DateUtils('2016-08-09');
			result = date1.isBefore(date2);
			expect(result).toBeTruthy();
		});

		it('should return false when date1 is not before date2', function() {
			date1 = new DateUtils('2016-08-08');
			date2 = new DateUtils('2016-01-10');
			result = date1.isBefore(date2);
			expect(result).toBeFalsy();
		});
	});

	describe('isSame()', function() {
		var result, date1, date2;
		it('should return true when date1 is the same as date2', function() {
			date1 = new DateUtils('2016-08-08');
			date2 = new DateUtils('2016-08-08');
			result = date1.isSame(date2);
			expect(result).toBeTruthy();
		});

		it('should return false when date1 is not the same as date2', function() {
			date1 = new DateUtils('2016-08-08');
			date2 = new DateUtils('2016-01-10');
			result = date1.isSame(date2);
			expect(result).toBeFalsy();
		});
	});

	describe('formatServer()', function () {
	    it('should use the server date format', function () {
            sinon.spy(DateUtils.prototype, 'format');

            DateUtils(1486469384032).formatServer();

            expect(DateUtils.prototype.format).toHaveBeenCalledOnce();
        });

        it('should return only the date if dateOnly is passed', function () {
            expect(DateUtils(1486469384032).formatServer(true)).toEqual('2017-02-07');
        });

        describe('in another language', function () {
            beforeEach(function () {
                let ar_sa__symbolMap = {
                    '1': '١',
                    '2': '٢',
                    '3': '٣',
                    '4': '٤',
                    '5': '٥',
                    '6': '٦',
                    '7': '٧',
                    '8': '٨',
                    '9': '٩',
                    '0': '٠'
                };
                DateUtils.defineLocale('ar-sa', {
                    postformat: function (string) {
                        return string.replace(/\d/g, function (match) {
                            return ar_sa__symbolMap[match];
                        }).replace(/,/g, '،');
                    },
                });
            });

            afterEach(function () {
                DateUtils.locale('ar-sa', null);
                DateUtils.locale('en');
            });

            it('should use the server locale to enforce ISO 8601', function () {
                let dateObj = DateUtils(1486469384032).locale('ar-sa');
                expect(dateObj.formatServer(true)).toEqual('2017-02-07');
            });
        });
    });

    describe('isBetween()', function() {
        var result, date1, startDate, endDate;

        it('should return true when date1 is between startDate & endDate', function() {
            date1 = '2013-01-10';
            startDate = '2013-01-01';
            endDate = '2013-01-31';
            result = DateUtils(date1).isBetween(startDate, endDate);
            expect(result).toBeTruthy();
        });

        it('should return false when date1 is not between startDate & endDate', function() {
            date1 = '2013-02-15';
            startDate = '2013-01-01';
            endDate = '2013-01-31';
            result = DateUtils(date1).isBetween(startDate, endDate);
            expect(result).toBeFalsy();
        });

        it('should return false when date1 is not between startDate & endDate without inclusive', function() {
            date1 = '2013-01-01';
            startDate = '2013-01-01';
            endDate = '2013-01-31';
            result = DateUtils(date1).isBetween(startDate, endDate, false);
            expect(result).toBeFalsy();
        });

        it('should return true when date1 is between startDate & endDate with inclusive', function() {
            date1 = '2013-01-01';
            startDate = '2013-01-01';
            endDate = '2013-01-31';
            result = DateUtils(date1).isBetween(startDate, endDate);
            expect(result).toBeTruthy();
        });
    });

    describe('duration format()', function() {
        it('should return string representation of the duration in days, hours, and minutes', function() {
            var duration = DateUtils.duration(7, 'days');
            duration.add(DateUtils.duration(7, 'hours'));
            duration.add(DateUtils.duration(7, 'minutes'));

            expect(duration.format()).toBe('7 LBL_DURATION_DAYS 7 LBL_DURATION_HOURS 7 LBL_DURATION_MINUTES');
        });

        it('should return singular nouns when referring to 1 day, hour, and minute', function() {
            var duration = DateUtils.duration(1, 'days');
            duration.add(DateUtils.duration(1, 'hours'));
            duration.add(DateUtils.duration(1, 'minutes'));

            expect(duration.format()).toBe('1 LBL_DURATION_DAY 1 LBL_DURATION_HOUR 1 LBL_DURATION_MINUTE');
        });

        it('should not return days if not specified.', function() {
            var duration = DateUtils.duration(7, 'hours');
            duration.add(DateUtils.duration(7, 'minutes'));

            expect(duration.format()).toBe('7 LBL_DURATION_HOURS 7 LBL_DURATION_MINUTES');
        });

        it('should not return hours if not specified.', function() {
            var duration = DateUtils.duration(7, 'days');
            duration.add(DateUtils.duration(7, 'minutes'));

            expect(duration.format()).toBe('7 LBL_DURATION_DAYS 7 LBL_DURATION_MINUTES');
        });

        it('should not return minutes if not specified.', function() {
            var duration = DateUtils.duration(7, 'days');
            duration.add(DateUtils.duration(7, 'hours'));

            expect(duration.format()).toBe('7 LBL_DURATION_DAYS 7 LBL_DURATION_HOURS');
        });

        it('should return the correct days even when it is more than 31.', function() {
            var duration = DateUtils.duration(62, 'days');
            duration.add(DateUtils.duration(7, 'hours'));
            duration.add(DateUtils.duration(5, 'minutes'));

            expect(duration.format()).toBe('62 LBL_DURATION_DAYS 7 LBL_DURATION_HOURS 5 LBL_DURATION_MINUTES');
        });
    });
});
