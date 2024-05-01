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

const User = require('../../../src/core/user');
const Language = require('../../../src/core/language');

describe('Core/User', function() {
    beforeEach(function() {
        this.app = SUGAR.App;
        this.sandbox = sinon.createSandbox();
    });

    afterEach(function() {
        this.sandbox.restore();
    });

    it("should be able to get, set, and unset attributes", function() {
        User.set('foo', 'foo value');
        User.set('bar', 'bar value');
        expect(User.get('foo')).toEqual('foo value');
        expect(User.get('bar')).toEqual('bar value');

        User.unset('bar');
        expect(User.get('bar')).toBeUndefined();
    });

    it("should not nuke old user app settings but should reset server settings", function() {
        User.set("non-server-setting", "foo");

        User.set({
            id: '1',
            full_name: 'Administrator 2'
        });

        expect(User.get('id')).toEqual('1');
        expect(User.get('full_name')).toEqual('Administrator 2');
        expect(User.get('non-server-setting')).toEqual('foo');
    });

    it("should reset and clear user on logout if clear flag", function() {
        this.app.events.trigger("app:logout", true);
        expect(User.get('id')).toBeUndefined();
    });

    it("should load user and verify the language", function() {
        var spy = this.sandbox.spy(User, 'set');
        var stub = this.sandbox.stub(Language, 'setLanguage');
        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/me.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(fixtures.api["/rest/v10/me"].GET.response)]);

        Language.setCurrentLanguage('en_us');
        User.load();
        SugarTest.server.respond();
        expect(spy).toHaveBeenCalled();
        expect(stub).not.toHaveBeenCalled();
        //Set current loaded language to fr_FR
        //The fixture returns en_us as the user preferred language, so setLanguage should have been called
        User.load();
        Language.setCurrentLanguage('fr_FR');
        SugarTest.server.respond();
        expect(stub).toHaveBeenCalled();
    });

    it('should load user last state as well when loading user', function() {
        let spy = this.sandbox.spy(User.lastState, 'load');

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith('GET', /.*\/rest\/v10\/me.*/, [
            200,
            {
                'Content-Type': 'application/json',
            },
            JSON.stringify(fixtures.api['/rest/v10/me'].GET.response),
        ]);

        User.load();
        SugarTest.server.respond();

        expect(spy).toHaveBeenCalled();
    });

    it("should reset itself with new data", function() {
        var newData = {
            "current_user": {
                "id": "2",
                "full_name": "Vasia"
              }
        };

        User.set(newData.current_user);

        expect(User.get('id')).toEqual('2');
        expect(User.get('full_name')).toEqual('Vasia');
        expect(User.get('user_name')).toBeUndefined();
        expect(User.getPreference('timezone')).toBeUndefined();
        expect(User.getPreference('datepref')).toBeUndefined();
        expect(User.getPreference('timepref')).toBeUndefined();
    });

    /**
     * @see app/app.js language process
     */
    it("should update lang if server returns a different language than current language", function() {
        var clock = sinon.useFakeTimers();
        var callbackSpy = sinon.spy();
        var isAuthenticatedStub = sinon.stub(this.app.api, "isAuthenticated").callsFake(function() { return true; });
        var ajaxSpy = sinon.spy($, 'ajax');
        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("PUT", /.*\/rest\/v10\/me.*/,
            [200, {"Content-Type": "application/json"},
                JSON.stringify({})]);
        User.updateLanguage('en_us', callbackSpy);
        SugarTest.server.respond();
        clock.tick(50);
        expect(User.getPreference('language')).toEqual('en_us');
        expect(this.app.cache.get("lang")).toEqual("en_us");
        expect(ajaxSpy).toHaveBeenCalledOnce();
        ajaxSpy.restore();
        isAuthenticatedStub.restore();
        clock.restore();
    });

    it("should not update lang if language update fails", function() {
        var clock = sinon.useFakeTimers();
        var callbackSpy = sinon.spy();
        var isAuthenticatedStub = sinon.stub(this.app.api, "isAuthenticated").callsFake(function() { return true; });
        var ajaxSpy = sinon.spy($, 'ajax');

        let errStub = sinon.stub(console, 'error');

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("PUT", /.*\/rest\/v10\/me.*/,
            [500, {"Content-Type": "application/json"},
                JSON.stringify({})]);
        User.updateLanguage('en_us', callbackSpy);
        SugarTest.server.respond();
        clock.tick(50);
        expect(this.app.cache.get("lang")).toBeUndefined();
        expect(ajaxSpy).toHaveBeenCalledOnce();
        ajaxSpy.restore();
        isAuthenticatedStub.restore();
        clock.restore();

        errStub.restore();
    });

    it("should update user's profile", function() {
        var clock = sinon.useFakeTimers();
        var callbackSpy = sinon.spy();
        var payload = {
            first_name: "Johnny",
            last_name: "Administrator",
            email: [
                {
                    email_address: "johnny@bgoode.com",
                    primary_address: "1",
                    hasAnchor: true,
                    flagLabel: "(Primary)"
                }
            ],
            phone_work: "213-555-1212"
        };
        var isAuthenticatedStub = sinon.stub(this.app.api, "isAuthenticated").callsFake(function() { return true; });
        var ajaxSpy = sinon.spy($, 'ajax');
        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("PUT", /.*\/rest\/v10\/me.*/,
            [200, {"Content-Type": "application/json"},
                JSON.stringify({})]);
        User.updateProfile(payload, callbackSpy);
        SugarTest.server.respond();
        clock.tick(50);
        expect(callbackSpy).toHaveBeenCalledOnce();
        expect(isAuthenticatedStub).toHaveBeenCalledOnce();
        expect(ajaxSpy).toHaveBeenCalledOnce();
        ajaxSpy.restore();
        isAuthenticatedStub.restore();
        clock.restore();
    });

    it('should be able to update lang without updating the user', function() {
        var spy = this.sandbox.spy(this.app.api, 'me');

        expect(User.getPreference('language')).toEqual('en_us');
        expect(spy).not.toHaveBeenCalledOnce();
    });

    describe('syncTimezone', function() {
        using('different timzone values', [
            {
                timezone: 'America/Denver',
                getTimezoneOffset: 420,
                string: 'Tue Jan 10 2023 17:01:18 GMT-0700 (Mountain Standard Time)'
            },
            {
                timezone: 'America/New_York',
                getTimezoneOffset: 300,
                string: 'Tue Jan 10 2023 19:04:33 GMT-0500 (Eastern Standard Time)'
            },
            {
                timezone: 'Europe/London',
                getTimezoneOffset: 0,
                string: 'Wed Jan 11 2023 00:05:30 GMT+0000 (Greenwich Mean Time)'
            },
            {
                timezone: 'Asia/Tokyo',
                getTimezoneOffset: -540,
                string: 'Wed Jan 11 2023 09:06:11 GMT+0900 (Japan Standard Time)'
            },
            {
                timezone: 'Asia/Calcutta',
                getTimezoneOffset: -330,
                string: 'Wed Jan 11 2023 05:36:43 GMT+0530 (India Standard Time)'
            }
        ], function(value) {
            it('should update timezone if user\'s and saved timezone are different', function() {
                this.sandbox.stub(User, 'updatePreferences');
                this.sandbox.stub(Date.prototype, 'getTimezoneOffset').returns(value.getTimezoneOffset);
                this.sandbox.stub(Date.prototype, 'toString').returns(value.string);
                let timezone = value.timezone;
                this.sandbox.stub(Intl, 'DateTimeFormat').callsFake(function() {
                    return {
                        resolvedOptions: function() {
                            return {
                                timeZone: timezone
                            }
                        }
                    }
                });

                const pref = {
                    timezone: 'America/Chicago',
                    tz_offset: '-0600',
                    tz_offset_sec: '-21600',
                };

                _.each(pref, (val, key) => {
                    User.setPreference(key, val);
                });

                User.syncTimezone();
                expect(User.updatePreferences).toHaveBeenCalledOnce();
            });
        });
    });

    describe('getCurrency()', function() {
        var currencyDefaults;
        beforeEach(function() {
            currencyDefaults = {
                currency_id: '-98',
                currency_iso: 'TST',
                currency_name: 'TestCurrency',
                currency_rate: 1,
                currency_show_preferred: false,
                currency_create_in_preferred: true,
                currency_symbol: 'T'
            };
        });

        it('should return the user preferred currency_id', function() {
            User.setPreference('currency_id', currencyDefaults.currency_id);
            expect(User.getCurrency().currency_id).toBe(currencyDefaults.currency_id);
        });

        it('should return the user preferred currency_iso', function() {
            User.setPreference('currency_iso', currencyDefaults.currency_iso);
            expect(User.getCurrency().currency_iso).toBe(currencyDefaults.currency_iso);
        });

        it('should return the user preferred currency_name', function() {
            User.setPreference('currency_name', currencyDefaults.currency_name);
            expect(User.getCurrency().currency_name).toBe(currencyDefaults.currency_name);
        });

        it('should return the user preferred currency_rate', function() {
            User.setPreference('currency_rate', currencyDefaults.currency_rate);
            expect(User.getCurrency().currency_rate).toBe(currencyDefaults.currency_rate);
        });

        it('should return the user preferred currency_show_preferred', function() {
            User.setPreference('currency_show_preferred', currencyDefaults.currency_show_preferred);
            expect(User.getCurrency().currency_show_preferred).toBe(currencyDefaults.currency_show_preferred);
        });

        it('should return the user preferred currency_create_in_preferred', function() {
            User.setPreference('currency_create_in_preferred', currencyDefaults.currency_create_in_preferred);
            expect(User.getCurrency().currency_create_in_preferred).toBe(currencyDefaults.currency_create_in_preferred);
        });

        it('should default user preferred currency_create_in_preferred to false if not set', function() {
            User.setPreference('currency_create_in_preferred', undefined);
            expect(User.getCurrency().currency_create_in_preferred).toBe(false);
        });

        it('should return the user preferred currency_symbol', function() {
            User.setPreference('currency_symbol', currencyDefaults.currency_symbol);
            expect(User.getCurrency().currency_symbol).toBe(currencyDefaults.currency_symbol);
        });
    });

    describe('Load last state', function() {
        it('should set the cache values for each stored key value pair', function() {
            let spy = this.sandbox.spy(User.lastState, 'set');

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('GET', /.*\/rest\/v10\/me\/last_states.*/, [
                200,
                {
                    'Content-Type': 'application/json'
                },
                JSON.stringify(fixtures.api['/rest/v10/me/last_states'].GET.response),
            ]);

            User.lastState.load();
            SugarTest.server.respond();

            expect(spy).toHaveBeenCalledTwice();
            expect(spy).toHaveBeenCalledWith('key1', 'value1', false);
            expect(spy).toHaveBeenCalledWith('key2', 'value2', false);
        });
    });

    describe('Get last state key', function() {
        it("should return the last state key when given a key name, the last state ID, and module via component object", function() {
            var lastStateKey = User.lastState.key('bar', {
                meta: {
                    last_state: {
                        id: 'foo'
                    }
                },
                module: 'Accounts'
            });

            expect(lastStateKey).toBe('Accounts:foo:bar');
        });

        it("should return the last state key when given a key name and the last state ID via component object", function() {
            var lastStateKey = User.lastState.key('bar', {
                meta: {
                    last_state: {
                        id: 'foo'
                    }
                }
            });

            expect(lastStateKey).toBe('foo:bar');
        });

        it("should return undefined when the last state ID has not been set via component object", function() {
            var lastStateKey = User.lastState.key('bar', {});
            expect(lastStateKey).not.toBeDefined();
        });

        it("should return the last state key when given a key name, the last state ID, and module directly", function() {
            var lastStateKey = User.lastState.buildKey('bar', 'foo', 'Accounts');
            expect(lastStateKey).toBe('Accounts:foo:bar');
        });

        it("should return the last state key when given a key name and the last state ID directly", function() {
            var lastStateKey = User.lastState.buildKey('bar', 'foo');
            expect(lastStateKey).toBe('foo:bar');
        });
    });

    describe('Register default last states', function() {
        it("should register defaults for last states when given last state ID and defaults", function() {
            var component = {
                meta: {
                    last_state: {
                        id: 'foo',
                        defaults: {
                            one: 'value_one',
                            two: 'value_two'
                        }
                    }
                },
                module: 'Accounts'
            };

            User.lastState.register(component);

            var key1 = User.lastState.key('one', component);
            var key2 = User.lastState.key('two', component);

            expect(User.lastState.defaults(key1)).toBe('value_one');
            expect(User.lastState.defaults(key2)).toBe('value_two');
        });

        it("should not register defaults last states when defaults are not given", function() {
            var component = {
                meta: {
                    last_state: {
                        id: 'foo'
                    }
                }
            };

            User.lastState.register(component);

            var key = User.lastState.key('bar', component);
            expect(User.lastState.defaults(key)).not.toBeDefined();
        });
    });

    describe('Set and get last states', function() {
        var module = 'Contacts';

        it("should get last state that was set when last state ID exists and module is specified", function() {
            var component = {
                meta: {
                    last_state: {
                        id: 'test'
                    }
                },
                module: module
            };

            var key = User.lastState.key('foo', component);

            User.lastState.set(key, 'bar');
            expect(User.lastState.get(key)).toBe('bar');
            User.lastState.set(key, '');
            expect(User.lastState.get(key)).toBe('');
            User.lastState.set(key, 0);
            expect(User.lastState.get(key)).toBe(0);
            User.lastState.set(key, false);
            expect(User.lastState.get(key)).toBe(false);
            User.lastState.set(key, null);
            expect(User.lastState.get(key)).toBe(null);

            User.lastState.remove(key);
        });

        it("should get last state that was set when last state ID exists and module is not specified", function() {
            var component = {
                meta: {
                    last_state: {
                        id: 'test'
                    }
                }
            };

            var key = User.lastState.key('foo', component);

            User.lastState.set(key, 'bar');
            expect(User.lastState.get(key)).toBe('bar');

            User.lastState.remove(key);
        });

        it("should set different values for each module", function() {
            var component1 = {
                meta: {
                    last_state: {
                        id: 'test'
                    }
                },
                module: 'Accounts'
            };

            var component2 = {
                meta: {
                    last_state: {
                        id: 'test'
                    }
                },
                module: 'Leads'
            };

            var key1 = User.lastState.key('foo', component1);
            var key2 = User.lastState.key('foo', component2);

            User.lastState.set(key1, 'one');
            User.lastState.set(key2, 'two');

            expect(User.lastState.get(key1)).toBe('one');
            expect(User.lastState.get(key2)).toBe('two');

            User.lastState.remove(key1);
            User.lastState.remove(key2);
        });

        it("should set different values for when module is and is not specified", function() {
            var component1 = {
                meta: {
                    last_state: {
                        id: 'test'
                    }
                },
                module: module
            };

            var component2 = {
                meta: {
                    last_state: {
                        id: 'test'
                    }
                }
            };

            var key1 = User.lastState.key('foo', component1);
            var key2 = User.lastState.key('foo', component2);

            User.lastState.set(key1, 'one');
            User.lastState.set(key2, 'two');

            expect(User.lastState.get(key1)).toBe('one');
            expect(User.lastState.get(key2)).toBe('two');

            User.lastState.remove(key1);
            User.lastState.remove(key2);
        });

        it("should get the default value if last state value doesn't exist in local storage", function() {
            var component = {
                meta: {
                    last_state: {
                        id: 'foo',
                        defaults: {
                            one: 'value_one',
                            two: 'value_two',
                            bool: true,
                        }
                    }
                },
                module: module
            };

            User.lastState.register(component);

            var key1 = User.lastState.key('one', component);
            var key2 = User.lastState.key('two', component);

            expect(User.lastState.get(key1)).toBe('value_one');
            expect(User.lastState.get(key2)).toBe('value_two');
        });

        it('should update the key stored in the DB as well by default', function() {
            let spy = this.sandbox.spy(User.lastState, '_syncDb');
            this.sandbox.stub(this.app.api, 'isAuthenticated').returns(true);

            User.lastState.set('key1', 'value1');
            expect(spy).toHaveBeenCalled();
        });

        it('should not update the key stored in the DB if told not to', function() {
            let spy = this.sandbox.spy(User.lastState, '_syncDb');
            this.sandbox.stub(this.app.api, 'isAuthenticated').returns(true);

            User.lastState.set('key1', 'value1', false);
            expect(spy).not.toHaveBeenCalled();
        });
    });

    describe('Remove last states', function() {
        it("should delete last state that was set when last state ID exists and module is specified", function() {
            var component = {
                meta: {
                    last_state: {
                        id: 'test'
                    }
                },
                module: 'Contacts'
            };

            var key = User.lastState.key('foo', component);

            expect(User.lastState.get(key)).not.toBeDefined();

            User.lastState.set(key, 'bar');
            expect(User.lastState.get(key)).toBe('bar');

            User.lastState.remove(key);
            expect(User.lastState.get(key)).not.toBeDefined();
        });

        it("should delete last state that was set when last state ID exists and module is not specified", function() {
            var component = {
                meta: {
                    last_state: {
                        id: 'test'
                    }
                }
            };

            var key = User.lastState.key('foo', component);

            expect(User.lastState.get(key)).not.toBeDefined();

            User.lastState.set(key, 'bar');
            expect(User.lastState.get(key)).toBe('bar');

            User.lastState.remove(key);
            expect(User.lastState.get(key)).not.toBeDefined();
        });
    });

    describe('Preserved lastState keys', function() {
        it('should preserve keys after cache clean', function() {
            User.lastState.set('important-key', 'important-value');
            User.lastState.set('unimportant-key', 'unimportant-value');
            User.lastState.preserve('important-key');
            this.app.cache.clean();
            expect(User.lastState.get('important-key')).toEqual('important-value');
            expect(User.lastState.get('unimportant-key')).toBeUndefined();
            User.lastState.remove('important-key');
        });
    });

    describe('update', function() {
       it('should immediately call the callback if not authenticated', function() {
           this.sandbox.stub(this.app.api, 'isAuthenticated').returns(false);
           var spy = sinon.spy();
           User.update('update', {}, spy);
           expect(spy).toHaveBeenCalled();
       });
    });

    describe('hasLicense', function() {
        using('different licenses', [
            {
                userLicenses: ['SUGAR_SELL'],
                desiredLicenses: ['SUGAR_SELL', 'SUGAR_SERVE'],
                hasAll: false,
                expected: true,
            },
            {
                userLicenses: ['SUGAR_SELL'],
                desiredLicenses: ['SUGAR_SELL', 'SUGAR_SERVE'],
                hasAll: true,
                expected: false,
            },
            {
                userLicenses: ['SUGAR_SERVE', 'SUGAR_SELL'],
                desiredLicenses: ['SUGAR_SELL', 'SUGAR_SERVE'],
                hasAll: true,
                expected: true,
            },
            {
                userLicenses: ['SUGAR_SELL', 'SUGAR_SERVE'],
                desiredLicenses: ['SUGAR_SELL'],
                hasAll: true,
                expected: true,
            },
            {
                userLicenses: ['SUGAR_SELL', 'SUGAR_SERVE'],
                desiredLicenses: 'SUGAR_SERVE',
                hasAll: true,
                expected: true,
            },
            {
                userLicenses: ['CURRENT'],
                desiredLicenses: ['SUGAR_SERVE'],
                hasAll: false,
                expected: false,
            },
            {
                userLicenses: null,
                desiredLicenses: ['SUGAR_SERVE'],
                hasAll: true,
                expected: false,
            }
        ], details => {
            it('should properly check user licenses', function() {
                this.sandbox.stub(User, 'get').callsFake(() => details.userLicenses);
                let hasLicense = User.hasLicense(details.desiredLicenses, details.hasAll);
                expect(hasLicense).toBe(details.expected);
            });
        });
    });

    describe('isSetupCompleted', function() {
        using('different user values', [
            {
                values: {
                    cookie_consent: true,
                    show_wizard: false,
                },
                expected: true,
            },
            {
                values: {
                    cookie_consent: true,
                    show_wizard: true,
                },
                expected: false,
            },
            {
                values: {
                    cookie_consent: false,
                    show_wizard: false,
                },
                expected: false,
            }
        ], details => {
            it('should properly check if the user is ready', function() {
                this.sandbox.stub(User, 'get').callsFake(key => details.values[key]);
                expect(User.isSetupCompleted()).toBe(details.expected);
            });
        });
    });
});
