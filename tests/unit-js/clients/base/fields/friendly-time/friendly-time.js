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

describe('Base.Fields.FriendlyTime', function() {
    var app;

    beforeEach(function() {
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.restore();
    });

    describe('format', function() {
        using('different durations',
            [
                {
                    minutes: void 0,
                    expected: 'No data',
                },
                {
                    minutes: null,
                    expected: 'No data',
                },
                {
                    minutes: 0,
                    expected: '0 minutes',
                },
                {
                    minutes: 65,
                    expected: '1 hour 5 minutes',
                },
                {
                    minutes: (24 * 2 * 60) + 60 + 30,
                    expected: '2 days 1 hour 30 minutes',
                }
            ],
            function(data) {
                it('should format a duration', function() {
                    var field = SugarTest.createField({
                        client: 'base',
                        type: 'friendly-time',
                    });
                    var langStub = sinon.stub(app.lang, 'get');
                    var strings = {
                        'LBL_DURATION_DAY': 'day',
                        'LBL_DURATION_DAYS': 'days',
                        'LBL_DURATION_HOUR': 'hour',
                        'LBL_DURATION_HOURS': 'hours',
                        'LBL_DURATION_MINUTE': 'minute',
                        'LBL_DURATION_MINUTES': 'minutes',
                        'LBL_NO_DATA': 'No data',
                    };
                    _.each(strings, function(value, key) {
                        langStub.withArgs(key).returns(value);
                    });

                    var result = field.format(data.minutes);

                    expect(result).toEqual(data.expected);
                });
            }
        );
    });
});
