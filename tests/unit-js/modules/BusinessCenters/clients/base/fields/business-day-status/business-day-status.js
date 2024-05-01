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
describe('View.Fields.Base.BusinessCenters.BusinessDayStatusField', function() {
    var app;
    var field;
    var fieldType = 'business-day-status';
    var model;
    var module = 'BusinessCenters';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        app.data.declareModels();

        model = app.data.createBean('BusinessCenters');

        field = SugarTest.createField(
            'base',
            'is_open_sunday',
            fieldType,
            'detail',
            {},
            module,
            model,
            null,
            true
        );
    });

    afterEach(function() {
        model.dispose();
        field.dispose();
        model = null;
        SugarTest.testMetadata.dispose();
    });

    describe('data changes', function() {
        describe('changes to this field', function() {
            it('should show and hide the timeselect fields as appropriate', function() {
                // initial settings
                var initialSettings = {
                    sunday_open_hour: 0,
                    sunday_open_minutes: 0,
                    sunday_close_hour: 0,
                    sunday_close_minutes: 0,
                };
                initialSettings[field.name] = 1;
                field.model.set(initialSettings, {silent: true});
                var fieldKeys = [
                    'sunday_open',
                    'sunday_close'
                ];
                var getFieldStub = sinon.stub(field.view, 'getField');
                var stubs = [];
                _.each(fieldKeys, function(key) {
                    var showStub = sinon.stub();
                    var hideStub = sinon.stub();
                    var stubObj = {show: showStub, hide: hideStub};
                    getFieldStub.withArgs(key).returns(stubObj);
                    stubs.push(stubObj);
                });

                field.model.set(field.name, 0); // setting to closed
                _.each(stubs, function(stubPair) {
                    expect(stubPair.hide).toHaveBeenCalled();
                });

                field.model.set(field.name, 1); // setting to open
                _.each(stubs, function(stubPair) {
                    expect(stubPair.show).toHaveBeenCalled();
                });

                field.model.set(field.name, 2); // setting to open all day
                _.each(stubs, function(stubPair) {
                    expect(stubPair.hide).toHaveBeenCalled();
                });
            });
        });
    });

    describe('format', function() {
        using(
            'different values',
            [
                {
                    // when a raw true is sent by the server, it should default to a simple Open
                    value: true,
                    expected: 'Open'
                },
                {
                    value: false,
                    expected: 'Closed'
                },
                {
                    value: null,
                    expected: 'Closed'
                },
                {
                    value: void 0,
                    expected: 'Closed'
                },
                {
                    value: 0,
                    expected: 'Closed'
                },
                {
                    value: 1,
                    expected: 'Open'
                },
                {
                    value: 2,
                    expected: 'Open 24 Hours'
                }
            ],
            function(data) {
                it('should convert the value to a dropdown key', function() {
                    expect(field.format(data.value)).toEqual(data.expected);
                });
            }
        );
    });

    describe('unformat', function() {
        using(
            'different values',
            [
                {
                    value: 'Open',
                    expected: 1
                },
                {
                    value: 'Open 24 Hours',
                    expected: 2
                },
                {
                    value: 'Closed',
                    expected: 0
                },
                {
                    value: false,
                    expected: 0
                },
                {
                    value: void 0,
                    expected: 0
                },
                {
                    value: null,
                    expected: 0
                }
            ],
            function(data) {
                it('should unformat the data', function() {
                    expect(field.unformat(data.value)).toEqual(data.expected);
                });
            }
        );
    });

    describe('isClosedValue', function() {
        it('should consider 0 to be closed', function() {
            expect(field.isClosedValue(0)).toBe(true);
        });

        using(
            'different values',
            // "Closed" deliberately included here to make control flow clearer.
            [2, 1, 3, -1, null, void 0, true, false, 'Closed'],
            function(value) {
                it('should consider everything else to not be closed', function() {
                    expect(field.isClosedValue(value)).toBe(false);
                });
            }
        );
    });

    describe('isOpenValue', function() {
        it('should consider 1 to be open', function() {
            expect(field.isOpenValue(1)).toBe(true);
        });

        using(
            'different values',
            // "Open" deliberately included here to make control flow clearer.
            [2, 0, 3, -1, null, void 0, true, false, 'Open'],
            function(value) {
                it('should consider everything else to not be open', function() {
                    expect(field.isOpenValue(value)).toBe(false);
                });
            }
        );
    });

    describe('isOpenAllDayValue', function() {
        it('should consider 2 to be open 24 hours', function() {
            expect(field.isOpenAllDayValue(2)).toBe(true);
        });

        using(
            'different values',
            // "Open 24 Hours" deliberately included here to make control flow clearer.
            [0, 1, 3, -1, null, void 0, true, false, 'Open 24 Hours'],
            function(value) {
                it('should consider everything else to not be open 24 hours', function() {
                    expect(field.isOpenAllDayValue(value)).toBe(false);
                });
            }
        );
    });

    describe('isOpenAllDay', function() {
        using(
            'different opening and closing times',
            [
                {data: [0, 0, 23, 59], expected: true},
                {data: [0, 0, 23, 30], expected: false},
                {data: [0, 10, 23, 59], expected: false},
                {data: [0, 0, 4, 59], expected: false},
                {data: [4, 0, 23, 59], expected: false},
                {data: [0, 0, 23, 45], expected: false}, // for Open 24 Hours, it has to end on 59, not 45.
                {data: [0, 0, 0, 0], expected: false},
                {data: [null, null, null, null], expected: false},
                {data: [10, 30, 9, 45], expected: false}
            ],
            function(input) {
                it('should determine if the business center is open all day', function() {
                    field.model.set({
                        sunday_open_hour: input.data[0],
                        sunday_open_minutes: input.data[1],
                        sunday_close_hour: input.data[2],
                        sunday_close_minutes: input.data[3]
                    }, {silent: true});
                    expect(field.isOpenAllDay()).toEqual(input.expected);
                });
            }
        );
    });
});
