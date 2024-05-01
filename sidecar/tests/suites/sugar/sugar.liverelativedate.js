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

describe('sugar.liverelativedate', function() {
    afterEach(function() {
        sinon.restore();
    });

    describe('plugin registration', function() {
        expect(typeof $.liverelativedate).toEqual('object');
        expect(typeof $.fn.liverelativedate).toEqual('function');
    });

    // static ("global")
    describe('$.liverelativedate', function() {
        describe('interval', function() {
            var defaultInterval;

            beforeEach(function() {
                defaultInterval = $.liverelativedate.interval();
            });

            afterEach(function() {
                $.liverelativedate.interval(defaultInterval);
            });

            describe('default', function() {
                it('should default to 1 minute', function() {
                    var updateIntervalMilliseconds = defaultInterval;
                    var updateIntervalMinutes = updateIntervalMilliseconds / 1000 / 60;
                    expect(updateIntervalMinutes).toEqual(1);
                });
            });

            describe('setting', function() {
                it('should update the interval', function() {
                    var result = $.liverelativedate.interval(3e4); // 30 seconds
                    expect(result).toBeUndefined(); // no return value for setting
                    expect($.liverelativedate.interval()).toEqual(3e4);
                });
            });
        });

        describe('pause', function() {
            it('should clear the current timeout', function() {
                var clearTimeoutStub = sinon.stub(window, 'clearTimeout');

                $.liverelativedate.pause();

                expect(clearTimeoutStub).toHaveBeenCalled();
            });
        });
    });

    // instance ("local")
    describe('$.fn.liverelativedate', function() {
        var $element;

        beforeEach(function() {
            $element = $(document.createElement('time'));
        });

        afterEach(function() {
            $element.liverelativedate('destroy');
        });


        describe('add', function() {
            // disabled because isLiveRelativeDate is broken
            xit('should add an element and then resume the updating', function() {
                var resumeStub = sinon.stub($.liverelativedate, 'resume');

                $element.liverelativedate('add');

                expect($element.liverelativedate('isLiveRelativeDate')).toBe(true);
                expect(resumeStub).toHaveBeenCalled();
            });
        });
    });
});
