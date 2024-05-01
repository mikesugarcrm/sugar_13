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
describe('Core/Cache', function () {
    beforeEach(function () {
        this.config = {uniqueKey: 'uniqueTestKey', env: 'test', appId: 'testId'};
        this.cache = require('../../../src/core/cache.js')(this.config);
        this.sandbox = sinon.createSandbox();
    });

    afterEach(function () {
        this.cache.cutAll(true);
        this.sandbox.restore();
    });

    describe('Storing and removing values', function() {
        beforeEach(function() {
            this.cache.init();
        });

        it('should store strings', function () {
            var value = 'This is a test.';
            var key = 'testKey';
            this.cache.set(key, value);
            expect(this.cache.get(key)).toEqual(value);
        });

        it('should store objects', function () {
            var value = {foo: 'test', bar: {more: 'a'}};
            var key = 'testKey';
            this.cache.set(key, value);
            expect(this.cache.get(key)).toEqual(value);
        });

        it('should remove values', function () {
            var value = 'Hello';
            var key = 'testKey';
            this.cache.set(key, value);
            expect(this.cache.get(key)).toEqual(value);

            this.cache.cut(key);
            expect(this.cache.get(key)).toBeFalsy();
        });

        it('should provide has to determine if key exists', function () {
            var value = 'Hello';
            var key = 'testKey';
            this.cache.set(key, value);
            this.cache.cut(key);
            expect(this.cache.has(key)).toBeFalsy();
        });

        it('should remove all values', function () {
            var value = 'Hello';
            var key = 'testKey';
            var key2 = 'testKey2';
            this.cache.set(key, value);
            this.cache.set(key2, value);
            expect(this.cache.get(key)).toEqual(value);
            expect(this.cache.get(key2)).toEqual(value);

            this.cache.cutAll();
            expect(this.cache.get(key)).toBeFalsy();
            expect(this.cache.get(key2)).toBeFalsy();
        });

        it('should clean up unimportant values when clean is called', function() {
            var k1 = 'notImportant';
            var k2 = 'important';
            var callback = function(cb) {
                cb([k2]);
            };

            this.cache.on('cache:clean', callback);

            this.cache.set(k1, 'foo');
            this.cache.set(k2, 'bar');

            this.cache.clean();

            expect(this.cache.get(k1)).toBeUndefined();
            expect(this.cache.get(k2)).toEqual('bar');

            this.cache.off('cache:clean', callback);
        });

        it('should call clean when a quota error occurs', function() {
            let e = {name: 'QUOTA_EXCEEDED_ERR'};
            let spy = this.sandbox.spy(this.cache, 'clean');
            let set = this.sandbox.stub(this.cache.store, 'set').throws(e);

            expect(() => this.cache.set('foo', 'bar')).toThrow(e);
            expect(spy).toHaveBeenCalled();
        });

        it('should noop when some other error occurs', function() {
            var e = {name: 'RANDOM_ERR'},
            spy = sinon.spy(this.cache, 'clean'),
            set = sinon.stub(this.cache.store, 'set').throws(e);

            this.cache.set('foo', 'bar');
            expect(spy).not.toHaveBeenCalled();
            spy.restore();
            set.restore();
        });
    });

    describe('Handling migration from `stash.js` to `store.js`', function() {
        it('should migrate from stash to store', function() {
            let buildKey = (key) => `${this.config.env}:${this.config.appId}:${key}`;

            let storage = {
                '1:last-state:footer-tutorial:toggle-show-tutorial': {
                    actual: '\'1679-ULT-7.8.0.0\'',
                    expected: '1679-ULT-7.8.0.0',
                },
                'meta:public:hash': {
                    actual: '\'a0b1d71dd8d6e2c71955a6483931df39\'',
                    expected: 'a0b1d71dd8d6e2c71955a6483931df39',
                },
                'tutorialPrefs': {
                    actual: "{'showTooltip':true,'viewedVersion':{'recordHome':1},'skipVersion':{}}",
                    expected: { showTooltip: true, viewedVersion: { recordHome: 1 }, skipVersion: {} },
                    equivalence: true,
                },
                'noQuotes': {
                    actual: 'aaa',
                    expected: 'aaa',
                },
                'already_migrated': {
                    actual: '"1679-ULT-7.8.0.0"',
                    expected: '1679-ULT-7.8.0.0',
                },
                'boolean_case1': {
                    actual: 'true',
                    expected: true,
                },
                'boolean_case2': {
                    actual: 'false',
                    expected: false,
                },
                'boolean_case3': {
                    actual: true,
                    expected: true,
                },
                'empty_case_1': {
                    actual: '""',
                    expected: '',
                },
                'empty_case_2': {
                    actual: '\'\'',
                    expected: '',
                },
                'empty_case_3': {
                    actual: '0',
                    expected: 0,
                },
                'empty_case_4': {
                    actual: '\'0\'',
                    expected: '0',
                },
                'empty_case_5': {
                    actual: '[]',
                    expected: [],
                    equivalence: true,
                },
                'empty_case_6': {
                    actual: 'null',
                    expected: undefined,
                },
            };

            _.each(storage, function(value, key) {
                localStorage.setItem(buildKey(key), value.actual);
            });
            this.cache.init();

            _.each(storage, function(value, key) {
                if (value.equivalence) {
                    expect(this.cache.get(key)).toEqual(value.expected);
                } else {
                    expect(this.cache.get(key)).toBe(value.expected);
                }
            }, this);
        });

        it('should not migrate localStorage keys if already migrated', function() {
            let buildKey = (key) => `${this.config.env}:${this.config.appId}:${key}`;
            let storage = {
                'key-that-should-be-migrated': '\'1679-ULT-7.8.0.0\'',
            };

            _.each(storage, function(value, key) {
                localStorage.setItem(buildKey(key), value);
            });
            this.cache.set('uniqueKey', this.config.uniqueKey);
            this.cache.init();

            expect(this.cache.get('uniqueKey')).toBe(this.config.uniqueKey);
            expect(this.cache.get('key-that-should-be-migrated')).toBe('\'1679-ULT-7.8.0.0\'');
        });

        it('should clear localStorage when uniqueKey does not match', function() {
            let buildKey = (key) => `${this.config.env}:${this.config.appId}:${key}`;

            let storage = {
                'uniqueKey': 'DifferentKey',
                'data': 'to-be-cleared',
            };

            _.each(storage, function(value, key) {
                localStorage.setItem(buildKey(key), value);
            });

            this.cache.init();

            expect(this.cache.get('uniqueKey')).toBe(this.config.uniqueKey);
            expect(this.cache.has('data')).toBeFalsy();
        });
    });
});
