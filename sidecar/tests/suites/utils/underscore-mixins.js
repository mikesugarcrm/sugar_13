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
describe('Utils/UnderscoreMixins', function() {
    describe('isEmptyValue', function() {
        using('different values',
            [
                {value: '', isEmptyValue: true},
                {value: undefined, isEmptyValue: true},
                {value: null, isEmptyValue: true},
                {value: {}, isEmptyValue: true},
                {value: [], isEmptyValue: true},

                {value: 'foo', isEmptyValue: false},
                {value: 123, isEmptyValue: false},
                {value: 123.456, isEmptyValue: false},
                {value: 0, isEmptyValue: false},
                {value: NaN, isEmptyValue: false},
                {value: true, isEmptyValue: false},
                {value: false, isEmptyValue: false},
                {value: new Date(), isEmptyValue: false},
                {value: {abc: 123}, isEmptyValue: false},
                {value: ['foo'], isEmptyValue: false}
            ], function(pair) {
                it('should return whether the value is empty', function() {
                    expect(_.isEmptyValue(pair.value)).toEqual(pair.isEmptyValue);
                });
            }
        );

    });

    describe('changed', function() {
        using('different objects', [
            {
                // empty case
                newObject: {},
                oldObject: {},
                expected: undefined
            },
            {
                // mix of new keys, nested objects, different values
                newObject: {
                    a: 1,
                    b: {create: true, edit: false},
                    c: 'test',
                    d: {a: 'b', c: {d: true}}
                },
                oldObject: {
                    a: 1,
                    b: {create: true},
                    c: undefined,
                    e: [1, 2]
                },
                expected: {
                    a: false,
                    b: true,
                    c: true,
                    d: true,
                    e: true
                }
            },
            {
                // should return the same object even if the params are flipped
                oldObject: {
                    a: 1,
                    b: {create: true, edit: false},
                    c: 'test',
                    d: {a: 'b', c: {d: true}}
                },
                newObject: {
                    a: 1,
                    b: {create: true},
                    c: undefined,
                    e: [1, 2]
                },
                expected: {
                    a: false,
                    b: true,
                    c: true,
                    d: true,
                    e: true
                }
            },
            {
                // same objects
                newObject: {priority: {create:'no', edit: 'no'}},
                oldObject: {priority: {create:'no', edit: 'no'}},
                expected: {priority: false}
            },
            {
                // new object contains nothing
                newObject: {},
                oldObject: {priority: {create:'no', edit: 'no'}},
                expected: {priority: true}
            }
        ], function(value) {
            it('should return the differences between the objects', function() {
                expect(_.changed(value.newObject, value.oldObject)).toEqual(value.expected);
            });
        });
    });
});

