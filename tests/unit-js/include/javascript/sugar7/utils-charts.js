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

describe('charts util functions', () => {
    let app;

    beforeEach(() => {
        app = SugarTest.app;
        SugarTest.loadFile('../include/javascript/sugar7', 'utils-charts', 'js', data => {
            eval(data);
        });
    });

    describe('round', () => {
        it('should round number to given precision', () => {
            expect(app.utils.charts.round(Math.PI, 5)).toEqual(3.14159);
            expect(app.utils.charts.round(Math.PI, 2)).toEqual(3.14);
            expect(app.utils.charts.round(Math.PI, 0)).toEqual(3);
        });
    });

    describe('isNumeric', () => {
        it('should check if a value is a finite number', () => {
            expect(app.utils.charts.isNumeric(123)).toEqual(true);
            expect(app.utils.charts.isNumeric('123')).toEqual(true);
            expect(app.utils.charts.isNumeric(1 / 0)).toEqual(false);
            expect(app.utils.charts.isNumeric('abc')).toEqual(false);
        });
    });

    describe('countSigFigsAfter', () => {
        it('should return the number of decimal places of a number', () => {
            expect(app.utils.charts.countSigFigsAfter(123)).toEqual(0);
            expect(app.utils.charts.countSigFigsAfter(123.345)).toEqual(3);
            expect(app.utils.charts.countSigFigsAfter(123.340)).toEqual(2);
            expect(app.utils.charts.countSigFigsAfter('123.340')).toEqual(2);
            expect(app.utils.charts.countSigFigsAfter('$123.340')).toEqual(2);
            expect(app.utils.charts.countSigFigsAfter('$123.343')).toEqual(3);
            expect(app.utils.charts.countSigFigsAfter('$123.34k')).toEqual(2);
            expect(app.utils.charts.countSigFigsAfter('$123,34k')).toEqual(2);
            expect(app.utils.charts.countSigFigsAfter('$123 34k')).toEqual(2);
        });
    });

    describe('countSigFigsBefore', () => {
        it('should return the significant digits before the decimal', () => {
            expect(app.utils.charts.countSigFigsBefore(123)).toEqual(3);
            expect(app.utils.charts.countSigFigsBefore(123.345)).toEqual(3);
            expect(app.utils.charts.countSigFigsBefore(1200.340)).toEqual(2);
        });
    });

    describe('siValue', () => {
        it('should return the exponential value of SI unit', () => {
            expect(app.utils.charts.siValue('k')).toEqual(1e3);
            expect(app.utils.charts.siValue('kilo')).toEqual(1e3);
        });
    });

    describe('siDecimal', () => {
        it('should return the thousands magnitude of number', () => {
            expect(app.utils.charts.siDecimal(10)).toEqual(1e1);
            expect(app.utils.charts.siDecimal(1000)).toEqual(1e3);
            expect(app.utils.charts.siDecimal(1234)).toEqual(1e3);
            expect(app.utils.charts.siDecimal(1000000)).toEqual(1e6);
            expect(app.utils.charts.siDecimal(1234567)).toEqual(1e6);
        });
    });

    describe('buildLocality', () => {
        it('should return a d3 locale options hashmap', () => {
            let opts = {
                'decimal': '.',
                'thousands': ',',
                'grouping': [3],
                'currency': ['$', ''],
                'precision': 2,
                'periods': ['AM', 'PM'],
                'days': ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'shortDays': ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                'months': [
                    'January', 'February', 'March', 'April', 'May', 'June', 'July',
                    'August', 'September', 'October', 'November', 'December'
                ],
                'shortMonths': ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'date': '%b %-d, %Y', //defines %x
                'time': '%-I:%M:%S %p', //defines %X
                'dateTime': '%B %-d, %Y at %X GMT%Z', //defines %c
                // Custom patterns
                'full': '%A, %c',
                'long': '%c',
                'medium': '%x, %X',
                'short': '%-m/%-d/%y, %-I:%M %p',
                'yMMMEd': '%a, %x',
                'yMEd': '%a, %-m/%-d/%Y',
                'yMMMMd': '%B %-d, %Y',
                'yMMMd': '%x',
                'yMd': '%-m/%-d/%Y',
                'yMMMM': '%B %Y',
                'yMMM': '%b %Y',
                'MMMd': '%b %-d',
                'MMMM': '%B',
                'MMM': '%b',
                'y': '%Y'
            };

            let locale = app.utils.charts.buildLocality();
            expect(locale).toEqual(opts);

            locale = app.utils.charts.buildLocality({
                'decimal': ',',
            });
            expect(locale.decimal).toEqual(',');
        });
    });

    describe('numberFormatSI', () => {
        it('should format number in SI units', () => {
            const fmtr = app.utils.charts.numberFormatSI;
            let locale = {
                'decimal': '.',
                'thousands': ',',
                'grouping': [3],
                'currency': ['$', ''],
                'dateTime': '%B %-d, %Y at %X %p GMT%Z', //%c
                'date': '%b %-d, %Y', //%x
                'time': '%-I:%M:%S', //%X
                'periods': ['AM', 'PM'],
                'days': ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'shortDays': ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                'months': [
                    'January', 'February', 'March', 'April', 'May', 'June', 'July',
                    'August', 'September', 'October', 'November', 'December'
                ],
                'shortMonths': ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                // Custom patterns
                'full': '%A, %c',
                'long': '%c',
                'medium': '%x, %X %p',
                'short': '%-m/%-d/%y, %-I:%M %p',
                'yMMMEd': '%a, %x',
                'yMEd': '%a, %-m/%-d/%Y',
                'yMMMMd': '%B %-d, %Y',
                'yMMMd': '%x',
                'yMd': '%-m/%-d/%Y',
                'yMMMM': '%B %Y',
                'yMMM': '%b %Y',
                'MMMd': '%b %-d',
                'MMMM': '%B',
                'MMM': '%b',
                'y': '%Y'
            };
            let currency = false;
            let precision = 0;
            expect(fmtr(1, precision, currency, locale)).toEqual('1');
            expect(fmtr(1, 2, true, locale)).toEqual('$1');
            expect(fmtr(1, 2, false, locale)).toEqual('1');
            expect(fmtr(10, 2, false, locale)).toEqual('10');
            expect(fmtr(100, 2, false, locale)).toEqual('100');
            expect(fmtr(100.23, 0, false, locale)).toEqual('100');
            expect(fmtr(100.23, 1, false, locale)).toEqual('100.2');
            expect(fmtr(100.23, 2, false, locale)).toEqual('100.23');
            expect(fmtr(1000, 2, false, locale)).toEqual('1k');
            expect(fmtr(10000, 2, false, locale)).toEqual('10k');
            expect(fmtr(100000, 2, false, locale)).toEqual('100k');
            expect(fmtr(1000000, 2, false, locale)).toEqual('1M');
            expect(fmtr(1000000, 0, false, locale)).toEqual('1M');
            expect(fmtr(100, 2, true, locale)).toEqual('$100');
            expect(fmtr(100.23, 1, true, locale)).toEqual('$100.20');
            expect(fmtr(100.23, 0, true, locale)).toEqual('$100');
            expect(fmtr('asdf', 0, true, locale)).toEqual('asdf');
            expect(fmtr(0.5, 0, true, locale)).toEqual('$1');
            expect(fmtr(0.5, 0, false, locale)).toEqual('1');
            expect(fmtr(0.5, 1, true, locale)).toEqual('$0.50');
            expect(fmtr(0.559, 2, false, locale)).toEqual('0.56');
            expect(fmtr(0.49, 0, false, locale)).toEqual('500m');
            expect(fmtr(100)).toEqual('100');
        });
    });

    describe('numberFormatSIFixed', () => {
        it('should format number in SI units to a fixed position', () => {
            const fmtr = app.utils.charts.numberFormatSIFixed;
            let locale = {
                'decimal': '.',
                'thousands': ',',
                'grouping': [3],
                'currency': ['$', ''],
                'dateTime': '%B %-d, %Y at %X %p GMT%Z', //%c
                'date': '%b %-d, %Y', //%x
                'time': '%-I:%M:%S', //%X
                'periods': ['AM', 'PM'],
                'days': ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'shortDays': ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                'months': [
                    'January', 'February', 'March', 'April', 'May', 'June', 'July',
                    'August', 'September', 'October', 'November', 'December'
                ],
                'shortMonths': ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                // Custom patterns
                'full': '%A, %c',
                'long': '%c',
                'medium': '%x, %X %p',
                'short': '%-m/%-d/%y, %-I:%M %p',
                'yMMMEd': '%a, %x',
                'yMEd': '%a, %-m/%-d/%Y',
                'yMMMMd': '%B %-d, %Y',
                'yMMMd': '%x',
                'yMd': '%-m/%-d/%Y',
                'yMMMM': '%B %Y',
                'yMMM': '%b %Y',
                'MMMd': '%b %-d',
                'MMMM': '%B',
                'MMM': '%b',
                'y': '%Y'
            };
            let currency = false;
            let precision = 0;
            expect(fmtr(1, precision, currency, locale)).toEqual('1');
            expect(fmtr(1, 2, true, locale, 0)).toEqual('$1.00');
            expect(fmtr(1, 2, false, locale)).toEqual('1.00');
            expect(fmtr(10, 2, false, locale)).toEqual('10.00');
            expect(fmtr(100, 2, false, locale)).toEqual('100.00');
            expect(fmtr(1000, 2, false, locale, 'k')).toEqual('1.00k');
            expect(fmtr(10000, 2, false, locale, 1000)).toEqual('10.00k');
            expect(fmtr(100000, 2, false, locale, 'kilo')).toEqual('100.00k');
            expect(fmtr(1000000, 2, false, locale, 'M')).toEqual('1.00M');
            expect(fmtr(1000000, 0, false, locale, 'M')).toEqual('1M');
            expect(fmtr(100, 2, true, locale)).toEqual('$100.00');
            expect(fmtr(100.24, 0, true, locale)).toEqual('$100');
            expect(fmtr('asdf', 0, true, locale)).toEqual('asdf');
            expect(fmtr(100)).toEqual('100');
            expect(fmtr(100, null, true)).toEqual('$100.00');
        });
    });

    describe('numberFormat', () => {
        it('should round a format number with given precision, currency, and locale', () => {
            const fmtr = app.utils.charts.numberFormat;
            expect(fmtr(Math.PI, 5, true)).toEqual('$3.14159');
            expect(fmtr(Math.PI, 4, true)).toEqual('$3.1416');
            expect(fmtr(Math.PI, 3, false)).toEqual('3.142');
            expect(fmtr(Math.PI, 2)).toEqual('3.14');
            expect(fmtr(0, 2)).toEqual('0');
            expect(fmtr(123.450, 2)).toEqual('123.45');
            expect(fmtr(123.450, 3)).toEqual('123.45');

            let locale = app.utils.charts.buildLocality({
                'decimal': ',',
                'thousands': ' ',
            });
            expect(fmtr(Math.PI * 1000, 1, true, locale)).toEqual('$3 141,6');
        });
    });

    describe('numberFormatFixed', () => {
        it('should format number with given precision, currency, and locale', () => {
            const fmtr = app.utils.charts.numberFormatFixed;
            expect(fmtr(Math.PI, 5, true)).toEqual('$3.14159');
            expect(fmtr(Math.PI, 4, true)).toEqual('$3.1416');
            expect(fmtr(Math.PI, 3, false)).toEqual('3.142');
            expect(fmtr(Math.PI, 2)).toEqual('3.14');
            expect(fmtr('asdf', 2)).toEqual('asdf');
            expect(fmtr(0, 2)).toEqual('0.00');
            expect(fmtr(Math.PI, null, true)).toEqual('$3.14');
            expect(fmtr(Math.PI, null, false)).toEqual('3');

            let locale = app.utils.charts.buildLocality({
                'decimal': ',',
                'thousands': ' ',
            });
            expect(fmtr(Math.PI * 1000, 1, true, locale)).toEqual('$3 141,6');
        });
    });

    describe('numberFormatPercent', () => {
        it('should format number as a percentage with given precision', () => {
            const fmtr = app.utils.charts.numberFormatPercent;
            expect(fmtr(1, 1)).toEqual('100%');
            expect(fmtr(1, 0)).toEqual('100%');
            expect(fmtr(0, 1)).toEqual('0%');
            expect(fmtr(1, 2)).toEqual('50%');

            let locale = app.utils.charts.buildLocality({
                'decimal': ',',
                'thousands': ' ',
                'precision': 3
            });
            expect(fmtr(Math.PI, 100, locale)).toEqual('3,142%');
        });
    });
});
