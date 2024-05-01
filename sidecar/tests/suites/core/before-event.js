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

const BeforeEvent = require('../../../src/core/before-event');

describe('Core/BeforeEvent', function () {
    beforeEach(function () {
        this.obj = {
            methodA: function () {
                if (!this.triggerBefore('eventA')) {
                    return false;
                }

                this.trigger('eventA');
            },

            namespacedMethodA: function() {
                if (!this.triggerBefore('namespace:eventA')) {
                    return false;
                }

                this.trigger('namespace:eventA');
            },

            methodB: function () {
                if (!this.triggerBefore('eventB')) {
                    return false;
                }

                this.trigger('eventB');
            },

            methodC: function () {},

            trigger: function () {},
        };
        _.extend(this.obj, BeforeEvent);
    });

    afterEach(function () {
        sinon.restore();
        this.obj = null;
    });

    describe('before', function () {
        it('should bind a before callback function', function () {
            var cb = sinon.spy();
            this.obj.before('eventA', cb);
            this.obj.methodA();

            expect(cb).toHaveBeenCalled();
        });

        it('should bind a before callback function for multiple events', function () {
            var cb = sinon.spy();
            this.obj.before('eventA eventB', cb);

            this.obj.methodA();
            expect(cb).toHaveBeenCalledOnce();

            this.obj.methodB();
            expect(cb).toHaveBeenCalledTwice();
        });

        it('should accept an event map to bind before callbacks', function () {
            var cb = sinon.spy();
            this.obj.before({
                'eventA': cb,
                'eventB': cb,
            });

            this.obj.methodA();
            expect(cb).toHaveBeenCalledOnce();

            this.obj.methodB();
            expect(cb).toHaveBeenCalledTwice();
        });

        it('should bind the before callback with a provided scope', function () {
            var context = {};
            var cb = function () {
                this.foo = 'bar';
            };

            this.obj.before('eventA', cb, context);
            this.obj.methodA();

            expect(context.foo).toEqual('bar');
        });

        it('should not call `a` when before `a` is false, even when one callback returns true', function () {
            var aSpy = sinon.spy(this.obj, 'trigger').withArgs('eventA');
            var cb1 = sinon.stub().returns(false);
            var cb2 = sinon.stub().returns(true);

            this.obj.before('eventA', cb1);
            this.obj.before('eventA', cb2);
            this.obj.methodA();

            expect(aSpy).not.toHaveBeenCalled();
            expect(cb1).toHaveBeenCalled();
            expect(cb2).toHaveBeenCalled();
        });
    });

    describe('triggerBefore', function () {
        it('should execute the before callback', function () {
            var cb = sinon.spy();

            this.obj.before('eventA', cb);
            var result = this.obj.triggerBefore('eventA');

            expect(cb).toHaveBeenCalled();
            expect(result).toBeTruthy();
        });

        it('should execute multiple before callbacks prior to multiple events', function () {
            var cb1 = sinon.stub();
            var cb2 = sinon.stub();

            this.obj.before({
                'eventA': cb1,
                'eventB': cb2,
            });
            var result = this.obj.triggerBefore('eventA eventB');

            expect(cb1).toHaveBeenCalled();
            expect(cb2).toHaveBeenCalled();
            expect(result).toBeTruthy();
        });

        it('should receive custom arguments in the before callback', function () {
            var cb = sinon.spy();

            this.obj.before('eventA', cb);
            var result = this.obj.triggerBefore('eventA', { foo: 'bar' }, { bar: 'foo' }, 0, 'eventA');

            expect(cb).toHaveBeenCalledWith({ foo: 'bar' }, { bar: 'foo' }, 0, 'eventA');
            expect(result).toBeTruthy();
        });

        it('should execute before callbacks on all events', function () {
            var cb = sinon.spy();

            this.obj.before('all', cb);
            var result = this.obj.triggerBefore('eventA eventB');

            expect(cb).toHaveBeenCalled();
            expect(result).toBeTruthy();
        });

        it('should execute before callbacks on all events, even if a callback returns false', function () {
            var cb = sinon.spy();

            this.obj.before('all', cb);
            this.obj.before('eventA', function () {
                return false;
            });

            var result = this.obj.triggerBefore('eventA eventB');

            expect(cb).toHaveBeenCalled();
            expect(result).toBeFalsy();
        });

        it('should execute before callbacks on all events, even if the `all` callback returns false', function () {
            var cb = sinon.spy();

            this.obj.before('all', function () {
                return false;
            });

            this.obj.before('eventA', cb);

            var result = this.obj.triggerBefore('eventA eventB');

            expect(cb).toHaveBeenCalled();
            expect(result).toBeFalsy();
        });
    });

    describe('offBefore', function () {
        it('should remove all callbacks for all events', function () {
            var cb = sinon.spy();
            this.obj.before('eventA eventB eventC', cb);

            this.obj.offBefore();

            this.obj.methodA();
            this.obj.methodB();
            this.obj.methodC();
            expect(cb).not.toHaveBeenCalled();
        });

        it('should remove all callbacks from a before event if none supplied', function () {
            var cb1 = sinon.stub();
            var cb2 = sinon.stub();

            this.obj.before('eventA', cb1);
            this.obj.before('eventA', cb2);
            this.obj.before('eventB', cb1);
            this.obj.before('eventB', cb2);

            this.obj.offBefore('eventA');

            // This shouldn't affect the event listeners.
            this.obj.offBefore('blah');

            this.obj.methodA();
            expect(cb1).not.toHaveBeenCalled();
            expect(cb2).not.toHaveBeenCalled();

            this.obj.methodB();
            expect(cb1).toHaveBeenCalled();
            expect(cb2).toHaveBeenCalled();
        });

        it('should remove all callbacks from a specified context', function () {
            var cb1 = sinon.stub();
            var cb2 = sinon.stub();
            var observer = {};

            this.obj.before('eventA', cb1, observer);
            this.obj.before('eventA', cb2);
            this.obj.offBefore(null, null, observer);

            this.obj.methodA();
            expect(cb1).not.toHaveBeenCalled();
            expect(cb2).toHaveBeenCalled();
        });

        it('should remove the specified callback from a before event and nothing else', function () {
            var cb1 = sinon.stub();
            var cb2 = sinon.stub();

            this.obj.before('eventA', cb1);
            this.obj.before('eventA', cb2);
            this.obj.offBefore('eventA', cb1);

            this.obj.methodA();
            expect(cb1).not.toHaveBeenCalled();
            expect(cb2).toHaveBeenCalled();
        });

        it('should not remove callbacks from a different namespace', function () {
            let cb1 = sinon.spy();
            this.obj.before('namespace:eventA', cb1);
            this.obj.offBefore('different-namespace:eventA', cb1);
            this.obj.namespacedMethodA();
            expect(cb1).toHaveBeenCalled();
        });

        it('should not do anything if no callbacks are bound', function() {
            // Make sure calling `offBefore` when `before` was not use does not
            // blow up.
            this.obj.offBefore();
            this.obj.offBefore('eventA');
        });
    });
});
