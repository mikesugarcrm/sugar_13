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

const math = require('../../../src/utils/math');
const User = require('../../../src/core/user');

describe('Utils/Math', function() {
    it('should add two numbers accurately', function() {
        var result = math.add(5.000001, 5.000001);
        expect(result).toEqual('10.000002');
        result = math.add(999999999.000001, 999999999.000001, 6);
        expect(result).toEqual('1999999998.000002');
        // accept strings too
        result = math.add('10', '10', 2);
        expect(result).toEqual('20');
        // should fix JS normal failure (would give 237.64999999999998 ≈ 237.649)
        result = math.add(123.52, 114.13, 3);
        expect(result).toEqual('237.65');
    });

    it('should subtract two numbers accurately', function() {
        var result = math.sub(5.000002, 1.000001);
        expect(result).toEqual('4.000001');
        result = math.sub(999999999.000002, 999999999.000001, 6);
        expect(result).toEqual('0.000001');
        // accept strings too
        result = math.sub('100', '50', 2);
        expect(result).toEqual('50');
        // should fix JS normal failure (would give 12.149999999999999 ≈ 12.149)
        result = math.sub(12.36, 0.21, 3);
        expect(result).toEqual('12.15');
    });

    it('should multiply two numbers accurately', function() {
        var result = math.mul(5.25, 5.25, 4);
        expect(result).toEqual('27.5625');
        result = math.mul(5.000001, 5.000001, 6);
        expect(result).toEqual('25.00001');
        result = math.mul(1000000.000001, 1000000.000001, 6);
        expect(result).toEqual('1000000000002');
        // 1.9 billion * 1.9 billion, big enough for testing
        result = math.mul(1999999999.000001, 1999999999.000001, 6);
        expect(result).toEqual('3999999996000004000.999998');
        // accept strings too
        result = math.mul('1000', '10', 2);
        expect(result).toEqual('10000');
        // should fix JS normal failure (would give 0.24499999999999997 ≈ 0.24)
        result = math.mul(0.7, 0.35, 2);
        expect(result).toEqual('0.25');
    });

    it('should divide two numbers accurately', function() {
        var result = math.div(10, 10);
        expect(result).toEqual('1');
        result = math.div(10.000001, 10.000001, 6);
        expect(result).toEqual('1');
        result = math.div(10.00001, 2, 6);
        expect(result).toEqual('5.000005');
        result = math.div(999.999999, 333.333333, 6);
        expect(result).toEqual('3');
        result = math.div(999999999.999999, 333333333.333333, 6);
        expect(result).toEqual('3');
        result = math.div(1000, 1.0, 2);
        expect(result).toEqual('1000');
        // accept strings too
        result = math.div('1000', '1', 2);
        expect(result).toEqual('1000');
        // should fix JS normal failure (would give 0.8749999999999999 ≈ 0.87)
        result = math.div(0.70, 0.80, 2);
        expect(result).toEqual('0.88');
    });

    it('should round decimals accurately', function() {
        var result = math.round(10 * 132.32, 2);
        expect(result).toEqual('1323.2');
        result = math.round(999.999998 + 0.000001, 6);
        expect(result).toEqual('999.999999');
        result = math.round(1.0 - 0.02, 2);
        expect(result).toEqual('0.98');
        // should fix JS normal failure (would give 237.64999999999998 ≈ 237.649)
        result = math.round(123.52 + 114.13, 3);
        expect(result).toEqual('237.65');
        result = math.round(40.5, 0);
        expect(result).toEqual('41');
    });

    using('isDifferentWithPrecision with precision less than 2',
        [0, 1],
        function(precision) {
            it('should calculate correctly', function() {
                User.setPreference('decimal_precision', precision);
                expect(math.isDifferentWithPrecision(100.00, 100.00)).toBeFalsy();
                expect(math.isDifferentWithPrecision(100.000001, 100.000001)).toBeFalsy();
                expect(math.isDifferentWithPrecision(100.90, 100.40)).toBeTruthy();
                expect(math.isDifferentWithPrecision(100.9, 100.04)).toBeTruthy();
                User.setPreference('decimal_precision', null);
            });
        }
    );

    using('isDifferentWithPrecision with precision greater than 1',
        [2,3,4,5,6],
        function(precision) {
            it('should calculate correctly', function() {
                User.setPreference('decimal_precision', precision);
                var mathPrecision = precision - 1;
                expect(math.isDifferentWithPrecision(100.00, 100.00)).toBeFalsy();
                expect(math.isDifferentWithPrecision(
                    (100 + (0.1 / Math.pow(10, precision))),
                    (100 + (0.1 / Math.pow(10, precision)))
                )).toBeFalsy();
                expect(math.isDifferentWithPrecision(
                    (100 + (0.1 / Math.pow(10, mathPrecision))),
                    100.00
                )).toBeTruthy();
                expect(math.isDifferentWithPrecision(
                    (100 + (0.9 / Math.pow(10, precision))),
                    100.00
                )).toBeTruthy();
                expect(math.isDifferentWithPrecision(
                    (100 + (0.1 / Math.pow(10, mathPrecision))),
                    (100 + (0.2 / Math.pow(10, mathPrecision)))
                )).toBeTruthy();
                expect(math.isDifferentWithPrecision(
                    (100 + (0.9 / Math.pow(10, precision))),
                    (100 + (0.2 / Math.pow(10, mathPrecision)))
                )).toBeTruthy();
                User.setPreference('decimal_precision', null);
            });
        }
    );

    describe('getDifference', function() {
        it('should return negative number when absolute not set', function() {
            expect(math.getDifference(99.00, 100.00)).toEqual('-1');
        });

        it('should return negative number when absolute set to false', function() {
            expect(math.getDifference(99.00, 100.00, false)).toEqual('-1');
        });

        it('should not return a negative number when absolute set to true', function() {
            expect(math.getDifference(99.00, 100.00, true)).toEqual(1.00);
        });

        it('should return a positive number when absolute not set', function() {
            expect(math.getDifference(100.00, 99.00)).toEqual('1');
        });

        it('should return a positive number when absolute set to false', function() {
            expect(math.getDifference(100.00, 99.00, false)).toEqual('1');
        });
    });

    it('should process exceptions accurately', function() {
        expect(math.add(1.00, undefined)).toEqual(1);
    });
});
