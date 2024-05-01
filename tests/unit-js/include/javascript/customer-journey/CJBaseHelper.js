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

describe('SUGAR.App.CJBaseHelper', function() {
    let app;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        sinon.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    describe('setConfig and getConfig', function() {
        using('input', [
            {},
            {
                author: 'Sugarcrm',
                license: 'Sell',
            },
        ],

        function(input) {
            it('_config variable value should match the set value', function() {
                app.CJBaseHelper.setConfig(input);
                let result = app.CJBaseHelper.getConfig();
                expect(result).toEqual(input);
            });
        });
    });

    describe('isTrue', function() {
        using('input', [
            {
                value: 1,
                result: true,
            },
            {
                value: '1',
                result: true,
            },
            {
                value: '',
                result: false,
            },
            {
                value: true,
                result: true,
            },
            {
                value: 'true',
                result: true,
            },
            {
                value: undefined,
                result: false,
            },
        ],

        function(input) {
            it('return value should match input.result', function() {
                let result = app.CJBaseHelper.isTrue(input.value);
                expect(result).toBe(input.result);
            });
        });
    });

    describe('getValueFromCache', function() {
        using('input', [
            {},
            {
                name: 'test',
                lastStateId: 'testId',
                module: 'Accounts',
                lastStateIdModule: 'Accounts',
                result: undefined,
            },
            {
                name: 'test',
                lastStateId: 'testId',
                module: 'Accounts',
                lastStateIdModule: 'Accounts',
                result: 'Test Passed!',
            },
        ],

        function(input) {
            it('return value should match input.result', function() {
                app.user.lastState.set('Accounts:testId:test', input.result);
                let result = app.CJBaseHelper.getValueFromCache(
                    input.name, input.lastStateId,
                    input.module, input.lastStateIdModule
                );
                expect(result).toEqual(input.result);
            });
        });
    });

    describe('getCJEnabledModules', function() {
        using('input', [
            {
                customer_journey: {},
                enabled_modules: [],
            },
            {
                customer_journey: {
                    enabled_modules: 'Accounts,Contacts,Leads',
                },
                enabled_modules: [
                    'Accounts', 'Contacts', 'Leads'
                ],
            },
        ],

        function(input) {
            it('should return enabled_modules as input enabled_modules', function() {
                app.config.customer_journey = input.customer_journey;

                expect(app.CJBaseHelper.getCJEnabledModules()).toEqual(input.enabled_modules);
            });
        });
    });

    describe('getCJRecordViewSettings', function() {
        using('input', [
            {
                customer_journey: {
                    enabled_modules: '',
                },
                module: 'Accounts',
                all: false,
                record_view_display_settings: '',
            },
            {
                customer_journey: {
                    enabled_modules: 'Accounts,Contacts,Leads',
                    recordview_display_settings: {
                        'Accounts': 'panel-bottom',
                        'Contacts': 'tab-first',
                    },
                },
                module: 'Accounts',
                all: true,
                record_view_display_settings: {
                    'Accounts': 'panel-bottom',
                    'Contacts': 'tab-first',
                },
            },
            {
                customer_journey: {
                    enabled_modules: 'Accounts,Contacts,Leads',
                    recordview_display_settings: {
                        'Accounts': 'panel-top',
                        'Contacts': 'tab-last',
                    },
                },
                module: 'Contacts',
                all: false,
                record_view_display_settings: 'tab-last',
            },
        ],

        function(input) {
            it('should return record_view_display_settings as input record_view_display_settings', function() {
                app.config.customer_journey = input.customer_journey;
                let result = app.CJBaseHelper.getCJRecordViewSettings(input.module, input.all);

                expect(result).toEqual(input.record_view_display_settings);
            });
        });
    });

    describe('invalidLicenseError', function() {
        beforeEach(function() {
            sinon.stub(app.alert, 'show');
            sinon.stub(app.lang, 'get').returns('Invalid license.');
        });

        it('should call alert show and should not call lang get', function() {
            app.CJBaseHelper.invalidLicenseError('invalid_license', 'License is not valid');

            expect(app.alert.show).toHaveBeenCalled();
            expect(app.lang.get).not.toHaveBeenCalled();
        });

        it('should call alert show and lang get', function() {
            app.CJBaseHelper.invalidLicenseError();

            expect(app.alert.show).toHaveBeenCalled();
            expect(app.lang.get).toHaveBeenCalled();
        });
    });

    describe('fetchActiveSmartGuideCount', function() {
        const callback = sinon.stub();

        beforeEach(function() {
            sinon.stub(app.api, 'buildURL');
            sinon.stub(app.api, 'call');
            sinon.stub(app.CJBaseHelper, 'getCJEnabledModules').returns(['Accounts']);
        });

        it('should call callback function', function() {
            app.CJBaseHelper.fetchActiveSmartGuideCount({}, {}, '', 'test-id', callback);

            expect(callback).toHaveBeenCalled();
        });

        it('should call app.api.buildURL function', function() {
            app.CJBaseHelper.fetchActiveSmartGuideCount({}, {}, 'Accounts', 'test-id', callback);

            expect(app.api.buildURL).toHaveBeenCalled();
        });

        it('should call app.api.call function', function() {
            app.CJBaseHelper.fetchActiveSmartGuideCount({}, {}, 'Accounts', 'test-id', callback);

            expect(app.api.call).toHaveBeenCalled();
        });
    });

    describe('getBatchChunk', function() {
        using('input', [
            {
                customer_journey: {},
                sugar_action_batch_chunk: 1,
            },
            {
                customer_journey: {
                    sugar_action_batch_chunk: '4',
                },
                sugar_action_batch_chunk: 4,
            },
        ],

        function(input) {
            it('should return sugar_action_batch_chunk as input sugar_action_batch_chunk', function() {
                app.config.customer_journey = input.customer_journey;

                expect(app.CJBaseHelper.getBatchChunk()).toEqual(input.sugar_action_batch_chunk);
            });
        });
    });
});
